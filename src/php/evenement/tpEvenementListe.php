<?php declare(strict_types=1);
namespace theBand\src\php\evenement;
/*******************************************************************************
 * 
 *
 * @author Alara
 *******************************************************************************/

use shared\php\toolbox\Toolbox_adressage as TbAdressage;

ob_start(); ?>

    <?php
    use shared\php\bricks\Brick_accordion as BkAccordion;
    use shared\php\modale\Toolbox_modal as TbModal;
    
    $numero = 0 ;
    echo (BkAccordion::accordion1Debut());
    foreach ($evenements as $evenement) {
        $numero += 1 ;
    ?>
        <?= BkAccordion::accordion2ItemAvantTitre($numero);?>
        <table class="table table-light" align="center" border="1" cellspacing="1" >
        <tr>
            <td rowspan=2 class="barreTitre"><?= TbModal::mettreEnFormeActions($actions,$evenement->id);?></td>
            <td class="barreTitre"><b>
                Le <?=date_format(date_create($evenement->dateHeure),"d/m/Y");?> 
                <?= $evenement->getHeure();?>
                <?= $evenement->getHeureFin();?></b> 
                <?= $evenement->getTagConfirme();?>
            </td>
            <td class="barreTitre">
                <b><?=$evenement->etablissement->nom;?></b>
            </td>
        </tr>
	<tr>
            <td class="barreTitre" >
                Adresse : <?=$evenement->etablissement->coordonnee->adresse;?>
            </td>
            <td class="barreTitre"> 
                <?=$evenement->etablissement->coordonnee->ville;?> 
            </td>
        </tr>
        <?php 
        if (strlen($evenement->commentaire->texte)>0){
            echo "<tr><td ><strong>" . $evenement::LIBELLE_COMMENTAIRE . ": </strong></td>" . 
                    "<td  colspan =2>" . $evenement->commentaire->texte."</td></tr>";
        }
        ?> 
            </table>
        <?= BkAccordion::accordion3ItemEntreTitreEtContenu($numero,$afficherSetlist);?>
        <!-- PARTIE LISTE DES CHANSONS -->
        <?= $evenement->listerSetlist();?>
        <!-- fin d'accordéon-->
        <?= BkAccordion::accordion4ItemApresContenu();?>
    <!-- fin de boucle php-->
    <?php } 
     
    //Rajout des div modales de supprimer, tester et abandonner
    echo (BkAccordion::accordion5Fin());
    echo(TbModal::afficherListeDivModales($actions));
    $content = ob_get_clean(); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
?>
