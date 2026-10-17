<?php
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Controleur du player de pads : domaine player, ctr=playerPad
 *******************************************************************************/
use shared\php\toolbox\Toolbox_liste                  as TbListeGenerique;
use shared\php\classes\lien\TypeLien                  as TypeLien;
use shared\php\toolbox\Toolbox                        as Tbx;
use shared\php\toolbox\Toolbox_adressage              as TbAdressage;
use theBand\src\php\evenement\Evenement_Concert       as Evenement_Concert;
use theBand\src\php\song\Song                         as Song;

//************************************************************************************
//PLAYER PAD
//***********************************************************************************
function ctPlayerPadTestConcert(){
//Affiche un player avec les pads des tests et concert
    $infos = Song::mdSongGetListePlayerPad();
    ctListePads($infos,"Pads des titres  Test et Concert");
}

function ctPlayerPadProchainConcert(){
    $idevt = Evenement_Concert::getIdProchain();
    //affichage 
    if ($idevt==0){
        $content = Tbx::messageColorer(false, "", "Pas de concert programmé");
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
    else{
        //affichage playlist à joueer
        $evt= new Evenement_Concert($idevt);
        $songs = Song::mdSongGetListePlayerFromSetList($evt->setlist->id,TypeLien::PAD); 
        ctListePads($songs,"Pads du prochain concert");
    }
    
}

function ctListePads(array $infos, string $messageBarreMenu){
//affiche juste laliste des titres sans les deux combo box de chhois
    $script = Tbx::includeJS(array('player','utils'));
    //zone = nom de la zone dans infos,
    //titre = titre à mettre , si rien c'est le nom de la zone 
    //fonction à appliquer
    $infosColonnes[] = ['zone'=>'numero','titre'=>'N°','align'=>'center'];
    $infosColonnes[] = ['zone'=>'titre'];
    $infosColonnes[] = ['zone'=>'description'];
    $infosColonnes[] = ['zone'=> 'lienMP3','titre'=>'player','fonction'=>'shared\php\toolbox\Toolbox_html::htmlAfficherLecteurAudio','parametres'=>array('$info[\'lienMP3\']'),'align'=>'center'];

    $content = TbListeGenerique::constituerListe($infos, $infosColonnes);
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}    
