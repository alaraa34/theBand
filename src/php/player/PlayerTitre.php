<?php
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Un titre affiché dans le player complet (ctPlayerAll) : élément de la liste de gauche
 * et détail de droite (description, métronome, audios, séquences, vidéos)
 * Même présentation que le répertoire de training (fct=repertoirePlayer)
 ******************************************************************************/
use shared\php\classes\lien\Lien                as Lien;
use shared\php\classes\lien\TypeLien            as TypeLien;
use shared\php\classes\lien\Multipiste          as Multipiste;
use shared\php\classes\lien\Sequence            as Sequence;
use shared\php\classes\socle\User               as User;
use shared\php\toolbox\Toolbox_html             as TbHtml;
use shared\php\toolbox\Toolbox_adressage        as TbAdressage;
use theBand\src\php\song\Repertoire             as Repertoire;

class PlayerTitre
{
    public Repertoire $song;

    public const string COULEUR = "bg-success ";
    public const string FORMAT_TITRE = " text-white h6 ";
    public const string ICONE = "bi bi-music-note-beamed";
    //MP3 multipistes affichés en tête des audios : libellé, libellé de l'étiquette « pas de mp3 »
    private const array TYPES_MP3 = [TypeLien::MP3ORI => ['Original', 'original'], TypeLien::MP3JOU => ['Groupe', 'groupe']];

    public function __construct(int $idSong) {
        $this->song = new Repertoire($idSong);
        //liens privés : seulement ceux de l'utilisateur connecté
        $this->song->liens = array_values(array_filter($this->song->liens, fn($lien) => self::lienVisible($lien)));
    }

    private static function lienVisible(Lien $lien): bool {
        return !$lien->prive || ($lien->user->id > 0 && $lien->user->id === (int)User::connectUserGetInfo('id', 0));
    }

    //------------------------------------------------------------------------------------------------
    //LISTE DE GAUCHE
    //-----------------------------------------------------------------------------------------------
    public static function itemHtml(int $numero, array $titre): string {
    //un titre de la liste ($titre : id, titre, interprete) ; clic : détail chargé en ajax à droite
        $zone = 'titre' . $numero;
        $url = TbAdressage::getURLControleurFromCFPAdress("player/playerAll;chargerDetail");
        return '<div class="list-item song" data-id="' . (int)$titre['id'] . '"'
             . ' onclick="jsPlayerFullDetail(this, \'' . $url . '\', \'' . $zone . '\');">'
             . '<input type="hidden" id="' . $zone . '" value="' . (int)$titre['id'] . '" />'
             . '<span class="item-id">' . $numero . '</span>'
             . '<i class="' . self::ICONE . ' text-success me-1"></i>'
             . '<span class="item-libelle">' . htmlspecialchars(trim((string)$titre['titre']))
             . ' <span class="text-muted">- ' . htmlspecialchars(trim((string)$titre['interprete'])) . '</span></span>'
             . '</div>';
    }

    public static function listeHtml(array $titres): string {
        if (count($titres) === 0) {return '<p class="text-muted">Aucun titre pour ce choix</p>';}
        $html = "";
        foreach (array_values($titres) as $index => $titre) {$html .= self::itemHtml($index + 1, $titre);}
        return $html;
    }

    //------------------------------------------------------------------------------------------------
    //DETAIL
    //-----------------------------------------------------------------------------------------------
    public function render(): string {
        require('tpPlayerTitre.php');
        return $content;
    }

    public function lecteursAudioHtml(): string {
    //MP3 original (tona de scène) et MP3 joué par le groupe (multipistes, rouges s'ils manquent), puis les autres MP3
        $premiers = [];
        $autres = [];
        foreach ($this->song->liens as $lien) {
            $type = (int)$lien->typeLien->id;
            if (isset(self::TYPES_MP3[$type])) {$premiers[$type] ??= $lien;}
            elseif ($type === TypeLien::MP3) {$autres[] = $lien;}
        }
        $etats = Multipiste::etatsParLien(array_map(fn($lien) => $lien->id, $premiers));
        $lignes = [];
        foreach (self::TYPES_MP3 as $type => [$libelle, $libelleEtiquette]) {
            $lien = $premiers[$type] ?? null;
            $tona = Repertoire::tonaAttendue($type, $this->song->tonaScene);
            $source = $lien === null ? Multipiste::sourceLecteur(0, '', null, 0)
                                     : Multipiste::sourceLecteur($lien->id, $lien->url, $etats[$lien->id] ?? null, $tona);
            $lignes[] = [$libelle, (string)($lien?->description ?? ''), Multipiste::lecteurHtml($source, $libelleEtiquette)];
        }
        foreach ($autres as $lien) {
            $lignes[] = ['', (string)$lien->description, Multipiste::lecteurHtml(Multipiste::sourceSimple($lien->url))];
        }
        $html = '<table class="table table-sm align-middle mb-1">';
        foreach ($lignes as [$libelle, $description, $lecteur]) {
            $html .= '<tr><td class="fw-semibold">'
                   . ($libelle !== '' ? '<span class="badge text-bg-secondary me-1">' . $libelle . '</span>' : '')
                   . htmlspecialchars($description) . '</td>'
                   . '<td class="w-50">' . $lecteur . '</td></tr>';
        }
        return $html . '</table>';
    }

    public function sequencesHtml(): string {
    //séquences de notes du titre (liens de type Séquence), au tempo du titre
        return Sequence::composantsDesLiens($this->song->liens, $this->song->tempo);
    }

    public function videosHtml(): string {
    //fichiers vidéo et liens YouTube, l'un sous l'autre sur toute la largeur
        $videos = [];
        foreach ($this->song->liens as $lien) {
            $type = (int)$lien->typeLien->id;
            if ($type === TypeLien::MP4 || ($type === TypeLien::SITE && TbHtml::youTubeId($lien->url) !== "")) {$videos[] = $lien;}
        }
        if (count($videos) === 0) {return '<p class="text-muted my-2">Aucune vidéo</p>';}
        $html = '<div class="row g-3 my-1">';
        foreach ($videos as $video) {
            $html .= '<div class="col-12">';
            if ($video->description !== '') {$html .= '<div class="fw-bold mb-1">' . htmlspecialchars($video->description) . '</div>';}
            $html .= TbHtml::htmlAfficherLecteurVideo($video->url) . '</div>';
        }
        return $html . '</div>';
    }

    public static function commentaireRenseigne(?string $html): bool {
    //vrai si le commentaire contient du texte (balises, espaces et &nbsp; ignorés)
        $texte = html_entity_decode(strip_tags((string)$html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(str_replace("\u{00A0}", ' ', $texte)) !== '';
    }
}
