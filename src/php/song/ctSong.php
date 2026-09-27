<?php
namespace theBand\src\php\song;
/*******************************************************************************
 * 
 ******************************************************************************/
use shared\php\classes\lien\Lien                       as Lien;
use shared\php\classes\lien\TypeLien                   as TypeLien;
use shared\php\classes\personalisation\Nomenclature    as Nomenclature;
use theBand\src\php\socle\UserTheBand                  as UserTB;
use shared\php\classes\socle\User                      as User;
use shared\php\toolbox\Toolbox                         as Tbx;
use shared\php\classes\socle\Login                     as Login;
use shared\php\toolbox\Toolbox_upload                  as TbUpload;
use shared\php\modale\Toolbox_modal                    as TbModal;
use shared\php\toolbox\Toolbox_date                    as TbDate;
use shared\php\toolbox\Toolbox_liste                   as TbListe;
use shared\php\bricks\Brick_table                      as BkTable;
use shared\php\toolbox\Toolbox_adressage               as TbAdressage;

function ctSongAdd():void {
    ctSongEditer();
}

function ctSongEditer(int $identifiant = 0) {
//actions addSong et editSong
     
    $typesLien = TypeLien::listePourUnSujet(Repertoire::SUJET);
    $correctionsTona = Nomenclature::mdNomenclatureGetListe("ECART_TONA","Nom","DESC","valeurN","Nom");
    $users = UserTB::listeMusiciens();
    //defaut on crée une instance concert (çà ne change rien)
    $song = new Repertoire($identifiant);
    $script =  Tbx::includeJS(['lien','liste','utils']);
    if ($song->id===0){
    //Saisie d'un nouveau titre avec contrlonnexion e de 
        $typesSong = Song::listeTypeSong();
        $messageBarreMenu = "Nouveau titre";
        
        if (Login::loginControl(__NAMESPACE__)){require('tpSongDetail.php');}
    }
    else{
    //Modification d'un titre existant
        $typesSong = song::listeTypeSong(true);
        $messageBarreMenu = "Modification d'un titre existant";
        //affichage
        require('tpSongDetail.php');
    }
}

function ctSongMAJ() {
//traitement de mise à jour base d'une song
    $song = new Repertoire();
    $song->id = TbAdressage::getPost("I", "idSong");
    $song->idTypeSong = TbAdressage::getPost("I","idTypeSong");
    $song->titre = TbAdressage::getPost("S","titre");
    $song->interprete = TbAdressage::getPost("S","interprete");
    $song->tonaOrigine = TbAdressage::getPost("S","tonaOrigine");
    $song->tonaScene = TbAdressage::getPost("S","tonaScene");
    $song->tempo = TbAdressage::getPost("I","tempo");
    $song->leadChant->id = TbAdressage::getPost("I","idLeadChant");
    $song->commentaire->id = TbAdressage::getPost("I","idCommentaire");
    $song->commentaire->texte = TbAdressage::getPost("S","commentaire");
    
    //retourne les liens avec leurs actions   pour les actions liens renseignés
    //Zones associatives 
    $song->chargerLiensParMouvements(TbAdressage::getValeurPostTableau(Lien::ZONES_MOUVEMENTS));
   
    //réception des fichiers
    $retour = TbUpload::uploadReceptionLien(); //ds Toolbox_upload, import des fichiers 
    
    //mise à jour de la base
    $retour = $song->update();
    $content = Tbx::messageColorer($retour,"Titre " . $song->titre . " traité avec succès");  
    unset($song);
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}    

