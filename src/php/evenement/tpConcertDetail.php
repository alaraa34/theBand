<?php 
    declare(strict_types=1);
    namespace theBand\src\php\evenement;
    /*******************************************************************************
     * Affichage détail de concert
     *
     * @author Alara
     *******************************************************************************/
    use shared\php\toolbox\Toolbox_liste as TbListe;
    use shared\php\modale\Toolbox_modal as TbModal;
    use shared\php\toolbox\Toolbox_html as TbHtml;
    use shared\php\toolbox\Toolbox_adressage as TbAdressage;
   
    ob_start(); 
?>
<br>
<form method="post" id="editorForm" action="<?= TbAdressage::getURLstatic('evenement','concertMAJ')?>">
    <!--Identifiants concert -->  
    <input type="hidden"  name="idConcert" id="idConcert" value="<?= $concert->id?>" />
    <input type="hidden"  name="idSetlist" id="idSetlist" value="<?= $concert->setlist->id?>" />
    <!-- date/lieu  -->
    <fieldset class="border col-12">
        <legend>Horaire et lieu</legend>
        <div class="row justify-content-center ">
            <div class="col-5">
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">Date et heure</span>
                    <input type="date" name="dateConcert"  min="2023-01-01" max="2030-12-31" required value="<?= $concert->getDate()?>"/>
                    <input type="time" name="heureConcert" required class="form-control" 
                            placeholder="Heure" aria-label="titre" value="<?= $concert->getHeure()?>"/>
                </div>
            </div>
            <!-- Etablissement --> 
            <div class="col-4">
                <div class="input-group mb-3">
                    <label class="input-group-text" for="lieuComboBox">Etablissement *</label>
                    <select id="lieuConcert" name="lieuConcert" class="form-select" required >
                        <?= TbListe::valeursChoixListe($etablissements,true,"idEtablissement","nom",selected:$concert->etablissement->id); ?>
                    </select>
                </div>
            </div>
            <div class="col-3">
                <!--Bouton pour créer un nouvel établissement avec zone de saisie seulement en création-->
                <div class="input-group m-0">
                    <button id="btNouveau" type="button" <?php if($id > 0) {echo(" hidden ");}?>" 
                            class="btn btn-light" data-bs-toggle="modal" data-bs-target="#<?= TbModal::modalGetNomDiv($actions[0]['modale']);?>">
                        Nouvel Etablissement
                    </button>
                 </div>
            </div>
        </div>
    </fieldset>
    <br>   
    <!--Choix set list standard ou spécifique -->
    <fieldset class="border col-12">
        <legend class="w-auto px-2">Initialiser ou Ré-initialiser la Setlist de ce concert par celle d'un autre concert</legend>
        <div class="row justify-content-center">
            <div class="col-5">
                <div class="input-group">
                    <label class="input-group-text" for="modeleSL">Setlist à copier</label>
                    <select id="modeleSL" name="modeleSL" class="form-select">
                        <!--Zone non initialisée -->
                        <?= TbListe::valeursChoixListe($modelesSL,true,"id","nomListe"); ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">&nbsp;</div>
    </fieldset>

    <!-- Commentaires --> 
    <?= $textbox->renderComplete($concert->commentaire->id,$concert->commentaire->texte);?>
   
    <!-- Soumission formulaire -->
     <?= TbHtml::htmlBoutonValidation()?>
</form>

<?php 

    echo (TbModal::afficherListeDivModales($actions));

    $content = ob_get_clean(); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
    
?>