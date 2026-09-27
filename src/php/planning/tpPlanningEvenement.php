<?php 
declare(strict_types=1);
namespace theBand\src\php\planning ;
/**
 * Description of Planning
 *
 * @author araib
 */
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
ob_start(); ?>

<section class="py-5">
    <div class="container">
        <div class ="row">
            <!-- datedébut  -->  
            <div class="col-4">
                <div class="input-group mb-3">
                  <span class="input-group-text" id="basic-addon1">Date début de planning</span>
                  <input type="date" id="dateDebutPlanning"  min="2023-01-01" max="2030-12-31" required 
                         value="<?= date("Y-m-d") ?>"
                         onchange="<?= "Get_Form_zone('" . $urlControleur ."', 'detailPlanning','dateDebutPlanning')"?>"/>
                </div>
            </div>
            <div class="col-2"></div>
            <div class="col-4">
                 <div class="input-group mb-5">
                    <span class="left-button bi bi-chevron-left" title="Affiche le planning avec 15 jours de moins" 
                          id="prev" onclick="<?= fonctionOnClick(-15,$urlControleur) ?>"></span> 
                    <span class="right-button bi bi-chevron-right" title="Affiche le planning avec 15 jours de plus"
                          id="next" onclick="<?= fonctionOnClick(15,$urlControleur) ?>"></span>
                </div> 
                 <div class="input-group mb-5">
                    <span class="left-button bi bi-chevron-double-left" title="Affiche le planning avec 30 joursde moins" 
                          id="prev" onclick="<?= fonctionOnClick(-30,$urlControleur) ?>"></span> 
                    
                    <span class="right-button bi bi-chevron-double-right" title="Affiche le planning avec 30 jours de plus"
                          id="next" onclick="<?= fonctionOnClick(30,$urlControleur) ?>"></span>
                </div> 
            </div>
        </div>
        <!--table bilan des plannings individuels $plnninggroupe initialisé dans le controlleur-->
        <div class ="row">
            <div id="detailPlanning" >
                <?= $planningGroupe ;?>
            </div>
        </div>
    </div>
</section>
  
<?php $content = ob_get_clean(); 
 require(TbAdressage::projetGetLayout(__NAMESPACE__));
 function fonctionOnClick(int $duree, string $urlControleur){
     return "jsPlanningAddDate(". $duree . ",'" . $urlControleur ."','detailPlanning','dateDebutPlanning')";
 }
?>
