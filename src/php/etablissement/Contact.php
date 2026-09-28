<?php
declare(strict_types=1);
/*******************************************************************************
 * 
 * 
 *******************************************************************************/
namespace theBand\src\php\etablissement;

use shared\php\toolbox\Toolbox_classe       as TbClasse;
use shared\php\classes\socle\Coordonnee     as Coordonnee;
use shared\php\classes\socle\Mere           as Mere;
use shared\php\classes\socle\Commentaire    as Commentaire;

class Contact extends Mere
/**
 * cette classe n'est utilisable que sous forme de collection dans un établissement
 * pas la mise à jour car c'est contactEtablissement qui le fait appelé au niveau d'établissement
 * @author Alara
 */
{
    // Attributs
    public int $id=0;
    public string $nomPrenom="";
    public Commentaire $commentaire;
    public Coordonnee $coordonnee;
      
    // Constantes
    public const ZONES_MOUVEMENTS = ['idContact&I','actionContact&S','nomPrenomContact&S',
                                                    'idCoordonneeContact&I', 'telCoordonneeContact&S','mailCoordonneeContact&S',
                                                    'idCommentaireContact&I','texteCommentaireContact&S'];
    public const CLE_EXTERNE = "idEtablissement" ; //cle externe dans la table
    public const TABLE=  PREFIXE_BDD . "etablissement_contact";
    public const INCLUDE_CRUD = ["commentaire","coordonnee"];
    
    // Méthodes
    public function __construct(int $idContact = 0) {
        $this->commentaire = new Commentaire();
        $this->coordonnee = new Coordonnee();
        if ($idContact >0){
            $this->id = $idContact;
            TbClasse::classeLoadFromId($this);
        }
    }
    
    //------------------------------------------------------------------------------------------------
    //CRUD
    //-----------------------------------------------------------------------------------------------
    
    //------------------------------------------------------------------------------------------------
    //CHARGEMENT 
    //-----------------------------------------------------------------------------------------------
    
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    
           
    //------------------------------------------------------------------------------------------------
    //MODELE
    //-----------------------------------------------------------------------------------------------
    
   
}



