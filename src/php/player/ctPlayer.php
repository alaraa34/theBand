<?php
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Controleur des l'affichage des players MP3 et document
 *
 * @author Alara
 *******************************************************************************/

use metronome\php\metronome                           as Metronome;
use shared\php\toolbox\Toolbox_liste                  as TbListeGenerique;
use shared\php\classes\socle\Login                    as Login;
use shared\php\bricks\Brick_table                     as BkTable;
use shared\php\classes\lien\TypeLien                  as TypeLien;
use shared\php\toolbox\Toolbox                        as Tbx;
use shared\php\toolbox\Toolbox_adressage              as TbAdressage;
use theBand\src\php\evenement\Evenement_Concert       as Evenement_Concert;
use theBand\src\php\evenement\Evenement_Repetition    as Evenement_Repetition;
use theBand\src\php\song\Setlist                      as Setlist;
use theBand\src\php\song\Song                         as Song;
use theBand\src\php\socle\UserTheBand                 as User;


define("CONTEXTE",["Concert","Répétition","Test","Concert et Test","Setlist"]);

//------------------------------------------------------------------------------
//LISTE POUR TRAVAILLER LES MORCEAUX 
//------------------------------------------------------------------------------
function ctPlayerProchaineRepet() :void{
    ctPlayerSongAffichageInitial(1);
}
function ctPlayerProchainConcert() :void{
    ctPlayerSongAffichageInitial(0);
}
function ctPlayerTest() :void{
    ctPlayerSongAffichageInitial(2);
}
function ctPlayerTestConcert() :void{
    ctPlayerSongAffichageInitial(3);
}
function ctPlayerPlayList(){
//controle connexion
    if (Login::loginControl(__NAMESPACE__)){
        //si pas de playlist constitué, message
        $user = new User(User::connectUserGetInfo('id'));
        if ($user->idSetlist===0){
            //envoi constitution play liste
            \theBand\src\php\song\ctPlaylistEditer();
        }
        else{
            $idContexte = 4;
            $songs = ctPlayerGetListeSongs($idContexte,TypeLien::MP3D ,$user->idSetlist); 
            ctPlayerAfficherPlayerAvecBox($songs,TypeLien::MP3D ,$idContexte);
        }
    }
   
}

function ctPlayerAfficherPlayerAvecBox(array $infos, int $idTypeSong, int $idContexte, int $idSetlist=0){
//Liste des morceaux avec le player et les combos de choix
//Paramètres $info = liste des chansons, $messageBarreMenu = message à afficher, $typeSong le type de mp3 choisi, $contexte le nom du player (test, concert ...)
    //traitement
    $contexte=CONTEXTE[$idContexte];
    $libelleContexte = ctLibelleLongContexte($idContexte,$idSetlist);
    if (count($infos)==0){
       $messageBarreMenu = "";
       $players  = Tbx::messageColorer(false,"","Aucun mp3 de ce type n'a été trouvé pour ce contexte");
    }
    else{
        $messageBarreMenu = "Cliquer sur le player pour jouer le mp3";
        //Paramétrage liste générique
        $players = ctPlayerListeSansBox($infos);
    }
    
    $typesSong = TypeLien::listePourUnSujet(72, zoneSelect: " CONCAT( '(', nomAffiche,') ', nomlong ) as nomCompose "); //Liste des MP3 autorisés pour les players
  
    $url = TbAdressage::getURLstatic("player", "playerAfficherChoix");
    //scripts à rajouter
    $script = Tbx::includeJS(array('ajax','player')) . Metronome::includeJS();
    
    require('tpPlayer.php'); 
}

