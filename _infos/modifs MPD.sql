-- =====================================================================
-- theBand - modifs MPD
-- 30/09/2026 : types de lien communs (sh_lien_type) et usages par sujet
-- Local (base theBand) d'abord, puis prod (konto2850421). Sauvegarde AVANT.
--
-- PREREQUIS : avoir importé dans la base
--   C:\wamp64\www\shared\php\classes\lien\sh_lien_type.sql
--   (en prod, une seule fois pour theBand et training : même base)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Contrôle : tb_lien_type doit être identique à la référence.
--    Doit renvoyer 0 ligne. Sinon : corriger sh_lien_type (et refaire
--    l'export de référence) avant de continuer.
-- ---------------------------------------------------------------------
SELECT tb.*
FROM tb_lien_type tb
LEFT JOIN sh_lien_type sh ON sh.id = tb.id
WHERE sh.id IS NULL
   OR NOT (sh.nom <=> tb.nom AND sh.nomAffiche <=> tb.nomAffiche AND sh.nomLong <=> tb.nomLong
       AND sh.externe <=> tb.externe AND sh.repertoire <=> tb.repertoire AND sh.couleur <=> tb.couleur
       AND sh.extensions <=> tb.extensions AND sh.icone <=> tb.icone);

-- Liens dont le type n'existe pas dans la référence : doit renvoyer 0 ligne
SELECT l.id, l.idTypeLien, l.url FROM tb_lien l
LEFT JOIN sh_lien_type sh ON sh.id = l.idTypeLien
WHERE sh.id IS NULL;

-- ---------------------------------------------------------------------
-- 2. Nouvelle table des usages : sujet en clair, sans saisie/affichage/Libelle
--    Les usages de nomenclature SUJET (71 à 77) deviennent les constantes SUJET_LIEN
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tb_lien_type_usage_new`;
CREATE TABLE `tb_lien_type_usage_new` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idTypeLien` int NOT NULL COMMENT 'Type de lien (sh_lien_type.id)',
  `sujet` varchar(20) NOT NULL COMMENT 'Constante SUJET_LIEN de la classe qui demande les liens',
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_sujet_type` (`sujet`,`idTypeLien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Types de lien admis par sujet';

INSERT INTO `tb_lien_type_usage_new` (`idTypeLien`, `sujet`)
SELECT DISTINCT u.idTypeLien,
       CASE u.idUsage WHEN 71 THEN 'SONG'        WHEN 72 THEN 'PLAYER'     WHEN 73 THEN 'ETABLISSEMENT'
                      WHEN 74 THEN 'CONCERT'     WHEN 75 THEN 'PROPOSITION' WHEN 76 THEN 'REPETITION'
                      WHEN 77 THEN 'TELECHARGEMENT' END
FROM tb_lien_type_usage u
WHERE u.idUsage BETWEEN 71 AND 77
  AND u.saisie = 1
  AND u.idTypeLien IN (SELECT id FROM sh_lien_type);

-- bascule (l'ancienne table est gardée sous le nom _old)
RENAME TABLE `tb_lien_type_usage`     TO `tb_lien_type_usage_old`,
             `tb_lien_type_usage_new` TO `tb_lien_type_usage`;

-- contrôle : nombre de types par sujet
SELECT sujet, COUNT(*) AS nbTypes, GROUP_CONCAT(idTypeLien ORDER BY idTypeLien) AS types
FROM tb_lien_type_usage GROUP BY sujet ORDER BY sujet;

-- ---------------------------------------------------------------------
-- 3. Menu : Utiles > Paramétrage > Types de lien par sujet
--    (changer les indentations si elles sont déjà prises)
-- ---------------------------------------------------------------------
INSERT INTO `tb_menu` (`indentation`, `domaine`, `controleur`, `typeLigne`, `libelle`, `action`, `production`) VALUES
(992000, '', '', 'divider', 'Types de liens', '', 1),
(992200, 'accueil', '', 'action', 'Types de lien par sujet', 'lienUsageEditer', 1);

-- ---------------------------------------------------------------------
-- 4. APRES avoir testé l'application (liens des titres, players,
--    concerts, répétitions, établissements, propositions, téléchargements) :
--    décommenter et exécuter
-- ---------------------------------------------------------------------
DROP TABLE `tb_lien_type_usage_old`;
DROP TABLE `tb_lien_type`;
DELETE FROM `tb_nomenclature` WHERE groupe = 'SUJET';

-- =====================================================================
-- 09/10/2026 : setlists en InnoDB pour que l'enregistrement soit en transaction
-- (MyISAM ignore les transactions : si l'écriture échouait, la setlist
--  pouvait rester vide). Sans risque, les données sont conservées.
-- Local d'abord, puis prod. Sauvegarde AVANT.
-- =====================================================================
ALTER TABLE `tb_setlist`        ENGINE=InnoDB;
ALTER TABLE `tb_setlist_detail` ENGINE=InnoDB;

-- contrôle : les deux lignes doivent afficher InnoDB
SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('tb_setlist','tb_setlist_detail');

-- =====================================================================
-- 10/10/2026 : lecteur multipiste, génération des pistes (branche multipistes)
-- Local d'abord, puis prod. Sauvegarde AVANT.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. État de génération des pistes, une ligne par lien MP3 / MP3TB traité
--    statut : 1 à traiter, 2 en cours, 3 terminé, 4 erreur (constantes de Multipiste)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tb_multipiste` (
  `idLien` int NOT NULL COMMENT 'Lien MP3 source (tb_lien.id)',
  `statut` tinyint NOT NULL DEFAULT '1' COMMENT '1 à traiter, 2 en cours, 3 terminé, 4 erreur',
  `dateDemande` datetime DEFAULT NULL,
  `idDemandeur` int NOT NULL DEFAULT '0' COMMENT 'tb_user.id de qui a demandé la génération',
  `dateDebut` datetime DEFAULT NULL COMMENT 'Prise en charge par un agent',
  `dateFin` datetime DEFAULT NULL,
  `agent` varchar(40) NOT NULL DEFAULT '' COMMENT 'Nom du PC qui fait ou a fait le traitement',
  `tailleSource` int NOT NULL DEFAULT '0' COMMENT 'Taille du MP3 source lors de la génération (détecte un remplacement)',
  `message` varchar(255) NOT NULL DEFAULT '' COMMENT 'Erreur éventuelle',
  PRIMARY KEY (`idLien`),
  KEY `i_statut` (`statut`,`dateDemande`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Génération des pistes du lecteur multipiste';

-- ---------------------------------------------------------------------
-- 2. Menu : Titres > Lecteur multipiste > Générer les pistes (administrateurs seulement)
--    La colonne adminOnly doit exister dans tb_menu (utilisée par Menu.php)
--    (changer les indentations si elles sont déjà prises)
-- ---------------------------------------------------------------------
INSERT INTO `tb_menu` (`indentation`, `domaine`, `controleur`, `typeLigne`, `libelle`, `action`, `production`, `adminOnly`) VALUES
(110000, '', '', 'divider', 'Lecteur multipiste', '', 1, 1),
(111000, 'song', '', 'action', 'Générer les pistes', 'genererPistesLecteur', 1, 1);

-- ---------------------------------------------------------------------
-- 3. Fichiers à créer à la main (local et LWS) :
--    - theBand/ENVIRagent.txt : une ligne, la clé secrète partagée avec les agents
--      (au moins 20 caractères, par exemple 40 caractères aléatoires)
--    - dossier theBand/fichiers/multipistes/ (créé automatiquement sinon)
-- ---------------------------------------------------------------------

-- ---------------------------------------------------------------------
-- 4. Reclassement des MP3 (10/10/2026)
--    - les MP3 (1) actuels sont des enregistrements du groupe : ils deviennent MP3TB (18)
--    - les MP3 détonnés (54, tona concert) deviennent les MP3 de référence (1)
--    L'ORDRE EST IMPORTANT : 1 -> 18 d'abord, sinon les anciens 54 passeraient aussi en 18.
--    Les fichiers ne bougent pas : les url (fichiers/mp3/..., fichiers/mp3d/...) restent valables.
--    tb_lien est en MyISAM (pas de transaction) : sauvegarde de tb_lien et tb_lien_type_usage AVANT.
-- ---------------------------------------------------------------------
-- contrôle avant : noter les nombres
SELECT idTypeLien, COUNT(*) AS nb FROM tb_lien WHERE idTypeLien IN (1, 18, 54) GROUP BY idTypeLien;

UPDATE tb_lien SET idTypeLien = 18 WHERE idTypeLien = 1;
UPDATE tb_lien SET idTypeLien = 1  WHERE idTypeLien = 54;

-- usages par sujet : MP3TB reprend les sujets de l'ancien MP3, MP3 ceux de l'ancien MP3 détonné
INSERT IGNORE INTO tb_lien_type_usage (idTypeLien, sujet) SELECT 18, sujet FROM tb_lien_type_usage WHERE idTypeLien = 1;
DELETE FROM tb_lien_type_usage WHERE idTypeLien = 1
   AND sujet NOT IN (SELECT sujet FROM (SELECT sujet FROM tb_lien_type_usage WHERE idTypeLien = 54) AS ancien54);
INSERT IGNORE INTO tb_lien_type_usage (idTypeLien, sujet) SELECT 1, sujet FROM tb_lien_type_usage WHERE idTypeLien = 54;
DELETE FROM tb_lien_type_usage WHERE idTypeLien = 54;

-- contrôle après : l'ancien nombre de 1 doit être en 18, l'ancien nombre de 54 en 1, plus aucun 54
SELECT idTypeLien, COUNT(*) AS nb FROM tb_lien WHERE idTypeLien IN (1, 18, 54) GROUP BY idTypeLien;
SELECT sujet, GROUP_CONCAT(idTypeLien ORDER BY idTypeLien) AS types FROM tb_lien_type_usage
WHERE idTypeLien IN (1, 18, 54) GROUP BY sujet;

-- ---------------------------------------------------------------------
-- 5. Suppression des types de lien obsolètes 6 (MP3autre), 31 (MP3 sans voix), 54 (MP3 détonné)
--    A passer APRES la partie 4.
-- ---------------------------------------------------------------------
-- contrôle : doit renvoyer 0 ligne (à faire aussi sur la table des liens de training en prod : même base)
SELECT id, idTypeLien, url FROM tb_lien WHERE idTypeLien IN (6, 31, 54);

DELETE FROM tb_lien_type_usage WHERE idTypeLien IN (6, 31, 54);

-- puis réimporter la référence (les 3 types en ont été retirés) :
--   C:\wamp64\www\shared\php\classes\lien\sh_lien_type.sql
-- contrôle : ne doit plus afficher 6, 31 ni 54
SELECT id, nom FROM sh_lien_type ORDER BY id;
