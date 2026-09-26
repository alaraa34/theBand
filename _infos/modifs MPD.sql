ALTER TABLE `proposition` DROP `totalVotes`;
ALTER TABLE `user` CHANGE `coeff` `coeff` FLOAT NULL DEFAULT '1' COMMENT 'coefficient pour vote maitrise';
ALTER TABLE `lien` CHANGE `prive` `prive` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'lien privé';
INSERT INTO `menu` (`indentation`, `domaine`, `typeLigne`, `libelle`, `action`, `production`) VALUES ('808000', 'utilitaires', 'externe', 'Webmail', 'https://mail58.lwspanel.com/roundcube/', '1');
INSERT INTO `parametre` (`ID`, `groupe`, `nom`, `libelle`, `valeurA`, `valeurN`, `valeurD`, `valeurB`) VALUES (NULL, 'PLAYER', 'DEFAUT', 'Player présenté par défaut', 'mp3', NULL, NULL, NULL);
ALTER TABLE `vote` ADD `perso` INT NOT NULL COMMENT 'note personnelle' AFTER `groupe`;

INSERT INTO `menu` (`indentation`, `domaine`, `typeLigne`, `libelle`, `action`, `production`) VALUES ('406500', '', 'divider', 'Players pad', '', '1');
INSERT INTO `menu` (`indentation`, `domaine`, `typeLigne`, `libelle`, `action`, `production`) VALUES ('407000', 'player', 'action', 'Pads Test et Concert', 'playerPadTestConcert', '1');
INSERT INTO `menu` (`indentation`, `domaine`, `typeLigne`, `libelle`, `action`, `production`) VALUES ('407500', 'player', 'action', 'Pads prochain Concert', 'playerPadProchainConcert', '1');

ALTER TABLE `menu` CHANGE `action` `action` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nom de la fonction sans ct';
ALTER TABLE `user_theband` CHANGE `Role` `role` INT NULL DEFAULT '0';
ALTER TABLE `commentaire` CHANGE `ID` `id` INT NOT NULL AUTO_INCREMENT;
ALTER TABLE `menu` ADD `controleur` TEXT NOT NULL COMMENT 'controleur du domaine' AFTER `domaine`;
ALTER TABLE `etablissement` CHANGE `ID` `id` INT NOT NULL AUTO_INCREMENT COMMENT 'identifiant';
ALTER TABLE `lien_type` ADD `icone` TEXT NOT NULL COMMENT 'icone à afficher' AFTER `extensions`;
//nouvelles
ALTER TABLE `tb_proposition` CHANGE `proposePar` `idUser` INT NULL DEFAULT '0' COMMENT 'proposé par';

-- 2026-09-26 : mots de passe hashés (password_hash, 60 caractères aujourd'hui, 255 recommandé)
-- À passer AVANT de déployer shared/php/classes/socle/User.php, en local puis sur LWS.
-- Les mots de passe actuels restent valables : chacun est converti en hash à la prochaine connexion du musicien.
ALTER TABLE `tb_user` CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT 'hash password_hash()';
