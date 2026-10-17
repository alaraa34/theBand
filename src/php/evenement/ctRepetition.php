<?php
declare(strict_types=1);
namespace theBand\src\php\evenement;
/*******************************************************************************
 * Controleur des répétitions (domaine evenement, ctr=repetition)
 *******************************************************************************/
require_once __DIR__ . '/ctEvenement.php';

use shared\php\modale\Toolbox_modal                as TbModal;
use shared\php\classes\lien\TypeLien               as TypeLien;
use shared\php\classes\socle\Login                 as Login;
use theBand\src\php\socle\UserTheBand              as User;
use shared\php\toolbox\Toolbox                     as Tbx;
use shared\php\toolbox\Toolbox_adressage           as TbAdressage;
use theBand\src\php\etablissement\Etablissement    as Etablissement;
use theBand\src\php\song\Song                      as Song;
use theBand\src\php\song\Detail                    as Detail;
use theBand\src\php\song\Performance               as Performance;
use shared\php\classes\socle\Commentaire           as Commentaire;

//------------------------------------------------------------------------------
//REPETITIONS
//------------------------------------------------------------------------------
function ctRepetitionConfirmer(){
//confirmation de la réservation par le studio suite à modale
    $repetition = new Evenement_Repetition();
    $repetition->id = TbModal::modalGetIdFromModal();
     //changement du statut à confirme
    $repetition->majConfirme();
    unset ($repetition);
    //message
     header('Location: ' . (string)filter_input(INPUT_SERVER,'HTTP_REFERER'));
}

function ctRepetitionProchaine():void{
    ctListeRepetition(Evenement::PROCHAIN);
}

