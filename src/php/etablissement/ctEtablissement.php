<?php
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * Controleur des établissements (domaine etablissement)
 * ctProspect.php est chargé : la création d'un prospect réaffiche la liste des prospects
 *******************************************************************************/
require_once __DIR__ . '/ctProspect.php';

use shared\php\modale\Toolbox_modal         as TbModal;
use shared\php\classes\lien\TypeLien        as TypeLien;
use shared\php\toolbox\Toolbox              as Tbx;
use shared\php\toolbox\Toolbox_liste        as TbListe;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use shared\php\classes\socle\Login          as Login;
use shared\php\bricks\Brick_table           as BkTable;
use shared\php\classes\lien\Lien            as Lien;
use shared\php\toolbox\Toolbox_upload       as TbUpload;

//------------------------------------------------------------------------------
//ETABLISSEMENTS
//------------------------------------------------------------------------------
function ctEtablissementEditer(int $id=0) {
//action editEtab, édition établissement
    $typesEtab = Etablissement::listeDesTypes();
    $typesLien = TypeLien::listePourUnSujet(Etablissement::SUJET_LIEN);
    $etab = new Etablissement($id);
    $messageBarreMenu = "Détail établissement";
    if (Login::loginControl(__NAMESPACE__)){require('tpEtablissementDetail.php');}
}

function ctEtablissementSupprimer() {
//Appelé par modale de suppression d'un établissement
    $etablissement = new Etablissement(TbModal::modalGetIdFromModal()); // issue de la modale
    $retour = $etablissement->delete();
    if($retour){
        //réaffichagede la liste
        header('Location: ' . (string)filter_input(INPUT_SERVER,'HTTP_REFERER'));
    }
    else{
        $content = Tbx::messageColorer($retour,"","Problème lors de la suppression de " . $etablissement->nom);  
        //affichage 
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
}

function ctEtablissementAjouter(){
//Ajout d'un établissement dans la saisie de concerte par la modale 
//et renvoi la liste des établissements avec en sélection celui qui vient d'être créé
    $etab = ctEtablissementCharger(true);
    $etab->update();
    $etablissements = [['identifiant'=>$etab->id,"zone"=>$etab->nom]];
    echo ( TbListe::valeursChoixListe($etablissements,selected:$etab->id)); 
}

function ctEtablissementProspecter(){
//Ajout d'un prospect pour l'établissement a partir de la liste etablissement via la modale
    $prospect = new Prospect();
    //test existant
    $prospect->etablissement->id = TbModal::modalGetIdFromModal();
    //test doublon
    if ($prospect->controleExistence()){
        $retour = false;
        $content = Tbx::messageColorer($retour,"","Prospect Non créé, probablement déjà existant et non cloturé"); 
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
    else{
        $retour = $prospect->update();
        if ($retour){
            //affichage propect unique
            $retour = ctProspectListerE($prospect->id);
        }
    }
}

function ctEtablissementCharger (bool $minimal ) : Etablissement{
    $etab = new Etablissement();
    $etab->id = TbAdressage::getPost("I","idEtab");
    $etab->nom = ucfirst(trim(TbAdressage::getPost("S","nomEtab")));
    $etab->idTypeEtab = TbAdressage::getPost("I","typeEtab");

    //mise à jour coordonnee
    $etab->coordonnee->loadFromArray(['id'=>TbAdressage::getPost("I","idCoordonnee"), 
                            'adresse'=>TbAdressage::getPost("S","adresseEtab"),
                            'ville'=>TbAdressage::getPost("S","villeEtab")]);

    //mise à jour commentaire
    $etab->commentaire->loadFromArray(['id'=>TbAdressage::getPost("I","idCommentaire"),
                                'texte'=>TbAdressage::getPost("S","texteCommentaire")]);

    //Zones associatives 
    if (!$minimal){
        $etab->chargerLiensParMouvements(TbAdressage::getValeurPostTableau(Lien::ZONES_MOUVEMENTS));

        $etab->chargerContactsParMouvements(TbAdressage::getValeurPostTableau(Contact::ZONES_MOUVEMENTS));
    }
    return $etab;
}

function ctEtablissementMAJ() {
    
    //réception des fichiers
    $retour = TbUpload::uploadReceptionLien(); //ds Toolbox_upload, import des fichiers 
    
    //Mise à jour d'un etab
    if ($retour){
        //Chargement
        $etab = ctEtablissementCharger(false);
       
        //mise à jour de la BDD
        $retour = $etab->update();

    }
    $content = Tbx::messageColorer($retour,"Etablissement " . $etab->nom . " enregistré");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctEtablissementStudioLister():void{
//liste des studios de répétition
    $messageBarreMenu = "Liste des studios de repetition";
    ctEtablissementlister(Studio::liste(true),Studio::composerListeActions(),$messageBarreMenu);
}
function ctEtablissementConcertLister():void{
//Liste des établissements pour concert
    $messageBarreMenu = "Liste des établissements pour concert";
    ctEtablissementLister(Etablissement::liste(true),Etablissement::composerListeActions(),$messageBarreMenu);
}

function ctEtablissementLister(array $infos, array $actions, string $messageBarreMenu) :void{
//Liste des etablissements 
    //traitement
    if (count($infos)==0){
       $content = Tbx::messageColorer(false,"","Aucun établissement ne correspond");
    }
    else{
        //Affichage actions si il y en a
        $script = Tbx::includeJS('utils');
        if (count($actions) > 0){
            //Colonnes à rajouter au début
            $infosColonnes[] = ['zone'=>'actions'];
        }
        //Paramétrage liste générique
        //zone = nom de la zone dans infos,
        //titre = titre à mettre , si rien c'est le nom de la zone 
        //fonction à appliquer
        $infosColonnes[] = ['zone'=> BkTable::COLONNE_NUMERO];
        $infosColonnes[] = ['zone'=>'nom','fonction'=> TbListe::FONCTION_VASN];
        $infosColonnes[] = ['zone'=>'type_etablissemt','titre'=> 'Nature', 'fonction'=> TbListe::FONCTION_VASN];
        $infosColonnes[] = ['zone'=> 'adresse','valeur'=> array("adresse","<br>","ville")];
        $infosColonnes[] = ['zone'=> 'liens','fonction'=>TbListe::FONCTION_FALC,'parametres'=>array('$valeur',false),'align'=>'MC'];
        $infosColonnes[] = ['zone'=> 'commentaire','fonction'=> TbListe::FONCTION_VASN];
        if (isset($infos[0]['nbProspects'])){
            $infosColonnes[] = ['zone'=> 'nbProspects','titre'=>'Prospect','align'=>'MC',
                'fonction'=> 'shared\php\toolbox\Toolbox_liste::afficherPastilleDansListe'];
        }
        //pour les modales
        $content = TbListe::constituerListe( $infos, $infosColonnes,$actions);
      }
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
    
}
