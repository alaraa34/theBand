<?php
namespace theBand\src\php\song;
/*******************************************************************************
 * Controleur des titres : saisie, mise à jour, changements de statut, suppression
 ******************************************************************************/
use shared\php\classes\lien\Lien                       as Lien;
use shared\php\classes\lien\TypeLien                   as TypeLien;
use shared\php\classes\lien\Multipiste                 as Multipiste;
use shared\php\classes\lien\MultipisteControle         as MultipisteControle;
use shared\php\classes\personalisation\Nomenclature    as Nomenclature;
use theBand\src\php\socle\UserTheBand                  as UserTB;
use shared\php\toolbox\Toolbox                         as Tbx;
use shared\php\classes\socle\Login                     as Login;
use shared\php\toolbox\Toolbox_upload                  as TbUpload;
use shared\php\modale\Toolbox_modal                    as TbModal;
use shared\php\toolbox\Toolbox_adressage               as TbAdressage;

function ctSongAdd():void {
    ctSongEditer();
}

function ctSongEditer(int $identifiant = 0) {
//actions addSong et editSong
     
    $typesLien = TypeLien::listePourUnSujet(Repertoire::SUJET_LIEN);
    $correctionsTona = Nomenclature::mdNomenclatureGetListe("ECART_TONA","Nom","DESC","valeurN","Nom");
    $users = UserTB::listeMusiciens();
    //defaut on crée une instance concert (çà ne change rien)
    $song = new Repertoire($identifiant);
    $script =  Tbx::includeJS(['lien','liste','utils','confirmationTona']);
    //titre existant : confirmation si la tona de scène change (pistes transposées et mp3 joué supprimés)
    $confirmationTona = MultipisteControle::confirmationTonaHtml($song->id, (string)$song->tonaScene,
        "La tonalité scène a été modifiée. Les enregistrements multipistes transposés et le mp3 joué seront supprimés. Continuer ?");
    if ($song->id===0){
    //Saisie d'un nouveau titre avec contrlonnexion e de 
        $typesSong = Song::listeTypeSong();
        $messageBarreMenu = "Nouveau titre";
        
        if (Login::loginControl(__NAMESPACE__)){require('tpSongDetail.php');}
    }
    else{
    //Modification d'un titre existant
        $typesSong = Song::listeTypeSong(true);
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
    //tona de scène : un changement sur un titre existant n'est pris que s'il a été confirmé (oui)
    $ancienneTona = $song->id > 0 ? Repertoire::mdTonaScene($song->id) : "";
    $decisionTona = $song->id > 0 ? MultipisteControle::tonaSceneAEnregistrer($ancienneTona, TbAdressage::getPost("S","tonaScene"))
                                  : ['tona'=>TbAdressage::getPost("S","tonaScene"), 'modifiee'=>false, 'refusee'=>false];
    $song->tonaScene = $decisionTona['tona'];
    $song->tempo = TbAdressage::getPost("I","tempo");
    $song->leadChant->id = TbAdressage::getPost("I","idLeadChant");
    $song->commentaire->id = TbAdressage::getPost("I","idCommentaire");
    $song->commentaire->texte = TbAdressage::getPost("S","commentaire");
    
    //retourne les liens avec leurs actions   pour les actions liens renseignés
    //Zones associatives 
    $song->chargerLiensParMouvements(TbAdressage::getValeurPostTableau(Lien::ZONES_MOUVEMENTS));

    //un seul MP3 original et un seul MP3 joué par titre
    $doublon = $song->typeMP3EnDouble();
    if ($doublon !== '') {
        $content = Tbx::messageColorer(false, "", "Titre " . $song->titre . " non enregistré : un titre ne peut avoir qu'un seul lien "
                 . $doublon . ". Supprimer l'ancien lien avant d'en ajouter un nouveau.");
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
        return;
    }

    //réception des fichiers
    $retour = TbUpload::uploadReceptionLien(); //ds Toolbox_upload, import des fichiers 
    
    //mise à jour de la base
    $retour = $song->update();
    //tona de scène modifiée : pistes transposées du MP3 original et MP3 joué supprimés
    $bilanTona = ['originaux'=>0, 'joues'=>0];
    if ($retour && $decisionTona['modifiee']) {
        $bilanTona = Multipiste::tonaSceneModifiee(Repertoire::mdLiensMP3DuTitre($song->id),
                                                   Repertoire::tonaAttendue(TypeLien::MP3ORI, $song->tonaScene), SongLien_ass::TABLE);
    }
    $content = Tbx::messageColorer($retour,"Titre " . $song->titre . " traité avec succès"
                                   . MultipisteControle::messageTonaScene($decisionTona, $bilanTona));
    unset($song);
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
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
