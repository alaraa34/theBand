<?php
declare(strict_types=1);
/* **************************************************************************************
 * cette classe décrit un titre dans sa généralité (titre, auteur...)
 * 
 ****************************************************************************/
namespace theBand\src\php\song;

use shared\php\database\Model                       as Model;
use shared\php\database\Model_utils                 as ModelU;
use shared\php\classes\lien\Lien                    as Lien;
use shared\php\classes\lien\TypeLien                as TypeLien;
use shared\php\classes\personalisation\Nomenclature as Nomenclature;
use shared\php\classes\socle\User                   as User;
use theBand\src\php\evenement\Evenement             as Evenement;
use shared\php\toolbox\Toolbox_classe               as TbClasse;


class Song
{
    // Attributs
    public int      $id=0;
    public string   $titre="";
    public string   $interprete="";
    public int      $idTypeSong=0;
    public array    $liens=[];//collections d'instances lieng
    
    // Constantes
    public const TABLE =  PREFIXE_BDD . "song";

     //Type
    public CONST TYPE_PROPOSITION = 7;
    public const TYPE_ABANDON = 32;
    public const TYPE_CONCERT = 5;
    public const TYPE_RESERVE = 16;
    public const TYPE_TEST = 6;
    // Méthodes
    public function __construct() {
   
    }
    //------------------------------------------------------------------------------------------------
    //STATIC
    //-----------------------------------------------------------------------------------------------
    public static function listeTypeSong(bool $autresSong = false, bool $prop = false){
        
        $tableau[] = ["identifiant"=>Repertoire::TYPE_CONCERT,"zone"=>"Concert"];
        $tableau[] = ["identifiant"=>Repertoire::TYPE_TEST, "zone"=>"Test"];
        if ($autresSong){
            $tableau[]=["identifiant"=>Repertoire::TYPE_ABANDON, "zone"=>"Abandonné"];
            $tableau[]=["identifiant"=>Repertoire::TYPE_RESERVE, "zone"=> "En Réserve"];
        }
        if ($prop){
            $tableau[]= ["identifiant"=>proposition::TYPE, "zone"=> "Proposition"];
        }
        return $tableau;
    }
    public static function getListeChoix(array $types) :array{
     // Retourne la liste des songs pour choisir (test & concert)
        $requete = 'SELECT son.ID, concat(trim(titre),"-", trim(interprete)) as zone
            FROM '. self::TABLE .' as son
            WHERE son.idTypeSong ' . Model::mdClauseIn($types) .
            ' ORDER BY son.Titre;';
        
         return Model::mdRequeteLister($requete);
     }
     
    //------------------------------------------------------------------------------------------------
    //CRUD une fois les classes mises à jour
    //-----------------------------------------------------------------------------------------------
    protected function add():bool {
        $retour = Model::mdInsert( self::TABLE,  TbClasse::classeValeurProprietes($this,classeMere:true));
        //récup Id si insert fait correctemlent
        if ($retour){$this->id = ModelU::mdGetMax(self::TABLE);}
 
        return $retour;
    }

    public function update() : bool {
    //met à jour  un établissement complet
    //Lien et contacts sont des tableaux de mouvements
    
        //Mise à jour de la table classe
        if ($this->id === 0) {
            //Id = 0 c'est un ajout
           $retour = $this->add();
           if (!$retour){die ("Update ". self::TABLE ." :  Erreur add 020");}
        } 
        else{
            //id renseigné
            $retour = Model::mdUpdate( self::TABLE,  TbClasse::classeValeurProprietes($this,classeMere:true), "id=" . $this->id);
            if (!$retour){die ("Update ". self::TABLE ." Erreur mise à jour 010");}
             
        }
        //mise à jour des collections après car besoin de l'ID 
        if(! TbClasse::classeUpdateCollection($this)){$retour = false;}
         
        return $retour;
    }

