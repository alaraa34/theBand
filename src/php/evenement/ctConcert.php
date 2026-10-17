<?php
declare(strict_types=1);
namespace theBand\src\php\evenement;
/*******************************************************************************
 * Controleur des concerts (domaine evenement, ctr=concert)
 *******************************************************************************/
require_once __DIR__ . '/ctEvenement.php';
require_once __DIR__ . '/ctSetlist.php';

use shared\php\modale\Toolbox_modal                as TbModal;
use shared\php\bricks\Brick_textbox                as BkTextbox;
use shared\php\classes\lien\TypeLien               as TypeLien;
use shared\php\classes\socle\Login                 as Login;
use theBand\src\php\socle\UserTheBand              as User;
use shared\php\toolbox\Toolbox                     as Tbx;
use shared\php\toolbox\Toolbox_adressage           as TbAdressage;
use theBand\src\php\etablissement\Etablissement    as Etablissement;
use theBand\src\php\song\Setlist                   as Setlist;
use shared\php\toolbox\Toolbox_classe              as TbClasse;

//------------------------------------------------------------------------------
//SPECIFIQUE CONCERT 
//------------------------------------------------------------------------------
function ctConcertSupprimer(){
    $idEvt = TbModal::modalGetIdFromModal(); // issue de la modale
    $evt = new Evenement_Concert($idEvt);
    $retour = $evt->delete();  
    unset($evt);
    //affichage 
    $content = Tbx::messageColorer($retour,"Concert supprimé","Erreur lors de la suppresssion");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function actionsEVTConcert(){
    $actions =[];
    $actions[] = ['texte'=> 'Jouer playlist','logoClass' =>'bi bi-file-earmark-music','href'=>'player/playerMp3;unConcert;player'];
    $actions[] = ['texte'=> 'Confirmer la date de concert','logoClass' =>'bi bi-calendar2-check',
                'modale'=>'evenement/concert;confirmer','message' => 'Le concert est confirmé ?'];
    $actions[] = ['texte'=> 'Imprimer playlist','logoClass' =>'bi bi-printer','href'=>'evenement/concert;printSetList'];
    
    if(User::connectUserGetInfo("id",0) > 0){
        $actions[] =['texte'=> 'Modifier','logoClass' =>'fa-regular fa-pen-to-square','href'=>'evenement/concert;editer'];
        $actions[] =['texte'=> 'Modifier playlist','logoClass' =>'bi bi-music-note-list','href'=>'evenement/setlist;editer'];
        $actions[] =['texte' => 'Supprimer','logoClass' => 'bi bi-trash','modale'=>'evenement/concert;supprimer',
                        'message'=> 'Supprimer le concert ?'];
    }
   
    return $actions;
}

function ctConcertConfirmer(){
//confirmation de la date du concert
    $concert= new Evenement_Concert();
    $concert->id = TbModal::modalGetIdFromModal();
     //changement du statut à confirme
    $concert->majConfirme();
    //message
     header('Location: ' . (string)filter_input(INPUT_SERVER,'HTTP_REFERER'));
}

function ctConcertProchain():void{
    ctListeConcert(Evenement::PROCHAIN);
}

function ctConcertListe():void{
    //suppression des anciennes répétitions (de plus d'un mois)
    Evenement_Concert::supprimerEvtsAnciens();
    //liste répétitions
    ctListeConcert(Evenement::TOUS);
}

function ctListeConcert(int $prochain) :void{
    $lienMisEnForme = false;
    $typesLien=  TypeLien::listePourUnSujet(Evenement_Concert::SUJET_LIEN);
    if ($prochain){$texte = "Prochain ";}
    else{$texte = "Liste ";}
    $evenements = Evenement_Concert::getListe($prochain);
    $texte .= "concert";
    // action spossibles
    $actions = actionsEVTConcert();
    ctListeEvenements($evenements,$lienMisEnForme,$typesLien,$texte,$actions);
}

function ctConcertPrintSetListProchain() {
//affiche en plein écran la set list d'un concert pour impression, action printsetlist
    $idevt = Evenement_Concert::getIdProchain();
    if ($idevt === 0){
        $content = Tbx::messageColorer(false,"","Aucun concert à venir");
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
    else {
        ctConcertPrintSetList($idevt);
    }
}

function ctConcertPrintSetList(int $idEvt) {
//affiche en plein écran la set list d'un concert pour impression, action printsetlist
    $evt = new Evenement_Concert($idEvt);
    $messageBarreMenu = "Pour l'impression utiliser la fonction imprimer du navigateur";
    $content = $evt->listerSetlist();
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctConcertEditSetListProchain(){
    $idevt = Evenement_Concert::getIdProchain();
    if ($idevt === 0){
        $content = Tbx::messageColorer(false,"","Aucun concert à venir");
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
    else{
        ctSetlistEditer($idevt);
    }
}


function ctConcertEditer(int $id=0, $idEtablissement=0) {
//Ajout/Modification d'un concert
    $concert = new Evenement_Concert($id);
    
    $etablissements = Etablissement::ListeSelection(Evenement_Concert::NATURE);
    $modelesSL = Setlist::listerSetlistConcert($concert->setlist->id);
     //JS et CSS
    $script = Tbx::includeJS(array('ajax','textbox','utils'));
    $css = Tbx::includeCSS('textbox');
    $textbox = new BkTextbox();
    $textbox->setTexte($concert->commentaire->texte);
    $textbox->setTexteDefaut($concert::initFeuilleDeRoute());
    //modale nouvel établissement : utilisée par le template dans tous les cas (bouton caché en modification)
    $actions =  [['texte' => 'Nouvel établissement',
                'modale'=>'etablissement;ajouter',
                'template'=> 'theBand/src/php/etablissement/tpEtablissementDetailMDL.php',
                'typesEtab'=>Etablissement::listeDesTypes()]];
    if ($id==0){
        //Ajout d'un  nouveau concert
        $messageBarreMenu = "Nouveau concert";
        
                      
        //affichage saisie ou login
        if(Login::loginControl(__NAMESPACE__)){require('tpConcertDetail.php');}
    }
    else{
        $messageBarreMenu = "Modification d'un concert";
        //affichage saisie ou login
        require('tpConcertDetail.php');
    }
}


function ctConcertMAJ(){
//maj MPD suite saisie concert   
    $messageBarreMenu = "";
    $concert = new Evenement_Concert();
    
    $concert->etablissement->id = TbAdressage::getPost("I","lieuConcert");
    //Chargement du nom pour intitulé setlist
    TbClasse::classeLoadFromId($concert->etablissement);
    $concert->id = TbAdressage::getPost("I","idConcert");
    $concert->dateHeure = TbAdressage::getPost("S","dateConcert") . " " . TbAdressage::getPost("S","heureConcert"). ":00";
    $concert->dateHeureFin = TbAdressage::getPost("S","dateRep") . " " .  "00:00:00";
    $concert->commentaire->id = TbAdressage::getPost("I","idCommentaire");
    $concert->commentaire->texte = BkTextbox::getTexte();
    
    $concert->setlist->id = TbAdressage::getPost("I","idSetlist");
    $concert->setlist->nomListe = "Concert du " . $concert->dateHeure . " à " . $concert->etablissement->getNom();
    $concert->setlist->idNature = $concert::NATURE;
    //si id setlist copié rensigné et différent de la set list d'origine  
    if (TbAdressage::getPost("I","modeleSL")>0){
       //case cochée quans elle vaut sa value, non retournée si pas cochée
       //réinitialisation de la setlist par celle citée en référence
        $concert->setlist->initialiserParAutreSetList(TbAdressage::getPost("I","modeleSL"));
    }
    $retour = $concert->update();
    $content = Tbx::messageColorer($retour,"Concert traité","Erreur, réessayer !");
     //Affichage
     require(TbAdressage::projetGetLayout(__NAMESPACE__));
}
