<?php
declare(strict_types=1);
namespace theBand\src\php\evenement;
/*******************************************************************************
 * Controleur des deux types d'évènements, concerts et répétition
 *
 * @author Alara
 *******************************************************************************/

use shared\php\modale\Toolbox_modal                as TbModal;
use shared\php\bricks\Brick_textbox                as BkTextbox;
use shared\php\classes\lien\TypeLien               as TypeLien;
use shared\php\classes\socle\Login                 as Login;
use theBand\src\php\socle\UserTheBand              as User;
use shared\php\toolbox\Toolbox                     as Tbx;
use shared\php\toolbox\Toolbox_adressage           as TbAdressage;
use theBand\src\php\etablissement\Etablissement    as Etablissement;
use theBand\src\php\song\Song                      as Song;
use theBand\src\php\song\Setlist                   as Setlist;
use theBand\src\php\song\Detail                    as Detail;



//------------------------------------------------------------------------------
//EVENEMENT
//------------------------------------------------------------------------------
function ctListeEvenements(array $evenements,bool $lienMisEnForme,array $typesLien, string $messageBarreMenu,array $actions):void {
//Liste des concerts ou répétitions
    //affichage 
    $script = Tbx::includeJS('utils');
    switch (count($evenements)){
        case 0:
            $content = Tbx::messageColorer(false,"","Aucun évènement à venir");
            require(TbAdressage::projetGetLayout(__NAMESPACE__));
            $afficherSetlist = false;
            break;
        case 1 :
            $afficherSetlist = false; //accordéon déployé
            require('tpEvenementListe.php');   
            break;
        default :
            $afficherSetlist = true; //accordéons fermés
            require('tpEvenementListe.php');    
    }
}

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
    ctListeRepetition(evenement::PROCHAIN);
}