function ctPlayerListeSansBox($infos){
//eaffiche juste laliste des titres sans les deux combo box de chhois
    //titre = titre à mettre , si rien c'est le nom de la zone 
    //fonction à appliquer
    $infosColonnes[] = ['zone'=> BkTable::COLONNE_NUMERO,'align'=>'MC'];
    $infosColonnes[] = ['zone'=>'titre','align'=>'M'];
    $infosColonnes[] = ['zone'=> 'tonaOrigine','titre'=>'Tona<br>origine','fonction'=> tbListeGenerique::FONCTION_VASN,'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'tonaScene','titre'=>'Tona<br>Scène<br>(1/2 Ton)','fonction'=> tbListeGenerique::FONCTION_VASN,'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'tempo','fonction'=> tbListeGenerique::FONCTION_VASN,'parametres'=>['$valeur',0,false,true],'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'leadChant','titre'=>'Lead<br>chant','fonction'=>tbListeGenerique::FONCTION_VASN,'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'tempo','titre'=>'Clic<br>batterie','fonction'=> 'metronome\php\Metronome::render','parametres'=>array('$info[\'tempo\']','$info[\'numero\']'),'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'lienMP3','titre'=>'player','fonction'=>'shared\php\toolbox\Toolbox_html::htmlAfficherLecteurAudio','parametres'=>array('$info[\'lienMP3\']'),'align'=>'MC'];
    
    return tbListeGenerique::constituerListe($infos, $infosColonnes);
}

function ctPlayerSongAffichageInitial(int $idContexte):void{
//Constitution de la liste de player initiale pour ce contexte
//Par défaur Mp3d
    $songs = ctPlayerGetListeSongs($idContexte,TypeLien::MP3D ); 
    ctPlayerAfficherPlayerAvecBox($songs,TypeLien::MP3D ,$idContexte);
}

function ctLibelleLongContexte(int $idContexte,int $idSetlist= 0){
    switch ($idContexte){
        case 3:
        case 2:
            $libelle = "";
            break;
        case 0:
            //Prochain concert
            $idevt = Evenement_Concert::getIdProchain();
            //affichage 
            if ($idevt==0){$libelle = "";}
            else{
                $evt= new Evenement_Repetition($idevt);
                $libelle = $evt->setlist->nomListe;
            }
            break;
        case 1:
            //Prochaine répet
             $idevt = Evenement_Repetition::getIdProchain();
            //affichage 
            if ($idevt==0){$libelle = "";}
            else{
                $evt= new Evenement_Repetition($idevt);
                $libelle = $evt->setlist->nomListe;
            }
            break;
        case 4:
            //une set liste particulière
            $setlist = new Setlist($idSetlist);
            $libelle = $setlist->nomListe;
    }
    return $libelle;
}

function ctPlayerGetListeSongs(int $idContexte,int $idTypeLien,int $idSetlist= 0 ){
//en fonction des paramètres de contexte retourne la liste des titres concernés
//Par défaut Mp3d
    switch ($idContexte){
        case 2:
            //Player en test
            $songs = Song::mdSongGetListePlayer(array(Song::TYPE_TEST),$idTypeLien); //liste des Songs en test et ayant du typemp3 demandé comme lien
            break;
        case 0:
            //Prochain concert
            $songs = ctPlayerProchainConcertGetSongs($idTypeLien);
            break;
        case 1:
            //Prochaine répet
            $songs = ctPlayerProchainRepetGetSongs($idTypeLien);
            break;
        case 3:
            //Concert et test
            $songs = Song::mdSongGetListePlayer(array(Song::TYPE_CONCERT,Song::TYPE_TEST),$idTypeLien);
            break;
        case 4:
            //une set liste particulière
            $songs = Song::mdSongGetListePlayerFromSetList($idSetlist,$idTypeLien);
            break;
        default :
            die ("ctPlayerGetListeSongs : idcontexte non reconnu " . $idContexte);
    }
    return $songs;
}

function ctPlayerProchainRepetGetSongs(int $idTypeSong){
//affiche la liste des chansons de la prochaine répet avec affichage dans l'ordre du concert
//du type voulu
    $idevt = Evenement_Repetition::getIdProchain();
    //affichage 
    if ($idevt==0){
        $songs=[];
    }
    else{
        //affichage playlist à joueer
        $evt= new Evenement_Repetition($idevt);
        $songs = Song::mdSongGetListePlayerFromSetList($evt->setlist->id,$idTypeSong); 
    }
    return $songs;
}


