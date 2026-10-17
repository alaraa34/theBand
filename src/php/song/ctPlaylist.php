<?php
namespace theBand\src\php\song;
/*******************************************************************************
 * Controleur de la playlist personnelle de l'utilisateur
 ******************************************************************************/
use shared\php\classes\socle\User                      as User;
use shared\php\toolbox\Toolbox                         as Tbx;
use shared\php\classes\socle\Login                     as Login;
use shared\php\toolbox\Toolbox_adressage               as TbAdressage;

/*******************************************************************************
 * Playlist
 ******************************************************************************/
function ctPlaylistEditer(){
//constitue la playlist utilisateur
    //création de la setlist utilisateur si existe pas
    if (Login::loginControl(__NAMESPACE__)){
        $user = new User(User::connectUserGetInfo('id'));
        $setlist = new Setlist($user->idSetlist);
        //liste des songs éligibles (concert ou test )
        $songs = Song::getListeChoix(array(Repertoire::TYPE_CONCERT,Repertoire::TYPE_TEST));
        //liste des studios de répétition
        $script =  Tbx::includeJS('song','theBand/src');
        $messageBarreMenu = "Modification de la playlist utilisateur";
        //affichage saisie 
        require('tpPlaylistDetail.php');
    }
}

function ctPlaylistMAJ(){
//mise à jour MPD d'une set list, evenement majSetlist
//suite à modification drang drop
    //set list pas initialisée car pas besoin d'elle, juste de la collection
    $setlist = new Setlist(TbAdressage::getPost("I","idSetlist"),true); //pas de chargement des détails
    if ($setlist->id ===0){$setlist->nomListe = "Playlist personnelle de " . User::connectUserGetInfo("prenom");}
    //Tout est mis en partie 1 de la setlist
    $numero = 0;
    foreach (explode("-", TbAdressage::getPost("S","idSongsSelected")) as $idsong){
        $numero +=1;
        $detail = new Detail();
        $detail->song->id = (int)$idsong;
        $detail->ordre = $numero;
        $detail->partie = 1;
        $setlist->details[] = $detail;
        unset ($detail);
    }
    //Mise à jour Physique
    $retour = $setlist->update();
    if ($retour){$retour = $setlist->updateCollectionDetail();}
    $content = Tbx::messageColorer($retour,"Playlist mise à jour","Erreur lors de la mise à jour");
     //Affichage
     require(TbAdressage::projetGetLayout(__NAMESPACE__));
}
