<?php
declare(strict_types=1);
/* **************************************************************************************
 * cette classe décrit une setlist dans sa généralité (titre, auteur...)
 * 
 ****************************************************************************/
namespace theBand\src\php\song;

use shared\php\database\Model                      as Model;
use shared\php\toolbox\Toolbox_classe              as TbClasse;
use shared\php\classes\socle\Mere                  as Mere;
use theBand\src\php\evenement\Evenement_Concert    as Evenement_Concert;
use theBand\src\php\evenement\Evenement            as Evenement;

class Setlist extends Mere
{
    // Attributs
    public string $nomListe="";
    public int $nombreParties =3;
    public int $idNature = 0;
    public array $details=[];
    // Constantes
    public const TABLE  = PREFIXE_BDD . "setlist";
    public const INCLUDE_CRUD=[];
      
    // Méthodes
    public function __construct(int $idSetlist=0, bool $noDetails = false) {
    //$notails = true, ne charge pas laliste
        parent::__construct($idSetlist);
        if ($idSetlist>0){
            $this->loadFromID($noDetails);
        }
    }
    //------------------------------------------------------------------------------------------------
    //STATIC
    //-----------------------------------------------------------------------------------------------
    static public function listerSetlistConcert(int $id=0){
    //Liste les setlist existantes
        return self::mdListeConcert($id);
    }
    //------------------------------------------------------------------------------------------------
    //CRUD
    //-----------------------------------------------------------------------------------------------
   
    #[\Override]
    public function update() : bool {
    //met à jour  une set list
        if($this->deleteDetails()){
            return parent::update();
        }
        else{return false;}
    }

    private function deleteDetails():bool {
    //suppression MPD, la remise à 0 de laliste d'instance
        $retour = true;
        if ($this->id > 0) {
            //suppression des détails
            $retour = Model::mdRequeteExecuter("DELETE from " . Detail::TABLE . " WHERE idSetlist=" . $this->id);
            //suppression de la collection de la classe si demandée
        }
        return $retour;
    }
   
    public function updateCollectionDetail() : bool{
    //mise à jour dde la collection
        //suppression des détails du MPD seulement si une liste existante pour la remplecer
        $retour=true;
        if (count($this->details) >0){$retour = $this->deleteDetails();}
        //creation des details de la classe
        if($retour){$retour= TbClasse::classeUpdateCollection($this);}
        return $retour;
    }
    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------
   
    private function chargementDetails(int $idSetlist=0):void{
    //chargement des chansons de la setlist.
        //traduction de l'Id passé en paramètre
        if ($idSetlist===0){$id = $this->id;}
        else{$id= $idSetlist;}
        //chargement des instances détail
        $this->loadDetailCollection ($this->mdSetListeDetails($id));
    }
    
    private function loadDetailCollection(array $infos):void{
    //charge les instances de detail pour 
        foreach ($infos as $info){
            $detail = new Detail();
            $detail->loadFromArray($info);
            TbClasse::classeLoadFromArray($detail->song, $info, "Song");
            $this->details[]=$detail;
            unset($detail);
        }
    }
    
    public function loadPartie0(){
    //Charge la partie non affectée de la set list
        $this->loadDetailCollection($this->mdSongGetRepertoireSetlistPart0());
    }
    
     
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
   
    public function initialiserParAutreSetList(int $id):void{
    //initialise la setlist actuelle par le detail d'une autre set list passée en paramètre
        //Chargement de la collection 
        unset($this->details);
        $this->chargementDetails($id);
        //mise à 0 des id pour qu'ils soient en création car c'est une copie
        for($i=0;$i< count($this->details);$i++){
            $this->details[$i]->id=0;
        }
    }
   
    //------------------------------------------------------------------------------------------------
    //MODELE
    //-----------------------------------------------------------------------------------------------
    private static function mdListeConcert(int $id) :array{
    //retourne la liste des set list existantes et éventuellement différente de celle donnée en paramètre
        $requete = "SELECT se.nomListe, se.id
                    FROM ". self::TABLE . " as se  JOIN ". Evenement::TABLE . " as ev  ON se.id = ev.idSetlist
                    WHERE (select count(de.id) from ". Detail::TABLE . " as de  where de.idSetlist = se.id) > 0 
                        AND se.idNature=? AND se.id <> ?
                    ORDER BY dateheure DESC;";
        return Model::mdRequeteLister($requete,[Evenement_Concert::NATURE,$id]);
    }
    
    private function mdSongGetRepertoireSetlistPart0() {
    // Retourne la liste des songs du répertoire concert.non utilisés dans la set list
            $requete = "SELECT so.id as idSong, titre as titreSong, interprete as interpreteSong , 0 as partie,1 as ordre, false as enchainement 
            FROM ". Song::TABLE . " as so
            WHERE idTypeSong = ? and so.id not in(select de.idSong from 
                ". Detail::TABLE . " as de where de.idSetlist = ?)
            ORDER BY partie, ordre, titre";

            return Model::mdRequeteLister($requete,[Song::TYPE_CONCERT,$this->id]);
    }
   
    private function mdSetListeDetails(int $idSetlist) {
    // Retourne la liste des songs du répertoire d'une set list
    // en paramètre on peut dem nder une autre set list que celle en cours dans le cas où il faut copier une set lmist
        $requete = "SELECT de.id as id,de.partie,de.ordre,de.enchainement,
                idSong, titre as titreSong, Interprete as interpreteSong,TonaOrigine as tonaOrigineSong, TonaScene as tonaSceneSong,
                tempo as tempoSong, idLeadChant as idUserSong
        FROM ". Detail::TABLE . " as de INNER JOIN ". Song::TABLE . " as so  ON so.id = de.idSong
        WHERE de.idSetlist=?
        ORDER BY de.partie, ordre;";
        return Model::mdRequeteLister($requete,[$idSetlist]);
    }
    
    #[\Override]
    public function mdInfosIdCollection(string $collection, string $cle = ""):array{
    //surcharge de la méthode appelée par classeLoadFromId_Collections de tbClasse pour trier par partie et ordre
    // les éléments de détail
     
        //Classe d'association externe, c'est l'identifiant de l'enregistrement qu'il faut trouver
        $requete = "SELECT id FROM " . $collection::TABLE . " WHERE idSetlist=? ORDER BY partie, ordre ASC" ;
        return Model::mdRequeteListerZoneUnique($requete,"id" ,[$this->id]);
        
    }
}



