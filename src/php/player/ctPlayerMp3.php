<?php
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Controleur du player MP3 (lecteur multipiste) : domaine player, ctr=playerMp3
 *******************************************************************************/
use metronome\php\Metronome                           as Metronome;
use shared\php\toolbox\Toolbox_liste                  as TbListeGenerique;
use shared\php\classes\socle\Login                    as Login;
use shared\php\bricks\Brick_table                     as BkTable;
use shared\php\classes\lien\TypeLien                  as TypeLien;
use shared\php\classes\lien\Multipiste                as Multipiste;
use shared\php\toolbox\Toolbox                        as Tbx;
use shared\php\toolbox\Toolbox_adressage              as TbAdressage;
use theBand\src\php\evenement\Evenement_Concert       as Evenement_Concert;
use theBand\src\php\evenement\Evenement_Repetition    as Evenement_Repetition;
use theBand\src\php\song\Repertoire                   as Repertoire;
use theBand\src\php\song\Setlist                      as Setlist;
use theBand\src\php\song\Song                         as Song;
use theBand\src\php\socle\UserTheBand                 as User;

define("CONTEXTE",["Concert","Répétition","Test","Concert et Test","Setlist"]);
//choix du MP3 joué par les players (boutons radio) : original (type MP3ORI) ou joué par le groupe (type MP3JOU)
define("TYPES_MP3_PLAYER",[TypeLien::MP3ORI => 'Original', TypeLien::MP3JOU => 'Groupe']);

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
            //envoi constitution play liste (controleur playlist du domaine song)
            require_once __DIR__ . '/../song/ctPlaylist.php';
            \theBand\src\php\song\ctPlaylistEditer();
        }
        else{
            $idContexte = 4;
            $songs = ctPlayerGetListeSongs($idContexte,TypeLien::MP3ORI ,$user->idSetlist); 
            ctPlayerAfficherPlayerAvecBox($songs,TypeLien::MP3ORI ,$idContexte, $user->idSetlist);
        }
    }
   
}

function ctPlayerAfficherPlayerAvecBox(array $infos, int $idTypeLien, int $idContexte, int $idSetlist=0){
//Liste des morceaux du contexte avec un lecteur multipiste par titre, et le choix Original / Groupe
//Paramètres $infos = liste des titres, $idTypeLien = type de MP3 joué (MP3ORI ou MP3JOU), $idContexte = périmètre (concert, répétition, test...)
    $contexte=CONTEXTE[$idContexte];
    $libelleContexte = ctLibelleLongContexte($idContexte,$idSetlist);
    $messageBarreMenu = count($infos)==0 ? "" : "Écoute des titres";
    $players = ctPlayerListeSansBox($infos, $idTypeLien);
    //choix du MP3 : original (tona de scène appliquée) ou enregistrement du groupe
    $typesMP3 = TYPES_MP3_PLAYER;
    $url = TbAdressage::getURLstatic("player", "playerAfficherChoix", 0, "playerMp3");
    //scripts et styles à rajouter
    $script = Tbx::includeJS(array('ajax','player','utils','multipiste')) . Metronome::includeJS();
    $css = Tbx::includeCSS('multipiste');

    require('tpPlayer.php'); 
}

