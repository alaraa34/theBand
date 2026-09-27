<?php
declare(strict_types=1);
namespace theBand\src\php\evenement;
/*******************************************************************************
 * Classe de pilotage des  concerts 
 *
 * @author Alara
 *******************************************************************************/
use shared\php\toolbox\Toolbox_liste    as TbListe;
use shared\php\toolbox\Toolbox          as Tbx;
use theBand\src\php\song\Detail         as Detail;
use shared\php\classes\lien\TypeLien    as TypeLien;

class Evenement_Concert extends Evenement
{
    // Attributs
    public const SUJET = 74;
    public const NATURE = 1; //evt concert
    public const DELAI_SUPPRESSION_MOIS = 24; //tout ce qui est antérieur à 24 mois est supprimé   
    public const LIBELLE_COMMENTAIRE = "FEUILLE DE ROUTE";
    // Méthodes
  
    //------------------------------------------------------------------------------------------------
    //Méthodes statiques publiques
    //-----------------------------------------------------------------------------------------------
   
    //------------------------------------------------------------------------------------------------
    //CRUD
    //-----------------------------------------------------------------------------------------------
     
    //------------------------------------------------------------------------------------------------
    // MISES A JOUR
    //-----------------------------------------------------------------------------------------------

    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------
     
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    public function getHeureFin():string {
        //met en forme l'heure de fin pour les concerts (y'en a pas)
        return "";
    }
    // ECRIRE une SETLIST
    public function listerSetlist(){
    //compose la page d'affichage d'une set liste de concert
        $myhtml = "<table>";
        $entetes = ["N","Titre","Interprète","Tonalité<br>initiale","Correction<br>(demi ton)","Lead <br> Chant","Tempo","Enchainé","Pad"];
        $myhtml .= "<tr><td colspan=" . count($entetes) . "  class=\"barreTitre text-center fw-bold\">" .$this->setlist->nomListe . "</td></tr>";
        $myhtml .= TbListe::formaterEntete($entetes);
        $numero = 0;
        $partie = 0 ;
        foreach ($this->setlist->details as $detail) {
            $numero += 1 ;
            if ($partie != $detail->partie){
                $partie = $detail->partie;
                $myhtml .= "<tr><td colspan=" . count($entetes) . " class=\"barreTitre\"><strong>";
               //parties
                if ($partie == Detail::RAPPEL )
                    {$myhtml .= "TO BE CONTINUED ";}
                else
                    {$myhtml .= "THE SHOW PARTIE " . $detail->partie;}
                // fin ligne
                $myhtml .= "</strong></span></td></tr>";
            }
            //Début de ligne
            $myhtml .= "<tr>";
            //détails communs
            $myhtml .= $this->listerSetlisteDeConcert2($numero,$detail);
            $myhtml .= "</tr>";
        }
        $myhtml .= "</table>";
        return $myhtml ;
    }
   

    private function listerSetlisteDeConcert2(int $numero, Detail $detail){
    //compose une ligne
            $myhtml = "<td>" . $numero . "</td>";
            $myhtml .= "<td>" . htmlspecialchars($detail->song->titre) . "</td>";
            $myhtml .= "<td>" . htmlspecialchars($detail->song->interprete). "</td>";
            $myhtml .= "<td align=\"center\">" . nl2br(Tbx::valeurAffichableSiNull($detail->song->tonaOrigine)) . "</td>";
            $myhtml .= "<td align=\"center\">" . nl2br(Tbx::valeurAffichableSiNull($detail->song->tonaScene)) . "</td>" ;         
            $myhtml .= "<td align=\"center\">" . nl2br(Tbx::valeurAffichableSiNull($detail->song->leadChant->getProperty("abrev"))) . "</td>";
            $myhtml .= "<td align=\"center\">" . nl2br((string)Tbx::valeurAffichableSiNull($detail->song->tempo)) . "</td>";
            $myhtml .= "<td align=\"center\">";
            if ($detail->enchainement){$myhtml.= "<span class=\"". Detail::ICONE_ENCHAINEMENT . " \"></span>";}
            $myhtml .= "</td><td align=\"center\">";
            //chargement du lien pad car aucun lien chargé
           $detail->song->liens=[];
            $detail->song->loadLiensParticulier(array(TypeLien::PAD));
            if (count($detail->song->liens) > 0){$myhtml .= $detail->song->liens[0]->genererLienHref();}
            $myhtml .= "</td>";
        return $myhtml ;
    }
    
    public static function initFeuilleDeRoute(){
    //formulaire de saisie de la feuille de route
        $html = 'Donner toutes les informations utiles pour aller sur site, installer et jouer.<br>
                <ul>
                <li>Négocié dans la prestation : 
                    &nbsp <input class="form-check-input" type="checkbox" value="" checked >Repas</input>
                    &nbsp <input class="form-check-input" type="checkbox" value="" checked >Boissons</input></li>
                <li>Négocié dans le repas : 
                    &nbsp <input class="form-check-input" type="checkbox" value="" >Entrée</input>                 
                    &nbsp <input class="form-check-input" type="checkbox" value=""  checked>Plat</input>
                    &nbsp <input class="form-check-input" type="checkbox" value=""  checked>Dessert</input>
                    &nbsp <input class="form-check-input" type="checkbox" value=""  >Café</input>
                 <li>Heure d\'arrivée : </li>
                 <li>Consignes pour trouver lieu, ex portail, panneau...:</li>
                 <li>Heure de début de balance : </li>
                 <li>Heure repas : </li>
                 <li>Heure de début du concert : </li>
                 <li>Durée de la pause si il il y en a : </li>
                 <li>Infos sur la scène : </li>
                </ul>';
        return $html;
                
    }
    //------------------------------------------------------------------------------------------------
    //MODELE
    //-----------------------------------------------------------------------------------------------
    
   
   
}



