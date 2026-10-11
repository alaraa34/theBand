<?php 
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Controleur des l'affichage des players MP3 et document
 *
 * @author Alara
 *******************************************************************************/

use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use metronome\php\Metronome                 as Metronome;


ob_start(); ?>
<section class="py-4">
    <div class="container">
        <input type="hidden" id="url" value="<?= $url ?>" />  
        <div class="row">
            <!-- Type de titre --> 
            <div class="col-3">
                <input type="hidden"  id="idContexte" name="idContexte" value="<?= $idContexte;?>" />    
                <input type="hidden"  id="idSetlist" name="idSetlist" value="<?= $idSetlist;?>" /> 
                <strong>Contexte : <?= $contexte;?></strong><br><?php if(isset($libelleContexte)){echo($libelleContexte);}?>
            </div>
            <!-- MP3 à jouer : original (tona de scène appliquée) ou enregistrement du groupe -->
            <div class="col-6">
                <span class="me-3"><strong>MP3 :</strong></span>
                <?php foreach ($typesMP3 as $idType => $libelleType) { ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="typeMP3" id="typeMP3_<?= $idType ?>" value="<?= $idType ?>"
                               <?= $idType === $idTypeLien ? 'checked' : '' ?> onchange="jsAffichagePlayer()">
                        <label class="form-check-label" for="typeMP3_<?= $idType ?>"><?= $libelleType ?></label>
                    </div>
                <?php } ?>
            </div>
            <div class="col-3">
                <?= Metronome::afficherVolume()?>
            </div>
        </div>

        <!--Affichage du player-->
        <div class="row ">
            <div id="player" >
                <?= $players ;?>
            </div>
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); 
require(TbAdressage::projetGetLayout(__NAMESPACE__));?>
  