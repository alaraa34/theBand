<?php
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * Controleur du namespace établissements 
 *
 * @author Alara
 *******************************************************************************/
use shared\php\database\Model               as Model;
use shared\php\modale\Toolbox_modal         as TbModal;
use shared\php\classes\lien\TypeLien        as TypeLien;
use shared\php\classes\socle\User           as User;
use shared\php\toolbox\Toolbox              as Tbx;
use shared\php\toolbox\Toolbox_liste        as TbListe;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use shared\php\classes\socle\Login          as Login;
use shared\php\classes\socle\Commentaire    as Commentaire;
use shared\php\bricks\Brick_table           as BkTable;

//------------------------------------------------------------------------------
//PROSPECTS
//------------------------------------------------------------------------------

function ctProspectListerE(int $id=0):void {
//contacts En cours avec des prospects $id est renseigné après ajout d'une usivi sur un prospect
     if(!isset($_SESSION['LOGGED_USER'])) 
        {require ('src/templates/login.php');}
     else
        {//Actions possibles
        $actions = [
            ['texte'=> 'Modifier le commentaire','logoClass' =>'fa-regular fa-pen-to-square',
                        'modale'=>'etablissement;modifier;prospect','fichier'=> __NAMESPACE__ . '\tpProspectCommentaireMDL'],
            ['texte'=> 'Nouveau suivi','logoClass' =>'bi bi-telephone-outbound',
                        'modale'=>'etablissement;ajouter;prospect','fichier'=> __NAMESPACE__ . '\tpSuiviDetailMDL'],
            ['texte'=> 'Cloturer le prospect','logoClass' =>'bi bi-door-closed',
                        'modale'=>'etablissement;cloturer;prospect','message' => 'Cloturer le prospect ?'],
            ['texte' => 'Supprimer','logoClass' => 'bi bi-trash',
                        'modale'=>'etablissement;supprimer;prospect','message'=> 'Supprimer le prospect et ses suivis (plus aucune trace!)?'
                . 'Sinon utiliser Cloturer qui conserve tout le suivi']
                    ];
        if ($id==0){
            ctProspectLister(Prospect::listeOuverts(),$actions);
        }
        else{
            ctProspectLister(Prospect::listeUnique($id),$actions);
        }
    }    
}

function ctProspectListerT():void{
//contacts terminés avec des prospects
     if(!isset($_SESSION['LOGGED_USER'])) 
        {require ('src/templates/login.php');}
     else 
        {//Actions possibles
        $actions = [['texte' => 'Supprimer','logoClass' => 'bi bi-trash','sujet'=> Prospect::SUJET_MDL,
                        'modale'=>'supprimer','message'=> 'Supprimer le prospect et ses suivis (plus aucune trace!)?']];
         ctProspectLister(Prospect::listeFermes(),$actions);}
}

function ctProspectLister(array $prospects, array $actions):void {
// liste de tous les prospect
    //Pas de controle login car déjà fait
    switch (count($prospects)){ 
        case 0:
            $content = Tbx::messageColorer(false, "","Il n'y a pas de prospects pour l'état demandé");
            require(TbAdressage::projetGetLayout(__NAMESPACE__));
            break;
        case 1:
            $messageBarreMenu = "Prospect en cours";
            $expandAccordeon = false;
            require('tpProspectListe.php');
            break;
        default:
            $messageBarreMenu = "Liste des prospects";
            $expandAccordeon = true;
            require('tpProspectListe.php');
    }
}

// *************************  fonctions modales ************************

function ctSuiviMDLajouter(){
//ajoute un suivi en retour de la modale d'ajout de suivi
    $suivi = new Suivi();
    $suivi->commentaire->texte = TbAdressage::getPost("S","commentaire");
    $suivi->idAction = TbAdressage::getPost("I","idAction");
    $retour = $suivi->update(TbModal::TbModal::modalGetIdFromModal());
    if ($retour){
       ctProspectListerE(TbModal::modalGetIdFromModal());
    }
    else{die ("Erreur ctSuiviMDLajouter sur ajout de suivi");}
}
function ctProspectModifier(){
//Modifie le commentaire du prospect
    $prospect = new Prospect(TbModal::modalGetIdFromModal());
    $prospect->commentaire->texte = TbAdressage::getPost("S","commentaire");
    $retour = $prospect->update();
    if ($retour){
       ctProspectListerE($prospect->id);
    }
    else{die ("Erreur ctSuiviMDLmodifier sur ajout de suivi");}
}
   
function ctProspectSupprimer(){
//ajoute un suivi en retour de la modale d'ajout de suivi
    $prospect = new Prospect(TbModal::modalGetIdFromModal());
    $retour = $prospect->delete();
    $content = Tbx::messageColorer($retour,"Prospect sur " . $prospect->etablissement->nom . " supprimé");  
    //affichage 
    unset ($prospect);
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctProspectCloturer(){
//Cloture du prospect, ajout d'un suivi de cloture et passage à fermé du prospect 
    $prospect = new Prospect(TbModal::modalGetIdFromModal());
    $retour = $prospect->cloturer();
    $content = Tbx::messageColorer($retour,"Prospect sur " . $prospect->etablissement->nom . " clôturé");  
    //affichage 
    unset ($prospect);
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

//------------------------------------------------------------------------------
//ETABLISSEMENTS
//------------------------------------------------------------------------------
function ctEtablissementEditer(int $id=0) {
//action editEtab, édition établissement
    $typesEtab = Etablissement::listeDesTypes();
    $typesLien = TypeLien::listePourUnSujet(Etablissement::SUJET); //73 = établissement
    $etab = new Etablissement($id);
    $messageBarreMenu = "Détail établissement";
    if (Login::loginControl(__NAMESPACE__)){require('tpEtablissementDetail.php');}
}

function ctEtablissementSupprimer() {
//Appelé par modale de suppression d'un établissement
    $etablissement = new etablissement(TbModal::modalGetIdFromModal()); // issue de la modale
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
    $prospect = new prospect();
    //test existant
    $prospect->etablissement->id = TbModal::TbModal::modalGetIdFromModal();
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
    $etab = new etablissement();
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
        $etab->chargerLiensParMouvements(TbAdressage::getValeurPostTableau(lien::ZONES_MOUVEMENTS));

        $etab->chargerContactsParMouvements(TbAdressage::getValeurPostTableau(contact::ZONES_MOUVEMENTS));
    }
    return $etab;
}

function ctEtablissementMAJ() {
    
    //réception des fichiers
    $retour = uploadReceptionLien(); //ds Toolbox_upload, import des fichiers 
    
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
    ctEtablissementlister(Studio::liste(true),studio::composerListeActions(),$messageBarreMenu);
}
function ctEtablissementConcertLister():void{
//Liste des établissements pour concert
    $messageBarreMenu = "Liste des établissements pour concert";
    ctEtablissementLister(Etablissement::liste(true),etablissement::composerListeActions(),$messageBarreMenu);
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

