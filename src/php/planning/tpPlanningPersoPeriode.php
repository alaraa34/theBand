<?php 
declare(strict_types=1);
namespace theBand\src\php\planning ;
/**
 * Description of Planning
 *
 * @author araib
 */
use shared\php\classes\lien\Lien            as Lien;
use shared\php\toolbox\Toolbox              as Tbx;
use shared\php\toolbox\Toolbox_date         as TbDate;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
ob_start(); ?>
<br><br<br>
<form method="post" enctype="multipart/form-data"  action="<?= TbAdressage::getURLstatic('planning','planningPeriodeMAJ'); ?>">
    <section class="py-5">
        <div class="container">
            <!-- Nom et coordonnées -->  
            <div class="row">
                <!-- Type de titre --> 
                <div class="col-8">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="titreUser">Planning de </span>
                        <input type="text" name="nomUser" disabled class="form-control" placeholder="Nom " 
                               aria-label="titre" aria-describedby="titreUser"
                               value="<?= Tbx::afficherData($user,'prenom') ?>"/>
                    </div>
                </div>
  
            </div>
 
            <br>
            <!---- Periodes ---->
            <div class="row">
                <div class="col-sm-8">
                    <table  class="table table-responsive table-striped table-bordered">
                        <thead class="table-light table align-middle">
                            <tr>
                                <th class="col-md-1">Supp. Ligne</th>
                                <td class="col-md-1">Début de la période(*)</td>
                                <td class="col-md-1">Fin de la période(*)</td>
                                <td class="col-md-1">Jours de la période</td>
                                <td class="col-md-2">Statut de la période</td>
                            </tr>
                        </thead>
                        <tbody id="periodes">
                            <!-- Ligne modèle --> 
                            <?= genererLignePeriode(Lien::MODELE_LIGNE); ?>
                            <?= genererLignePeriode(1); ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-1">
                    <img src="images/plus.png" class="img-fluid img-thumbnail" alt="Ajouter une ligne" onclick="jsListeAddNew('periode','dateDebut;dateFin')">               
                </div>
            </div>
            
        </div>
    </section>
    <!-- validation du bouton -->
    <div class ="row">
        <div class="mb-3">
            <button type="submit" class="btn btn-primary pull-right">Valider</button>
        </div>
    </div>
    <br>
    <div class ="row align-middle">
        <div class="col"> Le planning est mis à jour dans l'ordre des lignes. Pour l'utiliser : <br>
            <ul>
                <li>Indiquer en première ligne la période global concernée en disponible ex du 01/01 au 15/02 </li> 
                <li>Rajouter autant de lignes qu'il y a de changement à ce planning initial. Ex 15/01 au 18/01 Indisponible</li> 
                <li>Pour bloquer les week ends d'une période, enregistrer une première ligne disponible en décochant Sa et Di puis
                    une deuxième ligne avec la même période indisponible en ne cochant que Sa et Di</li>
                <li>Ce planning est ajustable également avec planning individuel</li>
                <li>Pour vérifier le résultat il faut consulter le planning individuel</li>
            </ul>
        </div>
    </div>
</form>

<?php 
    $content = ob_get_clean();
    require(TbAdressage::projetGetLayout(__NAMESPACE__));

function genererLignePeriode(int $indice): string{
    //Génère une ligne de periode
    //En cas de modification du type de periode ou du contenu du periode, le js periodeModification(ligne) est appelé 
    // en cas de suppression (clic sur poubelle) le periode periodeSuppression  est appelé
    $myhtml = "<tr id=\"periode" . $indice ."\"" ;
    if ($indice==Lien::MODELE_LIGNE){$myhtml.= " hidden ";}
    $required = $indice==Lien::MODELE_LIGNE ? "" : " required ";
    $myhtml .= ">";
    // colonne caché action toujours en création
    $myhtml .=  '<td hidden>' .
                '<input type="text" id="action' . $indice . '" name="action' . $indice . '" value="C">' .
                '</td>';
    
    //colonne suivante action suppresssion de la ligne ou téléchargement

     $myhtml .= '<td><div class="btn-group" role="group" >'
            . '<button type="button"  onclick="jsListeSuppression('. $indice . ',\'periode\')"  class="btn btn-sm mb-2">'
            . '<span class="bi bi-trash3-fill fa-lg" title="Supprimer" alt="Supprimer" />'
            . '</button></div></td>';
    
    //Date début et fin
    $myhtml .= mettreEnFormeControleDate("Debut",$required,$indice);
    $myhtml .= mettreEnFormeControleDate("Fin",$required,$indice);
    
    
    //jour semaine
    $myhtml .= "<td>";
    for ($i=1;$i<8;$i++){
        $myhtml .= genererCasesJoursSemaine($indice,$i);
    }
    $myhtml .= "</td>";
    
     //statut
    $myhtml .= "<td>" .genererCasesStatuts($indice,"&nbspDisponible&nbsp&nbsp","D","text-bg-success")
            .genererCasesStatuts($indice,"Indisponible","I","text-bg-danger")
            .genererCasesStatuts($indice,"&nbsp&nbsp&nbsp&nbspNeutre&nbsp&nbsp&nbsp&nbsp&nbsp","N","text-bg-info")
            ."</td>";
    return $myhtml;
}
function genererCasesJoursSemaine(int $indice,int $jour){
    $jourSemaine = TbDate::jourDeLaSemaine($jour,2,1);
    return "<div class=\"form-check form-switch\">
        <label class=\"form-check-label\" for=\"" .$jourSemaine . $indice . "\">" .$jourSemaine ."</label>
        <input class=\"form-check-input\" type=\"checkbox\" role=\"switch\" name=\"" .$jourSemaine . $indice . "\" id=\"" .$jourSemaine . $indice . "\" checked>
        </div>";
}
function genererCasesStatuts(int $indice,string $statut, string $codeStatut,$classTexte) :string{
// £statut = libellé, avec des blanc pour neutre, code statut sur 1 lette majuscuel
    return "<input  type=\"radio\" class=\"btn-check\" name=\"statut" . $indice . "\" id=\"" . $codeStatut . $indice . "\" autocomplete=\"off\"
                    onclick=\"jsCodeActionChanger(". $indice . ",'". $codeStatut ."')\" >
                    <label class=\"btn \" for=\"". $codeStatut . $indice . "\"><div class=\"" . $classTexte ." p-2\">$statut</div></label>";
  
}
function mettreEnFormeControleDate(string $debutfin, string $required, int$indice):string{
    return  '<td><input type="date" '. $required . ' name="date'. $debutfin . $indice . '"'.
                     'class="form-control" id="date'. $debutfin . $indice .  '"' .
                    ' onchange="jsChangerDateFin(\''. $indice . '\')">' .
            '</td>';
}
