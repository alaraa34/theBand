<?php
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * Classe de pilotage des liens d'une association lien établissement
 *
 * @author Alara
 *******************************************************************************/
use shared\php\classes\lien\Lien         as Lien;
use shared\php\toolbox\Toolbox_classe    as TbClasse;
use shared\php\database\Model            as Model;
use shared\php\database\Model_utils      as ModelU;

class EtablissementLien_ass {
    // Attributs
    private int $id=0;
    public Etablissement $etablissement;
    public Lien $lien;

    // Constantes
    public const TABLE  =  PREFIXE_BDD . "etablissement_lien";
    public const CLE = "idEtablissement";
    
    // Méthodes
    public function __construct($etab = null,  $lien  = null) {
        $this->etablissement = is_null($etab) ? new Etablissement() : $etab;
        $this->lien = is_null($lien) ? new Lien(): $lien ;
        $this->id = TbClasse::ClasseGetIdAssociation($this);
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
        $retour = Model::mdInsert(self::TABLE ,classeAssociativeValeurProprietes($this));
        //récup Id commentaire
        if ($retour){$this->id = ModelU::mdGetMax(self::TABLE);}
        
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
   
   public function delete(): bool{
   //suppression lien de la table
        return Model::mdDelete(self::TABLE ,['idLien' => $this->lien->id]);
   }
    
   //Fin de la classe
}
