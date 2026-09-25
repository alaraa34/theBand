-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 25 sep. 2026 à 12:10
-- Version du serveur : 8.2.0
-- Version de PHP : 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données theBand : structure + données de référence (sans utilisateurs ni données saisies)
--

-- --------------------------------------------------------

--
-- Structure de la table `tb_commentaire`
--

DROP TABLE IF EXISTS `tb_commentaire`;
CREATE TABLE IF NOT EXISTS `tb_commentaire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `texte` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tb_coordonnee`
--

DROP TABLE IF EXISTS `tb_coordonnee`;
CREATE TABLE IF NOT EXISTS `tb_coordonnee` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mail` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tel` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `adresse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ville` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Code postal et ville',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tb_etablissement`
--

DROP TABLE IF EXISTS `tb_etablissement`;
CREATE TABLE IF NOT EXISTS `tb_etablissement` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'identifiant',
  `idTypeEtab` int NOT NULL COMMENT 'lien vers paramètre',
  `idCoordonnee` int NOT NULL COMMENT 'lien vers coordonnées',
  `nom` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'nom de l''endroit',
  `idCommentaire` int NOT NULL,
  `dateCreation` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `etablissement` (`nom`(30))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Site de prospection';

-- --------------------------------------------------------

--
-- Structure de la table `tb_etablissement_contact`
--

DROP TABLE IF EXISTS `tb_etablissement_contact`;
CREATE TABLE IF NOT EXISTS `tb_etablissement_contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomPrenom` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `idCoordonnee` int NOT NULL,
  `idEtablissement` int NOT NULL COMMENT 'Lien vers établissement',
  `idCommentaire` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idProspect` (`idEtablissement`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tb_etablissement_lien`
--

