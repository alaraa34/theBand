<?php
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * Classe de pilotage des établissements 
 *
 * @author Alara
 *******************************************************************************/
use shared\php\database\Model                          as Model;
use shared\php\toolbox\Toolbox_classe                  as TbClasse;
use shared\php\classes\personalisation\Nomenclature    as Nomenclature;
use shared\php\classes\lien\Lien                       as Lien;
use shared\php\classes\lien\Lienhtml                   as Lienhtml;
use theBand\src\php\socle\UserTheBand                  as User;
use shared\php\classes\socle\Commentaire               as Commentaire;
use shared\php\classes\socle\Coordonnee                as Coordonnee;
use shared\php\classes\socle\Mere                      as Mere;
use theBand\src\php\evenement\Evenement_Concert        as Evenement_Concert;
use theBand\src\php\evenement\Evenement_Repetition     as Evenement_Repetition;

class Etablissement extends Mere
{
    // Attributs
    public string $nom="";
    public int $idTypeEtab=0;
    public Commentaire $commentaire;
    public Coordonnee $coordonnee;
    public array $liens=[];    //collection d'instances 
    public array $contacts=[];      //collection s=d'instances de contact
    // Constantes
    public const TABLE  =  PREFIXE_BDD . "etablissement";
    //types de lien admis : table <prefixe>lien_type_usage, colonne sujet
    public const string SUJET_LIEN = "ETABLISSEMENT";
    public const array RESTREINT = ["coordonnee","liens","contacts"];
    
    //------------------------------------------------------------------------------------------------
    //CONSTRUCTEUR
    //-----------------------------------------------------------------------------------------------
    public function __construct(int $idEtab = 0) {
        $this->id = $idEtab;
        $this->commentaire = new Commentaire();
        $this->coordonnee = new Coordonnee();
        if ($idEtab > 0){$this->loadFromId();}
    }
    //------------------------------------------------------------------------------------------------
    //FONCTIONS STATIQUES
    //-----------------------------------------------------------------------------------------------
    public static function ListeSelection(int $natureEvenement) {
        return self::mdEtabListeSelection($natureEvenement);
    }
  
    public static function liste(bool $miseEnFormeLien = false){
        $etablissements = self::mdEtabListeGenerique(false);
        return self::miseEnFormeListe ($etablissements,$miseEnFormeLien);
     }
   
     
     public static function listeDesTypes(){
         return Nomenclature::mdNomenclatureGetListe("TYPE_ETAB","valeurA");
     }
    
    //------------------------------------------------------------------------------------------------
    //CRUD une fois les classes mises à jour
    //-----------------------------------------------------------------------------------------------
    

    public function delete():bool {
    //Delete d'après l'Id, chargement des classes liées avant
        if ($this->id > 0) {
            //Suppression des prospects
            $tableau = Prospect::listeProspectEtablissement($this->id);
            foreach ($tableau as $id){
                $prospect = new Prospect((int)$id);
                $retour = $prospect->delete();
                unset ($prospect);
            }
            //Suppression des concerts
            $tableau = Evenement_Concert::evenementEtablissementGetListeID($this->id);
            foreach ($tableau as $id){
                $evt = new Evenement_Concert((int) $id);
                $retour = $evt->delete();
                unset ($evt);
            }
             //Suppression des répétitions
            $tableau = Evenement_Repetition::evenementEtablissementGetListeID($this->id);
            foreach ($tableau as $id){
                $evt = new Evenement_Repetition((int)$id);
                $retour = $evt->delete();
                unset ($evt);
            }
            //Suppression de l'établissement
            return parent::delete($this,[]);
        }
        else {return true;}
    }
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    public static function composerListeActions(){
        if (User::userConnecte()){
            return [['texte'=> 'Modifier','logoClass' =>'fa-regular fa-pen-to-square','href'=>'etablissement;editer'],
                    ['texte' => 'Créer un prospect','logoClass' => 'bi bi-telephone-outbound','modale'=>'etablissement;prospecter',
                        'message'=> 'Créer un prospect pour cet établissement ?'],
                    ['texte' => 'Supprimer','logoClass' => 'bi bi-trash','modale'=>'etablissement;supprimer',
                            'message'=> 'Supprimer cet établissement ?']];
        }
        else{
            return [];
        }
    }
    
