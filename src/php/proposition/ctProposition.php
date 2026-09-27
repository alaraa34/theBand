<?php
declare(strict_types=1);
namespace theBand\src\php\proposition;
/*******************************************************************************
 * 
 ******************************************************************************/
use shared\php\classes\lien\TypeLien                   as TypeLien;
use shared\php\classes\socle\Login                     as Login;
use shared\php\classes\personalisation\Nomenclature    as Nomenclature;
use shared\php\classes\personalisation\Parametre       as Parametre;
use shared\php\toolbox\Toolbox                         as Tbx;
use shared\php\classes\lien\Lien                       as Lien;
use shared\php\modale\Toolbox_modal                    as TbModal;
use shared\php\database\Model                          as Model;
use shared\php\toolbox\Toolbox_adressage               as TbAdressage;
use theBand\src\php\socle\UserTheBand                  as UserTB;
use shared\php\classes\socle\User                      as User;


function ctPropositionListerAB():void{
    ctPropositionLister(Proposition::ABANDON);
}
function ctPropositionListerValidees():void{
    ctPropositionLister(Proposition::VALIDE);
}
function ctPropositionListerParVote():void{
    ctPropositionLister(Proposition::ENCOURS,1);
}
function ctPropositionListerParTitre():void{
    ctPropositionLister(Proposition::ENCOURS,2);
}
function ctPropositionListerElimines() :void{
    ctPropositionLister(Proposition::ENCOURS,3);
}