function ctRepetitionListe():void{
    //suppression des anciennes répétitions (de plus d'un mois)
    Evenement_Repetition::supprimerEvtsAnciens();
    //liste répétitions
    ctListeRepetition(Evenement::TOUS);
}
function ctRepetitionSupprimer(){
    $idEvt = TbModal::modalGetIdFromModal(); // issue de la modale
    $evt = new Evenement_Repetition($idEvt);
    $retour = $evt->delete();  
    unset($evt);
    //affichage 
    $content = Tbx::messageColorer($retour,"Répétition supprimée","Erreur lors de la suppresssion");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctListeRepetition(int $prochain) :void{
    $lienMisEnForme = true;
    $typesLien= TypeLien::listePourUnSujet(Evenement_Repetition::SUJET_LIEN);
    if ($prochain){$texte = "Prochaine ";}
    else{$texte = "Liste ";}
    $evenements = Evenement_Repetition::getListe($prochain);
    $texte .= "répétition";
    $actions = actionsEVTRepetition();
    ctListeEvenements($evenements,$lienMisEnForme,$typesLien,$texte,$actions);
}

function ctRepetitionPrintSetList(int $idEvt) {
//affiche en plein écran la set list d'une répétition pour impression, action printsetlist
    $evt = new Evenement_Repetition($idEvt);
    $messageBarreMenu = "Pour l'impression utiliser la fonction imprimer du navigateur";
    $content = $evt->listerSetlist();
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctRepetitionCopier(int $id){
    $repetition = new Evenement_Repetition($id);
    $repetition->id = 0;
    $repetition->dateHeure = "";
    $repetition->setlist->id = 0;
    $repetition->commentaire = new Commentaire();
    ctRepetitionEditer1 ($repetition);
}

function ctRepetitionEditer(int $id=0) {
//Saisie et mise à jour d'une répétition
    $repetition = new Evenement_Repetition($id);
    ctRepetitionEditer1 ($repetition);
}
function ctRepetitionEditer1(Evenement_Repetition $repetition) {
//Saisie et mise à jour d'une répétition
    //liste des songs éligibles (concert ou test )
    $songs = Song::getListeChoix(array(Song::TYPE_CONCERT,Song::TYPE_TEST));
    //liste des studios de répétition
    $studios = Etablissement::ListeSelection(Evenement_Repetition::NATURE);
    $script = Tbx::includeJS('ajax'). Tbx::includeJS('song','theBand/src');
    if ($repetition->id==0){
        //Ajout d'une nouvelle répet
        $messageBarreMenu = "Nouvelle répétition";
        //affichage saisie ou login
         if (Login::loginControl(__NAMESPACE__)){require('tpRepetitionDetail.php');}
    }
    else{
        $messageBarreMenu = "Modification d'une répétition";
        //affichage saisie 
        require('tpRepetitionDetail.php');
    }
}

function ctRepetitionSelectSongAuto(){
//retour du clic pour alimenter automatiquement la liste de répétition
    //un seul critère possible : radios exclusifs de name "selectionAuto"
    switch (TbAdressage::getPost("S","selectionAuto")){
        case "titresPerformance":
            //les nb titres ayant la plus petite performance
            $idSongs = Performance::mdSongGetListeID(TbAdressage::getPost("I","titresNbPerf"));
            break;
        case "titresProchainConcert":
            //titres de la setlist du prochain concert
            $idSongs = Song::mdSongListeIDFromSetList(Evenement_Concert::getIdSetListProchainEvt());
            break;
        case "titresRepetPrecedente":
            //titres de la dernière répétition antérieure à la date du jour
            $idSongs = Song::mdSongListeIDFromSetList(Evenement_Repetition::getIdSetListPrecedentEvt());
            break;
        case "titresTest":
            //titres en test
            $idSongs = Song::mdSongListeIDFromType(Song::TYPE_TEST);
            break;
        default:
            //aucun critère coché
            $idSongs = [];
    }
    //retour des ids sans doublon "12-15-18" : song.js (jsRepetSelectionAuto) remplace la sélection
    //et recalcule la liste des titres disponibles
    echo implode("-", array_unique(array_map('intval', $idSongs)));
}

function ctRepetitionMAJ() {
// suppression des evts de plus d'une an
    
//mise à jour de la base après validation du formulaire
    $repetition = new Evenement_Repetition();
    $repetition->id  = TbAdressage::getPost("I","idRep");
    $repetition->dateHeure = TbAdressage::getPost("S","dateRep") . " " . TbAdressage::getPost("S","heureRep"). ":00";
    $repetition->dateHeureFin = TbAdressage::getPost("S","dateRep") . " " . TbAdressage::getPost("S","heureRepFin"). ":00";
    $repetition->etablissement->id = TbAdressage::getPost("I","lieuRep");
    $repetition->setlist->id = TbAdressage::getPost("I","idSetlist");
    $repetition->setlist->nomListe = "Répétition du " . $repetition->dateHeure;
    $repetition->setlist->idNature = $repetition::NATURE;
    $repetition->confirme = TbAdressage::getPost("C", "controle");
    $repetition->commentaire->id = TbAdressage::getPost("I","idCommentaire");
    $repetition->commentaire->texte = TbAdressage::getPost("S","commentaire") ;
    //lien song, retourne un champ contenant les identifiants
    $numero = 0;
    foreach (explode("-", TbAdressage::getPost("S","idSongsSelected")) as $idSong){
        if ((int)$idSong === 0){continue;} //aucun titre choisi : explode renvoie [""]
        $numero +=1;
        $detail = new Detail();
        $detail->song->id = (int)$idSong;
        $detail->ordre = $numero;
        $detail->partie = 1;
        $repetition->setlist->details[] = $detail;
        unset ($detail);
    }
            
//mise à jour BDD
    $retour = $repetition->update();
    //fin
    $content = Tbx::messageColorer($retour,"Répétition enregistrée");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function actionsEVTRepetition(){
    $actions = [];
    $actions[]= ['texte'=> 'Jouer playlist','logoClass' =>'bi bi-file-earmark-music','href'=>'player/playerMp3;uneRepetition;player'];
    $actions[]= ['texte'=> 'Confirmer réservation','logoClass' =>'bi bi-calendar2-check',
                        'modale'=>'evenement/repetition;confirmer','message' => 'La réservation a été validée par le studio'];
    if(User::userConnecte()){
    $actions[] = ['texte'=> 'Modifier','logoClass' =>'fa-regular fa-pen-to-square','href'=>'evenement/repetition;editer'];
    $actions[] = ['texte'=> 'Copier','logoClass' =>'bi bi-copy','href'=>'evenement/repetition;copier'];
    $actions[] = ['texte' => 'Supprimer','logoClass' => 'bi bi-trash','modale'=>'evenement/repetition;supprimer',
                        'message'=> 'Supprimer la répétition ?'];
    }
    return $actions;
}
