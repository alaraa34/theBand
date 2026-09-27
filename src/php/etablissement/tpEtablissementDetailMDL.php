<?php 
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * affichage saisie modale établissement sur un concert
 *
 * @author Alara
 *******************************************************************************/
use shared\php\toolbox\Toolbox_liste        as TbListe;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use shared\php\modale\Toolbox_modal         as TbModal;

?>
<!--Affichage des DIV établissement modale -->
<div class="modal fade" id="<?= TbModal::modalGetNomDiv($action['modale'])?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"> 
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel"><?=$action['texte'];?></h1>
            </div>
            <!-- formulaire modal <form method="POST" onsubmit="return Get_Form(urlControleur, nomFormulaire, nomComposant);">-->
            <form  method="POST"  id="form<?= TbModal::modalGetNomDiv($action['modale'])?>" />
            <!-- onsubmit="<?= "Get_Form_formulaire('" . TbAdressage::urlControleur("etablissement", "ajouter") ."','formMDL','lieuConcert')"?>"/>-->
                <div class="modal-body">
                    <!-- SAISIE -->    
                    <div class="input-group">
                        <span class="input-group-text" id="titreEtab">Etablissement *</span>
                        <input type="text" name="nomEtab" class="form-control" placeholder="Nom" 
                               aria-label="titre" aria-describedby="titreEtab"
                               value=""/>
                    </div>

                    <div class="input-group">
                        <label class="input-group-text" for="inputTypeEtab">Catégorie * </label>
                        <select name="typeEtab" class="form-select" id="inputTypeEtab" required>
                            <?= TbListe::valeursChoixListe($action['typesEtab'], selected: 37)?>
                        </select>
                    </div>

                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">Adresse *</span>
                        <input type="text" name="adresseEtab" required class="form-control" 
                               placeholder="1 rue du quai" aria-label="titre" aria-describedby="basic-addon1"
                               value=""/>
                    </div>    
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">Ville *</span>
                        <input type="text" name="villeEtab" required class="form-control" 
                               placeholder="34000 Montpellier" aria-label="titre" 
                               aria-describedby="basic-addon1" 
                               value=""/>
                    </div> 
                    <div class="input-group">
                        <span class="input-group-text">Commentaire</span>
                        <textarea class="form-control" name="texteCommentaire" id="commentaire" rows="4" 
                                  placeholder="Commentaire général sur l'établissement"></textarea>
                    </div>
                    <!-- FIN SAISIE -->                       
                </div>
                <div class="modal-footer"> 
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" onclick="<?= "Get_Form_formulaireModal('" . TbAdressage::getURLControleurFromCFPAdress($action["modale"]) . "','" . 
                                            TbModal::modalGetNomDiv($action["modale"]) ."','lieuConcert')"?>" 
                                  class="btn btn-primary">Valider</button>
                </div>
            </form>
            <!-- fin formulaire modal -->
        </div>
    </div>
</div>

