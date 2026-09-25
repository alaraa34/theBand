<?php
declare(strict_types=1);
namespace theBand\src\php\evenement;
/*******************************************************************************
 * Classe de pilotage des concerts 
 *
 * @author Alara
 *******************************************************************************/
use shared\php\toolbox\Toolbox_liste as TbListe;

class Evenement_Repetition extends Evenement
{
    // Attributs
      

    public const SUJET = 76;
    public const NATURE = 2; //evt répétition
    public const DELAI_SUPPRESSION_MOIS = 2; //tout ce qui est antérieur à un mois est supprimé
    public const LIBELLE_COMMENTAIRE = "Commentaire";
    
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
        //met en forme l'a date pour'heure de fin pour les répétitions
        return " à " . date_format(date_create($this->dateHeureFin),"H:i");
    }
                
    //*****************************************************************************
    // ECRIRE une SETLIST de REPETITION
    //*****************************************************************************
    function listerSetlist(){
    //Liste des chansons de la repet
        $myhtml = "<table>";
        $entetes = ["N","Titre","Interprète","Tonalité<br>initiale","Correction<br>(1/2 ton)","Chant","Tempo"];
        $myhtml .= TbListe::formaterEntete($entetes);
        $numero = 0;
        foreach ($this->setlist->details as $detail) {
            $numero += 1;
            //-- Détail des chansons -->
            $myhtml .= "<tr>";
            $myhtml .= "<td>" . $numero ."</td>";
            $myhtml .= "<td>" . htmlspecialchars($detail->song->titre)."</td>";
            $myhtml .= "<td>" .htmlspecialchars($detail->song->interprete) ."</td>";
            $myhtml .= "<td align= \"center\">". htmlspecialchars($detail->song->tonaOrigine) . "</td>";
            $myhtml .= "<td align=\"center\">" . htmlspecialchars($detail->song->tonaScene)."</td>";
            $myhtml .= "<td align= \"center\">" . htmlspecialchars($detail->song->leadChant->getProperty("abrev")) ."</td>";
            $myhtml .= "<td align= \"center\">" . $detail->song->tempo ."</td>";

            $myhtml .="</tr>";
        }
        return $myhtml. "</table>";
    }
    
    //------------------------------------------------------------------------------------------------
    //MODELE
    //-----------------------------------------------------------------------------------------------
    
   
   
}



