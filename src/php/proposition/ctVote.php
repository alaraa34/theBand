<?php
declare(strict_types=1);
namespace theBand\src\php\proposition;
/*******************************************************************************
 * Controleur des votes sur les propositions (domaine proposition, ctr=vote)
 ******************************************************************************/
use shared\php\classes\socle\Login                     as Login;
use shared\php\classes\personalisation\Parametre       as Parametre;
use shared\php\toolbox\Toolbox                         as Tbx;
use shared\php\database\Model                          as Model;
use shared\php\toolbox\Toolbox_adressage               as TbAdressage;
use shared\php\classes\socle\User                      as User;

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
        $vote->maitrise = TbAdressage::getPost("I","cvote_" . $idProposition . "_". Vote::MAITRISE);
        $vote->preference = TbAdressage::getPost("I","cvote_" . $idProposition . "_". Vote::PREFERENCE);
        
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

//================================================================================================
//ADMINISTRATION : ouverture de la saisie des propositions et des votes (entrée de menu adminOnly)
//paramètre PROPOSITIO/VOTER : 1 vote seul, 2 saisie seule, 3 tout fermé, 4 tout ouvert
//================================================================================================
function ctPropositionOuvertureEditer() {
//écran avec deux interrupteurs : saisie des propositions et vote
    if (!voteAccesAdministrateur()) {return;}
    $saisieOuverte = Proposition::saisieOuvert();
    $voteOuvert = Proposition::voteOuvert();
    $messageBarreMenu = "Ouverture des propositions et des votes";
    require('tpPropositionOuverture.php');
}

function ctPropositionOuvertureMAJ() {
//enregistre le paramètre à partir des deux interrupteurs
    if (!voteAccesAdministrateur()) {return;}
    $saisie = (bool)TbAdressage::getPost("C", "saisieOuverte");
    $vote = (bool)TbAdressage::getPost("C", "voteOuvert");
    $mode = match (true) {
        $saisie && $vote => 4,
        $vote            => 1,
        $saisie          => 2,
        default          => 3,
    };
    $retour = Model::mdUpdate(Parametre::TABLE, ['valeurN'=>$mode], "Groupe = 'PROPOSITIO' AND Nom='VOTER'");
    $etat = "Saisie des propositions " . ($saisie ? "ouverte" : "fermée") . ", vote " . ($vote ? "ouvert" : "fermé");
    $content = Tbx::messageColorer($retour, $etat, "Erreur lors de la mise à jour du paramètre");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function voteAccesAdministrateur(): bool {
//true si un administrateur est connecté, sinon affiche la connexion ou un refus
    if (!Login::loginControl(__NAMESPACE__)) {return false;}
    if (User::estAdministrateur()) {return true;}
    $content = Tbx::messageColorer(false, messageKO: "Page réservée à un administrateur.");
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
    return false;
}
