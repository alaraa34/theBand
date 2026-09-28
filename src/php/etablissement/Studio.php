<?php
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * Classe de pilotage des sudios de répétition
 *
 * @author Alara
 *******************************************************************************/
use shared\php\database\Model                          as Model;
use theBand\src\php\socle\UserTheBand                  as User;
use shared\php\classes\personalisation\Nomenclature    as Nomenclature;
use shared\php\classes\socle\Coordonnee                as Coordonnee;
use shared\php\classes\socle\Commentaire               as Commentaire;

class Studio extends Etablissement
{
    // Attributs
    public const NOM_STUDIO = 59;
    // Méthodes
    public function __construct() {
        parent::__construct(self::NOM_STUDIO);
    }
    //------------------------------------------------------------------------------------------------
    //FONCTIONS STATIQUES
    //-----------------------------------------------------------------------------------------------
    public static function liste(bool $miseEnFormeLien = false){  
       $etablissements = self::mdEtabListeGenerique(true);
       return self::miseEnFormeListe ($etablissements,$miseEnFormeLien);
    }
 
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    public static function composerListeActions(){
        if (User::userConnecte()){
            return [
                ['texte'=> 'Modifier','logoClass' =>'fa-regular fa-pen-to-square','href'=>'etablissement;editer'],
                ['texte' => 'Supprimer','logoClass' => 'bi bi-trash','modale'=>'etablissement;supprimer','message'=> 'Supprimer cet établissement ?']
                ];
        }
        else{
            return [];
        }
    }
    
    //------------------------------------------------------------------------------------------------
    //ACCES AU MODELE
    //-----------------------------------------------------------------------------------------------
    private static function mdEtabListeGenerique() {
    //Liste de etab pour utilisation liste generique
    // selection studio true ou false
        $requete = "SELECT pr.ID as id, pr.nom,
                    pa.valeurA as type_etablissemt,
                    co.adresse,co.ville,
                    cm.texte as commentaire
                    FROM ". Etablissement::TABLE ." as pr
                    INNER JOIN ". Nomenclature::TABLE ." as pa on pr.idTypeEtab = pa.ID
                    LEFT JOIN ". Coordonnee::TABLE ." as co on pr.idCoordonnee = co.ID
                    LEFT JOIN ". Commentaire::TABLE ." as cm on pr.idCommentaire = cm.ID
                    WHERE pr.idTypeEtab=?
                    ORDER BY pr.nom;";

        return Model::mdRequeteLister($requete,[self::NOM_STUDIO]);
    }
    
}