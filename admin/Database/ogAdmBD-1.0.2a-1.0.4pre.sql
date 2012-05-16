UPDATE ogAdmBD.idiomas SET descripcion = 'English' WHERE ididioma = 2;
UPDATE ogAdmBD.idiomas SET descripcion = 'Català' WHERE ididioma = 3;

ALTER TABLE ogAdmBD.menus MODIFY resolucion smallint(4);

ALTER TABLE `perfileshard` ADD `winboot` ENUM( 'reboot', 'kexec' ) NOT NULL DEFAULT 'reboot';

ALTER TABLE  `ordenadores_particiones` ADD `cache` varchar(500);