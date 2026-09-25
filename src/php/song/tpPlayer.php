<?php ob_start(); ?>
<section class="py-5">
    <div class="container">
        <input type="hidden" id="url" value="<?= $url ?>" />  
        <div class="row">
            <!-- Type de titre --> 
            <div class="col-3">
                <input type="hidden"  id="idContexte" name="idContexte" value="<?= $idContexte;?>" />    
                <input type="hidden"  id="idSetlist" name="idSetlist" value="<?= $idSetlist;?>" /> 
                <strong>Contexte : <?= $contexte;?></strong><br><?php if(isset($libelleContexte)){echo($libelleContexte);}?>
            </div>
            <div class="col-6">
                <div class="input-group mb-5">
                    <label class="input-group-text" for="typeMP3">(Code)Libellé du MP3 *</label>
                    <select required class="form-select" id="typeMP3" onchange="jsAffichagePlayer()">
                        <?= shared\php\toolbox\Toolbox_liste::valeursChoixListe($typesSong,false,"ID","nomCompose",selected: $idTypeSong); ?>
                    </select>
                </div>
            </div>
            <div class="col-3">
                  <p class='border h4'><i class="bi bi-volume-up-fill">&nbsp;Vol. Clic</i>
                  <input type="range"
                  min="0" max="100" value="50" tabindex="0"
                  oninput="masterVolume = event.target.value / 100;"></p>
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
require('training/src/php/socle/tpLayout.php');?>
  