function ctPropositionLister(int $statut,int $vote1titre2Elim3 = 1) {
//Liste des propositions
//Statuts 17 en cours, 18 validée,19 abandonnée ; pas d'action pour les validées 
    
    $messageBarreMenu = "Liste de propositions <strong>" . Nomenclature::mdNomenclatureGetDetailfromID($statut,"valeurA") . "s</strong>";
    // liste des users
    $listeUsers = UserTB::listeMusiciens();
    //Actions possibles
    $actions = actionsSelonStatut($statut);
    $userConnecte = User::userConnecte();
    
    //tableau des id de Propositions
    if ($statut == Proposition::ENCOURS and $vote1titre2Elim3==1){
        $complement = " triée par vote global";
        $propositions = Proposition::listeParVotes();
    }
    elseif ($statut == Proposition::ENCOURS and $vote1titre2Elim3==3){
        // en cours eliminés potentiels
        $complement = " triée par titre";
        $propositions = Proposition::listeParVotesElimines();
    }
    else{
        $complement = " triée par titre";
        $propositions = Proposition::listeParTitres($statut);
    }
    if(count($propositions)==0){
        $content = Tbx::messageColorer(false,"","Aucune proposition n'est enregistrée avec ce statut");
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
    else{
        //transformations en tableaux d'instances'
        for($i=0; $i<count($propositions);$i++){
            $propositions[$i] = new Proposition($propositions[$i]['id']);
        }
        //affichage 
        require('tpPropositionListe.php');
    }
}

function actionsSelonStatut(int $statut) :array {
    //retourne un tableau des actions possibles se lon le statut
        //statuts self::ENCOURS en cours, self::VALIDE validé, self::ABANDON abandonné
        //Chaque code rajoute
        $actions = [];
        if (User::userConnecte()){
            if ($statut== Proposition::ENCOURS){
                $actions[] = ['texte'=> 'Modifier','logoClass' =>'fa-regular fa-pen-to-square','href'=>'proposition;editer'];}
            //suppression si En cours ou abandonné
            if ($statut==Proposition::ABANDON or $statut== Proposition::ENCOURS){
                $actions[]=['texte' => 'Supprimer','logoClass' => 'bi bi-trash','modale'=>'proposition;supprimer',
                        'message'=> 'Supprimer la proposition ?'];}
            //Validation si Statut en cours
            if ($statut==Proposition::ENCOURS){
                $actions[]=['texte' => 'Valider pour répétition','logoClass' => 'bi bi-journal-check',
                    'modale'=>'proposition;valider',
                    'message' => 'La proposition est adoptée pour test en répétition?'];}
            //abandon si statut en cours 
            if ($statut==Proposition::ENCOURS){
                $actions[]=['texte' => 'Abandonner', 'logoClass'=>'bi bi-ban','modale'=>'proposition;abandonner',
                            'message'=> 'La proposition est rejetée ?'];}
            // re soumettre ay vote
            if ($statut==Proposition::ABANDON){
                $actions[]=['texte' => 'Reproposer', 'logoClass'=>'fa-solid fa-check-to-slot','modale'=>'proposition;reproposer',
                    'message'=> 'La proposition à nouveau soumise au vote ?'];}
        }        
        return $actions;
    }

function ctPropositionEditer(int $identifiant =0) {
//nouvelle proposition ou édition d'une proposition existante si id renseigné
    if (!Proposition::saisieOuvert($identifiant)){
        //test si la saisie est activée ou pas.
        //Passage de l'identifiant car une proposition existante est toujours modifiable
        $content = Tbx::messageColorer(false,"","La saisie de nouvelles propositions est fermée.");
        require(TbAdressage::projetGetLayout(__NAMESPACE__)); 
    }
    else{
        $proposition = new Proposition($identifiant);
        if ($proposition->id==0){
            $messageBarreMenu = "Nouvelle proposition";
           }
        else {
            //info propositions. Pas de controle login car déjà fait
            $messageBarreMenu = "Modification de proposition";
        }
         if (Login::loginControl(__NAMESPACE__)){require('tpPropositionDetail.php');}

    }
}

// retour MISES A JOUR des actions
function ctPropositionMAJ() {
    //traitement de mise à jour d'une proposition
    // infos song correspondant à la zone de la table song
    $messageBarreMenu = "";
    
    //Chargement de l'instance
    $proposition = new Proposition();
    $proposition->id = TbAdressage::getPost("I","id");
    $proposition->titre = TbAdressage::getPost("S","titre");
    $proposition->interprete = TbAdressage::getPost("S","interprete");
    $proposition->commentaire->id = TbAdressage::getPost("I","idCommentaire");
    $proposition->commentaire->texte = TbAdressage::getPost("S","texteCommentaire");
    //Car on ne peut modifier que les propositions en cours
    $proposition->statut = Proposition::ENCOURS;
    
    //retourne les liens modifiés en tableau de tableaux   
    $actionLien="M";
    if (TbAdressage::getPost("I","idLien")==0){$actionLien="C";}
    $mvtsLiens = [['idLien' => TbAdressage::getPost("I","idLien"),
                'actionLien' => $actionLien,
                'idTypeLien' => TypeLien::SITE ,
                'urlLien'=> TbAdressage::getPost("S","urlLien")]];
   
    $proposition->liens = Lien::chargerLiensParMouvements($proposition);
      
    //Mise à jour Physiqye
    $retour = $proposition->update();
    $content = Tbx::messageColorer($retour,"La proposition " . $proposition->titre .
                " a été traitée avec succès");
       //Affichage
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
    
}
function ctPropositionSupprimer(){
//validation de la ùmodale propositionMDLsupprimer
//supprime la proposition si elle est en statut proposition
    $proposition = new Proposition(modalGetIdFromModal());
    $retour = $proposition->delete(); 
    if($retour){
        //réaffichagede la liste
        header('Location: ' . (string)filter_input(INPUT_SERVER,'HTTP_REFERER'));
    }
    else{
        $content = Tbx::messageColorer($retour,"","La proposition a été validée en test, elle doit être supprimée via les listes de titres");
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
}

function ctPropositionMDLReproposer(){
//validation de la modale propositionMDLreproposer
//soumet à nouveau une proposition aux votes
    //changement du statut à Validé
    $proposition = new Proposition();
    $proposition->id = TbModal::modalGetIdFromModal();
    $proposition->mdPropositionMajStatut(Proposition::ENCOURS); //repasse statut en cours
    unset($proposition);
    header('Location: ' . (string)filter_input(INPUT_SERVER,'HTTP_REFERER'));
}

function ctPropositionMDLValider(){
//Passe une proposition en test
    $proposition = new Proposition();
    $proposition->id = TbModal::modalGetIdFromModal();
    $proposition->mdPropositionMajStatut(Proposition::VALIDE); //repasse statut en cours
    $proposition->mdSongMajType(repertoire::TYPE_TEST);
    unset($proposition);
 
    //résultat
    header('Location: ' .(string)filter_input(INPUT_SERVER,'HTTP_REFERER'));
}
function ctPropositionMDLAbandonner(){
//abandon d'une proposition
    $proposition = new Proposition();
    $proposition->id = TbModal::modalGetIdFromModal();
    $proposition->mdPropositionMajStatut(Proposition::ABANDON); //repasse statut en cours
    unset($proposition); 
    header('Location: ' . (string)filter_input(INPUT_SERVER,'HTTP_REFERER'));
}

//================================================================================================
//Fonctions VOTES
//================================================================================================
function ctPropositionMajVotes() {
    //Enregistre les votes suite à une saisie
    $messageBarreMenu = "";
    //extrait les lignes qui ont bougé
    $mouvements= array_filter($_POST, function($v, $k) {
                                    return str_starts_with($k,"vote") & $v == "M";
                                }, ARRAY_FILTER_USE_BOTH);
    //Balayage de toutes les propositions éligibles au vote 
    $idUser = User::connectUserGetInfo('id');
    foreach ($mouvements as $key=>$value) {
        // un mouvement est sous la forme vote + id proposition en clé
        $vote = new Vote();
        $idProposition = intval(substr($key,4));
        $vote->user->id = $idUser;
        $vote->rechercheID($idProposition); //recherche de l'ID du vote pour ne pas dupliquer
        //cvotes sous la forme cvote_" . $proposition->id . "_". $objet . 
        $vote->maitrise = TbAdressage::getPost("I","cvote_" . $idProposition . "_". vote::MAITRISE);
        $vote->preference = TbAdressage::getPost("I","cvote_" . $idProposition . "_". vote::PREFERENCE);
        
        //Mise à jour avec identifiant externe
        $vote->update($idProposition);
        unset($vote);
    }
        
    //affichage 
    $content = '<div id="pageAffichee" align="center"  class = "d-block mx-auto img-fluid">
        <br>
        <img src="images/urne-vote.gif"  alt="A vote">
        <h4> A VOT&Eacute; ! </h4>';
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctPropositionVoterTous() :void{
    ctPropositionVoter(1);
}
function ctPropositionVoterNV() :void{
    ctPropositionVoter(2);
}
function ctPropositionVoter(int $mode) {
//Liste des propositions avec saisie des votes
//actif est paramétré dans les actions voteProp et voteProp NV
    if (!Proposition::voteOuvert()){
         //test si le vote est activé ou pas
        $content = Tbx::messageColorer(false,"","Le vote est fermé.");
        require(TbAdressage::projetGetLayout(__NAMESPACE__)); 
    }
    else{
        $messageBarreMenu = "Vote pour proposition";
        //proposition et vote user
        $propositions = Proposition::listePourVote(User::connectUserGetInfo('id'),$mode);
        //transformatoin en instances
        for($i=0; $i<count($propositions);$i++){
            $objproposition = new Proposition();
            $objproposition->loadFromArray($propositions[$i]);
            $objproposition->loadVotes(User::connectUserGetInfo('id'));
            $objproposition->loadLiens();
            $propositions[$i] = $objproposition;
            unset($objproposition);
        }
        $script = Tbx::includeJS(['song'],'theBand/src');
        //si pas de propositions à voter
        if ($mode== Vote::TOUS_VOTES and count($propositions)==0){
            $content = Tbx::messageColorer(true,"Il n'y a aucune proposition éligible au vote");
            require(TbAdressage::projetGetLayout(__NAMESPACE__)); 
        }
        elseif ($mode== Vote::NON_VOTES and count($propositions)==0){
            //tout a été voté
            $content = Tbx::messageColorer(true,"BRAVO !! Il ne reste plus aucun vote en attente");
            require(TbAdressage::projetGetLayout(__NAMESPACE__)); }
        //affichage 
        else {
             if (Login::loginControl(__NAMESPACE__)){require('tpPropositionListeVote.php');}
        }
    }
}
    
function ctPropositionOuvrirVote(int $mode) {
// a lancer manuellement pour changer le statur des bvotes
//paramètre voter  1 vote seul, 2 saisie seule, 3 tout fermé,4 tout ouvert
// exemple localhost/TheBand_Dev/index.php?action=propositionOuvrirVote&id=2
    $retour = Model::mdUpdate(Parametre::TABLE, ['valeurN'=>$mode], "Groupe = 'PROPOSITIO' AND Nom='VOTER'");
    $content = Tbx::messageColorer($retour,"Paramètre modifié à " .$mode,"Erreur");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}
