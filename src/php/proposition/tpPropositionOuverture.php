<?php 
declare(strict_types=1);
namespace theBand\src\php\proposition;
/*******************************************************************************
 * Template d'ouverture de la saisie des propositions et des votes (administrateur)
 ******************************************************************************/
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
ob_start(); 
?>
<section class="py-5">
    <div class="container" style="max-width: 560px;">
        <form method="post" action="<?= TbAdressage::getURLstatic('proposition','propositionOuvertureMAJ',0,'vote') ?>">
            <div class="card">
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="saisieOuverte" name="saisieOuverte" value="1"
                               <?= $saisieOuverte ? 'checked' : '' ?>>
                        <label class="form-check-label" for="saisieOuverte">Saisie de nouvelles propositions ouverte</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="voteOuvert" name="voteOuvert" value="1"
                               <?= $voteOuvert ? 'checked' : '' ?>>
                        <label class="form-check-label" for="voteOuvert">Vote sur les propositions ouvert</label>
                    </div>
                    <p class="text-muted small mb-0">Une proposition existante reste toujours modifiable, même saisie fermée.</p>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</section>
<?php
    $content = ob_get_clean(); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
?>
