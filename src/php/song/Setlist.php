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
        foreach ($infos as $info){
            $this->details[]=new Detail($info['id']);
        }
    }
    
    public function loadPartie0(){
    //Charge la partie non affectée de la set list
        $this->loadDetailCollection($this->Model::mdSongGetRepertoireSetlistPart0());
    }
    
    public function loadFromArray(array $infos):void{
    //Charge l'instance via un tableau de données
        //Identifiant
        TbClasse::classeLoadFromArray($this,$infos);
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
                    FROM ". self::TABLE . " as se  JOIN ". Evenement::TABLE . " as ev  ON se.ID = ev.idSetlist
                    WHERE (select count(de.ID) from ". Detail::TABLE . " as de  where de.idSetlist = se.ID) > 0 
                        AND se.idNature=? AND se.id <> ?
                    ORDER BY dateheure DESC;";
        return Model::mdRequeteLister($requete,[Evenement_Concert::NATURE,$id]);
    }
    private function mdSongGetRepertoireSetlistPart0() {
    // Retourne la liste des songs du répertoire concert.non utilisés dans la set list
            $requete = "SELECT so.ID as idSong, titre as titreSong, interprete as interpreteSong , 0 as partie,1 as ordre, false as enchainement 
            FROM ". Song::TABLE . " as so
            WHERE idTypeSong = ? and so.ID not in(select de.idSong from 
                ". Detail::TABLE . " as de where de.idSetlist = ?)
            ORDER BY partie, ordre, titre";

            return Model::mdRequeteLister($requete,[Song::TYPE_CONCERT,$this->id]);
    }
   
    private function mdSetListeDetails(int $idSetlist) {
    // Retourne la liste des songs du répertoire d'une set list
    // en paramètre on peut dem nder une autre set list que celle en cours dans le cas où il faut copier une set lmist
        $requete = "SELECT de.ID as id,de.partie,de.ordre,de.enchainement,
                idSong, titre as titreSong, Interprete as interpreteSong,TonaOrigine as tonaOrigineSong, TonaScene as tonaSceneSong,
                tempo as tempoSong, idLeadChant as idUserSong
        FROM ". Detail::TABLE . " as de INNER JOIN ". Song::TABLE . " as so  ON so.ID = de.idSong
        WHERE de.idSetlist=?
        ORDER BY de.partie, ordre;";
        return Model::mdRequeteLister($requete,[$idSetlist]);
    }
  
}



