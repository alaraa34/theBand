<?php
declare(strict_types=1);
/* **************************************************************************************
 * cette classe décrit un titre qui n'est pas une proposition
 * 
 ****************************************************************************/
namespace theBand\src\php\song;

use shared\php\database\Model                          as Model;
use shared\php\database\Model_utils                    as ModelU;
use shared\php\classes\socle\User                      as User;
use shared\php\classes\socle\Commentaire               as Commentaire;
use shared\php\classes\lien\Lienhtml                   as Lienhtml;
use shared\php\classes\lien\Lien                       as Lien;
use shared\php\classes\lien\TypeLien                   as TypeLien;
use shared\php\toolbox\Toolbox_classe                  as TbClasse;
use shared\php\classes\personalisation\Nomenclature    as Nomenclature;
use theBand\src\php\proposition\Proposition            as Proposition;


class Repertoire extends Song
{
    // Attributs
    public string $tonaOrigine="";
    public string $tonaScene="0";
    public int $tempo=0;
    public User $leadChant;
    public Commentaire $commentaire;
 
    // Constantes
    //types de lien admis : table <prefixe>lien_type_usage, colonne sujet
    public const string SUJET_LIEN = "SONG";
   
     
    // Méthodes
    public function __construct(int $idSong = 0) {
        parent::__construct();
        $this->id = $idSong;
        $this->commentaire = new Commentaire();
        $this->leadChant = new User();
        $this->idTypeSong = self::TYPE_CONCERT;//par défaut
        if ($idSong > 0){$this->loadFromId();}
    }
 
    //------------------------------------------------------------------------------------------------
    //CRUD
    //-----------------------------------------------------------------------------------------------
    protected function add():bool {
    //Ajoute un enregistrement 
       
        //insertion 
        $retour = Model::mdInsert(self::TABLE , TbClasse::classeValeurProprietes($this));
        //récup Id
        if ($retour){$this->id = ModelU::mdGetMax(self::TABLE);}
     
        return $retour;
    }

    public function update() : bool {
    //met à jour  un repertoire et ses liens 
        //mise à jour commentaire
        $this->commentaire->update();
        //répertoire
        if ($this->id === 0) {
            //Id = 0 c'est un ajout
           $retour = $this->add();
        } 
        else{
            //id renseigné
            $retour = Model::mdUpdate(self::TABLE, TbClasse::classeValeurProprietes($this), "id=" . $this->id);
        }
         //mise à jour des collections après car besoin de l'ID de la classe mère
        if($retour){$retour = TbClasse::classeUpdateCollection($this);}
          
        return $retour;
    }

    public function delete():bool {
        if ($this->id > 0) {
            $retour = $this->commentaire->delete();
            //suppression de la partie proposition si elle existe
            if ($retour){$retour = Proposition::deleteFromId($this);}
            //suppression song (repertoire)
            if($retour){$retour = parent::delete();}
            return $retour;
        }
    }

    //------------------------------------------------------------------------------------------------
    //STATIQUES
    //-----------------------------------------------------------------------------------------------
    public Static function getListe(array $types, bool $miseEnFormeLien = true){
    //utilisé pour liste des songs concert, test....
        $songs = self::mdSongGetListe($types);
        //tableau des liens avec ou sans mise  forme
        for($i=0;$i < count($songs); $i++){
            $songs[$i]['liens'] = SongLien_ass::getListe($songs[$i]['id']);
            if ($miseEnFormeLien) {
                //remplace les tableaux liens par leur mise en forme HTML
                $songs[$i]['liens'] = Lienhtml::mettreEnFormeCollection($songs[$i]['liens']);
            }
        }
        return $songs;
    }
    
    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------
    public function loadFromArray(array $infos):void{
    //Charge l'instance via un tableau de données
        //Identifiant
        TbClasse::classeLoadFromArray($this,$infos);
    }
    
    public function chargerLiensParMouvements(array $mvtsLiens):void{
    //Mets à jour la collection des liens sur la base des mouvements
    //ne traite pas la mise à jour physique sauf pour la suppression
        $this->liens = Lien::chargerCollectionAvecUrl($this, $mvtsLiens);
    }
    
    private function loadFromId():void{
    // chargement par identifiant
    TbClasse::classeLoadFromId($this);
    }
    
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    
    //------------------------------------------------------------------------------------------------
    //MODELE
    //-----------------------------------------------------------------------------------------------
    public function typeMP3EnDouble(): string {
    //après chargement des liens (les suppressions demandées sont déjà faites) : nom du type de MP3 qui serait
    //présent plus d'une fois sur le titre (MP3 original, MP3 joué), "" si tout va bien
        $nombres = $this->id > 0 ? self::mdNombreLiensParType($this->id, TypeLien::TYPES_MP3_UNIQUES) : [];
        foreach ($this->liens as $lien) {
            $type = (int)$lien->typeLien->id;
            if ($lien->id === 0 && in_array($type, TypeLien::TYPES_MP3_UNIQUES, true)) {
                $nombres[$type] = ($nombres[$type] ?? 0) + 1;
            }
        }
        foreach ($nombres as $type => $nombre) {
            if ($nombre > 1) {return (new TypeLien($type))->nomLong ?: (new TypeLien($type))->nom;}
        }
        return '';
    }

