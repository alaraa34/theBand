<?php
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Player complet : domaine player, ctr=playerAll (comme le répertoire de training)
 * colonne de gauche : choix du contexte (prochain concert, prochaine répétition, test...) et liste des titres
 * colonne de droite : détail du titre cliqué (description, métronome, audios multipistes, séquences, vidéos)
 *******************************************************************************/
use metronome\php\Metronome                     as Metronome;
use shared\php\classes\lien\TypeLien            as TypeLien;
use shared\php\classes\lien\Sequence            as Sequence;
use shared\php\toolbox\Toolbox                  as Tbx;
use shared\php\toolbox\Toolbox_adressage        as TbAdressage;
use theBand\src\php\socle\UserTheBand           as User;
use theBand\src\php\song\Song                   as Song;

//contextes et listes de titres : repris du player MP3
require_once __DIR__ . '/ctPlayerMp3.php';

//contextes proposés dans le combo, dans l'ordre d'affichage (numéros de ctPlayerGetListeSongs, sauf 5)
const PLAYER_FULL_CONCERT = 5;   //titres concert seuls : propre au player complet
const PLAYER_FULL_CONTEXTES = [0=>"Prochain concert", 1=>"Prochaine répétition", PLAYER_FULL_CONCERT=>"Titres concert",
                               2=>"Titres en test", 3=>"Titres concert et test", 4=>"Ma playlist"];
const PLAYER_FULL_CONTEXTE_DEFAUT = 0;

function ctPlayerFull(): void {
//page du player complet : seules les sources qui ont des titres sont proposées ;
//ouverture sur la première d'entre elles (le prochain concert s'il y en a un) et son premier titre
    $titresParContexte = playerFullTitresParContexte();
    $contextes = [];
    foreach ($titresParContexte as $id => $titresContexte) {$contextes[] = ['identifiant'=>$id, 'zone'=>PLAYER_FULL_CONTEXTES[$id]];}
    $idContexte = array_key_first($titresParContexte) ?? PLAYER_FULL_CONTEXTE_DEFAUT;
    $titres = $titresParContexte[$idContexte] ?? [];
    $liste = PlayerTitre::listeHtml($titres);
    $libelleContexte = playerFullLibelle($idContexte);
    $detail = count($titres) === 0 ? "" : (new PlayerTitre((int)$titres[0]['id']))->render();
    $urlListe = TbAdressage::getURLControleurFromCFPAdress("player/playerAll;chargerListe");
    $script = Tbx::includeJS(['ajax', 'utils', 'multipiste']) . Metronome::includeJS()
            . Sequence::includes() . Tbx::includeJS('playerFull', 'theBand/src');
    $css = Tbx::includeCSS('multipiste') . Tbx::includeCSS('playerFull', 'theBand/src');
    $messageBarreMenu = "Player complet";
    require('tpPlayerFull.php');
}

function ctPlayerAllChargerListe(): void {
//ajax : liste des titres du contexte choisi dans le combo
    $idContexte = TbAdressage::getPost("I", "idContexte");
    if (!array_key_exists($idContexte, PLAYER_FULL_CONTEXTES)) {$idContexte = PLAYER_FULL_CONTEXTE_DEFAUT;}
    $libelle = playerFullLibelle($idContexte);
    echo ($libelle !== "" ? '<div class="small text-muted mb-2">' . htmlspecialchars($libelle) . '</div>' : '')
       . PlayerTitre::listeHtml(playerFullTitres($idContexte));
    die();
}

function ctPlayerAllChargerDetail(): void {
//ajax : détail du titre cliqué (zone titreN = id du titre)
    $idSong = 0;
    foreach (TbAdressage::getValeurPostZoneGenerique("titre") as $valeur) {$idSong = (int)$valeur;}
    echo $idSong > 0 ? (new PlayerTitre($idSong))->render() : "";
    die();
}

//------------------------------------------------------------------------------
//OUTILS
//------------------------------------------------------------------------------
function playerFullTitresParContexte(): array {
//titres de chaque source du combo, dans l'ordre d'affichage, en ne gardant que les sources qui ont des titres
//(« Ma playlist » seulement pour un utilisateur connecté qui en a une)
    $resultat = [];
    foreach (array_keys(PLAYER_FULL_CONTEXTES) as $id) {
        $titres = playerFullTitres($id);
        if (count($titres) > 0) {$resultat[$id] = $titres;}
    }
    return $resultat;
}

function playerFullIdPlaylist(): int {
    $idUser = (int)User::connectUserGetInfo('id', 0);
    return $idUser === 0 ? 0 : (new User($idUser))->idSetlist;
}

function playerFullTitres(int $idContexte): array {
//titres du contexte, dans l'ordre de la setlist pour un concert, une répétition ou la playlist
    $idPlaylist = $idContexte === 4 ? playerFullIdPlaylist() : 0;
    if ($idContexte === 4 && $idPlaylist === 0) {return [];}
    if ($idContexte === PLAYER_FULL_CONCERT) {return Song::mdSongGetListeLecteur([Song::TYPE_CONCERT], TypeLien::MP3ORI);}
    return ctPlayerGetListeSongs($idContexte, TypeLien::MP3ORI, $idPlaylist);
}

function playerFullLibelle(int $idContexte): string {
//nom de la setlist du contexte (prochain concert, prochaine répétition, playlist)
    if ($idContexte === PLAYER_FULL_CONCERT) {return "";}
    return (string)ctLibelleLongContexte($idContexte, $idContexte === 4 ? playerFullIdPlaylist() : 0);
}
