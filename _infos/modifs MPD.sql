-- 2026-09-27 : menu « Clôturés » : l'action contenait un retour à la ligne parasite
UPDATE `tb_menu` SET `action` = 'prospectListerT' WHERE `indentation` = 606000;
