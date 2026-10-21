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

//contextes et listes de titres : repris du player MP3
require_once __DIR__ . '/ctPlayerMp3.php';

//contextes proposés dans le combo (numéros de ctPlayerGetListeSongs)
const PLAYER_FULL_CONTEXTES = [0=>"Prochain concert", 1=>"Prochaine répétition", 2=>"Titres en test",
                               3=>"Titres concert et test", 4=>"Ma playlist"];
const PLAYER_FULL_CONTEXTE_DEFAUT = 0;

function ctPlayerFull(): void {
//page du player complet, ouverte sur le prochain concert et son premier titre
    $contextes = playerFullContextes();
    $idContexte = PLAYER_FULL_CONTEXTE_DEFAUT;
    $titres = playerFullTitres($idContexte);
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
function playerFullContextes(): array {
//choix du combo ; « Ma playlist » seulement pour un utilisateur connecté qui en a une
    $choix = [];
    foreach (PLAYER_FULL_CONTEXTES as $id => $libelle) {
        if ($id === 4 && playerFullIdPlaylist() === 0) {continue;}
        $choix[] = ['identifiant'=>$id, 'zone'=>$libelle];
    }
    return $choix;
}

function playerFullIdPlaylist(): int {
    $idUser = (int)User::connectUserGetInfo('id', 0);
    return $idUser === 0 ? 0 : (new User($idUser))->idSetlist;
}

function playerFullTitres(int $idContexte): array {
//titres du contexte, dans l'ordre de la setlist pour un concert, une répétition ou la playlist
    $idPlaylist = $idContexte === 4 ? playerFullIdPlaylist() : 0;
    if ($idContexte === 4 && $idPlaylist === 0) {return [];}
    return ctPlayerGetListeSongs($idContexte, TypeLien::MP3ORI, $idPlaylist);
}

function playerFullLibelle(int $idContexte): string {
//nom de la setlist du contexte (prochain concert, prochaine répétition, playlist)
    return (string)ctLibelleLongContexte($idContexte, $idContexte === 4 ? playerFullIdPlaylist() : 0);
}
