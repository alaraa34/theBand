<?php
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * Controleur des prospects et de leurs suivis (domaine etablissement, ctr=prospect)
 *******************************************************************************/
use shared\php\modale\Toolbox_modal         as TbModal;
use shared\php\toolbox\Toolbox              as Tbx;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use shared\php\classes\socle\Login          as Login;

//------------------------------------------------------------------------------
//PROSPECTS
//------------------------------------------------------------------------------

function ctProspectListerE(int $id=0):void {
//contacts En cours avec des prospects $id est renseigné après ajout d'une usivi sur un prospect
     if (Login::loginControl(__NAMESPACE__))
        {//Actions possibles
        $actions = [
            ['texte'=> 'Modifier le commentaire','logoClass' =>'fa-regular fa-pen-to-square',
                        'modale'=>'etablissement/prospect;modifier','template'=> 'theBand/src/php/etablissement/tpProspectCommentaireMDL.php'],
            ['texte'=> 'Nouveau suivi','logoClass' =>'bi bi-telephone-outbound',
                        'modale'=>'etablissement/prospect;ajouter;suivi','template'=> 'theBand/src/php/etablissement/tpSuiviDetailMDL.php'],
            ['texte'=> 'Cloturer le prospect','logoClass' =>'bi bi-door-closed',
                        'modale'=>'etablissement/prospect;cloturer','message' => 'Cloturer le prospect ?'],
            ['texte' => 'Supprimer','logoClass' => 'bi bi-trash',
                        'modale'=>'etablissement/prospect;supprimer','message'=> 'Supprimer le prospect et ses suivis (plus aucune trace!)?'
                . 'Sinon utiliser Cloturer qui conserve tout le suivi']
                    ];
        if ($id==0){
            ctProspectLister(Prospect::listeOuverts(),$actions);
        }
        else{
            ctProspectLister(Prospect::listeUnique($id),$actions);
        }
    }    
}

function ctProspectListerT():void{
//contacts terminés avec des prospects
     if (Login::loginControl(__NAMESPACE__))
        {//Actions possibles
        $actions = [['texte' => 'Supprimer','logoClass' => 'bi bi-trash',
                        'modale'=>'etablissement/prospect;supprimer','message'=> 'Supprimer le prospect et ses suivis (plus aucune trace!)?']];
         ctProspectLister(Prospect::listeFermes(),$actions);}
}

function ctProspectLister(array $prospects, array $actions):void {
// liste de tous les prospect
    //Pas de controle login car déjà fait
    $script = Tbx::includeJS('utils');  //traitement des actions de la liste
    switch (count($prospects)){ 
        case 0:
            $content = Tbx::messageColorer(false, "","Il n'y a pas de prospects pour l'état demandé");
            require(TbAdressage::projetGetLayout(__NAMESPACE__));
            break;
        case 1:
            $messageBarreMenu = "Prospect en cours";
            $expandAccordeon = false;
            require('tpProspectListe.php');
            break;
        default:
            $messageBarreMenu = "Liste des prospects";
            $expandAccordeon = true;
            require('tpProspectListe.php');
    }
}

// *************************  fonctions modales ************************

function ctSuiviAjouter(){
//ajoute un suivi en retour de la modale d'ajout de suivi
    $suivi = new Suivi();
    $suivi->commentaire->texte = TbAdressage::getPost("S","commentaire");
    $suivi->idAction = TbAdressage::getPost("I","idAction");
    $retour = $suivi->update(TbModal::modalGetIdFromModal());
    if ($retour){
       ctProspectListerE(TbModal::modalGetIdFromModal());
    }
    else{die ("Erreur ctSuiviAjouter sur ajout de suivi");}
}
function ctProspectModifier(){
//Modifie le commentaire du prospect
    $prospect = new Prospect(TbModal::modalGetIdFromModal());
    $prospect->commentaire->texte = TbAdressage::getPost("S","commentaire");
    $retour = $prospect->update();
    if ($retour){
       ctProspectListerE($prospect->id);
    }
    else{die ("Erreur ctProspectModifier sur modification du commentaire");}
}
   
function ctProspectSupprimer(){
//ajoute un suivi en retour de la modale d'ajout de suivi
    $prospect = new Prospect(TbModal::modalGetIdFromModal());
    $retour = $prospect->delete();
    $content = Tbx::messageColorer($retour,"Prospect sur " . $prospect->etablissement->nom . " supprimé");  
    //affichage 
    unset ($prospect);
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctProspectCloturer(){
//Cloture du prospect, ajout d'un suivi de cloture et passage à fermé du prospect 
    $prospect = new Prospect(TbModal::modalGetIdFromModal());
    $retour = $prospect->cloturer();
    $content = Tbx::messageColorer($retour,"Prospect sur " . $prospect->etablissement->nom . " clôturé");  
    //affichage 
    unset ($prospect);
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}