function ctRepertoireRestreint(array $types, array $actions, string $messageBarreMenu){
//Liste d'une partie des morceaux test, concert, réserve, abandonné
//parametres    $types types de repertoire à afficher (convert, test....)
//              $actions : actions possibles
//              
    //Liste des titres 
    $infos = Repertoire::getListe($types,true);
          
    //Actions possibles
    $actionsListe = User::userConnecte() ? composerListeActions($actions) : [];
    $script = Tbx::includeJS('utils');
    //Paramétrage liste générique
     //zone = nom de la zone dans infos,
    //titre = titre à mettre , si rien c'est le nom de la zone 
    //fonction à appliquer
    $infosColonnes[] = ['zone'=>BkTable::COLONNE_NUMERO]; //Colonne numérotation
    $infosColonnes[] = ['zone'=>'titre','fonction'=> TbListe::FONCTION_VASN];
    $infosColonnes[] = ['zone'=>'interprete','titre'=>'Interprète','fonction'=>TbListe::FONCTION_VASN];
    $infosColonnes[] = ['zone'=> 'tonaOrigine','titre'=>'Tona<br>origine','fonction'=> TbListe::FONCTION_VASN,'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'tonaScene','titre'=>'Tona<br>concert','fonction'=>TbListe::FONCTION_VASN,'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'tempo','titre'=>'Tempo<br>(bpm)','fonction'=>TbListe::FONCTION_VASN,'align'=>'MC'];
    if (count($types)>1){$infosColonnes[] = ['zone'=> 'typeSong','titre'=>'type','fonction'=>TbListe::FONCTION_VASN];}
    $infosColonnes[] = ['zone'=> 'leadChant','titre'=>'chant','fonction'=>TbListe::FONCTION_VASN,'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'liens','fonction'=> TbListe::FONCTION_FALC,'parametres'=>array('$valeur',false),'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'texteCommentaire','titre'=>'Commentaire','fonction'=>TbListe::FONCTION_VASN];
    $content = TbListe::constituerListe($infos, $infosColonnes, $actionsListe);
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
    
}

function ctRepertoire() {
//Liste des chansons jouées par le groupe, pas d'action possible
// Paramètre la liste des types de songs à afficher
    ctRepertoireRestreint([Repertoire::TYPE_CONCERT, Repertoire::TYPE_TEST],[],"Titres joués en concert ou testés");
}
function ctRepertoireConcert() {
//Liste des chansons jouées par le groupe avec actions
    ctRepertoireRestreint([Repertoire::TYPE_CONCERT],['modifier','reserver'],"Titres joués en concert");
}
function ctRepertoireTest() {
//Liste des chansons jouées par le group
    ctRepertoireRestreint([Repertoire::TYPE_TEST],['modifier','valider','abandonner'],"Titres en cours de test");
}
function ctRepertoireReserve() {
//Liste des chansons jouées naguère par le group
    ctRepertoireRestreint([Repertoire::TYPE_RESERVE],['modifier','reprendre','abandonner'],"Titres déjà joué mais sortis de la playlist");
}
function ctRepertoireAbandon() {
//Liste des chansons jouées par le group
    ctRepertoireRestreint([Repertoire::TYPE_ABANDON],['supprimer','retester'],"Titres abandonnés (test négatif ou plus souhaité)");
}

