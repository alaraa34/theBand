<?php
declare(strict_types=1);
namespace theBand\src\php\evenement;
/*******************************************************************************
 * 
 *
 * @author Alara
 *******************************************************************************/

use theBand\src\php\song\Detail             as Detail;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use theBand\src\php\song\Setlist            as Setlist;

ob_start(); ?>
<br><br<br>
<!<!-- Modification de la set list en mode drag and dop -->
<p align='center'>Playlist : <?= $evt->setlist->nomListe;?><p>
<div class="scrumboard row">
    <!<!-- Titres non affectés -->
    <div class="column flex" id="liste0">
        <div class="d-grid  mx-auto">
            <button type="button" disabled class="btn btn-info position-relative">
                Titres disponibles
                <?= compterAffichageUnePartie(Detail::NON_AFFECTE,$evt->setlist);?>
            </button>
        </div>
        <!-- affichage des titres non affectés-->
        <?= initialiserAffichageUnePartie(Detail::NON_AFFECTE,$evt->setlist) ;?>
    </div>
    <!-- affichage des titres Partie 1-->
    <div class="column flex" id="liste1">
        <div class="d-grid  mx-auto">
            <button type="button" disabled class="btn btn-primary position-relative">
                Première partie
                <?= compterAffichageUnePartie(1,$evt->setlist);?>
            </button>
        </div>
        <!-- affichage des tites partie 1-->
        <?= initialiserAffichageUnePartie(1,$evt->setlist) ;?>
    </div>
    <!-- affichage des titres Partie 2-->
    <div class="column flex" id="liste2">
        <div class="d-grid  mx-auto">
            <button type="button" disabled class="btn btn-primary position-relative">
                Deuxième partie
                <?= compterAffichageUnePartie(2,$evt->setlist);?>
            </button>
        </div>
        <!-- affichage des tites partie 2-->
        <?= initialiserAffichageUnePartie(2,$evt->setlist) ;?>
    </div>
    <!-- affichage des titres Partie 3 Rappel-->
    <div class="column flex" id="liste3">
        <div class="d-grid  mx-auto">
            <button type="button" disabled class="btn btn-primary position-relative">
                Rappel
                <?= compterAffichageUnePartie(Detail::RAPPEL,$evt->setlist);?>
              </button>
        </div>
        <!-- affichage des tites partie 3-->
        <?= initialiserAffichageUnePartie(Detail::RAPPEL,$evt->setlist) ;?>
    </div>
</div>
<div class="scrumboard row ">
    <form enctype="multipart/form-data"  method="post" action="<?= TbAdressage::getURLstatic('evenement','setlistMAJ')?>">
        <input type="hidden"  name="idSetlist" value="<?= $evt->setlist->id?>" />
        <input type="hidden"  name="listePartie1" id="listePartie1" value="" />
        <input type="hidden"  name="listePartie2" id="listePartie2" value="" />
        <input type="hidden"  name="listePartie3" id="listePartie3" value="" />
        <button type="submit" class="btn btn-success">Enregistrer la SetList</button>
    </form>
</div>


<?php $content = ob_get_clean(); 
 require(TbAdressage::projetGetLayout(__NAMESPACE__));
 
function initialiserAffichageUnePartie($partie, Setlist $setlist){
//envoie le code pour afficher une partie du répertoire
    $myhtml = "";
    foreach($setlist->details as $detail){
        if ($detail->partie==$partie) {$myhtml .= dessinerUnTitre($detail);}
    }
    return $myhtml;
 }
 
 function compterAffichageUnePartie(int $partie, Setlist $setlist){
 //envoie nombre de titres dans une partie
    $myhtml = "<span id=\"compteur" . $partie . "\" class=\"position-absolute 
            badge rounded-pill text-bg-danger\">";
    $ctr = 0;
    foreach($setlist->details as $detail){
        if ($detail->partie==$partie) {$ctr += 1;}
    }
    return $myhtml . $ctr . "</span>";
 }
  
function dessinerUnTitre(Detail $detail){
//constitue l'affichage d'un titre
//contient d'Id de la song et une flèche verticale si enchainement
//enchaînement : double-clic sur PC, bouton maillon sur tablette (le double-tap n'existe pas)
    $id = $detail->song->id;
    $myhtml = "<div id=\"song". $id . "\" ondblclick=\"jsEnchainer('" . $id . "')\" class=\"portlet\">";
    $myhtml .= "<p hidden>" . $id . "</p>";
    $myhtml .= "<div class=\"portlet-header\">";
    $myhtml .= "<button type=\"button\" class=\"bouton-enchainer btn btn-link p-0\" title=\"Enchaîner avec le titre suivant\""
             . " onclick=\"jsEnchainer('" . $id . "')\" ondblclick=\"event.stopPropagation()\"><i class=\"bi bi-link-45deg\"></i></button>";
    $myhtml .= "<strong>" . htmlspecialchars((string)$detail->song->titre) . "</strong><br>" .
                htmlspecialchars((string)$detail->song->interprete) .
                "    <span id=\"arrow". $id . "\"" ;
    //indicateur d'enchainement
    if(!$detail->enchainement){$myhtml.= "style=\"display:none;\"";}
    $myhtml .= " class=\"". Detail::ICONE_ENCHAINEMENT . "\"></span></div>";
    $myhtml .= "</div>";
    return $myhtml;
 }
?>