DROP TABLE IF EXISTS `tb_etablissement_lien`;
CREATE TABLE IF NOT EXISTS `tb_etablissement_lien` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'identifiant du lien',
  `idEtablissement` int NOT NULL COMMENT 'Identifiant prospect',
  `idLien` int NOT NULL COMMENT 'Identifiant du lien',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tb_evenement`
--

DROP TABLE IF EXISTS `tb_evenement`;
CREATE TABLE IF NOT EXISTS `tb_evenement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dateHeure` datetime NOT NULL,
  `idNature` int NOT NULL COMMENT '1:Concert 2:Répétition',
  `idEtablissement` int NOT NULL,
  `idSetlist` int NOT NULL COMMENT 'setList pour cet évènement',
  `idCommentaire` int NOT NULL COMMENT 'commentaire sur le concert',
  `confirme` tinyint(1) NOT NULL COMMENT 'false non confirmé, true confirmé',
  `dateHeureFin` datetime NOT NULL COMMENT 'heure de fin',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='concerts et répétitions';

-- --------------------------------------------------------

--
-- Structure de la table `tb_habilitation`
--

DROP TABLE IF EXISTS `tb_habilitation`;
CREATE TABLE IF NOT EXISTS `tb_habilitation` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'identifiant auto',
  `idUser` int NOT NULL COMMENT 'identifiant user connecté',
  `idFonction` int NOT NULL COMMENT 'fonction définie dans paramètres',
  `lecture` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'accès en lecture',
  `modification` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'peut modifier  dans la fonction',
  `suppression` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'supprimer pour la fonction',
  `ajout` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'peut ajouter dans la fonction',
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_user_fonction` (`idUser`,`idFonction`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='habilitation par fonction';

-- --------------------------------------------------------

--
-- Structure de la table `tb_lien`
--

DROP TABLE IF EXISTS `tb_lien`;
CREATE TABLE IF NOT EXISTS `tb_lien` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idTypeLien` int DEFAULT '0' COMMENT 'id de la table type_lien',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'url du lien',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Description du lien',
  `idUser` int NOT NULL DEFAULT '0' COMMENT 'Id table user qui a créé le lien',
  `prive` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'lien privé',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tb_lien_type`
--

DROP TABLE IF EXISTS `tb_lien_type`;
CREATE TABLE IF NOT EXISTS `tb_lien_type` (
  `id` int NOT NULL COMMENT 'Identifiant à saisir , pas auto',
  `nom` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Nom pour afficher dans les combos',
  `nomAffiche` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Nom pour affichage',
  `nomLong` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Libellé long du TDL',
  `externe` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'True : externe (url)/ False : fichier',
  `repertoire` varchar(56) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Répertoire de classement',
  `couleur` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Classe utilisée pour l''affichage',
  `extensions` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Extensions et MIME le séparateur est un blanc autorisés, exempl mp3 audio/mpeg ',
  `icone` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'icone à utiliser',
  `actif` tinyint(1) NOT NULL COMMENT 'si utilisé dans appli',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tb_lien_type`
--

INSERT INTO `tb_lien_type` (`id`, `nom`, `nomAffiche`, `nomLong`, `externe`, `repertoire`, `couleur`, `extensions`, `icone`, `actif`) VALUES
(1, 'MP3', 'mp3', 'mp3 enregistré en répétition ou li', 0, 'mp3', 'texteMP3', 'audio/mp3', '<i class=\"bi bi-volume-up-fill\"></i>', 1),
(2, 'Accords', 'accords', '', 0, 'accords', 'texteACCORDS', 'application/pdf', '<i class=\"bi bi-file-earmark-pdf\"></i>', 1),
(3, 'Partition', 'partition', '', 0, 'partition', 'textePARTITION', 'application/pdf', '<i class=\"bi bi-file-earmark-pdf\"></i>', 1),
(4, 'You Tube', 'site', '', 1, '', 'texteSITE', '', '<i class=\"bi bi-globe\"></i>', 1),
(29, 'Vidéo', 'vidéo', '', 0, 'video', 'texteVIDEO', 'video/mpeg,video/mp4,video/mkv,video/webm', '<i class=\"fa-solid fa-video\"></i>', 1),
(54, 'MP3 détonn', 'mp3d', 'mp3 original à la tona concert', 0, 'mp3d', 'texteMP3D', 'audio/mp3', '<i class=\"bi bi-volume-up-fill\"></i>', 1),
(9, 'Paroles', 'paroles', '', 0, 'paroles', 'textePAROLES', 'application/pdf,application.txt', '<i class=\"bi bi-file-earmark-pdf\"></i>', 1),
(28, 'PAD', 'pad', '', 0, 'pad', 'textePAD', 'mp3 audio/mpeg', '<i class=\"bi bi-volume-up-fill\"></i>', 1),
(5, 'Image', 'image', '', 0, 'image', 'textePHOTO', 'png image/png jpeg jpg image/jpeg', '<i class=\"bi bi-file-image\"></i>', 1),
(11, 'Google Maps', 'maps', '', 1, '', 'texteMAPS', '', '<i class=\"fa-solid fa-location-dot\"></i>', 1),
(21, 'Autre', 'autre docu', '', 0, 'autres', 'texteAUTRE', '.pdf ,.txt', '<i class=\"bi bi-file-earmark-pdf\"></i>', 1),
(6, 'MP3autre', 'mp3a', 'mp3 autre', 0, 'mp3', 'texteMP3', 'audio/mp3', '<i class=\"bi bi-volume-up-fill\"></i>', 1),
(31, 'MP3 ss voix', 'mp3svx', 'MP3 détonné sans la voix', 0, 'mp3s', 'texteMP3S', 'audio/mp3', '<i class=\"bi bi-volume-up-fill\"></i>', 1),
(49, 'Choeurs', 'choeurs', '', 0, 'choeurs', 'texteCHOEURS', 'application/pdf', '<i class=\"bi bi-file-earmark-pdf\"></i>', 1),
(12, 'Facebook', 'facebook', 'facebook', 1, '', 'texteMAPS', '', '<i class=\"bi bi-facebook\"></i>', 1),
(13, 'Instagram', 'instagram', 'instagram', 1, '', 'texteMAPS', '', '<i class=\"bi bi-instagram\"></i>', 1),
(15, 'PDF', 'pdf', 'fichier pdf', 0, '', 'textePARTITION', 'application/pdf', '<i class=\"bi bi-file-earmark-pdf\"></i>', 1);

-- --------------------------------------------------------

--
-- Structure de la table `tb_lien_type_usage`
--

DROP TABLE IF EXISTS `tb_lien_type_usage`;
CREATE TABLE IF NOT EXISTS `tb_lien_type_usage` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Id du type ',
  `idTypeLien` int NOT NULL COMMENT 'Id type de lien ',
  `idUsage` int NOT NULL COMMENT 'Id de nomenclature SUJET',
  `saisie` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Autorisé en saisie oui/non',
  `affichage` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Autorisé en restitution',
  `Libelle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'commentaire pour lecture table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_idTypeLien` (`idTypeLien`,`idUsage`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tb_lien_type_usage`
--

INSERT INTO `tb_lien_type_usage` (`id`, `idTypeLien`, `idUsage`, `saisie`, `affichage`, `Libelle`) VALUES
(1, 1, 71, 1, 1, 'MP3/SONG'),
(2, 1, 76, 1, 1, 'MP3/REPETITION'),
(3, 2, 71, 1, 1, 'ACCORD/SONG'),
(4, 3, 71, 1, 1, 'PARTITION/SONG'),
(5, 4, 71, 1, 1, 'SITE/SONG'),
(6, 4, 73, 1, 1, 'SITE/ETABLISSEMENT'),
(7, 4, 75, 1, 1, 'SITE/PROPOSITION'),
(8, 54, 71, 1, 1, 'MP3d/SONG'),
(9, 54, 76, 1, 1, 'MP3d/REPETITION'),
(10, 1, 74, 1, 1, 'MP3/CONCERT'),
(11, 54, 74, 1, 1, 'MP3d/CONCERT'),
(12, 6, 71, 1, 1, 'MP3a/SONG'),
(13, 31, 71, 1, 1, 'MP3svx/SONG'),
(14, 49, 71, 1, 1, 'CHOEURS/SONG'),
(15, 11, 73, 1, 1, 'MAPS/Etablissement'),
(16, 28, 71, 1, 1, 'PAD/SONG'),
(17, 54, 72, 1, 1, 'MP3d/PLAYER'),
(18, 1, 72, 1, 1, 'MP3/PLAYER'),
(19, 6, 72, 1, 1, 'MP3a/PLAYER'),
(20, 31, 72, 1, 1, 'MP3svx/PLAYER'),
(21, 12, 73, 1, 1, 'Facebook/ETABLISSEMENT'),
(22, 13, 73, 1, 1, 'Instagram/ETABLISSEMENT'),
(23, 1, 77, 1, 1, 'MP3/TELECHARGEMENT'),
(24, 4, 77, 1, 1, 'SITE/TELECHARGEMENT'),
(25, 15, 77, 1, 1, 'PDF/TELECHARGEMENT'),
(26, 29, 77, 1, 1, 'VIDEO/TELECHARGEMENT'),
(27, 5, 77, 1, 1, 'IMAGE/TELECHARGEMENT'),
(28, 29, 71, 1, 1, 'VIDEO/SONG'),
(29, 9, 71, 1, 1, 'Paroles/SONG');

-- --------------------------------------------------------

--
-- Structure de la table `tb_ln_requete`
--

DROP TABLE IF EXISTS `tb_ln_requete`;
CREATE TABLE IF NOT EXISTS `tb_ln_requete` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupe` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sujet` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `requete` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `i_groupe` (`groupe`(10)) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Requetes pour restituerdes listes';

-- --------------------------------------------------------

--
-- Structure de la table `tb_menu`
--

DROP TABLE IF EXISTS `tb_menu`;
CREATE TABLE IF NOT EXISTS `tb_menu` (
  `indentation` int NOT NULL COMMENT 'sur 3 digits de 2 caractères',
  `domaine` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nom du controleur sans ct',
  `controleur` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'controleur du domaine',
  `typeLigne` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'type de ligne',
  `libelle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Texte affiché',
  `action` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nom de la fonction sans ct',
  `production` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`indentation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tb_menu`
--

INSERT INTO `tb_menu` (`indentation`, `domaine`, `controleur`, `typeLigne`, `libelle`, `action`, `production`) VALUES
(206000, 'proposition', '', 'action', 'Abandonnées', 'propositionListerAB', 1),
(300500, '', '', 'divider', 'Répétitions', '', 1),
(902000, 'telechargement', '', 'action', 'Tutos sur appli', 'tutoLister', 1),
(901000, 'telechargement', '', 'action', 'Nouveau', 'teleEditer', 1),
(307000, 'evenement', '', 'action', 'Modifier la setlist', 'concertEditSetListProchain', 1),
(701000, 'planning', '', 'action', 'Individuel', 'editerPlanningIndividuel', 1),
(703000, 'planning', '', 'action', 'Collectif', 'AfficherPlanningCollectif', 1),
(303500, '', '', 'divider', 'Concerts', '', 1),
(305000, 'evenement', '', 'action', 'Nouveau', 'concertEditer', 1),
(304000, 'evenement', '', 'action', 'Prochain', 'concertProchain', 1),
(306000, 'evenement', '', 'action', 'Liste', 'concertListe', 1),
(301000, 'evenement', '', 'action', 'Prochaine', 'repetitionProchaine', 1),
(302000, 'evenement', '', 'action', 'Nouvelle', 'repetitionEditer', 1),
(303000, 'evenement', '', 'action', 'Liste', 'repetitionListe', 1),
(201000, 'proposition', '', 'action', 'Nouvelle', 'propositionEditer', 1),
(202000, 'proposition', '', 'action', 'Voter pour toutes les propositions', 'propositionVoterTous', 1),
(204000, 'proposition', '', 'action', 'En cours par vote', 'propositionListerParVote', 1),
(207000, 'proposition', '', 'action', 'Validées', 'PropositionListerValidees', 1),
(606000, 'etablissement', '', 'action', 'Clôturés', 'prospectListerT\r\n', 1),
(605000, 'etablissement', '', 'action', 'En cours', 'prospectListerE', 1),
(603000, 'etablissement', '', 'action', 'Studios répétition', 'etablissementStudioLister', 1),
(903000, 'telechargement', '', 'action', 'Concerts et Répet', 'concertLister', 1),
(101000, 'song', '', 'action', 'Nouveau', 'songEditer', 1),
(103000, 'song', '', 'action', 'Concert', 'repertoireConcert', 1),
(104000, 'song', '', 'action', 'Test', 'repertoireTest', 1),
(106000, 'song', '', 'action', 'Abandon', 'repertoireAbandon', 1),
(105000, 'song', '', 'action', 'Reserve', 'repertoireReserve', 1),
(102000, 'song', '', 'action', 'Répertoire (Test et Concert)', 'repertoire', 1),
(604000, '', '', 'divider', 'Prospection', '', 1),
(602000, 'etablissement', '', 'action', 'Pour concert', 'etablissementConcertLister', 1),
(601000, 'etablissement', '', 'action', 'Nouveau', 'etablissementEditer', 1),
(700000, '', '', 'dropdown', 'Planning', '', 1),
(400000, '', '', 'dropdown', 'Players', '', 1),
(308000, 'evenement', '', 'action', 'Imprimer la setlist', 'concertPrintSetListProchain', 1),
(203000, 'proposition', '', 'action', 'Voter pour les votes Non Exprimés', 'propositionVoterNV', 1),
(205000, 'proposition', '', 'action', 'En cours par titres', 'propositionListerParTitre', 1),
(404000, 'player', '', 'action', 'Sur les titres Test et Concert', 'playerTestConcert', 1),
(403000, 'player', '', 'action', 'Sur les titres en test', 'playerTest', 1),
(402000, 'player', '', 'action', 'Titres du prochain concert', 'playerProchainConcert', 1),
(401000, 'player', '', 'action', 'Titres de la prochaine répétition', 'playerProchaineRepet', 1),
(200000, '', '', 'dropdown', 'Propositions', '', 1),
(702000, 'planning', '', 'action', 'Par périodes', 'editerPlanningPeriode', 1),
(300000, '', '', 'dropdown', 'Evènements', '', 1),
(100000, 'song', '', 'dropdown', 'Titres', '', 1),
(600000, '', '', 'dropdown', 'Lieux', '', 1),
(804000, 'utilitaires', '', 'externe', 'Détonner un MP3', 'https://vocalremover.org/fr/pitch', 1),
(805000, 'utilitaires', '', 'externe', 'Enlever le chant sur un mp3', 'https://vocalremover.org/fr/', 1),
(806000, 'utilitaires', '', 'externe', 'MP4 to MP3 (convertio)', 'https://convertio.co/fr/mp4-mp3/', 1),
(807000, 'utilitaires', '', 'externe', 'Métronome en ligne', 'https://www.metronome-en-ligne.com/', 1),
(600500, '', '', 'divider', 'Etablissements', '', 1),
(203500, '', '', 'divider', '', '', 1),
(306500, '', '', 'divider', 'Prochain concert', '', 1),
(201500, '', '', 'divider', '', '', 1),
(900000, '', '', 'dropdown', 'Téléchargements', '', 1),
(50000, 'accueil', '', 'menu', 'Accueil', 'accueil', 1),
(800000, '', '', 'dropdown', 'Utiles', '', 1),
(102500, '', '', 'divider', 'Liste de titres', '', 1),
(805500, 'utilitaires', '', 'externe', 'Détecter Tona et beat d\'un mp3', 'https://vocalremover.org/fr/key-bpm-finder', 1),
(806500, 'utilitaires', '', 'externe', 'M4A to MP3 (freeconvert)', 'https://www.freeconvert.com/fr/m4a-to-mp3', 1),
(805700, 'utilitaires', '', 'externe', 'Détecter BPM d\'un mp3', 'https://myedit.online/fr/audio-editor/bpm-finder/edit', 1),
(904000, 'telechargement', '', 'action', 'Documentation Sono', 'documentationLister', 1),
(905000, 'telechargement', '', 'action', 'Promotion', 'promotionLister', 1),
(106500, '', '', 'divider', 'Performance', '', 1),
(107000, 'song', '', 'action', 'Evaluation performance', 'repertoireNotation', 1),
(108000, 'song', '', 'action', 'Classement par performance', 'repertoireAfficherPerformance', 1),
(906000, 'telechargement', '', 'action', 'Vidéos de concerts', 'videoConcertLister', 1),
(404500, '', '', 'divider', 'Players documents', '', 1),
(405000, 'player', '', 'action', 'Titres du prochain concert', 'playerDocProchainConcert', 1),
(406000, 'player', '', 'action', 'Titres Test et Concert', 'playerDocTestConcert', 1),
(400500, '', '', 'divider', 'Players MP3', '', 1),
(205500, 'proposition', '', 'action', 'Titres en cours à éliminer', 'propositionListerElimines', 1),
(405500, 'player', '', 'action', 'Titres prochaine Repet', 'playerDocProchaineRepet', 1),
(406500, '', '', 'divider', 'Players pad', '', 1),
(407000, 'player', '', 'action', 'Pads Test et Concert', 'playerPadTestConcert', 1),
(407500, 'player', '', 'action', 'Pads prochain Concert', 'playerPadProchainConcert', 1),
(108500, '', '', 'divider', 'Playlist', '', 1),
(109000, 'song', '', 'action', 'Ma playlist', 'playlistEditer', 1),
(404100, 'player', '', 'action', 'Sur les titres de ma Playlist', 'playerPlayList', 1),
(808000, 'accueil', '', 'divider', 'Référentiel Accords', '', 1),
(808100, 'accueil', '', 'action', 'Notes d\'un accord', 'accordDetecter', 1),
(808300, 'accueil', '', 'action', 'Identifier un accord', 'accordIdentifier', 1),
(808500, 'accueil', '', 'action', 'Gammes et Modes', 'gammesEtModes', 1),
(803000, 'accueil', '', 'divider', 'Musique', '', 1);

-- --------------------------------------------------------

--
-- Structure de la table `tb_nomenclature`
--

DROP TABLE IF EXISTS `tb_nomenclature`;
CREATE TABLE IF NOT EXISTS `tb_nomenclature` (
  `id` int NOT NULL,
  `groupe` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `nom` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ordre` int NOT NULL COMMENT 'Ordre d''apparition dans une liste',
  `libelle` varchar(56) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Zone inutilisée dans les traitements',
  `valeurA` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Valeur alphanumerique',
  `valeurN` float DEFAULT NULL COMMENT 'Valeur Numérique, avec décimales',
  `valeurD` datetime DEFAULT NULL COMMENT 'Valeur date ',
  `valeurB` tinyint(1) DEFAULT NULL COMMENT 'Valeur Booleenne',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Groupe` (`groupe`,`nom`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tb_nomenclature`
--

INSERT INTO `tb_nomenclature` (`id`, `groupe`, `nom`, `ordre`, `libelle`, `valeurA`, `valeurN`, `valeurD`, `valeurB`) VALUES
(5, 'TYPE_SONG', 'CONCERT', 3, NULL, 'Concert', NULL, NULL, 0),
(6, 'TYPE_SONG', 'TEST', 2, NULL, 'Test', NULL, NULL, 0),
(7, 'TYPE_SONG', 'PROPOSITION', 1, NULL, 'Proposition', NULL, NULL, 0),
(16, 'TYPE_SONG', 'RESERVE', 4, NULL, 'En réserve', NULL, NULL, 0),
(17, 'STATUT', 'EN COURS', 1, NULL, 'En cours', NULL, NULL, 0),
(18, 'STATUT', 'VALIDE', 2, NULL, 'Valide', NULL, NULL, 0),
(19, 'STATUT', 'ABANDONNE', 3, NULL, 'Abandonnée', NULL, NULL, 0),
(32, 'TYPE_SONG', 'ABANDONNE', 5, NULL, 'Abandonné', NULL, NULL, 0),
(26, 'ROLE', '2', 0, NULL, 'Membre', NULL, NULL, 0),
(27, 'ROLE', '3', 0, NULL, 'Invité', NULL, NULL, 0),
(33, 'TYPE_ETAB', 'RESTAU', 0, NULL, 'Restaurant', NULL, NULL, 0),
(34, 'TYPE_ETAB', 'BAR', 0, NULL, 'Bar', NULL, NULL, 0),
(35, 'TYPE_ETAB', 'VITICULTEUR', 0, NULL, 'Domaine Vinicole', NULL, NULL, 0),
(36, 'TYPE_ETAB', 'PAILLOTTE', 0, NULL, 'Paillotte', NULL, NULL, 0),
(37, 'TYPE_ETAB', 'AUTRE', 0, NULL, 'Autre', NULL, NULL, 0),
(38, 'STATUT_PRO', 'CREE', 1, 'prospect juste créé', 'Non contacté', NULL, NULL, 0),
(39, 'STATUT_PRO', 'ACTIF', 2, 'Prospect avec au moins un suivi', 'En cours', NULL, NULL, 0),
(40, 'STATUT_PRO', 'REFUS', 3, 'Prospect qui n\'est pas intéressé', 'Refus', NULL, NULL, 0),
(41, 'ECART_TONA', '-4', 7, 'Correction en demi ton', NULL, -4, NULL, 0),
(42, 'ECART_TONA', '-3', 6, 'Correction en demi ton', 'Ecart de tona à 1.5 ton', -3, NULL, 0),
(43, 'ECART_TONA', '-2 ', 5, 'Correction en demi ton', 'Ecart 1 ton', -2, NULL, 0),
(44, 'ECART_TONA', '-1', 4, 'Correction en demi ton', 'ecart 1/2 ton', -1, NULL, 0),
(45, 'ECART_TONA', '0', 3, 'Pas de correction', 'Jouée comme l\'orignal', 0, NULL, 0),
(46, 'ECART_TONA', '+1', 2, 'Correction en demi ton', NULL, 1, NULL, 0),
(47, 'ECART_TONA', '+2', 1, 'Correction en demi ton', NULL, 2, NULL, 0),
(50, 'STATUT_PRO', 'ACCORD', 3, 'prospect ayant fixé une date', 'Accord date', NULL, NULL, 0),
(71, 'SUJET', 'SONG', 0, 'Utilisation d\'une donnée pour les songs', 'Song', NULL, NULL, NULL),
(72, 'SUJET', 'PLAYER', 0, 'Utilisation d\'une donnée pour les songs', 'Player', NULL, NULL, NULL),
(73, 'SUJET', 'ETABLISSEMEN', 0, 'Utilisation d\'une donnée pour les songs', 'Etablissement', NULL, NULL, NULL),
(57, 'NATURE', 'CONCERT', 0, 'Type d\'évènement concert', 'Concert', NULL, NULL, NULL),
(58, 'NATURE', 'REPETITION', 0, 'nature d\'évènement répétition', 'Répétition', NULL, NULL, NULL),
(59, 'TYPE_ETAB', 'STUDIO', 0, 'Studio de répétition', 'Studio de répétition', NULL, NULL, NULL),
(61, 'TYPE_ETAB', 'CAMPING', 0, 'Camping', 'Camping', NULL, NULL, 0),
(75, 'SUJET', 'PROPOSITION', 0, 'Utilisation d\'une donnée pour les  propositions', 'Proposition', NULL, NULL, NULL),
(74, 'SUJET', 'CONCERT', 0, 'Utilisation d\'une donnée pour les concerts', 'Concerts', NULL, NULL, NULL),
(76, 'SUJET', 'REPETITION', 0, 'Domaine des répétitions', 'Répétition', NULL, NULL, NULL),
(87, 'TYPE_ETAB', 'FESTIVAL', 0, 'Festival, manifestation...', 'Festival', NULL, NULL, 0),
(86, 'TYPE_ETAB', 'BAR_RESTAU', 0, 'Bar restaurant', 'Bar Restaurant', NULL, NULL, 0),
(51, 'STATUT_PRO', 'ABANDON', 4, 'Prospect qui ne répond pas, on laisse tomber', 'Abandon', NULL, NULL, 0),
(88, 'TYPE_ETAB', 'BRASSERIE', 0, 'Brasserie', 'Brasserie', NULL, NULL, 0),
(20, 'ACTION_PRS', 'CREATION', 38, 'Création du prospect', 'Création', 0, NULL, 0),
(22, 'ACTION_PRS', 'ACCORD', 2, 'Accord de l\'établissement pour une date,, valeur N = sta', 'Accord', 50, NULL, 0),
(23, 'ACTION_PRS', 'REFUS', 3, 'Refus de l\'établissement pour une date', 'Refus', 40, NULL, 0),
(24, 'ACTION_PRS', 'ABANDON', 4, 'Clôture du suivi faute de réponse,, valeur N = statut pr', 'Abandon', 0, NULL, 0),
(25, 'ACTION_PRS', 'PLANIF', 5, 'Date de concert obtenue, valeur N = statut prospect lié', 'Planification', 50, NULL, 0),
(21, 'ACTION_PRS', 'CONTACT', 1, 'Contact simple avec l\'établissement', 'Contact', 39, NULL, 0),
(1, 'ETAT_PRS', 'OUVERT', 1, 'Prospect ouvert', 'Ouvert', NULL, NULL, 0),
(2, 'ETAT_PRS', 'FERME', 2, 'Prospect fermé', 'Fermé', NULL, NULL, 0),
(28, 'ACTION_PRS', 'CLOTURE', 7, 'Cloture du prospect, utilisé par système seulement', 'Clôture', 0, NULL, 0),
(65, 'DOMAINE', 'CONCERT', 1, 'Fichiers concerts, bool si création repertoire poss', 'Concerts', NULL, NULL, 1),
(66, 'DOMAINE', 'DOCUMENTATIO', 2, 'documentation,, bool si création repertoire poss', 'Documentation', NULL, NULL, 0),
(67, 'DOMAINE', 'APPLI', 2, 'Tutos sur l\'application, bool si création repertoire pos', 'Application', NULL, NULL, 0),
(68, 'DOMAINE', 'PROMOTION', 2, 'supports de promotion,Tutos sur l\'application, bool si c', 'Promotion', NULL, NULL, 1),
(77, 'SUJET', 'TELECHARGEME', 0, 'Domaine des Téléchargements', 'Téléchargement', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `tb_parametre`
--

DROP TABLE IF EXISTS `tb_parametre`;
CREATE TABLE IF NOT EXISTS `tb_parametre` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupe` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `nom` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ordre` int NOT NULL COMMENT 'pour ordonner dans liste',
  `libelle` varchar(56) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `valeurA` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `valeurN` float DEFAULT NULL,
  `valeurD` datetime DEFAULT NULL,
  `valeurB` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Groupe` (`groupe`,`nom`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tb_parametre`
--

INSERT INTO `tb_parametre` (`id`, `groupe`, `nom`, `ordre`, `libelle`, `valeurA`, `valeurN`, `valeurD`, `valeurB`) VALUES
(60, 'IMAGE', 'ACCUEIL', 0, 'nom du fichier image pour l\'accueil, à mettre dans réper', 'imageAccueil.png', 1, NULL, 0),
(63, 'PROPOSITIO', 'VOTER', 0, 'Permet d\'activer ou déactiver le vote des propositions', 'valeurN 1 : Vote possible et saisie fermée\r\nvaleurN 2 : Vote fermé et saisie possible\r\nvaleurN 3 : tout fermé\r\nvaleurN 4 : Vote et saisie possible', 4, NULL, 0),
(64, 'GENERAL', 'NOMGROUPE', 0, 'Nom du groupe', 'The Band', NULL, NULL, NULL),
(66, 'GENERAL', 'VERSION', 0, 'Numéro de version', '1.0', NULL, NULL, NULL),
(67, 'PLAYER', 'DEFAUT', 0, 'Player présenté par défaut', 'mp3', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `tb_planning`
--

DROP TABLE IF EXISTS `tb_planning`;
CREATE TABLE IF NOT EXISTS `tb_planning` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'identifiant',
  `jour` date NOT NULL COMMENT 'Jour de disponibilité',
  `idUser` int NOT NULL COMMENT 'User concerné',
  `disponible` text NOT NULL COMMENT 'Disponible,Indisponible,Neutre',
  PRIMARY KEY (`id`),
  KEY `ijour` (`jour`),
  KEY `iUser` (`idUser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COMMENT='indication des jours de disponibilité';

-- --------------------------------------------------------

--
-- Structure de la table `tb_proposition`
--

DROP TABLE IF EXISTS `tb_proposition`;
CREATE TABLE IF NOT EXISTS `tb_proposition` (
  `id` int NOT NULL DEFAULT '0',
  `idUser` int DEFAULT '0' COMMENT 'proposé par',
  `statut` int DEFAULT '17',
  `idCommentaire` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tb_prospect`
--

DROP TABLE IF EXISTS `tb_prospect`;
CREATE TABLE IF NOT EXISTS `tb_prospect` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idEtablissement` int NOT NULL COMMENT 'identifiant de l''établissement',
  `idStatut` int NOT NULL COMMENT 'Lien vers nomenclature',
  `idEtat` int NOT NULL COMMENT 'etat ouvert/clos',
  `idSuiveur` int NOT NULL COMMENT 'Id du user qui suit le prospect',
  `idCommentaire` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `i_etablissement` (`idEtablissement`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Site de prospection';

-- --------------------------------------------------------

--
-- Structure de la table `tb_prospect_suivi`
--

DROP TABLE IF EXISTS `tb_prospect_suivi`;
CREATE TABLE IF NOT EXISTS `tb_prospect_suivi` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'identifiant',
  `idProspect` int NOT NULL COMMENT 'Id de la prospection',
  `date` date NOT NULL,
  `idAuteur` int NOT NULL COMMENT 'auteur du suivi',
  `idCommentaire` int NOT NULL COMMENT 'lien commentaire',
  `idAction` int NOT NULL COMMENT 'action faite sur ce suivi',
  PRIMARY KEY (`id`),
  KEY `i_prospect` (`idProspect`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tb_repertoire`
--

DROP TABLE IF EXISTS `tb_repertoire`;
CREATE TABLE IF NOT EXISTS `tb_repertoire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'nom du répertoire',
  `idParent` int NOT NULL COMMENT 'id du répertoire parent',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tb_setlist`
--

DROP TABLE IF EXISTS `tb_setlist`;
CREATE TABLE IF NOT EXISTS `tb_setlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomListe` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nom de la liste',
  `idNature` int NOT NULL COMMENT 'paramètre usage (répet ou concert) de la setliste',
  `nombreParties` int NOT NULL DEFAULT '3' COMMENT 'Nombre de parties de la setlist, rappel inclus',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tb_setlist_detail`
--

DROP TABLE IF EXISTS `tb_setlist_detail`;
CREATE TABLE IF NOT EXISTS `tb_setlist_detail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idSetlist` int DEFAULT '1',
  `partie` tinyint DEFAULT '0',
  `idSong` int DEFAULT '0',
  `ordre` int DEFAULT '0',
  `enchainement` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'true : enchainé avec précédent',
  PRIMARY KEY (`id`),
  UNIQUE KEY `i-idSetlist-idSong` (`idSong`,`idSetlist`),
  KEY `i-idSetlist` (`idSetlist`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='liste de titre pour concert ou répétition';

-- --------------------------------------------------------

--
-- Structure de la table `tb_slider`
--

DROP TABLE IF EXISTS `tb_slider`;
CREATE TABLE IF NOT EXISTS `tb_slider` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'identifiant auto',
  `numero` int NOT NULL COMMENT 'numero du slider',
  `url` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'url de l''image',
  `titre` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'titre de l''image',
  `description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'description longue de l''image',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='description des sliders ';

-- --------------------------------------------------------

--
-- Structure de la table `tb_song`
--

DROP TABLE IF EXISTS `tb_song`;
CREATE TABLE IF NOT EXISTS `tb_song` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `interprete` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `tonaOrigine` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `tonaScene` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `tempo` int NOT NULL DEFAULT '0' COMMENT 'tempo du morceaux par seconde',
  `idTypeSong` int DEFAULT '0',
  `idCommentaire` int NOT NULL DEFAULT '0',
  `idLeadChant` int NOT NULL DEFAULT '0' COMMENT 'Id du chanteur principal',
  PRIMARY KEY (`id`),
  KEY `Titre` (`titre`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tb_song_lien`
--

DROP TABLE IF EXISTS `tb_song_lien`;
CREATE TABLE IF NOT EXISTS `tb_song_lien` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idSong` int NOT NULL DEFAULT '0',
  `idLien` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_lien` (`idLien`),
  KEY `i_song` (`idSong`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tb_telechargement`
--

DROP TABLE IF EXISTS `tb_telechargement`;
CREATE TABLE IF NOT EXISTS `tb_telechargement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idDomaine` int NOT NULL COMMENT 'Fichier ou doc',
  `repertoire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'R&pertoire où est rangé le doc',
  `ordre` int NOT NULL COMMENT 'position de restitution',
  `idLien` int NOT NULL COMMENT ' lien',
  `idCommentaire` int NOT NULL COMMENT 'commentaire',
  PRIMARY KEY (`id`),
  KEY `i_domaine` (`idDomaine`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='fichiers ou URL';

-- --------------------------------------------------------

--
-- Structure de la table `tb_user`
--

DROP TABLE IF EXISTS `tb_user`;
CREATE TABLE IF NOT EXISTS `tb_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `prenom` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `abrev` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `pseudo` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `password` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '0',
  `mail` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `administrateur` tinyint(1) DEFAULT '0',
  `avatar` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_abrev` (`abrev`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tb_user_theband`
--

DROP TABLE IF EXISTS `tb_user_theband`;
CREATE TABLE IF NOT EXISTS `tb_user_theband` (
  `id` int NOT NULL COMMENT 'id de table user',
  `role` int DEFAULT '0',
  `coeff` float DEFAULT '1' COMMENT 'coefficient pour vote maitrise',
  `idSetlist` int NOT NULL COMMENT 'id de la set personnelle',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `tb_vote`
--

DROP TABLE IF EXISTS `tb_vote`;
CREATE TABLE IF NOT EXISTS `tb_vote` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idSong` int DEFAULT '0' COMMENT 'identifiant du titre',
  `idUser` int DEFAULT '0' COMMENT 'user qui a voté',
  `preference` tinyint NOT NULL COMMENT 'vote préférence',
  `maitrise` tinyint NOT NULL COMMENT 'vote maitrise',
  `groupe` tinyint NOT NULL DEFAULT '3' COMMENT 'Performance du groupe',
  `perso` int NOT NULL COMMENT 'note personnelle',
  `date` date DEFAULT NULL COMMENT 'date maj performance',
  PRIMARY KEY (`id`),
  KEY `i_song` (`idSong`),
  KEY `i_user` (`idUser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
