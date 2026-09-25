<?php 
declare(strict_types=1);
namespace theBand\src\php\planning ;
/**
 * Description of Planning
 *
 * @author araib
 */
use shared\php\toolbox\Toolbox_date as TbDate;
use shared\php\toolbox\Toolbox_adressage as TbAdressage;
ob_start(); 
?>

<!-- zone cachée qui contient les infos de planning de départ -->
<input type="hidden"  id="listeMEF" value="<?= $listeMEF ?>" />
<input type="hidden"  id="url" value="<?= $url ?>" />
<div class="row justify-content-center">
    <div class="col-md-3"></div>
    <div class="col-md-5">
        <div class="calendar-container">
            <div class="calendar"> 
                <div class="year-header"> 
                    <span class="left-button fa fa-chevron-left" id="prev"> </span> 
                    <span class="year" id="year"></span> 
                    <span class="right-button fa fa-chevron-right" id="next"> </span>
                </div> 
                <?= ecrireLignesMois();?>
                <?= ecrireLignesJour();?> 
                <div class="frame"> 
                    <table class="dates-table w-100"> 
                        <tbody class="tbody"/>             
                    </table>
                </div> 
            </div>
        </div>
    </div>
    <div class="col-md-3 align-self-center">
        <div class="btn-group-vertical text-left">
            <button type="button" class="btn btn-success " id="add-buttonDisponible">Disponible</button>
            <button type="button" class="btn btn-danger " id="add-buttonIndisponible">Indisponible</button>
            <button type="button" class="btn btn-info " id="add-buttonNeutre">Neutre</button>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); 
 require(TbAdressage::projetGetLayout(__NAMESPACE__));
 
function ecrireLignesJour(){
    $myhtml = "<table class=\"days-table w-100\">" ;
    for ($i=1;$i<8;$i++){
        $myhtml  .= "<td class=\"day\">" . TbDate::jourDeLaSemaine($i,3,1). "</td>";
    }
    return $myhtml. "</table>" ;
}
function ecrireLignesMois(){
    $myhtml = "<table class=\"months-table w-100\"><tbody><tr class=\"months-row\">" ;
    for ($i=1;$i<13;$i++){
        $myhtml  .= "<td class=\"month\">" . TbDate::moisEnLettres($i,3,1). "</td>";
    }
    return $myhtml. "</tr></tbody></table>" ;
}
?>
 