function composerListeActions(array $actions){
    //Compose la liste des actions selon codes
    $listeA=[];
    foreach($actions as $action){
        switch ($action) {
            case 'modifier':
                $actionT = ['texte'=> 'Modifier le titre','logoClass' =>'fa-regular fa-pen-to-square','href'=>'song;editer'];
                break;
            case 'supprimer':
                $actionT = ['texte' => 'Supprimer le titre','logoClass' => 'bi bi-trash',
                    'modale'=>'song;supprimer','message' => 'Supprimer définitivement'];
                break;
            Case 'valider' :
                $actionT = ['texte' => 'Valider pour concert','logoClass' => 'fa-solid fa-microphone',
                    'modale'=>'song;valider','message' => 'Intégrer ce titre dans la playlist'];
                 break;
            Case 'retester' :
                $actionT = ['texte' => 'Retester', 'logoClass'=>'fa-solid fa-vial-circle-check',
                    'modale'=>'song;tester','message' => 'Remettre en test'];
                break;
            Case 'reprendre' :
                $actionT = ['texte' => 'Reprendre en concert','logoClass' => 'fa-solid fa-microphone',
                    'modale'=>'song;valider','message' => 'Remettre ce titre dans la playlist'];
                break;
            case 'abandonner' :
                $actionT= ['texte' => 'Abandonner', 'logoClass'=>'fa-solid fa-microphone-slash',
                    'modale'=>'song;abandonner','message' => 'Ce titre ne sera plus joué par le groupe ?'];
                break;
            case 'reserver':
                $actionT = ['texte' => 'Mettre en réserve', 'logoClass'=>'fa-solid fa-snowflake',
                    'modale'=>'song;reserver','message'=>'Sortir de la liste des titres joués en concert? <br>Attention ce titre sera supprimé de toutes les playlists des concerts à venir!'];
                break;
        }
 
        //ajout général
        $listeA[]= $actionT;
    }
    return $listeA;
}
function ctSongReserver(){
// Passe un titre en statut réserve, appelé par Modale
    $song = new Repertoire();
    $song->id = TbModal::modalGetIdFromModal();
    //changement du statut à mise en réserve
    $song->loadTitreSimple();
    $retour = $song->mdSongMajType(Repertoire::TYPE_RESERVE);
    //Suppression des set lste
    if ($retour){$retour = $song->mdSetlistRemoveSong();}
    //info
    $content = Tbx::messageColorer($retour,"Le titre " . $song->titre . " est mis en réserve"); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctSongAbandonner(){
// Passe un titre en statut Abandonné, appelé par la modale    
    $song = new Repertoire();
    $song->id = TbModal::modalGetIdFromModal();
     //changement du statut Abandonner
    $song->loadTitreSimple();
    $retour = $song->mdSongMajType(Repertoire::TYPE_ABANDON);
    //Suppression des set liste à venir
    if ($retour){$retour = $song->mdSetlistRemoveSong();}
    $content = Tbx::messageColorer($retour,"Le titre " . $song->titre . " est abandonné"); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctSongValider(){
// Passe un titre en statut valide (concert)
        //changement du statut à Validé
    $song = new Repertoire();
    $song->id = TbModal::modalGetIdFromModal();;
    $song->loadTitreSimple();
    $retour = $song->mdSongMajType(Repertoire::TYPE_CONCERT);
    $content = Tbx::messageColorer($retour,"Le titre " . $song->titre . " est entré dans la liste des titres pour concert"); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}
function ctSongTester(){
// Passe un titre en statut test
    $song = new Repertoire();
    $song->id = TbModal::modalGetIdFromModal();
    $song->loadTitreSimple();
     //changement du type à Validé
    $retour = $song->mdSongMajType(Repertoire::TYPE_TEST);
    $content = Tbx::messageColorer($retour,"Le titre " . $song->titre . " est entré dans la liste des titres en test"); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctSongSupprimer(){
    $song = new Repertoire(TbModal::modalGetIdFromModal()); // issue de la modale
    $retour = $song->delete();
    $content = Tbx::messageColorer($retour,"Titre " . $song->titre . " supprimé", "Erreur lors de la suppression du titre ");  
    //affichage 
    unset ($song);
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

/*******************************************************************************
 * Performances
 ******************************************************************************/
function ctRepertoireNotation(){
//notation de la performance du groupe
     $performances= Performance::listePourVotePerformance();
     $script =  Tbx::includeCSS('song','theBand/src');
     if(Login::loginControl(__NAMESPACE__)){require('tpSongListeVote.php');}
}

function ctRepertoireMajVotes() {
    //Enregistre les votes suite à une saisie
    $messageBarreMenu = "";
    //extrait les lignes qui ont bougé
    $mouvements = array_filter($_POST, function($v, $k) {
                                    return str_starts_with($k,"changevote") & $v == "M";
                                }, ARRAY_FILTER_USE_BOTH);
    //Balayage de toutes les propositions éligibles au vote 
    $idUser = User::connectUserGetInfo('id');
    foreach ($mouvements as $key=>$value) {
        // un mouvement est sous la forme vote + id proposition en clé
        $performance = new Performance();
        $performance->song->id = intval(substr($key,10));
        $performance->user->id = $idUser;
        $performance->rechercheID(); //recherche de l'ID de la performance pour ne pas dupliquer
        //cvotes sous la forme cvote_" . $proposition->id . "_". $objet . 
        $performance->groupe = TbAdressage::getPost("I","cvote_" . $performance->song->id );
        $performance->date = TbDate::dateDuJour();
        //Mise à jour avec identifiant externe
        $performance->update();
        unset($performance);
    }
        
    //affichage 
    $content = "<div id=\"pageAffichee\" align=\"center\"  class = \"d-block mx-auto img-fluid\">>
        <br>
        <img src=\"https://www.gifimili.com/gif/2019/05/urne-vote.gif\"  alt=\"A vote\">
        <p> A VOTE ! </p>
        </div>";
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctRepertoireAfficherPerformanceGroupe() {
    $infos= Performance::tableauMoyennePerformanceGroupe();
    ctRepertoireAfficherPerformance($infos,"sommeGroupe");
}

function ctRepertoireAfficherPerformancePerso() {
    $infos= Performance::tableauMoyennePerformancePerso();
    ctRepertoireAfficherPerformance($infos,"perso");
}

function ctRepertoireAfficherPerformance(array $infos,string $zone) {
//afficha la liste des performances pour les morceaux en cours ou test
          
    //traitement
    if (count($infos)==0){
       $content = messageColorer(false,"","Aucune performance n'a été estimée");
       require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
    else{
        //Aucune Action possibles
        $actions = [];
        $messageBarreMenu = "Performance par ordre croissant";
  
        //zone = nom de la zone dans infos,
        //titre = titre à mettre , si rien c'est le nom de la zone 
        //fonction à appliquer
        $infosColonnes[] = ['zone'=>BkTable::COLONNE_NUMERO]; //Colonne numérotation
        $infosColonnes[] = ['zone'=>'titre','fonction'=> TbListe::FONCTION_VASN];
        $infosColonnes[] = ['zone'=>'interprete','titre'=>'Interprète'];
        $infosColonnes[] = ['zone'=>'typeSong','titre'=>'Concert<br>Test','fonction'=>TbListe::FONCTION_VASN,'align'=>'C'];
        $infosColonnes[] = ['zone'=> $zone,'titre'=>'Moyenne <br>groupe','align'=>'C',TbListe::FONCTION_VASN];
        $content = TbListe::constituerListe( $infos, $infosColonnes, $actions);
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
}
/*******************************************************************************
 * Playlist
 ******************************************************************************/
function ctPlaylistEditer(){
//constitue la playlist utilisateur
    //création de la setlist utilisateur si existe pas
    if (Login::loginControl(__NAMESPACE__)){
        $user = new User(User::connectUserGetInfo('id'));
        $setlist = new Setlist($user->idSetlist);
        //liste des songs éligibles (concert ou test )
        $songs = Song::getListeChoix(array(Repertoire::TYPE_CONCERT,Repertoire::TYPE_TEST));
        //liste des studios de répétition
        $script =  Tbx::includeJS('song','theBand/src');
        $messageBarreMenu = "Modification de la playlist utilisateur";
        //affichage saisie 
        require('tpPlaylistDetail.php');
    }
}

function ctPlaylistMAJ(){
//mise à jour MPD d'une set list, evenement majSetlist
//suite à modification drang drop
    //set list pas initialisée car pas besoin d'elle, juste de la collection
    $setlist = new Setlist(TbAdressage::getPost("I","idSetlist"),true); //pas de chargement des détails
    if ($setlist->id ===0){$setlist->nomListe = "Playlist personnelle de " . user::connectUserGetInfo("prenom");}
    //Tout est mis en partie 1 de la setlist
    $numero = 0;
    foreach (explode("-", TbAdressage::getPost("S","idSongsSelected")) as $idsong){
        $numero +=1;
        $detail = new Detail();
        $detail->song->id = (int)$idsong;
        $detail->ordre = $numero;
        $detail->partie = 1;
        $setlist->details[] = $detail;
        unset ($detail);
    }
    //Mise à jour Physique
    $retour = $setlist->update();
    if ($retour){$retour = $setlist->updateCollectionDetail();}
    $content = Tbx::messageColorer($retour,"Playlist mise à jour","Erreur lors de la mise à jour");
     //Affichage
     require(TbAdressage::projetGetLayout(__NAMESPACE__));
}
