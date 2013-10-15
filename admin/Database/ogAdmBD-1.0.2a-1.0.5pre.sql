UPDATE ogAdmBD.idiomas SET descripcion = 'English' WHERE ididioma = 2;
UPDATE ogAdmBD.idiomas SET descripcion = 'Català' WHERE ididioma = 3;

# Habilita el comando PArticionar y formatear
UPDATE `ogAdmBD`.`comandos` SET `activo` = '1' WHERE `comandos`.`idcomando` =10;


ALTER TABLE ogAdmBD.menus MODIFY resolucion smallint(4);

ALTER TABLE ogAdmBD.perfileshard ADD winboot enum( 'reboot', 'kexec' ) NOT NULL DEFAULT 'reboot';

ALTER TABLE ogAdmBD.ordenadores_particiones
	MODIFY codpar int(8) NOT NULL,
	ADD numdisk tinyint(4) NOT NULL DEFAULT 1 AFTER idordenador,
	ADD cache varchar(500),
	DROP INDEX idordenadornumpar,
	ADD UNIQUE idordenadornumdisknumpar(idordenador,numdisk,numpar);

ALTER TABLE ogAdmBD.imagenes MODIFY codpar int(8) NOT NULL;
ALTER TABLE ogAdmBD.sistemasficheros MODIFY codpar int(8) NOT NULL;
ALTER TABLE ogAdmBD.tipospar MODIFY codpar int(8) NOT NULL;
INSERT INTO ogAdmBD.tipospar (codpar,tipopar,clonable) VALUES
	(6, 'FAT16', 1),
	(CONV('A5',16,10), 'FREEBSD', 1),
	(CONV('A6',16,10), 'OPENBSD', 1),
	(CONV('AF',16,10), 'HFS', 1),
	(CONV('BE',16,10), 'SOLARIS-BOOT', 1),
	(CONV('DA',16,10), 'DATA', 1),
	(CONV('EE',16,10), 'GPT', 0),
	(CONV('EF',16,10), 'EFI', 1),
	(CONV('FB',16,10), 'VMFS', 1),
	(CONV('0700',16,10), 'WINDOWS', 1),
	(CONV('0C01',16,10), 'WIN-RESERV', 1),
	(CONV('7F00',16,10), 'CHROMEOS-KRN', 1),
	(CONV('7F01',16,10), 'CHROMEOS', 1),
	(CONV('7F02',16,10), 'CHROMEOS-RESERV', 1),
	(CONV('8200',16,10), 'LINUX-SWAP', 0),
	(CONV('8300',16,10), 'LINUX', 1),
	(CONV('8301',16,10), 'LINUX-RESERV', 1),
	(CONV('8E00',16,10), 'LINUX-LVM', 1),
	(CONV('A500',16,10), 'FREEBSD-DISK', 0),
	(CONV('A501',16,10), 'FREEBSD-BOOT', 1),
	(CONV('A502',16,10), 'FREEBSD-SWAP', 0),
	(CONV('A503',16,10), 'FREEBSD', 1),
	(CONV('AB00',16,10), 'HFS-BOOT', 1),
	(CONV('AF00',16,10), 'HFS', 1),
	(CONV('AF01',16,10), 'HFS-RAID', 1),
	(CONV('BE00',16,10), 'SOLARIS-BOOT', 1),
	(CONV('BF00',16,10), 'SOLARIS', 1),
	(CONV('BF01',16,10), 'SOLARIS', 1),
	(CONV('BF02',16,10), 'SOLARIS-SWAP', 0),
	(CONV('BF03',16,10), 'SOLARIS-DISK', 1),
	(CONV('BF04',16,10), 'SOLARIS', 1),
	(CONV('BF05',16,10), 'SOLARIS', 1),
	(CONV('CA00',16,10), 'CACHE', 0),
	(CONV('EF00',16,10), 'EFI', 1),
	(CONV('EF01',16,10), 'MBR', 0),
	(CONV('EF02',16,10), 'BIOS-BOOT', 0),
	(CONV('FD00',16,10), 'LINUX-RAID', 1),
	(CONV('FFFF',16,10), 'UNKNOWN', 1);

ALTER TABLE ogAdmBD.ordenadores ADD fotoord VARCHAR (250) NOT NULL;

UPDATE ogAdmBD.aulas SET urlfoto = SUBSTRING_INDEX (urlfoto, '/', -1) WHERE urlfoto LIKE '%/%';

# Actualización SQL para crear el comando Eliminar Imagen Cache.
INSERT INTO ogAdmBD.comandos
	SET idcomando=11, descripcion='Eliminar Imagen Cache',
	    pagina='../comandos/EliminarImagenCache.php',
	    gestor='../comandos/gestores/gestor_Comandos.php',
	    funcion='EliminarImagenCache', aplicambito=31,
	    visuparametros='iph;tis;dcr;scp', parametros='nfn;iph;tis;dcr;scp', activo=1;

# Cambios para NetBoot con ficheros dinámicos (tickets #534 #582).
DROP TABLE IF EXISTS menuboot;
DROP TABLE IF EXISTS itemboot;
DROP TABLE IF EXISTS menuboot_itemboot;
ALTER TABLE ordenadores
	MODIFY arranque VARCHAR(30) NOT NULL DEFAULT '00unknown';
UPDATE ordenadores SET arranque = '01' WHERE arranque = '1';
UPDATE ordenadores SET arranque = '19pxeadmin' WHERE arranque = 'pxeADMIN';

# Habilita el comando Particionar y formatear.
UPDATE comandos SET activo = '1' WHERE idcomando = 10;
ALTER TABLE sistemasficheros
	ADD UNIQUE INDEX descripcion (descripcion);
INSERT INTO sistemasficheros (descripcion, nemonico) VALUES
	('EMPTY', 'EMPTY'),
	('CACHE', 'CACHE'),
	('BTRFS', 'BTRFS'),
	('EXFAT', 'EXFAT'),
	('EXT2', 'EXT2'),
	('EXT3', 'EXT3'),
	('EXT4', 'EXT4'),
	('FAT12', 'FAT12'),
	('FAT16', 'FAT16'),
	('FAT32', 'FAT32'),
	('HFS', 'HFS'),
	('HFSPLUS', 'HFSPLUS'),
	('JFS', 'JFS'),
	('NTFS', 'NTFS'),
	('REISERFS', 'REISERFS'),
	('REISER4', 'REISER4'),
	('UFS', 'UFS'),
	('XFS', 'XFS')
	ON DUPLICATE KEY UPDATE
		descripcion=VALUES(descripcion), nemonico=VALUES(nemonico);

# Añadir proxy para aulas.
ALTER TABLE aulas
       ADD proxy VARCHAR(30) AFTER dns;

# Valores por defecto para incorporar ordenadores (ticket #609).
ALTER TABLE ordenadores
	ALTER fotoord SET DEFAULT 'fotoordenador.gif',
	ALTER idproautoexec SET DEFAULT 0;

