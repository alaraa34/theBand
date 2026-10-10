<?php 
declare(strict_types=1);
namespace theBand\src\php\song;
/*******************************************************************************
 * Affichage détail de répétition
 *
 * @author Alara
 *******************************************************************************/
use shared\php\toolbox\Toolbox_liste        as TbListe;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use theBand\src\php\song\Song               as Song;

ob_start();

?>
<form method="post" id="playlistPostForm" action="<?= TbAdressage::getURLstatic('song','playlistMAJ')?>">
    <section class="py-5">
        <div class="container">
            <!--Identifiants répétition et liste des titres sélectionnés-->  
            <input type="hidden"  name="idSetlist" id="idSetlist" value="<?= $setlist->id ?>" />
            <input type="hidden"  name="referer"  value="<?= TbAdressage::getReferer($setlist->id) ?>" />
            <input type="hidden"  name="idSongsSelected" id="idSongsSelected" 
                   value="<?= implode("-",idSongSelectionnes($setlist))?>" />
            <!-- date/lieu  -->  
            
            <!--songs à répéter -->
            <div class="row">
                <div class="col-6">
                    <span class="h4">Sélectionner (Touche Ctrl pour choix multiple)</span>
                    <select id="choixSong" onclick="jsListerChoixRepet()"  name="choixSong" class="form-select" size="25" multiple required>
                       <?= TbListe::valeursChoixListe($songs,selected:idSongSelectionnes($setlist)); ?>
                     </select>
                </div>
                <div class="col-1"></div>
                <div class="col-5">
                    <!-- Zone contenant les song sélectionnées -->
                    <span class="h4">Sélection actuelle</span>
                    <span id="nbChoisis" class="badge rounded-pill bg-info text-dark"><?= count($setlist->details);?></span>
                    <ul id="morceauxChoisis" class="list-group">
                         <?= songSelectionnes($setlist); ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- validation du bouton -->
    <div class ="row">
        <div class="mb-3">
            <button type="submit" class="btn btn-primary pull-right">Valider la Playlist</button>
        </div>
    </div>
</form>

<?php 
    $content = ob_get_clean(); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
 
function idSongSelectionnes(Setlist $setlist){
//extraite le tableau des identifiants des songs concernés par la répétition
    $liste = [];
    foreach($setlist->details as $detail){
        $liste[]= $detail->song->id;
    }
    return $liste;
}


function songSelectionnes(Setlist $setlist){
//extraite le tableau des songs concernés et récupère leur titre formatté dans $songs
    $liste = "";
    foreach($setlist->details as $detail){
        $liste .= "<li>" . $detail->song->titre . " " .  $detail->song->interprete . "</li>";
    }
    return $liste;
}

?>