function ctPlayerListeSansBox(array $infos, int $idTypeLien): string {
//liste des titres avec leur lecteur multipiste (sans les choix du haut de page)
//tous les titres sont affichés, 3 cas par titre pour le type de MP3 choisi :
//  pas de MP3 : lecteur rouge « Pas de mp3 de type ... pour ce titre » ; MP3 sans pistes générées : le MP3 ; pistes générées : lecteur multipiste
    if (count($infos)==0){
        return Tbx::messageColorer(false,"","Aucun titre pour ce contexte");
    }
    $etats = Multipiste::etatsParLien(array_column($infos, 'idLien'));
    foreach ($infos as $index => $info) {
        //transposition : tona de scène pour un MP3 original, 0 pour un enregistrement du groupe
        $tona = Repertoire::tonaAttendue($idTypeLien, $info['tonaScene']);
        $idLien = (int)$info['idLien'];
        $source = Multipiste::sourceLecteur($idLien, (string)$info['lienMP3'], $etats[$idLien] ?? null, $tona);
        $infos[$index]['lecteur'] = Multipiste::lecteurHtml($source, mb_strtolower(TYPES_MP3_PLAYER[$idTypeLien] ?? ''));
    }
    $infosColonnes[] = ['zone'=> BkTable::COLONNE_NUMERO,'align'=>'MC'];
    $infosColonnes[] = ['zone'=>'titre','align'=>'M'];
    $infosColonnes[] = ['zone'=> 'tonaOrigine','titre'=>'Tona<br>origine','fonction'=> TbListeGenerique::FONCTION_VASN,'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'tonaScene','titre'=>'Tona<br>Scène<br>(1/2 Ton)','fonction'=> TbListeGenerique::FONCTION_VASN,'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'tempo','fonction'=> TbListeGenerique::FONCTION_VASN,'parametres'=>['$valeur',0,false,true],'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'tempo','titre'=>'Clic<br>batterie','fonction'=> 'metronome\php\Metronome::render','parametres'=>array('$info[\'tempo\']','$info[\'numero\']'),'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'lecteur','titre'=>'Lecteur','align'=>'M'];
    
    return TbListeGenerique::constituerListe($infos, $infosColonnes);
}

function ctPlayerSongAffichageInitial(int $idContexte):void{
//Constitution de la liste de player initiale pour ce contexte
//Par défaut MP3 (tona concert)
    $songs = ctPlayerGetListeSongs($idContexte,TypeLien::MP3ORI ); 
    ctPlayerAfficherPlayerAvecBox($songs,TypeLien::MP3ORI ,$idContexte);
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
            $songs = Song::mdSongGetListeLecteur(array(Song::TYPE_TEST),$idTypeLien); //titres en test, avec ou sans MP3 du type demandé
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
            $songs = Song::mdSongGetListeLecteur(array(Song::TYPE_CONCERT,Song::TYPE_TEST),$idTypeLien);
            break;
        case 4:
            //une set liste particulière
            $songs = Song::mdSongGetListeLecteurFromSetList($idSetlist,$idTypeLien);
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
        $songs = Song::mdSongGetListeLecteurFromSetList($evt->setlist->id,$idTypeSong); 
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
        $songs = Song::mdSongGetListeLecteurFromSetList($evt->setlist->id,$idTypeSong); 
    }
    return $songs;
}

function ctPlayerUnConcert(int $idEvt){
//player pour un concert particulier
    $evt= new Evenement_Concert($idEvt);
    $songs = Song::mdSongGetListeLecteurFromSetList($evt->setlist->id,TypeLien::MP3ORI); 
    //appel avec 4 comme idcontexte
    ctPlayerAfficherPlayerAvecBox($songs,TypeLien::MP3ORI ,4,$evt->setlist->id);
}

function ctPlayerUneRepetition(int $idEvt){
//player pour un concert particulier
    $evt= new Evenement_Repetition($idEvt);
    $songs = Song::mdSongGetListeLecteurFromSetList($evt->setlist->id,TypeLien::MP3ORI); 
    ctPlayerAfficherPlayerAvecBox($songs,TypeLien::MP3ORI ,4,$evt->setlist->id);
}

function ctPlayerAfficherChoix(){
//Met à jour la liste des lecteurs après le choix Original / Groupe
    $typeMP3 = TbAdressage::getpost("I","typeMP3") === TypeLien::MP3JOU ? TypeLien::MP3JOU : TypeLien::MP3ORI;
    $songs = ctPlayerGetListeSongs(TbAdressage::getpost("I","idContexte"),$typeMP3,TbAdressage::getpost("I","idSetlist"));
    $reponse = ctPlayerListeSansBox($songs, $typeMP3);
    echo json_encode($reponse, JSON_FORCE_OBJECT);
    die();
}