    private static function mdNombreLiensParType(int $idSong, array $types): array {
    //nombre de liens du titre par type (tous les liens, privés compris)
        $requete = "SELECT lie.idTypeLien, COUNT(*) as nombre FROM ". SongLien_ass::TABLE . " as sla
                    INNER JOIN ". Lien::TABLE . " as lie ON lie.id = sla.idLien
                    WHERE sla.idSong = ? AND lie.idTypeLien " . Model::mdClauseIn($types) . " GROUP BY lie.idTypeLien;";
        $retour = [];
        foreach (Model::mdRequeteLister($requete, [$idSong]) as $ligne) {$retour[(int)$ligne['idTypeLien']] = (int)$ligne['nombre'];}
        return $retour;
    }

    public static function tonaAttendue(int $idTypeLien, ?string $tonaScene): int {
    //transposition en demi-tons à appliquer aux pistes d'un MP3 : tona de scène pour un MP3 original (MP3ORI), 0 pour un MP3 joué (MP3JOU)
        return $idTypeLien === TypeLien::MP3ORI ? (int)trim((string)$tonaScene) : 0;
    }

    public static function mdLienMP3(int $idLien): array {
    //url et transposition attendue d'un lien MP3 (via le titre auquel il est rattaché)
        $requete = "SELECT lie.url, lie.idTypeLien, so.tonaScene
                    FROM ". Lien::TABLE . " as lie
                        LEFT JOIN ". SongLien_ass::TABLE . " as sla ON sla.idLien = lie.id
                        LEFT JOIN ". Song::TABLE . " as so ON so.id = sla.idSong
                    WHERE lie.id = ? LIMIT 1;";
        $ligne = Model::mdRequeteListerUnique($requete, [$idLien]);
        if (count($ligne) === 0) {return ['url'=>'', 'tona'=>0];}
        return ['url'=>(string)$ligne['url'], 'tona'=>self::tonaAttendue((int)$ligne['idTypeLien'], $ligne['tonaScene'])];
    }

    public static function mdSongGetListeAvecLiens(array $typesSong, array $typesLien): array {
    // Retourne les titres des types demandés avec leurs liens des types demandés (une ligne par lien,
    // une ligne sans lien si le titre n'en a pas), triés par titre
        $requete = "SELECT so.id as idSong, titre, interprete, tonaScene, lie.id as idLien, lie.url, lie.idTypeLien, lt.nomAffiche
                    FROM ". Song::TABLE . " as so
                        LEFT JOIN (". SongLien_ass::TABLE . " as sla
                                   INNER JOIN ". Lien::TABLE . " as lie ON sla.idLien = lie.id AND lie.idTypeLien " . Model::mdClauseIn($typesLien) . "
                                   INNER JOIN ". TypeLien::TABLE . " as lt ON lt.id = lie.idTypeLien)
                              ON sla.idSong = so.id
                    WHERE so.idTypeSong " . Model::mdClauseIn($typesSong) . "
                    ORDER BY so.titre, so.id, lie.idTypeLien, lie.id;";
        return Model::mdRequeteLister($requete);
    }

    private static function mdSongGetListe(array $typesSong) {
    // Retourne la liste des songs du répertoire en test ou concert. avec les liens mis en forme
        $requete = "SELECT so.id as id, no.valeurA as typeSong, titre, interprete,tonaOrigine,tonaScene,tempo, 
                        co.texte as texteCommentaire ,us.abrev as leadChant,idLeadChant, co.id as idCommentaire
                    FROM ". Song::TABLE . " as so INNER JOIN ". Nomenclature::TABLE . " as no ON so.idTypeSong = no.id 
                            LEFT JOIN ". Commentaire::TABLE . " as co ON so.idCommentaire = co.id
                            LEFT JOIN ". User::TABLE . " as us ON so.idLeadChant = us.id
                    WHERE so.idTypeSong " . Model::mdClauseIn($typesSong). " ORDER BY so.idTypeSong,so.Titre;";
        return Model::mdRequeteLister($requete,[]);
    }
   
    //Autres fonctions
    function mdSongDetail() {
    //Retourne les infos sur une song
        $requete= "SELECT titre,interprete,tonaOrigine,tonaScene,tempo,idTypeSong,idLeadChant as idUser, 
                    idCommentaire, co.texte as texteCommentaire 
                FROM ". Song::TABLE . " as so LEFT JOIN ". Commentaire::TABLE . " as co ON so.idCommentaire = co.id WHERE so.id= ?";
        return Model::mdRequeteListerUnique($requete,[$this->id]);
    }
    
    public function mdInfosDetail():array{
    //infos de la table de la classe
        $requete = "SELECT * FROM " . $this::TABLE. " WHERE id=?";
        return Model::mdRequeteListerUnique($requete, [$this->id]);
    }
    
}



