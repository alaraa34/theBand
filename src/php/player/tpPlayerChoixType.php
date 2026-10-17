<?php 
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Choix du type de document pour le player de documents
 *
 * @author Alara
 *******************************************************************************/


use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use shared\php\toolbox\Toolbox_liste        as TbListe;


ob_start(); ?> 
<br><br<br>
 
    <form method="post" action="<?= TbAdressage::getURLstatic('player','playerDocAffichage',0,'playerDoc') ?>">  
        <br><br>
        <input type="hidden" name="idSetlist" value="<?=$idSetlist?>" />
        <fieldset class="border rounded-5">
        <legend class="float-none w-auto px-3" ><h6>Type de document à afficher</h6></legend>
        <div class="row justify-content-center gx-5">
   
            <div class="col-1"></div>
            <div class="col-3">
             
                    
                    <select required class="form-select" id="typePDF" name="typePDF">
                        <?= TbListe::valeursChoixListe($extensions,true); ?>
                    </select>
                    <br>
                    <input type="submit" class="btn btn-primary" value="Valider"  />
                    <br>
            </div>
            <div class="col-1"></div>
            <br>
        </div>
</fieldset>
     </form>
   
<?php $content = ob_get_clean(); 
 require(TbAdressage::projetGetLayout(__NAMESPACE__)) ?>