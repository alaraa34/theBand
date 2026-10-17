<?php
declare(strict_types=1);
namespace theBand\src\php\evenement;
/*******************************************************************************
 * Fonctions communes aux évènements (concerts et répétitions)
 * chargé par ctRepetition et ctConcert, pas d'entrée de menu directe
 *******************************************************************************/
use shared\php\toolbox\Toolbox                     as Tbx;
use shared\php\toolbox\Toolbox_adressage           as TbAdressage;

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
