<?php
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * Classe de pilotage des prospects , c'est à dire une établissement pour lequel
 * une demande de concert a été lancée
 * @author Alara
 *******************************************************************************/

use shared\php\database\Model               as Model;
use theBand\src\php\socle\UserTheBand       as User;
use shared\php\toolbox\Toolbox_classe       as tbClasse;
use shared\php\classes\socle\Commentaire    as Commentaire;
use shared\php\classes\socle\Mere           as Mere;


class Prospect extends Mere
{
     // Attributs
    public int $idStatut=0;
    public int $idEtat = 0;
    public Etablissement $etablissement;
    public Commentaire $commentaire;
    public User $suiveur;
    public array $suivis=[];    //collection d'instances de suivi avec un s à la fin
    // Constantes
    public const TABLE  =  PREFIXE_BDD . "prospect"; 
    public const INCLUDE_CRUD =["commentaire"]; //Variables  concernées par opération de CRUD update delete
    public const SUJET_MDL  = "prospect";
    private const NOM_GROUPE ="STATUT_PRO"; //Cle de nomenclature
    public const ETAT_OUVERT = 1;
    private const ETAT_CLOTURE = 2;
    public const STATUT_CREE = 38;
    public const STATUT_ACTIF = 39;
    public const STATUT_REFUS = 40;
    public const STATUT_ACCORD = 50;
    public const STATUT_ABANDON = 51;
    
    // Méthodes
    public function __construct(int $idProspect = 0) {
    //Paramètre 
        $this->id = $idProspect;
        $this->idStatut = self::STATUT_CREE;
        $this->idEtat = self::ETAT_OUVERT;
        $this->commentaire = new Commentaire();
        $this->etablissement = new Etablissement();
        $this->suiveur = new User($_SESSION['LOGGED_USER']['id']);
        //pas d'instanciations de liens
        if ($idProspect > 0){tbClasse::classeLoadFromId($this,["liens","contacts"]);}
    }
    //------------------------------------------------------------------------------------------------
    //FONCTIONS STATIQUES
    //-----------------------------------------------------------------------------------------------
    public static function listeStatuts() :array {
    //liste de la nomenclauture statut
        return mdNomenclatureGetListe(self::NOM_GROUPE);
    }
    public static function listeUnique($id):array{
        $ids[0] = ['id'=>$id];
        return self::buildCollectionFromIds($ids);
    }
    public static function listeOuverts():array{
        $ids =  self::mdProspectListe(self::ETAT_OUVERT);
        return self::buildCollectionFromIds($ids);
    }
    public static function listeFermes():array{
        $ids =  self::mdProspectListe(self::ETAT_CLOTURE);
        return self::buildCollectionFromIds($ids);
    }
    
    //------------------------------------------------------------------------------------------------
    //CRUD une fois les classes mises à jour
    //-----------------------------------------------------------------------------------------------
    public function update() : bool {
    //met à jour  un prospect 
        //si pas de suivis (donc ajout) ajout d'un suivi de création
        if (count($this->suivis)===0){$this->suivis[] = new Suivi();}
        $retour = parent::update();
        
        return $retour;
    }
     
    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------
    
    //------------------------------------------------------------------------------------------------
    //TECHNIQUE
    //-----------------------------------------------------------------------------------------------
    public function getNamespaceCollection(string $nomClasseCollection):string{
    //appelé par classeLoadID pour trouver la liste des ID de la collection 
    //renvoie le namespace completde la classe de collection 
    //par défaut celui de la classe si dans le même name space
    //NAMESPACE dans la classe mere renvoi shared et pas la classe fille
        switch ($nomClasseCollection) {
            case "collection1":
                return "namespece";
            default:
                return  __NAMESPACE__ .'\\' . $nomClasseCollection; 
        }
    }
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    public function controleExistence(): bool{
        if (count($this->mdExist()) == 0){return false;}
        else{return true;}
    }
    
    public static function majEtat(int $id,int $idStatut) :bool{
    //met à jour un code atat sans passer par la classe 
        return Model::mdUpdate(Prospect::TABLE,["idStatut"=>$idStatut],"ID=".$id);
    }
    
    public function cloturer():bool{
    //cloture du prospect
        //pose d'un suiviProspect de cloture,
        $suivi = new Suivi();
        $suivi->idAction  = Suivi::ACTION_CLOTURE;
        $retour = $suivi->update($this->id);
        if(!$retour){die("Erreur clôture prospect ref003");}
        unset ($suivi);
        //passage de prospect à l'état cloturé car le suivi cloture ne ferme pas
        $this->idEtat = self::ETAT_CLOTURE;
        return $this->update();      
    }
   
    //------------------------------------------------------------------------------------------------
    //Accès au modèle
    //-----------------------------------------------------------------------------------------------
       
    private function mdExist(){
        $requete = "select id from ". self::TABLE ." where idEtablissement = ? and idEtat = ?";
        return Model::mdRequeteLister($requete,[$this->etablissement->id,self::ETAT_OUVERT]);
    }
    
    protected static function mdProspectListe(int $idEtat) :array{
    //Liste de prospect 
        $requete = "SELECT ID as id from ". self::TABLE ." where idEtat = ?";
        return Model::mdRequeteLister($requete,[$idEtat]);
    }
    
    public static function listeProspectEtablissement(int $idEtablissement):array {
    //liste des prospects d'un établissement
        $requete = "SELECT ID FROM ". self::TABLE ." WHERE idEtablissement = ?;";
        return Model::mdRequeteLister($requete, [$idEtablissement]);
    }
    
}
