<?php 
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Controleur des l'affichage des players MP3 et document
 *
 * @author Alara
 *******************************************************************************/

use shared\php\toolbox\Toolbox_liste        as TbListe;
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
            <!-- Type de MP3 à jouer --> 
            <div class="col-6">
                <div class="input-group mb-3">
                    <label class="input-group-text" for="typeMP3">(Code)Libellé du MP3 *</label>
                    <select required class="form-select" id="typeMP3" onchange="jsAffichagePlayer()">
                        <?= TbListe::valeursChoixListe($typesSong,false,"ID","nomCompose",selected: $idTypeSong); ?>
                    </select>
                </div>
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
  