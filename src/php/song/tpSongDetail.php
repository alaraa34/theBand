<?php 
namespace theBand\src\php\song;
/*******************************************************************************
 * 
 ******************************************************************************/
use shared\php\toolbox\Toolbox_html         as TbHtml;
use shared\php\classes\lien\Lienhtml        as Lienhtml;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use shared\php\toolbox\Toolbox_liste        as TbListe;
ob_start(); ?>
<form enctype="multipart/form-data"  method="post" action="<?= TbAdressage::getURLstatic('song','songMAJ')?>">
    <section class="py-5">
        <div class="container">
            <input type="hidden"  name="idSong" id="idSong" value="<?= $song->id ?>" />
            <input type="hidden"  name="referer"  value="<?= TbAdressage::getReferer($song->id) ?>" />
            <!-- Titre et interprète -->  
            <div class="row">
                <!-- Type de titre --> 
                <div class="col-2">
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="inputGroupSelect02">Type *</label>
                        <select name="idTypeSong" required class="form-select" id="inputGroupSelect02">
                            <?= TbListe::valeursChoixListe($typesSong,true, selected: $song->idTypeSong); ?>
                        </select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Titre *</span>
                        <input type="text" name="titre" class="form-control" required placeholder="Titre" aria-label="titre" 
                               aria-describedby="basic-addon1" value="<?= $song->titre ?>"/>
                    </div>
                </div>
                <div class="col-5">
                  <div class="input-group mb-3">
                      <span class="input-group-text" id="inter">Interprète *</span>
                      <input type="text" name="interprete" class="form-control" required 
                             placeholder="Interprète" aria-label="auteur" aria-describedby="inter" 
                             value="<?= $song->interprete ?>"/>
                    </div>
                </div>
            </div>
            <!-- Tonalités -->   
            <div class ="row">
                <div class="col-2">
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="tonaOrigine" >Tona initiale *</label>
                        <input type="text" id="tonaOrigine"  name="tonaOrigine" required class="form-control" placeholder="Ex : C, C#,Amin,Bb,DbMin..." 
                               aria-label="titre" aria-describedby="basic-addon1" value="<?= $song->tonaOrigine ?>"/>
                    </div>
                </div>
                <div class = "col-3">
                    <div class="input-group mb-3">
                          <label class="input-group-text" for="inputGroupSelect03">Ecart tona (1/2 tons)</label>
                          <select name="tonaScene" class="form-select" id="inputGroupSelect03">
                            <?= TbListe::valeursChoixListe($correctionsTona,true, selected:$song->tonaScene); ?>
                          </select>
                    </div>
                </div>
                <div class = "col-3">
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="tempo">Tempo (bpm)</label>
                        <input style="text-align:center;" type="number"  name="tempo" id="tempo" 
                              width="20"  min="0" max="200" value="<?= $song->tempo ?>">
                    </div>
                </div>
                <div class = "col-3">
                    <div class="input-group mb-3">
                          <label class="input-group-text" for="inputGroupSelect05">Lead Singer</label>
                          <select name="idLeadChant" class="form-select" id="inputGroupSelect05">
                            <?= TbListe::valeursChoixListe($users,true,"id","prenom",selected:$song->leadChant->id); ?>
                          </select>
                    </div>
                </div>
            </div>
            
             <!-- Liens --> 
            <?= Lienhtml::afficherBlocLien ($typesLien,$song->liens );?>
    
            <!-- Commentaires --> 
            <div class ="row">
                <div class="mb-3">
                  <input type="hidden"  name="idCommentaire" id="idCommentaire" value="<?= $song->commentaire->id ?>" />
                  <label for="commentaire" class="input-text">Commentaires</label>
                  <textarea class="form-control" name="commentaire" id="commentaire" rows="2"><?= $song->commentaire->texte ?></textarea>
                </div>
            </div>
            
           <!-- Soumission formulaire -->
            <?= TbHtml::htmlBoutonValidation()?>
        </div>
    </section>
    <?= TbHtml::mentionTailleFichiers()?>
</form>

<?php 
$content = ob_get_clean();
require(TbAdressage::projetGetLayout(__NAMESPACE__));?>

