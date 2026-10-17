<?php
namespace theBand\src\php\song;
/*******************************************************************************
 * Controleur du lecteur multipiste : génération des pistes et points d'entrée de l'agent
 * adresses construites par shared MultipisteControle (dom=song, ctr=multipiste)
 ******************************************************************************/
use shared\php\classes\lien\Multipiste                 as Multipiste;
use shared\php\classes\lien\MultipisteControle         as MultipisteControle;
use shared\php\classes\socle\User                      as User;
use shared\php\toolbox\Toolbox                         as Tbx;
use shared\php\classes\socle\Login                     as Login;
use shared\php\toolbox\Toolbox_adressage               as TbAdressage;

//==================================================================================================
// LECTEUR MULTIPISTE : génération des pistes (page réservée aux administrateurs)
// Le calcul est fait par l'agent Python agent_pistes.py sur un PC (voir shared/outils/multipistes)
//==================================================================================================
function ctGenererPistesLecteur() {
//liste des titres concert et test avec l'état de génération des pistes de leurs MP3
    pistesAfficherListe(false);
}

function ctGenererPistesATransposer() {
//même liste limitée aux titres à transposer : MP3 original avec une tona de scène différente de 0
//dont les pistes ne sont pas encore générées dans cette tonalité
    pistesAfficherListe(true);
}

function pistesAfficherListe(bool $aTransposerSeulement) {
//page commune (shared MultipisteControle) avec les titres concert et test de theBand
    if (!pistesAccesAdministrateur()) {return;}
    $lignes = Repertoire::mdSongGetListeAvecLiens([Repertoire::TYPE_CONCERT, Repertoire::TYPE_TEST], Multipiste::TYPES_SOURCE);
    foreach ($lignes as $index => $ligne) {
        $lignes[$index]['tona'] = Repertoire::tonaAttendue((int)$ligne['idTypeLien'], $ligne['tonaScene']);
    }
    ['content'=>$content, 'script'=>$script, 'messageBarreMenu'=>$messageBarreMenu] =
        MultipisteControle::pageGeneration($lignes, $aTransposerSeulement, ['correction'=>'tona de scène', 'jou'=>'groupe']);
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctPistesDemanderLien(int $idLien = 0) {
//clic sur l'icône d'un MP3 : file d'attente avec la transposition de la tona de scène (MP3 original)
    if (!pistesAccesAdministrateur()) {return;}
    $lien = Repertoire::mdLienMP3($idLien);
    MultipisteControle::demanderLien($idLien, $lien['url'], $lien['tona'], (int)User::connectUserGetInfo('id'));
}

function pistesAccesAdministrateur(): bool {
//true si un administrateur est connecté, sinon affiche la connexion ou un refus
    if (!Login::loginControl(__NAMESPACE__)) {return false;}
    if (User::estAdministrateur()) {return true;}
    $content = Tbx::messageColorer(false, messageKO: "Page réservée à un administrateur.");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
    return false;
}

//--------------------------------------------------------------------------------------------------
// Points d'entrée de l'agent (POST, réponse JSON, sans session ni layout)
// Tous reçoivent cle (contenu de ENVIRagent.txt) et agent (nom du PC)
//--------------------------------------------------------------------------------------------------
function ctPistesAgentProchain() {
    MultipisteControle::agentProchain();
}

function ctPistesAgentRecevoir() {
    MultipisteControle::agentRecevoir();
}

function ctPistesAgentTerminer() {
    MultipisteControle::agentTerminer();
}
