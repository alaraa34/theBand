<?php
declare(strict_types=1);
namespace theBand\src\php\evenement;
/*******************************************************************************
 * Controleur des setlists de concert (domaine evenement, ctr=setlist)
 *******************************************************************************/
use shared\php\classes\socle\Login                 as Login;
use shared\php\toolbox\Toolbox                     as Tbx;
use shared\php\toolbox\Toolbox_adressage           as TbAdressage;
use theBand\src\php\song\Setlist                   as Setlist;
use theBand\src\php\song\Detail                    as Detail;

//------------------------------------------------------------------------------
//SETLIST
//------------------------------------------------------------------------------

function ctSetlistEditer($idEvt){
//Modif d'une playlist avec drag and drop. Action editSetList
    // Paramètre la liste des types de songs à afficher
    $evt = new Evenement_Concert($idEvt);
    $messageBarreMenu = "Modification de Playlist";
    $evt->setlist->loadPartie0();
    //touch-punch (shared) traduit le toucher de la tablette en souris pour jQuery UI : à charger avant concert
    $script = Tbx::includeJS(['jquery.ui.touch-punch.min']) . Tbx::includeJS(['concert'],'theBand/src');
    $css = Tbx::includeCSS(['concert'],'theBand/src');
    //affichage sans actions
    if(Login::loginControl(__NAMESPACE__)){require('tpConcertBuildSetlist.php');}
}

function ctSetlistMAJ(){
//mise à jour MPD d'une set list, evenement majSetlist
//suite à modification drag drop
    //set list pas initialisée car pas besoin d'elle, juste de la collection
    $setlist = new Setlist();
    $setlist->id = TbAdressage::getPost("I","idSetlist");
    //balayage des 3 parties (0 c'est les non utilisés)
    for ($i=1;$i<=3;$i++){
        $zone = TbAdressage::getPost("S","listePartie" .$i);
        if (\strlen($zone)>0) {
            foreach(explode("-",$zone) as $song){
                $setlist->details[]=setlistMajDetail($i,$song,count($setlist->details));
            }
        }
    }
    //Mise à jour Physique
    $retour = $setlist->updateCollectionDetail();
    $content = Tbx::messageColorer($retour,"Setlist mise à jour","Erreur lors de la mise à jour");
     //Affichage
     require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function setlistMajDetail(int $partie , string $song, int $lastOrdre) {
// Mets à jour une partie de set list
// le tableau d'Idsong contient soit idsong(ex 33) soit idsont + E si enchainement(ex33E)
    //la Suppression de la liste existante a été faite avant cet appel dans ctMajSetlist
    $detail = new Detail();
    $detail->partie = $partie;
    $detail->ordre = $lastOrdre +1;
    //strstr retourne false si trouve pas
    if (strstr($song,'E')){
           //true retourne la partie gauche
        $detail->song->id = intval(strstr($song, 'E', true));
        $detail->enchainement = true;
    }
    else{
        $detail->song->id = intval($song);
        $detail->enchainement = false;
    }
    
    return $detail;
}
