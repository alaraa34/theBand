<?php 
declare(strict_types=1);
namespace theBand\src\php\proposition;
/*******************************************************************************
 * Template de saisie de proposition
 ******************************************************************************/
use shared\php\classes\lien\TypeLien        as TypeLien;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
ob_start(); 

?>


<form enctype="multipart/form-data" id="editorForm" method="post" action="<?= TbAdressage::getURLstatic('proposition','propositionMAJ')?>">
    <section class="py-5">
        <div class="container">
            <!--Identifiants proposition et song -->  
            <input type="hidden"  name="id" id="id" value="<?= $proposition->id ?>"/>
            <input type="hidden"  name="referer"  value="<?= TbAdressage::getReferer($proposition->id) ?>" />
            <!-- Titre et interprète -->  
            <div class="row">
                <!-- Titre --> 
                <div class="col-4">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Titre *</span>
                        <input type="text" name="titre" required class="form-control" placeholder="Titre" aria-label="titre" 
                               aria-describedby="basic-addon1" value="<?= $proposition->titre ?>"/>
                    </div>
                </div>
                <!-- Interprete --> 
                <div class="col-4">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon2">Interprète *</span>
                        <input type="text" name="interprete" class="form-control" required placeholder="Interprète" aria-label="auteur" 
                               aria-describedby="basic-addon1" value="<?= $proposition->interprete ?>"/>
                    </div>
                </div>
            </div>
            
            <!-- Liens --> 
            <div class="row">
                <div class="col-8">
                  <div class="input-group mb-3">
                      <input type="hidden" name="idLien" class="form-control" value="<?= getLienSite($proposition,"id") ?>"/>
                      <span class="input-group-text" id="basic-addon3">Lien YouTube *</span>
                      <input type="url" name="urlLien" class="form-control" required id="url" value="<?= getLienSite($proposition,"url") ?>"/>       
                  </div>
                </div>
            </div>
            <!-- Commentaires --> 
            <div class ="row">
                <div class="mb-3">
                  <input type="hidden"  name="idCommentaire" value="<?= $proposition->commentaire->id ?>"/>
                  <label for="commentaire" class="form-label">Commentaires</label>
                  <textarea class="form-control" name="texteCommentaire" id="commentaire" rows="5" placeholder="Quartier libre"><?= $proposition->commentaire->texte ?></textarea>
                </div>
            </div>
        </div>
    </section>
    <!-- validation du bouton -->
    <div class ="row">
        <div class="mb-3">
            <button type="submit" class="btn btn-primary pull-right">Valider</button>
        </div>
    </div><!-- comment -->
</form>
<?php
    $content = ob_get_clean(); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
    function getLienSite(object $proposition, string $zone){
        //récupère l'instance de type site ou une instance de lien à vide si pas trouvé
        $lien = $proposition->getlienfromCollection(TypeLien::SITE);
        return $lien->$zone;
    } 
    
    ?>
