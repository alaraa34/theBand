<?php
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Détail d'un titre dans le player complet : description, métronome, audios, vidéos
 * ($this : PlayerTitre)
 ******************************************************************************/
use shared\php\classes\lien\Lienhtml        as Lienhtml;
use metronome\php\Metronome                 as Metronome;
use shared\php\toolbox\Toolbox_tonalite     as TbTonalite;
ob_start();
$song = $this->song;
$sequences = $this->sequencesHtml();
?>
<div class="list-headerS text-success">
    <div>
        <strong><i class="<?= self::ICONE ?>"></i></strong>
        <strong><?= htmlspecialchars($song->titre) ?></strong>&nbsp; par <?= htmlspecialchars($song->interprete) ?>
    </div>
</div>
<div class="list-container-choix" id="detail-song">
    <!-- description -->
    <div class="row">
        <fieldset class="border rounded-3 g-0">
            <legend class="<?= self::COULEUR . self::FORMAT_TITRE ?>">&nbsp;<i class="bi bi-easel"></i>&nbsp;Description</legend>
            <div class="row mx-0 justify-content-center align-items-center">
                <div class="input-group mb-2">
                    <span class="input-group-text">Liens</span>
                    <div class="col border zone-liens">
                        <?= Lienhtml::afficherLiensCelluleObjet($song->liens, false, false); ?>
                    </div>
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text">Tona origine</span>
                    <input disabled type="text" class="form-control text-center" value="<?= htmlspecialchars($song->tonaOrigine) ?>"/>
                    <span class="input-group-text" title="Écart en demi-tons appliqué au MP3 original, et tonalité jouée">Tona scène</span>
                    <input disabled type="text" class="form-control text-center"
                           value="<?= htmlspecialchars(TbTonalite::libelleScene($song->tonaOrigine, $song->tonaScene)) ?>"/>
                    <?php if (($song->leadChant->prenom ?? '') !== ''): ?>
                        <span class="input-group-text">Lead</span>
                        <input disabled type="text" class="form-control text-center" value="<?= htmlspecialchars($song->leadChant->prenom) ?>"/>
                    <?php endif; ?>
                </div>
                <!-- commentaire : affiché seulement s'il est renseigné -->
                <?php if (self::commentaireRenseigne($song->commentaire->texte)): ?>
                <div class="input-group mb-2">
                    <span class="input-group-text">Commentaire</span>
                    <div class="form-control text-start bg-light"><?= $song->commentaire->texte ?></div>
                </div>
                <?php endif; ?>
            </div>
        </fieldset>
    </div>
    <!-- métronome -->
    <div class="row mt-2">
        <fieldset class="border rounded-3 g-0">
            <legend class="<?= self::COULEUR . self::FORMAT_TITRE ?>">&nbsp;<i class="fa-solid fa-gauge-high"></i>&nbsp;&nbsp;Métronome</legend>
            <div class="row mx-0 align-items-center">
                <div class="col"><h6 class="mb-0"><i class="fa-solid fa-drum"></i> <?= $song->tempo ?> bpm</h6></div>
                <div class="col my-2"><?= Metronome::render($song->tempo) ?></div>
                <div class="col"><?= Metronome::afficherVolume("Volume", "h6", false); ?></div>
            </div>
        </fieldset>
    </div>
    <!-- audios : MP3 original et MP3 groupe (multipistes), autres MP3, séquences -->
    <div class="row mt-2">
        <fieldset class="border rounded-3 g-0">
            <legend class="<?= self::COULEUR . self::FORMAT_TITRE ?>">&nbsp;<i class="fa-solid fa-volume-high"></i>&nbsp;&nbsp;Audios</legend>
            <div class="row mx-0 justify-content-center align-items-center">
                <?= $this->lecteursAudioHtml() ?>
            </div>
            <?php if ($sequences !== ""): ?>
                <div class="mx-0 px-2 pb-2"><?= $sequences ?></div>
            <?php endif; ?>
        </fieldset>
    </div>
    <!-- vidéos -->
    <div class="row mt-2">
        <fieldset class="border rounded-3 g-0">
            <legend class="<?= self::COULEUR . self::FORMAT_TITRE ?>">&nbsp;<i class="fa-solid fa-film"></i>&nbsp;&nbsp;Vidéos</legend>
            <div class="row mx-0 justify-content-center align-items-center">
                <?= $this->videosHtml() ?>
            </div>
        </fieldset>
    </div>
</div>
<?php
$content = ob_get_clean();