    protected function delete():bool {
    //Delete d'après l'Id, chargement des classes liées avant
        $retour = true;
        if ($this->id > 0) {
            $retour = TbClasse::deleteCollectionBDD($this->liens,$this);
            //suppression de la song
            if ($retour){$retour= Model::mdDelete(self::TABLE, ['ID' => $this->id]);}
        }
        return $retour;
    }
    
  
    
    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------
    public function loadLiens(){
    //Charge tous les liens de l'instance
        $liens = SongLien_ass::getListe($this->id,TypeLien::listePourUnSujet(static::SUJET,true));
        $this->loadLiensChargement ($liens);
    }
    public function loadLiensParticulier(array $typesLien):void{
    //Charge tous les liens de l'instance
        $liens = SongLien_ass::getListe($this->id,$typesLien);
        $this->loadLiensChargement ($liens);
    }
    private function loadLiensChargement(array $liens ){
    //Charge tous les liens de l'instance
        foreach($liens as $lien){
            $this->liens[] = new Lien($lien['id']);
        }
    }
    public function getLienFromCollection(int $idTypeLien,  $defaut = "new") {
    //Recherche dans les liens de la collection un lien particulier
        $trouve = false;
        foreach($this->liens as $lien){
            if ($lien->typeLien->id == $idTypeLien){
                $trouve = true;
                break;
            }
        }
        if ($trouve){
            return $lien;}
        else{
            //retour defaut
            switch ($defaut){
                case "new" :
                    return new Lien();
                default:
                    return $defaut;
            }
        }
    }
    
    public function loadTitreSimple():void{
    //charge juste les propriétés de la classe song
        $infos = $this->mdSongSimple();
        $this->loadFromArray($infos);
    }
   
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    
    public function mettreEnFormeLiens(){
        $html="";
        foreach ($this->liens as $lien){
            $html.= $lien->genererLienHref();
        }
        return $html;
    }
    public function mdSongMajType(int $nouveauType){
        // met à jour le typt d'une song au statut de la classe
        $retour = Model::mdUpdate(self::TABLE,["idTypeSong" => $nouveauType],"ID=" . $this->id);
        return $retour ;
    }
    public function mdSetlistRemoveSong(){
    //supprime une song des setlists qui la contiennent et qui sont postérieures à la date du jour
        $requete = "DELETE FROM ". Detail::TABLE ." WHERE idSong=? AND idSetlist IN
                (SELECT stl.ID FROM ". Setlist::TABLE ." as stl INNER JOIN ". Evenement::TABLE ." as eve on eve.idSetlist = stl.ID WHERE eve.dateHeure > CURDATE())";
        return Model::mdRequeteExecuter($requete, array($this->id));
    }
    
    //------------------------------------------------------------------------------------------------
    //MODELE
    //-----------------------------------------------------------------------------------------------
    public function mdSongGetInfoLiens(array $liste =[]):array{
    //Retourne les liens dédiés à une song 
        //soit retourne tous les liens (par défaut)
        //soit ceux cités dans le tableau $liste qui contient les ID
        return mdGetInfoLiensIdentifiantsSeuls("song",$this->id,$liste);
    }
    private function mdSongSimple() {
        $requete = "SELECT ID as id, Titre as titre, Interprete as interprete, idTypeSong
                    FROM ". self::TABLE ." as son
                    WHERE son.ID=? ;";
        return Model::mdRequeteListerUnique($requete,array($this->id));
    }
    
    public static function mdSongGetListePlayer(array $typesSong, int $idTypeLien ) {
    // Retourne la requete
    $requete = "SELECT son.ID, nom.valeurA AS typeSong, titre, interprete,tonaOrigine,tonaScene,tempo,abrev as leadChant, lie.url as lienMP3
            FROM ". self::TABLE ." as son INNER JOIN ". Nomenclature::TABLE ." as nom ON son.idTypeSong = nom.ID
            LEFT JOIN ". User::TABLE ." as usr ON son.idLeadChant = usr.ID
            LEFT JOIN (". SongLien_ass::TABLE ." as sla  JOIN ". Lien::TABLE ." as lie ON sla.idLien=lie.ID) ON son.ID = sla.idSong
            WHERE  lie.idTypeLien= ?  AND son.idTypeSong ". Model::mdClauseIn($typesSong) .
            " ORDER BY son.idTypeSong,son.Titre;";
    return Model::mdRequeteLister($requete,[$idTypeLien]);
    }
    
    public static function mdSongGetListePlayerFromSetList(int $idSetList, int $idTypeLien) {
    // Retourne la liste des songs d'une set list dans l'ordre de la set list
    //Paramètre la set list et le type de mp3 à jouer
        $requete = "SELECT son.ID, titre,tonaOrigine,tonaScene,tempo,abrev as leadChant, lie.url as lienMP3
                FROM ". self::TABLE ." as son INNER JOIN ". Detail::TABLE ." as det ON son.ID = det.idSong
                LEFT JOIN ". User::TABLE ." as usr ON son.idLeadChant = usr.ID
                JOIN (". SongLien_ass::TABLE ." as sla  JOIN ". Lien::TABLE ." as lie ON sla.idLien=lie.ID) ON son.ID = sla.idSong
                WHERE lie.idTypeLien= ? and det.idSetlist = ?
                ORDER BY det.ordre;";

        return Model::mdRequeteLister($requete,[$idTypeLien,$idSetList]);
    }
    
