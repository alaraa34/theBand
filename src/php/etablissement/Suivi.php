<?php
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * Description of Suivi
 * Un suivi ne dépends que d'un propsect
 * @author Alara
 * Un prospect est un contact engagé avec un établissement pour définir une date de concert
 * Un suivi est un évènement (mail appel, rencontre....pour aboutir au concert
 * Pas besoin d'étendre mère car la table a des cles internes qui nécessite classeValeurProprietesAvecCleExterne
 *******************************************************************************/
use shared\php\database\Model as Model;
use theBand\src\php\socle\UserTheBand as User;
use shared\php\classes\personalisation\Nomenclature as Nomenclature;
use shared\php\toolbox\Toolbox_classe as tbClasse;
use shared\php\toolbox\Toolbox_date as tbDate;
use shared\php\classes\socle\Commentaire as Commentaire;
use shared\php\classes\socle\Mere_instance as MereInstance;

class Suivi extends MereInstance{
     // Attributs
    public string $date="";
    public int $idAction = 0;
    public Commentaire $commentaire;
    public User $auteur;

    // Constantes
    public const TABLE  =  PREFIXE_BDD . "prospect_suivi";
    public const INCLUDE_CRUD =["auteur"];
    public const CLE_EXTERNE = "idProspect" ; //cle externe dans la table
    private const CLE_NOM = "ACTION_PRS";
    public const ACTION_CREATION = 20;
    public const ACTION_CLOTURE = 28;
    public const SUJET_MDL = "suivi";
    
    // Méthodes
    public function __construct(int $idSuivi = 0) {
    //Paramètre 
        parent::__construct($idSuivi);
        $this->date = tbDate::dateDuJour();
        $this->idAction = self::ACTION_CREATION;
        $this->commentaire = new Commentaire();
        $this->auteur = new user($_SESSION['LOGGED_USER']['id']);
        if ($idSuivi > 0){$this->loadFromId();}
    }
    //------------------------------------------------------------------------------------------------
    //FONCTIONS STATIQUES
    //-----------------------------------------------------------------------------------------------
 
    //------------------------------------------------------------------------------------------------
    //CRUD une fois les classes mises à jour
    //-----------------------------------------------------------------------------------------------
    #[\Override]
    public function update(int $idProspect) : bool {
        $retour = parent::update($idProspect);
        if ($retour){ $retour = $this->MAJStatutProspect($idProspect);}
        return $retour;
    }
    
    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------
        
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    public static function listeAction():array{
    //retourne le liste des actions possibles sanss la création et la cloture car ils sont générés par le système
        $liste = Nomenclature::mdNomenclatureGetListe(self::CLE_NOM,"valeurA");
        $nbpostes = count($liste);
        for($i=0;$i<$nbpostes;$i++) {
            if ($liste[$i]['identifiant']==self::ACTION_CREATION  or $liste[$i]['identifiant']==self::ACTION_CLOTURE){
                unset ($liste[$i]); 
            }
        }
        return $liste;
    }
    
    private function MAJStatutProspect(int $idProspect): bool{
    //met à jour le statut du prospect
        $idEtat = (int) mdNomenclatureGetDetailFromID($this->idAction,"valeurN");
        
        if($idEtat==0){$retour = true;}
        else{$retour = prospect::majEtat($idProspect, $idEtat);}

        return $retour;
    }
    //------------------------------------------------------------------------------------------------
    //Accès au modèle
    //-----------------------------------------------------------------------------------------------
   
    function mdSuiviDetail() {
    //Détail d'un suivi existant 
        $requete = "select ps.*, co.texte as texteCommentaire
                FROM ". self::TABLE ." as ps LEFT JOIN ". Commentaire::TABLE ." as co ON co.id = ps.idCommentaire
                WHERE ps.ID=?"  ;
        return Model::mdRequeteListerUnique($requete,array($this->id));
    }
  
}