<?php 
namespace theBand\src\php\etablissement;
use shared\php\modale\Toolbox_modal as TbModal;
?>
<!--Modale standard -->
<div class="modal fade" id="<?=$action['modale']?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"> 
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?=$action['texte'];?></h1>
            </div>
            <!-- formulaire modal -->
            <form method="POST" action="index.php?action=<?=$action['sujet'] . "MDL" . $action['modale']?>"> 
                <div class="modal-body">
                    <span class="input-group-text" id="basic-addon1">Commentaire *</span>
                        <input type="text" name="commentaire" required class="form-control" 
                               placeholder="Nouveau commentaire"
                               value=""/>
                    </span>
                    <!-- Nom de la modale -->
                    <input type="hidden" name="nomModale" value="<?=$action['modale']?>"/>
                     <!-- bouton identifiant -->
                    <input type="hidden" id="<?=TbModal::modalNomInputIdentifiant($action['modale'])?>" 
                           name="<?= TbModal::modalNomInputIdentifiant($action['modale'])?>" value="-1"/>
                </div>
                <div class="modal-footer"> 
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                    <button type="submit" class="btn btn-primary">Oui</button>
                </div>
            </form>
            <!-- fin formulaire modal -->
        </div>
    </div>
</div>


