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
