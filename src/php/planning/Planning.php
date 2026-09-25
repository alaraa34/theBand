<?php
declare(strict_types=1);
namespace theBand\src\php\planning ;
/**
 * Description of Planning
 *
 * @author araib
 */
use shared\php\database\Model as Model;
use theBand\src\php\socle\UserTheBand as User;
use shared\php\toolbox\Toolbox as Tbx;
use shared\php\toolbox\Toolbox_date as TbDate;

class Planning {

    // Constantes
    public const TABLE = PREFIXE_BDD . "planning";

    public static function mdGetListeDisponibilite(int $idUser ){
        $requete = "SELECT * FROM ". self::TABLE ." WHERE idUser = ?;";
        return Model::mdRequeteLister($requete,[$idUser]);
    }
    public static function mdSupprimerDisponibiliteEchues( ){
        $requete = "DELETE FROM ". self::TABLE ." WHERE JOUR < " . date("Y-m-d") . " ;";
        return Model::mdRequeteExecuter($requete);
    }
    public static function mdDisponibiliteMAJ(string $dispo){
    //disponibilité sous forme D01-10-2024 ou I01-10-2024 ou N01-10-2024
    // quand il y a des / php lit au format m/d/y
        $mydate = date_format(date_create(substr($dispo,1)),"Y-m-d");
        $zones = ["idUser"=>User::connectUserGetInfo("id"),"jour"=> $mydate];
        //Suppression de l'enregistrement
        $retour = Model::mdDelete(self::TABLE,$zones);
        //création selon le cas
        if (substr($dispo,0,1)!="N"){
            $zones["disponible"]= substr($dispo,0,1);
            $retour = Model::mdInsert( self::TABLE, $zones);
        }
        return $retour;
    }
    public static function mdListeDispoEntreDeuxDatesPourUnUser(int $idUser, string $dateDebut, string $dateFin) :array {
        //requete
        $requete = "SELECT jour,disponible FROM ". self::TABLE ." WHERE jour between '" . $dateDebut ."' AND '" . $dateFin . "' ";
        if ($idUser > 0) {$requete .=  " AND idUser=" . $idUser ;}
        $requete .= " ORDER BY jour;";
        //exécution
        return Model::mdRequeteLister($requete );
    }
//==============================================================================
//                  PLANNING
//==============================================================================

    public static function genererPlanningGroupe(string $dateDebut, array $users, int $nombreDates = 30) :string {
    //affiche la partie intérieure du planning  et la ligne de date 
    //tableaux des dates 
    $tableauDates =[];
    for ($i=0 ; $i<$nombreDates ; $i++){
        $tableauDates[] =  TbDate::dateAjouter($dateDebut,$i,"d","Y-m-d");
    }
    //affichage
    $myhtml = "<table class='table table-bordered table-sm'>";
    $myhtml .= self::genererPlanningGroupeEntete($dateDebut,$nombreDates);
    $myhtml .= "<tbody>";
    foreach($users as $user){
        $myhtml .= self::genererPlanningGroupeLigne($user,$dateDebut,$tableauDates);
    }
    //ligne récap
    $myhtml .= self::genererPlanningGroupeLigneRecap($dateDebut,$tableauDates, count($users));
    //fin
    $myhtml .= "</tbody></table>";
    return $myhtml;
}
    private static function genererPlanningGroupeEntete(string $dateDebut, int $nombreDates ) :string {
    //affiche la partie dates du planning 
        $myhtml = "<thead><tr><th class=\"col-md-5\" scope='col'>Mois</th>";
        //ligne dates en mois, en faisant du rowspan
        $myhtml .="<th scope='col' colspan=";
        $mois = (int)TbDate::dateAjouter($dateDebut,0,"d","m");
        $ctr = 0;
        for ($i=0 ; $i<$nombreDates ; $i++){
            $ctr +=1;
            //si rupture de mois affichage
            $moisLu = (int) TbDate::dateAjouter($dateDebut,$i,"d","m");
            if ($mois != $moisLu){
                $myhtml .= $ctr - 1 .">". TbDate::moisEnLettres($mois,99,1) ."</th>";
                if($i<$nombreDates -1){$myhtml.= "<th scope='col' colspan=";}
                $mois = $moisLu;
                $ctr = 1;
            }
        }
        //fin fermeture ligne
        if($ctr > 0 ){$myhtml .= $ctr .">".  TbDate::moisEnLettres($mois,99,1) ."</th>";}
        $myhtml .= "</tr><tr><th class=\"col-md-5\" scope='col'>Jour</th>";

        //ligne dates en jour 
        for ($i=0 ; $i<$nombreDates ; $i++){
            $myhtml .= "<th scope='col'>" .  TbDate::dateAjouter($dateDebut,$i,"d","d") ."</th>";
        }
        //ligne de date en semaine
        $myhtml .= "</tr><tr><th class=\"col-md-5\" scope='col'>Jour Semaine</th>";
        for ($i=0;$i<$nombreDates;$i++){
            $myhtml .= "<th scope='col'>" .  TbDate::jourDeLaSemaine((int)TbDate::dateAjouter($dateDebut,$i,"d","N"),2,2) ."</th>";
        }

        return $myhtml . "</tr></thead>";
    }
    private static function genererPlanningGroupeLigne(array $user , string $dateDebut, array $tableauDates ) :string {
    //affiche une ligne du planning
        $dateFin = $tableauDates[count($tableauDates)-1];
        $datas = self::mdListeDispoEntreDeuxDatesPourUnUser($user["id"],$dateDebut,$dateFin);
        $myhtml = "<tr>";
        //colonne prénom
        $myhtml.= "<td scope='row'>" . $user["prenom"] . "</td>";
        //ligne dates en jour 
        for ($i=0 ; $i< count($tableauDates) ; $i++){
            //recherche si existe dans dispo si existe signalement
            $myhtml .= "<td";
            if (in_array(['disponible'=>'D','jour'=>$tableauDates[$i]], $datas)){
               //$myhtml .= " class=\"planningDispo\" ";
               $myhtml .= " style=\"border-style:groove;border-color:#00FF00;background-color:green\"";
            }elseif (in_array(['disponible'=>'I','jour'=>$tableauDates[$i]], $datas)){
                $myhtml .= " style=\"border-style:groove;border-color:#00FF00;background-color:red\"";
            }
            $myhtml .= "></td>";
        }
        return $myhtml . "</tr>";
    }
    private static function genererPlanningGroupeLigneRecap(string $dateDebut, array $tableauDates, int $nbUsers ) :string {
    //affiche une ligne du planning
        $dateFin = $tableauDates[count($tableauDates)-1];
        $datas = self::mdListeDispoEntreDeuxDatesPourUnUser(0,$dateDebut,$dateFin);
        $myhtml = "<tr>";
        //colonne prénom
        $myhtml.= "<td scope='row'><strong>SYNTHESE </strong>(Dispo si tous le sont)</td>";
        //ligne dates en jour 
        for ($i=0 ; $i< count($tableauDates) ; $i++){
            //recherche si existe dans dispo si existe signalement
            $myhtml .= "<td";
            if (Tbx::nombreOccurencesDansTableau($tableauDates[$i], $datas)== $nbUsers){
               //$myhtml .= " class=\"planningDispo\" ";
               $myhtml .= " style=\"border-style:groove;border-color:#00FF00;background-color:green\"";
            }
            else{
                 $myhtml .= " style=\"border-style:groove;border-color:#00FF00;background-color:red\"";
            }
            $myhtml .= "></td>";
        }
        return $myhtml . "</tr>";
    }
}