    public static function miseEnFormeListe(array $etablissements,bool $miseEnFormeLien){
        for($i=0;$i < count($etablissements); $i++){
            $etablissements[$i]['liens'] = EtablissementLien_ass::getListe($etablissements[$i]['id']);
            if ($miseEnFormeLien) {
                //remplace les tableaux liens par leur mise en forme HTML
                $etablissements[$i]['liens'] = Lienhtml::mettreEnFormeCollection($etablissements[$i]['liens']);
            }
        }
        return $etablissements;
    }
    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------
    public function getNom(){
        if(strlen(trim($this->nom))==0){
            $this->nom = $this->mdInfosDetail()['nom'];
        }
        return $this->nom;
    }
    
    public function loadFromArrayAlias(array $infos,$alias=""):void{
    //Charge l'instance via un tableau de données avec alias
        //Identifiant
        TbClasse::classeLoadFromArrayAndAlias($this,$infos, $alias);
        $this->coordonnee->loadFromArrayAlias($infos,"Etablissement");
    }
    public function chargerLiensParMouvements(array $mvtsLiens):void{
    //Mets à jour la collection des liens sur la base des mouvements
    //ne traite pas la mise à jour physique sauf pour la suppression
        $this->liens = TbClasse::classeChargerCollectionParMouvements($this, $mvtsLiens, Lien::class);
    }
    public function chargerContactsParMouvements(array $mvtsContacts):void{
    //Mets à jour la collection des contacts sur la base des mouvements
    //ne traite pas la mise à jour physique sauf pour la suppression
        $this->contacts = TbClasse::classeChargerCollectionParMouvements($this, $mvtsContacts, Contact::class);
    }
    
    //------------------------------------------------------------------------------------------------
    //ACCES AU MODELE
    //-----------------------------------------------------------------------------------------------
    private static function mdEtabListeSelection(int $natureEvenement) {
    //Liste de etab pour concert ou répétition
        $requete = "SELECT pr.id as idEtablissement,pr.nom, pa.valeurA as typeEtab
            FROM ". self::TABLE ." as pr
            INNER JOIN ". Nomenclature::TABLE ." as pa on pr.idTypeEtab = pa.id
            WHERE pa.Nom ";
        if ($natureEvenement==Evenement_Concert::NATURE){
            $requete .= " <> 'STUDIO'";
        }
        else{
            $requete .= " = 'STUDIO'";
        }
        $requete .= " ORDER BY nom";
        return Model::mdRequeteLister($requete . ";");
    }
    
    private static function mdEtabListeGenerique() {
    //Liste de etab pour utilisation liste generique
    // selection studio true ou false
        $requete = "SELECT et.id as id, et.nom,
                    pa.valeurA as type_etablissemt,
                    co.adresse,co.ville,
                    cm.texte as commentaire,
                    (select count(pr.id) FROM ". Prospect::TABLE ." as pr WHERE pr.idEtablissement=et.id and pr.idEtat=?) as nbProspects
                    FROM ". self::TABLE ." as et
                    INNER JOIN ". Nomenclature::TABLE ." as pa on et.idTypeEtab = pa.id
                    LEFT JOIN ". Coordonnee::TABLE ." as co on et.idCoordonnee = co.id
                    LEFT JOIN ". Commentaire::TABLE ." as cm on et.idCommentaire = cm.id
                    WHERE et.idTypeEtab<>?
                    ORDER BY et.nom;";

        return Model::mdRequeteLister($requete,[Prospect::ETAT_OUVERT,Studio::NOM_STUDIO]);
    }
}