    public static function mdSongGetListePlayerDocument(array $typesSong,int $idTypeLien) {
    // Retourne la liste des songs test et concert.
    //Tous les morceaux sont retournés avec ou sans document lié
        $requete = "SELECT titre,
                        (select lie.url  FROM ". Lien::TABLE ." as lie INNER JOIN ". SongLien_ass::TABLE ." as sla ON lie.ID = sla.idLien
                        WHERE sla.idSong = son.id and  lie.idTypeLien=?
                        and " . Lien::wherePrive(). ") as lienPDF
                FROM ". self::TABLE ." as son
                WHERE son.idTypeSong ". Model::mdClauseIn($typesSong) .
                " ORDER BY son.Titre;";

        return Model::mdRequeteLister($requete,[$idTypeLien]);
    }
    public static function mdSongGetListePlayerSetlistDocument(int $idSetList, int $idTypeLien) {
    // Retourne la liste des songs d'une set list dans l'ordre de la set list
    //Tous les morceaux sont retournés avec ou sans document lié
        $requete = "SELECT titre,
                        (select lie.url  FROM ". Lien::TABLE ." as lie INNER JOIN ". SongLien_ass::TABLE ." as sla ON lie.ID = sla.idLien
                        WHERE sla.idSong = son.id and  lie.idTypeLien=?
                        and " . lien::wherePrive(). ") as lienPDF
                FROM ". self::TABLE ." as son INNER JOIN ". Detail::TABLE ." as det ON son.ID = det.idSong
                WHERE det.idSetlist = ?
                ORDER BY det.ordre;";

        return Model::mdRequeteLister($requete,[$idTypeLien,$idSetList]);
    }
    public static function mdSongListeIDFromSetList(int $idSetList) {
        $requete = "select idSong FROM " . detail::TABLE . " WHERE idSetList=?";
        return Model::mdRequeteListerZoneUnique($requete,"idSong",[$idSetList]);
    }
  
    public static function mdSongListeIDFromType(int $idTypeSong) {
     //Retourne les id liés à un type (concert, test...)
        $requete = "select ID as idSong FROM " . self::TABLE . " WHERE idTypeSong=?";
        return Model::mdRequeteListerZoneUnique($requete,"idSong",[$idTypeSong]);
    }
    
    public function mdInfosIdCollection(string $collection, string $cle = ""):array{
    //Liste des id d'une collection. Le paramètre $collection est le nom de la classe instanciée avec son namespace
    // exemple "theBand\src\php\etablissement\suivi" ou "theBand\src\php\song\SongLien_ass
        $cleExterne = "idSong";
        if (str_ends_with($collection, "_ass")){
            //Classe associative c'est l'identifiant lié qu'il faut trouver
            $requete = "SELECT " . $cle . " FROM " . $collection::TABLE . " WHERE " . $cleExterne ."=? " ;
            return Model::mdRequeteListerZoneUnique($requete,$cle ,[$this->id]);
        }
        else{
            //Classe d'association externe, c'est l'identifiant de l'enregistrement qu'il faut trouver
            $requete = "SELECT id FROM " . $collection::TABLE . " WHERE " . $cleExterne ."=? " ;
            return Model::mdRequeteListerZoneUnique($requete,"id" ,[$this->id]);
        }
    }
    
    public static function mdSongGetListePlayerPad() {
    // Retourne la liste des songs test et concert qui ont un lien de type PAD.
    //Tous les morceaux sont retournés avec ou sans document lié
        $requete = "SELECT son.id, titre,lie.url as lienMP3, lie.description
                FROM ". SongLien_ass::TABLE ." as sla INNER JOIN ". self::TABLE ." as son on son.id=sla.idSong
                                INNER JOIN ". Lien::TABLE ." as lie ON lie.id = sla.idLien
                WHERE lie.idTypeLien=? AND son.idTypeSong ". Model::mdClauseIn([self::TYPE_CONCERT,self::TYPE_TEST]) .
                " ORDER BY son.Titre;";

        return Model::mdRequeteLister($requete,[28]);
    }
}



