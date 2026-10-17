<?php
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Controleur du player de documents PDF : domaine player, ctr=playerDoc
 *******************************************************************************/
use shared\php\classes\lien\TypeLien                  as TypeLien;
use shared\php\toolbox\Toolbox                        as Tbx;
use shared\php\toolbox\Toolbox_adressage              as TbAdressage;
use theBand\src\php\evenement\Evenement_Concert       as Evenement_Concert;
use theBand\src\php\evenement\Evenement_Repetition    as Evenement_Repetition;
use theBand\src\php\song\Repertoire                   as Repertoire;
use theBand\src\php\song\Song                         as Song;

//************************************************************************************
//PLAYER DOCUMENT
//***********************************************************************************
function ctPlayerDocProchainConcert(){
//affiche un document PDF dans l'ordre de la set list de concert
    playerDocProchainEvt( Evenement_Concert::getIdSetListProchainEvt());
}
function ctplayerDocProchaineRepet(){
//affiche un document PDF dans l'ordre de la set list de répet
    playerDocProchainEvt( Evenement_Repetition::getIdSetListProchainEvt());
}

function playerDocProchainEvt(int $idSetList){
//affiche les documents PDF dans l'ordre de la setlist du prochain évènement (concert ou répétition)
    if($idSetList==0) {
        $content = Tbx::messageColorer(false, "", "Pas d'évènement programmé");
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
    else{
        ctPlayerDocAffichage($idSetList);
    }
}

function ctPlayerDocTestConcert(){
    ctPlayerDocAffichage();
}

function ctPlayerDocAffichage(int $idSetList=0){
//1er appel : choix du type de document ; retour du formulaire (typePDF posté) : affichage des documents
//$idSetList = 0 : titres de test et de concert, sinon titres de la setlist
    $idTypeLien = TbAdressage::getPost("I", "typePDF");
    if ($idTypeLien===0){
        $idSetlist = $idSetList;    //reporté dans le formulaire
        $extensions = TypeLien::mdTypesLiensListePDF(Repertoire::SUJET_LIEN);//liste des documents PDF pour le sujet SONG
        $messageBarreMenu = "Player de documents";
        require('tpPlayerChoixType.php');
        return;
    }
    $idSetList = TbAdressage::getPost("I","idSetlist");
    $songs = $idSetList===0
        ? Song::mdSongGetListePlayerDocument(array(Song::TYPE_CONCERT,Song::TYPE_TEST),$idTypeLien)
        : Song::mdSongGetListePlayerSetlistDocument($idSetList,$idTypeLien);
    if (count($songs)===0){
        $content = Tbx::messageColorer(false, "", "Aucun titre pour ce choix");
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
        return;
    }
    $urlControleur = TbAdressage::getURLstatic("accueil","accueil");   //bouton retour accueil
    $script = Tbx::includeJS(['ajax','player']);
    require('tpPlayerDoc.php');
}
