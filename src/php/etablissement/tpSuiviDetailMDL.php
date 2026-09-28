<?php 
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * affichage modif détail MDL 
 *
 * @author Alara
 *******************************************************************************/
use shared\php\modale\Toolbox_modal         as TbModal;
use shared\php\toolbox\Toolbox_liste        as tbListe;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
?>
<!--modale spécifique pour l'ajout d'un suivi prospect -->
<div class="modal fade" id="<?= TbModal::modalGetNomDiv($action['modale'])?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"> 
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?=$action['texte'];?></h1>
            </div>
            <!-- formulaire modal -->
            <form method="POST" action="<?= TbAdressage::getURLControleurFromCFPAdress($action['modale'])?>"> 
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="inputGroupSelect02">Action</label>
                        <select name="idAction" class="form-select" id="idAction" required>
                             <?= tbListe::valeursChoixListe(Suivi::listeAction(),true); ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text">Commentaire</span>
                        <textarea class="form-control" name="commentaire" id="commentaire" rows="2" 
                                  placeholder="Commentaire général"></textarea>
                    </div>
                    <!-- Nom de la modale -->
                    <input type="hidden" name="nomModale" value="<?=$action['modale']?>"/>
                    <!--id alimenté lors du clic sur l'icone qui active la modale -->
                    <input type="hidden" id="<?= TbModal::modalNomInputIdentifiant($action['modale'])?>" name="<?= TbModal::modalNomInputIdentifiant($action['modale'])?>" value="-1"/>
                </div>
                <div class="modal-footer"> 
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abandonner</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
            <!-- fin formulaire modal -->
        </div>
    </div>
</div>