function ctRepetitionListe():void{
    //suppression des anciennes répétitions (de plus d'un mois)
    Evenement_Repetition::supprimerEvtsAnciens();
    //liste répétitions
    ctListeRepetition(evenement::TOUS);
}
function ctRepetitionSupprimer(){
    $idEvt = TbModal::modalGetIdFromModal(); // issue de la modale
    $evt = new Evenement_Concert($idEvt);
    $retour = $evt->delete();  
    unset($evt);
    //affichage 
    $content = Tbx::messageColorer($retour,"Répétition supprimée","Erreur lors de la suppresssion");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctListeRepetition(int $prochain) :void{
    $lienMisEnForme = true;
    $typesLien= TypeLien::listePourUnSujet(Evenement_Repetition::SUJET);
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
    $repetition->commentaire = new commentaire();
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
    $songs = song::getListeChoix(array(Song::TYPE_CONCERT,Song::TYPE_TEST));
    //liste des studios de répétition
    $studios = etablissement::ListeSelection(Evenement_Repetition::NATURE);
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
    $idSongs = [];
    //recherche des nb titres ayant la plus petite performance
    if(TbAdressage::getpost("C","titresPerformance")){
        $ids = Performance::mdSongGetListeID(getpost("I","titresNbPerf"));
        $idSongs = array_merge_recursive($idSongs,$ids);
    }
    //Recherche des titres du prochain concert
    if(TbAdressage::getpost("C","titresProchainConcert")){
        $ids = Song::mdSongListeIDFromSetList(Evenement_Concert::getIdSetListProchainConcert());
        $idSongs = array_merge_recursive($idSongs,$ids);
    }
    //Recherche des titres en test
    if(TbAdressage::getpost("C","titresTest")){//tites en test
        $ids = Song::mdSongListeIDFromType(song::TYPE_TEST);
        $idSongs = array_merge_recursive($idSongs,$ids);
    }
    //suppression des doublons
    $idSongs =  array_unique($idSongs);
    
    //Recherche de la liste des titres possibles (test + concert)
    $songs = Song::getListeChoix(array(repertoire::TYPE_CONCERT,repertoire::TYPE_TEST));
    //pose de selected sur les titres concernés et constitution liste avec déclenchement de click
    $myhtml = "click££ID££" . shared\php\toolbox\Toolbox_liste::valeursChoixListe($songs,
                                            zone:"titre",
                                            selected:$idSongs);
    echo ($myhtml);
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
    $actions[]= ['texte'=> 'Jouer playlist','logoClass' =>'bi bi-file-earmark-music','href'=>'player;uneRepetition'];
    $actions[]= ['texte'=> 'Confirmer réservation','logoClass' =>'bi bi-calendar2-check',
                        'modale'=>'evenement;confirmer;repetition','message' => 'La réservation a été validée par le studio'];
    if(User::userConnecte()){
    $actions[] = ['texte'=> 'Modifier','logoClass' =>'fa-regular fa-pen-to-square','href'=>'evenement;Editer;repetition'];
    $actions[] = ['texte'=> 'Copier','logoClass' =>'bi bi-copy','href'=>'repetition;Copier'];
    $actions[] = ['texte' => 'Supprimer','logoClass' => 'bi bi-trash','modale'=>'evenement;supprimer;repetition',
                        'message'=> 'Supprimer la répétition ?'];
    }
    return $actions;
}

//------------------------------------------------------------------------------
//SPECIFIQUE CONCERT 
//------------------------------------------------------------------------------
function ctConcertSupprimer(){
    $idEvt = TbModal::modalGetIdFromModal(); // issue de la modale
    $evt = new Evenement_Concert($idEvt);
    $retour = $evt->delete();  
    unset($evt);
    //affichage 
    $content = Tbx::messageColorer($retour,"Concert supprimé","Erreur lors de la suppresssion");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function actionsEVTConcert(){
    $actions =[];
    $actions[] = ['texte'=> 'Jouer playlist','logoClass' =>'bi bi-file-earmark-music','href'=>'player;UnConcert'];
    $actions[] = ['texte'=> 'Confirmer la date de concert','logoClass' =>'bi bi-calendar2-check',
                'modale'=>'evenement;confirmer;concert','message' => 'Le concert est confirmé ?'];
    $actions[] = ['texte'=> 'Imprimer playlist','logoClass' =>'bi bi-printer','href'=>'evenement;PrintSetList;concert'];
    
    if(User::connectUserGetInfo("id",0) > 0){
        $actions[] =['texte'=> 'Modifier','logoClass' =>'fa-regular fa-pen-to-square','href'=>'evenement;concertEditer'];
        $actions[] =['texte'=> 'Modifier playlist','logoClass' =>'bi bi-music-note-list','href'=>'song;setlistEditer'];
        $actions[] =['texte' => 'Supprimer','logoClass' => 'bi bi-trash','modale'=>'evenement;supprimer;concert',
                        'message'=> 'Supprimer le concert ?'];
    }
   
    return $actions;
}

function ctConcertConfirmer(){
//confirmation de la date du concert
    $concert= new Evenement_Concert();
    $concert->id = TbModal::modalGetIdFromModal();
     //changement du statut à confirme
    $concert->majConfirme();
    //message
     header('Location: ' . (string)filter_input(INPUT_SERVER,'HTTP_REFERER'));
}

function ctConcertProchain():void{
    ctListeConcert(evenement::PROCHAIN);
}

function ctConcertListe():void{
    //suppression des anciennes répétitions (de plus d'un mois)
    Evenement_Concert::supprimerEvtsAnciens();
    //liste répétitions
    ctListeConcert(evenement::TOUS);
}

function ctListeConcert(int $prochain) :void{
    $lienMisEnForme = false;
    $typesLien=  TypeLien::listePourUnSujet(Evenement_Concert::SUJET);
    if ($prochain){$texte = "Prochain ";}
    else{$texte = "Liste ";}
    $evenements = Evenement_Concert::getListe($prochain);
    $texte .= "concert";
    // action spossibles
    $actions = actionsEVTConcert();
    ctListeEvenements($evenements,$lienMisEnForme,$typesLien,$texte,$actions);
}

function ctConcertPrintSetListProchain() {
//affiche en plein écran la set list d'un concert pour impression, action printsetlist
    $idevt = Evenement_Concert::getIdProchain();
    if ($idevt === 0){
        $content = Tbx::messageColorer(false,"","Aucun concert à venir");
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
    else {
        ctConcertPrintSetList($idevt);
    }
}

function ctConcertPrintSetList(int $idEvt) {
//affiche en plein écran la set list d'un concert pour impression, action printsetlist
    $evt = new Evenement_Concert($idEvt);
    $messageBarreMenu = "Pour l'impression utiliser la fonction imprimer du navigateur";
    $content = $evt->listerSetlist();
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctConcertEditSetListProchain(){
    $idevt = Evenement_Concert::getIdProchain();
    if ($idevt === 0){
         $content = Tbx::messageColorer(false,"","Aucun concert à venir");
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
    else{
        ctSetlistEditer($idevt);
    }
}


function ctConcertEditer(int $id=0, $idEtablissement=0) {
//Ajout/Modification d'un concert
    $concert = new Evenement_Concert($id);
    
    $etablissements = Etablissement::ListeSelection(Evenement_Concert::NATURE);
    $modelesSL = Setlist::listerSetlistConcert($concert->setlist->id);
     //JS et CSS
    $script = Tbx::includeJS(array('ajax','textbox'));
    $css = Tbx::includeCSS('textbox');
    $textbox = new BkTextbox();
    $textbox->setTexte($concert->commentaire->texte);
    $textbox->setTexteDefaut($concert::initFeuilleDeRoute());
    if ($id==0){
        //Ajout d'un  nouveau concert
        $messageBarreMenu = "Nouveau concert";
        
        //Action et paramètres pour modale établissement
        $actions =  [['texte' => 'Nouvel établissement',
                    'modale'=>'etablissement;ajouter',
                    'template'=> 'theBand\src\php\etablissement\tpEtablissementDetailMDL.php',
                    'typesEtab'=>Etablissement::listeDesTypes()]];
                      
        //affichage saisie ou login
        if(Login::loginControl(__NAMESPACE__)){require('tpConcertDetail.php');}
    }
    else{
        $messageBarreMenu = "Modification d'un concert";
        //affichage saisie ou login
        require('tpConcertDetail.php');
    }
}


function ctConcertMAJ(){
//maj MPD suite saisie concert   
    $messageBarreMenu = "";
    $concert = new Evenement_Concert();
    
    $concert->etablissement->id = TbAdressage::getPost("I","lieuConcert");
    //Chargement du nom pour intitulé setlist
    $concert->etablissement->loadFromIdRestreint();
    $concert->id = TbAdressage::getPost("I","idConcert");
    $concert->dateHeure = TbAdressage::getPost("S","dateConcert") . " " . TbAdressage::getPost("S","heureConcert"). ":00";
    $concert->dateHeureFin = TbAdressage::getPost("S","dateRep") . " " .  "00:00:00";
    $concert->commentaire->id = TbAdressage::getPost("I","idCommentaire");
    $concert->commentaire->texte = BkTextbox::getTexte();
    
    $concert->setlist->id = TbAdressage::getPost("I","idSetlist");
    $concert->setlist->nomListe = "Concert du " . $concert->dateHeure . " à " . $concert->etablissement->getNom();
    $concert->setlist->idNature = $concert::NATURE;
    //si id setlist copié rensigné et différent de la set list d'origine  
    if (TbAdressage::getPost("I","modeleSL")>0){
       //case cochée quans elle vaut sa value, non retournée si pas cochée
       //réinitialisation de la setlist par celle citée en référence
        $concert->setlist->initialiserParAutreSetList(TbAdressage::getPost("I","modeleSL"));
    }
    $retour = $concert->update();
    $content = Tbx::messageColorer($retour,"Concert traité","Erreur, réessayer !");
     //Affichage
     require(TbAdressage::projetGetLayout(__NAMESPACE__));
}
//------------------------------------------------------------------------------
//SETLIST
//------------------------------------------------------------------------------

function ctSetlistEditer($idEvt){
//Modif d'une playlist avec drag and drop. Action editSetList
    // Paramètre la liste des types de songs à afficher
    $evt = new Evenement_Concert($idEvt);
    $messageBarreMenu = "Modification de Playlist";
    $evt->setlist->loadPartie0();
    $script = Tbx::includeJS(['jquery_ui']) . Tbx::includeJS(['concert'],'theBand/src');
    $css = Tbx::includeCSS(['concert'],'theBand/src');
    //affichage sans actions
    if(Login::loginControl(__NAMESPACE__)){require('concertBuildSetlist');}
}

function ctSetlistMAJ(){
//mise à jour MPD d'une set list, evenement majSetlist
//suite à modification drang drop
    //set list pas initialisée car pas besoin d'elle, juste de la collection
    $setlist = new setlist();
    $setlist->id = TbAdressage::getPost("I","idSetlist");
    //balayage des 3 parties (0 c'est les non utilisés)
    for ($i=1;$i<4;$i++){
        $zone = TbAdressage::getPost("S","listePartie" .$i);
        if (strlen($zone)>0) {
            foreach(explode("-",$zone) as $song){
                $setlist->details[]=ctSetlistMajDetail($i,$song,count($setlist->details));
            }
        }
    }
    //Mise à jour Physique
    $retour = $setlist->updateCollectionDetail();
    $content = Tbx::messageColorer($retour,"Setlist mise à jour","Erreur lors de la mise à jour");
     //Affichage
     require(TbAdressage::projetGetLayout(__NAMESPACE__));
}
function ctSetlistMajDetail(int $partie , string $song, int $lastOrdre) {
// Mets à jour une partie de set list
// le tableau d'Idsong contient soit idsong(ex 33) soit idsont + E si enchainement(ex33E)
    //la Suppression de la liste existante a été faite avant cet appel dans ctMajSetlist
    $detail = new Detail();
    $detail->partie = $partie;
    $detail->ordre = $lastOrdre +1;
    //strstr retourne false si trouve pas
    if (strstr($song,'E')){
           //true retourne la partie gauche
        $detail->song->id = intval(strstr($song, 'E', true));
        $detail->enchainement = true;
    }
    else{
        $detail->song->id = intval($song);
        $detail->enchainement = false;
    }
    
    return $detail;
}