function ctPlayerProchainConcertGetSongs(int $idTypeSong){
//affiche la liste des chansons du prochain concert avec affichage dans l'ordre du concert
//du type voulu
    $idevt = Evenement_Concert::getIdProchain();
    //affichage 
    if ($idevt==0){
        $songs=[];
    }
    else{
        //affichage playlist à joueer
        $evt= new Evenement_Concert($idevt);
        $songs = Song::mdSongGetListePlayerFromSetList($evt->setlist->id,$idTypeSong); 
    }
    return $songs;
}

function ctPlayerUnConcert(int $idEvt){
//player pour un concert particulier
    $evt= new Evenement_Concert($idEvt);
    $songs = Song::mdSongGetListePlayerFromSetList($evt->setlist->id,TypeLien::MP3D); 
    //appel avec 4 comme idcontexte
    ctPlayerAfficherPlayerAvecBox($songs,TypeLien::MP3D ,4,$evt->setlist->id);
}

function ctPlayerUneRepetition(int $idEvt){
//player pour un concert particulier
    $evt= new Evenement_Repetition($idEvt);
    $songs = Song::mdSongGetListePlayerFromSetList($evt->setlist->id,TypeLien::MP3D); 
    ctPlayerAfficherPlayerAvecBox($songs,TypeLien::MP3D ,4,$evt->setlist->id);
}

function ctPlayerAfficherChoix(){
//Met à jour la liste des players après un choix effectué à l'écran sur le type de mp3
    $songs = ctPlayerGetListeSongs(TbAdressage::getpost("I","idContexte"),TbAdressage::getpost("I","typeMP3"),TbAdressage::getpost("I","idSetlist"));
    //renvoi à l'affichage
    if (count($songs)==0){
        $reponse = Tbx::messageColorer(false,"","Aucun player n'est disponible pour ce contexte et ce type de MP3");
    }
    else{
        $reponse =ctPlayerListeSansBox($songs);
    }
    echo json_encode($reponse, JSON_FORCE_OBJECT);
    die();
}

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
//affiche un document PDF dans l'ordre de la set list de concert
    $idSetList = Evenement_Concert::getIdSetListProchainEvt();
    if($idSetList==0) {   
        //message si pas de concert 
        $content = Tbx::messageColorer(false, "", "Pas de concert programmé");
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
    else{
        //affichage setlist du concert
        ctPlayerDocAffichage ($idSetList);
    }
}

function ctPlayerDocTestConcert(){
    ctPlayerDocAffichage ();
}
function ctPlayerDocAffichage(int $idSetList=0){
//affiche un document PDF dans pour les morceaux de test et de concert
    $idTypeLien = TbAdressage::getPost("I", "typePDF");
    if ($idTypeLien==0){
        //pour faire le choix du type de docuùments
        //$idsetlist est rajouté dans laa fenêtre
        $extensions = TypeLien::mdTypesLiensListePDF(mdNomenclatureGetDetail("SUJET", Song::SUJET_MDL,"ID"));//liste des doucments PDF pour le sujet SONG
        require('src/templates/Song/playerChoixType.php');
    }
    else{
        //pour afficher la liste 
        $urlControleur = urlServeur() . "index.php?action=accueil";//pour bouton accueil
        //récupération idsetlist dans la fenêtre
        if (TbAdressage::getPost("I","idSetlist")===0){
            //playlists Test et idSetlistconcert
            $songs = Song::mdSongGetListePlayerDocument(array(Song::TYPE_CONCERT,Song::TYPE_TEST),$idTypeLien);
        }
        else{
            //plylist du prochain concert
            $songs = Song::mdSongGetListePlayerSetlistDocument($idSetList,$idTypeLien); //listes des Songs $
        }
        require('src/templates/Song/playerDoc.php');
    }

}
//************************************************************************************
//PLAYER PAD
//***********************************************************************************
function ctPlayerPadTestConcert(){
//Affiche un player avec les pads des tests et concert
    $infos = song::mdSongGetListePlayerPad();
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
        $songs = song::mdSongGetListePlayerFromSetList($evt->setlist->id,TypeLien::PAD); 
        ctListePads($songs,"Pads du prochain concert");
    }
    
}

function ctListePads(array $infos, string $messageBarreMenu){
//affiche juste laliste des titres sans les deux combo box de chhois
    $script =Tbx::includeJS('player');
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