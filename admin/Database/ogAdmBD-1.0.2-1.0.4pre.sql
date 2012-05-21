UPDATE ogAdmBD.entornos SET ipserveradm = 'SERVERIP' WHERE ipserveradm = '' LIMIT 1;

UPDATE ogAdmBD.parametros SET tipopa = '1' WHERE idparametro = 30;

UPDATE ogAdmBD.idiomas SET descripcion = 'English' WHERE ididioma = 2;
UPDATE ogAdmBD.idiomas SET descripcion = 'Català' WHERE ididioma = 3;

ALTER TABLE ogAdmBD.menus MODIFY resolucion smallint(4);

ALTER TABLE ogAdmBD.perfileshard ADD winboot enum( 'reboot', 'kexec' ) NOT NULL DEFAULT 'reboot';

ALTER TABLE ogAdmBD.ordenadores_particiones
	MODIFY codpar int(8) NOT NULL,
	ADD cache varchar(500);

ALTER TABLE ogAdmBD.tipospar MODIFY codpar int(8) NOT NULL;
INSERT INTO ogAdmBD.tipospar (codpar,tipopar,clonable) VALUES
	(6, 'FAT16', 1),
	(CONV('A5',16,10), 'FREEBSD', 1),
	(CONV('A6',16,10), 'OPENBSD', 1),
	(CONV('AF',16,10), 'HFS', 1);
	(CONV('BE',16,10), 'SOLARIS-BOOT', 1),
	(CONV('DA',16,10), 'DATA', 1),
	(CONV('EE',16,10), 'GPT', 0),
	(CONV('EF',16,10), 'EFI', 0),
	(CONV('FB',16,10), 'VMFS', 1),
	(CONV('0700',16,10), 'WINDOWS', 1),
	(CONV('8200',16,10), 'LINUX-SWAP', 0),
	(CONV('8300',16,10), 'LINUX', 1),
	(CONV('8301',16,10), 'CACHE', 0),
	(CONV('8E00',16,10), 'LINUX-LVM', 1),
	(CONV('A500',16,10), 'FREEBSD-DISK', 0),
	(CONV('A501',16,10), 'FREEBSD-BOOT', 1),
	(CONV('A502',16,10), 'FREEBSD-SWAP', 0),
	(CONV('A503',16,10), 'FREEBSD', 1),
	(CONV('AF00',16,10), 'HFS', 1),
	(CONV('BE00',16,10), 'SOLARIS-BOOT', 1),
	(CONV('BF00',16,10), 'SOLARIS', 1),
	(CONV('BF01',16,10), 'SOLARIS', 1),
	(CONV('BF02',16,10), 'SOLARIS-SWAP', 0),
	(CONV('BF03',16,10), 'SOLARIS-DISK', 1),
	(CONV('BF04',16,10), 'SOLARIS', 1),
	(CONV('BF05',16,10), 'SOLARIS', 1),
	(CONV('EF00',16,10), 'EFI', 0),
	(CONV('EF01',16,10), 'MBR', 0),
	(CONV('EF02',16,10), 'BIOS-BOOT', 0),
	(CONV('FD00',16,10), 'LINUX-RAID', 1),
	(CONV('FFFF',16,10), 'UNKNOWN', 1);

ALTER TABLE ogAdmBD.ordenadores ADD fotoord VARCHAR (250) NOT NULL;