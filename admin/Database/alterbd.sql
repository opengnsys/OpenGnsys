SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de datos: `ogBDAdmin`
--

ALTER TABLE `ordenadores`ADD `arranque` VARCHAR( 30 ) NOT NULL DEFAULT '1',
ADD `netiface` enum('eth0','eth1','eth2') DEFAULT 'eth0',
ADD `netdriver` VARCHAR( 30 ) NOT NULL DEFAULT 'generic';





ALTER TABLE `aulas` ADD `router` VARCHAR( 30 ),
ADD `netmask` VARCHAR( 30 ),
ADD `dns` VARCHAR (30),
ADD `modp2p` enum('seeder','peer','leecher') DEFAULT 'seeder',
ADD `timep2p` INT(11) NOT NULL DEFAULT '60';






CREATE TABLE IF NOT EXISTS `itemboot` (
  `label` varchar(50) collate utf8_spanish_ci NOT NULL,
  `kernel` varchar(100) collate utf8_spanish_ci NOT NULL,
  `append` varchar(500) collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;



INSERT INTO `itemboot` (`label`, `kernel`, `append`) VALUES
('1', 'KERNEL syslinux/chain.c32', 'APPEND hd0'),
('1_localboot', 'LOCALBOOT 0', ' '),
('11', 'KERNEL syslinux/chain.c32', 'APPEND hd0 1'),
('12', 'KERNEL syslinux/chain.c32', 'APPEND hd0 2'),
('ogClientUser', 'KERNEL ogclient/vmlinuz-2.6.32-21-generic-pae', 'APPEND initrd=ogclient/initrd.img-2.6.32-21-generic-pae ro boot=oginit vga=788 irqpoll acpi=on og2nd=sqfs ogprotocol=smb engine=testing ogactiveadmin=false'),
('ogClientAdmin', 'KERNEL ogclient/vmlinuz-2.6.32-21-generic-pae', 'APPEND initrd=ogclient/initrd.img-2.6.32-21-generic-pae ro boot=oginit vga=788 irqpoll acpi=on og2nd=sqfs ogprotocol=smb engine=testing ogactiveadmin=true'),
('ogInitrdUser', 'KERNEL linux', 'APPEND initrd=initrd.gz ip=dhcp ro vga=788 irqpoll acpi=on boot=user '),
('ogInitrdAdmin', 'KERNEL linux', 'APPEND initrd=initrd.gz ip=dhcp ro vga=788 irqpoll acpi=on boot=admin ');




CREATE TABLE IF NOT EXISTS `menuboot` (
  `label` varchar(50) collate utf8_spanish_ci NOT NULL,
  `prompt` int(11) NOT NULL,
  `timeout` int(30) default NULL,
  `description` varchar(50) collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;



INSERT INTO `menuboot` (`label`, `prompt`, `timeout`, `description`) VALUES
('1', 0, 10, 'mbr 1hd'),
('11', 0, 10, '1hd 1particion'),
('12', 0, 10, '1hd 2particion'),
('pxe', 0, 10, 'og client - user'),
('pxeADMIN', 0, 10, 'OgClient - admin');


CREATE TABLE IF NOT EXISTS `menuboot_itemboot` (
  `labelmenu` varchar(100) NOT NULL,
  `labelitem` varchar(100) NOT NULL,
  `default` tinyint(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


INSERT INTO `menuboot_itemboot` (`labelmenu`, `labelitem`, `default`) VALUES
('0', '0', 0),
('11', '11', 0),
('12', '12', 0),
('1', '1', 0),
('pxe', 'ogClientUser', 0),
('pxeADMIN', 'ogClientAdmin', 0);


INSERT INTO `ogAdmBD`.`comandos` (
`idcomando` ,
`descripcion` ,
`pagina` ,
`gestor` ,
`funcion` ,
`urlimg` ,
`aplicambito` ,
`visuparametros` ,
`parametros` ,
`comentarios` ,
`activo`
)
VALUES 
('11', 'Asistente Clonacion Particiones Remotas', '../comandos/AsistenteCloneRemotePartition.php', '../comandos/gestores/gestor_Comandos.php', 'EjecutarScript', ' ', '31', 'iph;tis;dcr;dsp', 'nfn;iph;tis;dcr;scp', ' ', '1'),
('12', 'Asistente "Deploy" de Imagenes', '../comandos/AsistenteDeployImage.php', '../comandos/gestores/gestor_Comandos.php', 'EjecutarScript', ' ', '31', 'iph;tis;dcr;dsp', 'nfn;iph;tis;dcr;scp', ' ', '1'),
('13', 'Asistente "UpdateCache" con Imagenes', '../comandos/AsistenteUpdateCache.php', '../comandos/gestores/gestor_Comandos.php', 'EjecutarScript', ' ', '31', 'iph;tis;dcr;dsp', 'nfn;iph;tis;dcr;scp', ' ', '0'),
('14', 'Asistente Restauracion de Imagenes', '../comandos/AsistenteRestoreImage.php', '../comandos/gestores/gestor_Comandos.php', 'EjecutarScript', ' ', '31', 'iph;tis;dcr;dsp', 'nfn;iph;tis;dcr;scp', ' ', '0'),
('15', 'Asistente Particionado', '../comandos/AsistenteParticionado.php', '../comandos/gestores/gestor_Comandos.php', 'EjecutarScript', ' ', '31', 'iph;tis;dcr;dsp', 'nfn;iph;tis;dcr;scp', ' ', '1');



