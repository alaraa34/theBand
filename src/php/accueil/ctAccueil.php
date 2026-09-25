<?php
declare(strict_types=1);
namespace theBand\src\php\accueil;


use shared\php\classes\socle\Login as Login;
use chords\php\Chords as Chords;
use shared\php\toolbox\Toolbox_adressage as TbAdressage;
use shared\php\toolbox\Toolbox as Tbx;
 
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
function ctRAZ() {
//efface la base de donnée
//appel ?fct=RAZ&ctr=accueil
    require_once(ROOT_PATH . 'shared/php/database/ctUtilitaires.php');
    \shared\php\database\viderTablesAvecPrefixe(exclusions: ['nomenclature','menu','user','requete','parametre','lien_type','lien_type_usage']);
    echo("Terminé");
}

function ctRenommer() {
//attention dans index il faut que le prefixe soit à blanc
//renomme toutes les tables : une fois fait il faut changer le nom du préfixe dans index
//appel https://kontouma.fr/theBand/index.php?fct=renommer&ctr=accueil
    $fichier = ROOT_PATH . 'shared/php/database/ctUtilitaires.php';
    if (file_exists($fichier)) {
        require_once($fichier);
        \shared\php\database\ajouterPrefixeTables(prefixe:"tb_" );
        echo("Terminé");
    }
    else{die("fichier ". $fichier . "introuvable");}
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



