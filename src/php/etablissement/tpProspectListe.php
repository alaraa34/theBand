<?php 
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * affichage liste des prospects
 *
 * @author Alara
 *******************************************************************************/


use shared\php\bricks\Brick_accordion                  as BkAccordion;
use shared\php\classes\personalisation\Nomenclature    as Nomenclature;
use shared\php\modale\Toolbox_modal                    as TbModal;
use shared\php\toolbox\Toolbox_adressage               as TbAdressage;

ob_start();
?>

    <?php
    $numero = 0 ;
    echo (BkAccordion::accordion1Debut());
    foreach ($prospects as $prospect) {
        $numero += 1 ;
    ?>
        <?= BkAccordion::accordion2ItemAvantTitre($numero,false);?>
        <table width='100%'  border='1' cellspacing='1' >
        <tr>
            <td rowspan=2 class="barreTitre" align="left"><?= TbModal::mettreEnFormeActions($actions,$prospect->id);?></td>
            <td class="barreTitreGauche"><b><?= $prospect->etablissement->nom;?></b> </td>
            <td class="barreTitre" > <?= $prospect->etablissement->coordonnee->adresse?></td>
            <td class="barreTitre" > <?= $prospect->etablissement->coordonnee->ville?></td>
            <td class="barreTitre">
                Initié par : <?= $prospect->suiveur->abrev;?>
                <?= getTagStatut($prospect->idStatut);?>
            </td>
        </tr>
	<tr>
            <td  colspan = 4 class="barreTitreGauche" >
                Commentaire : <?=$prospect->commentaire->texte;?>
            </td>
        </tr>
        </table>
        <?= BkAccordion::accordion3ItemEntreTitreEtContenu($numero,$expandAccordeon);?>
        <!-- Liste des suivis -->
        <?= listerSuivis($prospect->suivis);?>
        <!-- fin d'accordéon-->
        <?= BkAccordion::accordion4ItemApresContenu();?>
    <!-- fin de boucle php-->
    <?php } 
     
    //Rajout des div modales de supprimer, tester et abandonner
    echo (BkAccordion::accordion5Fin());
    
    //ajout des div modales standard et spécifique
    echo TbModal::afficherListeDivModales($actions);
    
    $content = ob_get_clean(); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
    ?>
<?php
    function listerSuivis(array $suivis) :string{
    //affiche une liste de suivis , tableau collection d'instances de classes en parametre
        $myhtml = "<table width=\"70%\" class=\"table table-striped\">";
        $myhtml .="<tr><th>Date</th><th>Auteur</th><th>Action</th><th>Commentaire</th></tr>";
        foreach ($suivis as $suivi){
            $myhtml .="<tr><td>" . date_format(date_create($suivi->date),"d/m/Y") ."</td><td>". $suivi->auteur->abrev ."</td>";
            $myhtml .= "<td>".(string) Nomenclature::mdNomenclatureGetDetailFromID($suivi->idAction,"valeurA")."</td>";
            $myhtml .= "<td>". $suivi->commentaire->texte . "</td></tr>";
        }
        return $myhtml ."</table>";
    }
    function getTagStatut(int $idStatut) : string{
        switch ($idStatut) {
            case Prospect::STATUT_CREE;
                $couleur = "bg-warning";
                break;
            case Prospect::STATUT_ACTIF;
                $couleur = "bg-primary";
                break;
            case Prospect::STATUT_ACCORD;
                $couleur = "bg-success";
                break;
            case Prospect::STATUT_REFUS;
                $couleur = "bg-danger";
                break;
            case Prospect::STATUT_ABANDON;
                $couleur = "bg-dark";
        }
        return "<span class=\"position-absolute top-0 start-100 translate-middle badge rounded-pill " . $couleur . "\">" .
                Nomenclature::mdNomenclatureGetDetailFromID($idStatut) .  "<span class=\"visually-hidden\">Statut du propect</span> </span>";
    }
