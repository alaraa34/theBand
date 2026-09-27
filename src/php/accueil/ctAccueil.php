<?php
declare(strict_types=1);
namespace theBand\src\php\accueil;


use shared\php\classes\socle\Login as Login;
use shared\php\classes\socle\User as User;
use chords\php\Chords as Chords;
use shared\php\toolbox\Toolbox_adressage as TbAdressage;
use shared\php\toolbox\Toolbox as Tbx;
use shared\php\classes\personalisation\Parametre as Parametre;

function ctAccueil() {
    $mode = "accueil";
    require('tpAccueil.php');
}

 
function ctLoginFromToolbar() {
//réception du bouton d'authentification de la barre d'outils
// déconnexion si clic sur se déconnecter
    Login::loginControlDemand(__NAMESPACE__);
}

function ctLoginUser() {
//réception du formulaire d'authentification 
    Login::loginControlReception(__NAMESPACE__);
}

function ctEnConstruction() {
//affichage emplate en construction
    $mode = "enConstruction";
    require('accueil.php');
}
function accesAdministrateur(): bool {
//true si un administrateur est connecté, sinon affiche un message de refus
    if (User::estAdministrateur()) {
        return true;
    }
    $content = Tbx::messageColorer(false, messageKO: "Action réservée à un administrateur connecté.");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
    return false;
}

function ctRAZ() {
//efface la base de donnée
//appel ?fct=RAZ&ctr=accueil  - réservé à un administrateur connecté
    if (!accesAdministrateur()) {return;}
    require_once(ROOT_PATH . 'shared/php/database/ctUtilitaires.php');
    \shared\php\database\viderTablesAvecPrefixe(exclusions: ['nomenclature','menu','user','requete','parametre','lien_type','lien_type_usage']);
    echo("Terminé");
}

function ctRenommer() {
//attention dans index il faut que le prefixe soit à blanc
//renomme toutes les tables : une fois fait il faut changer le nom du préfixe dans index
//appel https://kontouma.fr/theBand/index.php?fct=renommer&ctr=accueil  - réservé à un administrateur connecté
    if (!accesAdministrateur()) {return;}
    $fichier = ROOT_PATH . 'shared/php/database/ctUtilitaires.php';
    if (file_exists($fichier)) {
        require_once($fichier);
        \shared\php\database\ajouterPrefixeTables(prefixe:"tb_" );
        echo("Terminé");
    }
    else{die("fichier ". $fichier . "introuvable");}
}

function ctManifest() {
//manifeste PWA (application installable sur smartphone)
//appel index.php?ctr=accueil&fct=manifest depuis tpLayout - le nom du groupe vient de GENERAL/NOMGROUPE
    $nomGroupe = (string)Parametre::mdParametreGetDetail("GENERAL", "NOMGROUPE", "valeurA");

    $manifeste = [
        'name'             => $nomGroupe !== '' ? "theBand – " . $nomGroupe : "theBand",
        'short_name'       => $nomGroupe !== '' ? $nomGroupe : "theBand",
        'description'      => "Organisation du groupe : répertoire, setlists, concerts, planning.",
        'lang'             => "fr",
        'start_url'        => "./",
        'scope'            => "./",
        'display'          => "standalone",
        'orientation'      => "portrait",
        'background_color' => "#b76533",
        'theme_color'      => "#b76533",
        'icons' => [
            ['src' => "icons/icon-192.png",          'sizes' => "192x192", 'type' => "image/png"],
            ['src' => "icons/icon-512.png",          'sizes' => "512x512", 'type' => "image/png"],
            ['src' => "icons/icon-512-maskable.png", 'sizes' => "512x512", 'type' => "image/png", 'purpose' => "maskable"],
        ],
    ];
    header('Content-Type: application/manifest+json; charset=utf-8');
    echo json_encode($manifeste, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit; //pas de layout
}

function ctAccordDetecter(){
    $content = Chords::visualise();
    $script =  Tbx::includeJS(['piano_chords'],"chords");
    $css = Tbx::includeCSS(['piano_chords'],"chords");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctAccordIdentifier(){
    $content = Chords::detect();
    $script =  Tbx::includeJS(['piano_chords'],"chords");
    $css = Tbx::includeCSS(['piano_chords'],"chords");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctGammesEtModes(){
    $content = Chords::scales();
    $script =  Tbx::includeJS(['piano_chords'],"chords");
    $css = Tbx::includeCSS(['piano_chords'],"chords");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}



