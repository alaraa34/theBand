<?php
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Player complet : à gauche le choix du contexte et la liste des titres, à droite le détail du titre cliqué
 ******************************************************************************/
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use shared\php\toolbox\Toolbox_liste        as TbListe;
ob_start();
?>
<div class="card shadow-sm player-full">
    <div class="card-body">
        <div class="container-fluid">
            <div class="row">
                <!-- colonne gauche : contexte et titres -->
                <div class="col-md-5">
                    <div class="input-group entete-colonne">
                        <label class="input-group-text" for="idContexte">Titres</label>
                        <select name="idContexte" class="form-select" id="idContexte"
                                onchange="Get_Form_zone('<?= $urlListe ?>', 'liste-choix-song', 'idContexte');">
                            <?= TbListe::valeursChoixListe($contextes, false, selected: $idContexte); ?>
                        </select>
                    </div>
                    <div class="list-container-choix" id="liste-choix-song">
                        <?= $libelleContexte !== "" ? '<div class="small text-muted mb-2">' . htmlspecialchars($libelleContexte) . '</div>' : '' ?>
                        <?= $liste ?>
                    </div>
                </div>
                <!-- colonne droite : détail du titre (le premier de la liste à l'ouverture) -->
                <div class="col-md-7" id="detail-choix">
                    <?= $detail ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require(TbAdressage::projetGetLayout(__NAMESPACE__));
