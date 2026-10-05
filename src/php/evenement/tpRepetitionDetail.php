<?php 
declare(strict_types=1);
namespace theBand\src\php\evenement;
/*******************************************************************************
 * Affichage détail de répétition
 *
 * @author Alara
 *******************************************************************************/
use shared\php\toolbox\Toolbox_html         as TbHtml;
use shared\php\toolbox\Toolbox_liste        as TbListe;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use theBand\src\php\song\Song               as Song;

ob_start();

?>
<form method="post" id="repetitionPostForm" action="<?= TbAdressage::getURLstatic('evenement','repetitionMAJ')?>">
    
        <div class="container">
            <!--Identifiants répétition et liste des titres sélectionnés-->  
            <input type="hidden"  name="idRep" id="idRep" value="<?= $repetition->id ?>" />
            <input type="hidden"  name="idSetlist" id="idSetlist" value="<?= $repetition->setlist->id ?>" />
            <input type="hidden"  name="idSongsSelected" id="idSongsSelected" 
                   value="<?= implode("-",repetitionIdSongSelectionnes($repetition))?>" />
            <!-- date/lieu  -->  
            <fieldset class="border rounded-3 p-3">
                <legend class="float-none w-auto" ><h6>Date, heure et lieu </h6></legend>
                <div class="row">
                    <div class="col-5">
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1">Date</span>
                            <input type="date" name="dateRep"  required value="<?= $repetition->GetDate()?>" />
                            <span class="input-group-text" id="basic-addon1">De</span>
                            <input type="time" name="heureRep" required class="form-control" 
                                                placeholder="Heure" aria-label="titre" value="<?= $repetition->GetHeure()?>"/>
                            <span class="input-group-text" id="basic-addon1">&Agrave;</span>
                            <input type="time" name="heureRepFin" required class="form-control" 
                                                placeholder="Heure de fin" aria-label="titre" value="<?= repetitionGetHeureFin($repetition)?>"/>
                        </div>
                    </div>
                    <!-- Lieu de répétition --> 
                    <div class="col-3">
                        <div class="input-group">
                            <label class="input-group-text" for="inputGroupSelect02">Lieu * </label>
                            <select name="lieuRep" class="form-select" id="inputGroupSelect02" required>
                                 <?= TbListe::valeursChoixListe($studios,true,"idEtablissement","nom",selected:$repetition->etablissement->id); ?>
                            </select>
                        </div>
                    </div>
                    <!--confirmation --> 
                    <div class="col-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="controle" name="controle" 
                                <?php if($repetition->confirme){echo ("checked");}?> >
                            <label class="form-check-label" for="controle">Resa Validée</label>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset class="border rounded-3 p-3">
                <legend class="float-none w-auto" ><h6>Sélection automatique</h6></legend>
                <div class="row">
                    <!-- Périmètre de répétition --> 
          
                    <div class="col">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="selectionAuto" value="titresTest" id="titresTest">
                            <label class="form-check-label" for="titresTest">Titres en test</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="selectionAuto" value="titresProchainConcert" id="titresProchainConcert">
                            <label class="form-check-label" for="titresProchainConcert">Setlist du prochain concert</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="selectionAuto" value="titresRepetPrecedente" id="titresRepetPrecedente">
                            <label class="form-check-label" for="titresRepetPrecedente">Titres de la dernière répétition</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="selectionAuto" value="titresPerformance" id="titresPerformance">
                            <label class="form-check-label" for="titresPerformance">
                                <input style="text-align:center;" name="titresNbPerf" width="12"  min="1" max="12" type="number" value="8" />
                                Performances les moins notées
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <button type="button" onclick="jsRepetSelectionAuto('<?= TbAdressage::urlControleur("evenement","repetitionSelectSongAuto");?>')" class="btn btn-success btn-sm">Sélectionner</button>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset class="border rounded-3 p-3">
                <legend class="float-none w-auto" ><h6>Titres choisis</h6></legend>
                <!--songs à répéter -->
                <div class="row">
                    <div class="col-6">
                        <span class="h5">Titres disponibles</span> <small class="text-muted">(Ctrl + clic pour choix multiple)</small>
                        <!-- contenu fixe = tous les titres test + concert ; seule la sélection change -->
                        <select id="choixSong" onchange="jsRepetSynchroniser()" name="choixSong" class="form-select" size="15" multiple required>
                           <?= TbListe::valeursChoixListe($songs,identifiant:"id", selected:repetitionIdSongSelectionnes($repetition)); ?>
                         </select>
                    </div>
                    <div class="col-1"></div>
                    <div class="col-5">
                        <!-- Zone contenant les song sélectionnées -->
                        <span class="h4">Sélection actuelle</span>
                        <span id="nbChoisis" class="badge rounded-pill bg-info text-dark"><?= count($repetition->setlist->details);?></span>                        <ul id="morceauxChoisis" class="list-group">
                             <?= repetitionSongSelectionnes($repetition); ?>
                        </ul>
                    </div>
                </div>
            </fieldset>
            <!-- Commentaires --> 
            <div class ="row my-3">
                <div class="input-group">
                    <input type="hidden"  name="idCommentaire" id="idCommentaire" value="<?= $repetition->commentaire->id ?>" />
                    <textarea class="form-control" name="commentaire" id="commentaire" rows="2" 
                        placeholder="Commentaire"><?= $repetition->commentaire->texte?></textarea>
                </div>
            </div>
        </div>
        <!-- Soumission formulaire -->
        <?= TbHtml::htmlBoutonValidation()?>
    </div>
</form>
<script>
    //song.js est chargé en fin de page : initialisation une fois le document prêt
    document.addEventListener('DOMContentLoaded', jsRepetInit);
</script>

<?php 
    $content = ob_get_clean(); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
 
function repetitionIdSongSelectionnes($repetition){
//extraite le tableau des identifiants des songs concernés par la répétition
    $liste = [];
    foreach($repetition->setlist->details as $detail){
        $liste[]= $detail->song->id;
    }
    return $liste;
}

function repetitionGetHeureFin($repetition):string {
    //met en forme la date
    if (strlen($repetition->dateHeureFin)==0){
        return "21:00";
    }
    else{
       return date_format(date_create($repetition->dateHeureFin),"G:i");
    }
}

function repetitionSongSelectionnes($repetition){
//extraite le tableau des songs concernés et récupère leur titre formatté dans $songs
    $liste = "";
    foreach($repetition->setlist->details as $detail){
        //même libellé que la liste de gauche (titre-interprete) ; data-id lu par song.js
        $liste .= '<li class="list-group-item" data-id="' . $detail->song->id . '">'
                . htmlspecialchars(trim((string)$detail->song->titre) . "-" . trim((string)$detail->song->interprete)) . "</li>";
    }
    return $liste;
}

function valeurCheckBox($repetition){
    if ($repetition->confirme){return "checked";} 
}
?>