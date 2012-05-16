ALTER TABLE ogAdmBD.menus MODIFY resolucion smallint(4);

ALTER TABLE `perfileshard` ADD `winboot` enum( 'reboot', 'kexec' ) NOT NULL DEFAULT 'reboot';

ALTER TABLE  `ordenadores_particiones` ADD `cache` varchar(500);