<?php
namespace theBand\src\php\song;
/*******************************************************************************
 * 
 ******************************************************************************/
use shared\php\bricks\Brick_table as BkTable;
use theBand\src\php\socle\UserTheBand as User;
use shared\php\toolbox\Toolbox_date as TbDate;
use shared\php\toolbox\Toolbox_adressage as TbAdressage;

ob_start(); 
echo('<form method="post" action="' . TbAdressage::getURLstatic("song","repertoireMajVotes") . '">');

$matable = new BkTable(tableClass:'table table-hover table-sm');
//pas de numérotation sur première et dernière ligne
$matable->addHeaders(["#;1;-1","Vote<br>Exprimé","TITRE","INTERPRETE","Performance<br>Groupe","Dernière<br>Eval le"]);

//<!--BOUCLE SUR LES PERFORMANCES-->

$voteLibelle = "1=Pas top 3=Moyen 5=Parfait"; 
//ligne bouton de vote du haut 
$matable->addCell(voteligneBouton(1,$voteLibelle),"barreTitre", nouveau: true, colspan: 6);

foreach ($performances as $performance) {
    //<!--Balise  récupère un M si vote -->
    $texte = '<input hidden type="text" id="changevote' . $performance->song->id . '" '.
                   'name="changevote' . $performance->song->id . '" value=""/>';
    $matable->addCell($texte, nouveau:true,hidden:true,alignement:"MC");

    //<!--Balise  qui afficha le logo si il y a votevote -->
    $texte = '<p id="vote'. $performance->song->id . '"</p>';
    $matable->addCell($texte, alignement:"MC");

    $matable->addCell(htmlspecialchars($performance->song->titre));
    $matable->addCell(htmlspecialchars($performance->song->interprete));

    //<!--Affichage du vote, id=idsong car toujours renseigné-->
    $texte= ' <input style="text-align:center;" type="number"  name="cvoteg_'. $performance->song->id .'"'.
        ' oninput="jsVoteModif(\'vote' . $performance->song->id . '\')"  width="15"  min="1" max="5"  
            value="' . $performance->groupe . '"/>';
    
    $texte1= ' <input style="text-align:center;" type="number"  name="cvotep_'. $performance->song->id .'"'.
        ' oninput="jsVoteModif(\'vote' . $performance->song->id . '\')"  width="15"  min="1" max="5"  
            value="' . $performance->perso . '"/>';

    $matable->addCell($texte, alignement: "C");
    $matable->addCell($texte1, alignement: "C");
    $matable->addCell(songListeFormatterDate($performance->date), alignement: "C");

}
//ligne bouton de vote du bas
$matable->addCell(voteligneBouton(2,$voteLibelle),"barreTitre", nouveau: true, colspan: 6);

//affichage table
echo $matable->render();
echo "</form>";

$content = ob_get_clean(); 
require(TbAdressage::projetGetLayout(__NAMESPACE__));

function voteligneBouton(int $ligne, string $voteLibelle){
    return '<input id="btnvote' . $ligne . '" style="display:none" type="submit"  
            value="Enregistrer les votes de "' . User::connectUserGetInfo("prenom",""). '" /> 
            <span class="badge bg-secondary"> Maitrise du titre par le groupe : "' . $voteLibelle . '</span>';
}   
function songListeFormatterDate(string $date){
    if ($date == "0000-00-00"  or $date==""){
        $dateFormattee = '<span title="Pas de performance déjà exprimée pour ce titre"> <i class="bi bi-calendar-x"></i></span>';
    }
    else{
        $dateFormattee = TbDate::dateFormatJJMMAAAA($date);
    }
    return $dateFormattee;
}
