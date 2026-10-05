<?php
declare(strict_types=1);
/**
 * Description of des liens d'une song
 *
 * @author Alara
 */
namespace theBand\src\php\song;

use shared\php\classes\lien\Lien         as Lien;
use shared\php\toolbox\Toolbox_classe    as TbClasse;
use shared\php\database\Model            as Model;
use shared\php\database\Model_utils      as ModelU;

class SongLien_ass {
    // Attributs
    private int $id=0;
    public Song $song;
    public Lien $lien;

    // Constantes
    public const TABLE  =  PREFIXE_BDD . "song_lien";
    public const CLE = "idSong";

    
    // Méthodes
    public function __construct( $song = null,  $lien = null) {
        $this->song = is_null($song) ? new Song() : $song;
        $this->lien = is_null($lien) ? new Lien(): $lien ;
 
        //'Recherche si çà existe'
        if(!is_null($song)){$this->id = TbClasse::classeGetIdAssociation($this);}
    }
    //------------------------------------------------------------------------------------------------
    //STATIC
    //-----------------------------------------------------------------------------------------------
     public static function getListeId(int $identifiant, array $liste =[]){
    //retourne une liste de liens de l'objet en Id
        return Lien::mdGetInfoLiensIdentifiantsSeuls($identifiant, $liste,self::TABLE,self::CLE);
    }
    
    public static function getListe(int $identifiant, array $liste =[]){
    //paramètre identifiant de l'objet (song ou étab) et array la liste des types de lien...ou rien
        return Lien::mdGetInfoLiensIdentifiantsSeuls($identifiant, $liste,self::TABLE,self::CLE);
    }
    //------------------------------------------------------------------------------------------------
    //CRUD
    //-----------------------------------------------------------------------------------------------
    private function add():bool {
    //Ajoute une instance
        $retour = Model::mdInsert(self::TABLE , TbClasse::classeAssociativeValeurProprietes($this));
        //récup Id commentaire
        if ($retour){$this->id= ModelU::mdGetMax(self::TABLE);}
        
        return $retour;
    }

    public function update() : bool {
    //met à jour 
        //si pas d'id connu 
        if ($this->id === 0) {
           //sipas d'id détecté ajout
           $retour = $this->add();
        } 
        else{
            //id renseigné, pas besoin de modifier l'instance de lien
            $retour = true;
        }
        return $retour;
    }
   
    public function delete():bool {
    //supprime l'association 
        if ($this->id > 0) {
            return Model::mdDelete(self::TABLE, ['id' => $this->id]);
        }
    }
    //
    
   //Fin de la classe
}
