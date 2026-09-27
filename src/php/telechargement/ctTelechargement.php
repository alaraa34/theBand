<?php
declare(strict_types=1);
/* **************************************************************************************
 * cette classe décrit un titre dans sa généralité (titre, auteur...)
 * 
 ****************************************************************************/
namespace theBand\src\php\telechargement;

use shared\php\toolbox\Toolbox_adressage                as TbAdressage;
use shared\php\toolbox\Toolbox_upload                   as TbUpload;
use shared\php\toolbox\Toolbox                          as Tbx;
use shared\php\classes\personalisation\Nomenclature     as Nomenclature;
use shared\php\classes\telechargement\Telechargement    as Telechargement;
use shared\php\modale\Toolbox_modal                     as TbModal;



Function ctVideoConcertLister():void{
    $messageBarreMenu = "Vidéos de concerts";
    $content = TbUpload::lireFichiersSsDossiers (TbUpload::fichierPath() . Telechargement::REPERTOIRE . "/VideosConcerts");
     //affichage 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctTutoLister(){
// appelé pour le code 'documentsListe'
    $idDomaine = Nomenclature::mdNomenclatureGetDetail("DOMAINE", "APPLI","ID"); 
    ctTeleLister($idDomaine);
}

function ctDocumentationLister(){
// appelé pour le code 'documentsListe'
    $idDomaine = Nomenclature::mdNomenclatureGetDetail("DOMAINE", "DOCUMENTATIO","ID"); 
    ctTeleLister($idDomaine);
}

function ctConcertLister(){
// appelé pour le code 'documentsListe'
    $idDomaine = Nomenclature::mdNomenclatureGetDetail("DOMAINE", "CONCERT","ID"); 
    ctTeleLister($idDomaine);
}

function ctPromotionLister(){
// appelé pour le code 'documentsListe'
    $idDomaine = Nomenclature::mdNomenclatureGetDetail("DOMAINE", "PROMOTION","ID"); 
    ctTeleLister($idDomaine);
}

function ctTeleLister(int $domaine) {
//Liste une fois le répertoire sélectionné la listedes téléchargements faits sur ce domaine
    Telechargement::teleLister($domaine, __NAMESPACE__);
}
  
function ctGetTeleExistants(){
//fonction qui restitue les téléchargements effectués pour un répetoire
//parametre le nom du répertoire
    echo (Telechargement::getTeleExistants());
}
function ctTeleEditer(int $id = 0) {
//affiche laliste des téléchargements 
    Telechargement::teleEditer(__NAMESPACE__, $id);
}

function ctTeleGetRepertoires(){
//Appelé sur le click de domaine pour lister les répertoires qui lui correspondent
    $repertoires = Telechargement::listeRepertoires(TbAdressage::getPost("I","domaine"));
    echo (Telechargement::selectBoutonRepertoire(true,$repertoires));
}

function ctTeleMAJ() {
//mise à jour suite à ecran de saisie nouveau
    //réception des liens 
    ctTeleLister(Telechargement::teleMAJ());
}    

function ctTeleSupprimer(){
    $tele = new Telechargement(TbModal::modalGetIdFromModal());
    $retour = $tele->delete();
    $content = Tbx::messageColorer($retour,"Téléchargement supprimé","Erreur lors de la suppresssion"); 
    //affichage 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}