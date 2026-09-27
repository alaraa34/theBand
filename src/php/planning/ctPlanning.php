<?php
declare(strict_types=1);
namespace theBand\src\php\planning ;
/**
 * Description of Planning
 *
 * @author araib
 */

use theBand\src\php\socle\UserTheBand       as User;
use shared\php\toolbox\Toolbox              as Tbx;
use shared\php\classes\socle\Login          as Login;
use shared\php\toolbox\Toolbox_date         as TbDate;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;

function ctEditerPlanningIndividuel(){
    $messageBarreMenu = "Planning individuel";
    $listeDispo = Planning::mdGetListeDisponibilite(User::connectUserGetInfo("id"));
    
    //suppression des dispo antérieures à la date du jour
    $retour = Planning::mdSupprimerDisponibiliteEchues();
    
    //mise en forme des dispos pour json
    $listeMEF = "";
    $script = Tbx::includeJS('planning','theBand/src') .  Tbx::includeJS('ajax');
    $css = Tbx::includeCSS('planning','theBand/src');
    
    //url du controleur pour la mise à jour
    $url = TbAdressage::getURLstatic("planning","majPlanning");
    //l'affichage est géré dans planning js
    foreach($listeDispo as $dispo){
        //date sous forme "2024-03-27"
        $listeMEF .= $dispo["disponible"].$dispo["jour"] . ";";
    }
    if (Login::loginControl(__NAMESPACE__)){ require('tpPlanningPerso.php');}
}
function ctEditerPlanningPeriode(){
 // affiche la saisie de planning par Période
    $user = User::getUserConnecte();
    $script =  Tbx::includeJS(['liste','ajax']);
    if (Login::loginControl(__NAMESPACE__)){require('tpPlanningPersoPeriode.php');}
}

function ctPlanningPeriodeMAJ(){
//retour de l'interface de saisie des périodes
    //Récupération des plages de dates, action au poste 0
    $retour = true;
    $periodes = TbAdressage::getValeurPostTableau(['action&S','dateDebut&S' ,'dateFin&S','statut&S','Lu&S','Ma&S','Me&S','Je&S','Ve&S','Sa&S','Di&S'],0);
    foreach ($periodes as $periode){
        $dateJour=$periode['dateDebut'];
        while ($dateJour <= $periode['dateFin']){
            //disponibilité sous forme D01-10-2024 ou I01-10-2024 ou N01-10-2024
            $jour = (int) date('w', strtotime($dateJour));
            $jourSemaine = TbDate::jourDeLaSemaine($jour, 2,1);
            if (isset($periode[$jourSemaine]) and $periode[$jourSemaine] == 'on'){
                if($retour){$retour = Planning::mdDisponibiliteMAJ($periode['action'] . $dateJour);}
            }
            $dateJour = date('Y-m-d', strtotime($dateJour. ' + 1 days'));
        }
    }
    $content = Tbx::messageColorer($retour,"Planning mis à jour","Erreur lors de la mise à jour");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctAfficherPlanningCollectif(){
    $messageBarreMenu = "Planning du groupe";
    $users = User::listeTous();
    //mise en forme des dispos pour json
    $script = Tbx::includeJS('ajax');
     $css = Tbx::includeCSS('planning','theBand/src');
    //url du controleur pour la mise à jour quand on clique sur le chagment de dat
    $urlControleur = TbAdressage::urlControleur("planning", "visuPlanning");
    //affichage à partir date du jour
    $planningGroupe = Planning::genererPlanningGroupe(date("Y-m-d"),$users);
    require('tpPlanningEvenement.php');
}


function ctVisuPlanning(){
//appelé en cas de changement de date du planning général, gère l'action "visuPlanning"
    $output = Planning::genererPlanningGroupe((string)filter_input(INPUT_POST,"dateDebutPlanning"),User::listeTous());
    //echo (json_encode($output, JSON_FORCE_OBJECT));
    echo ($output);
    die();
}

function ctMAJplanning(){
//Récupère des disponibilité et met à jour la disponibilité passée
    $retour = Planning::mdDisponibiliteMAJ((string)filter_input(INPUT_POST,"changements"));
    http_response_code(200);
}