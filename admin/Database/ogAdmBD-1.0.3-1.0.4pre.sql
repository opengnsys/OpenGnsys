ALTER TABLE ogAdmBD.menus MODIFY resolucion smallint(4);

ALTER TABLE perfileshard ADD winboot enum( 'reboot', 'kexec' ) NOT NULL DEFAULT 'reboot';

ALTER TABLE ordenadores_particiones MODIFY codpar int(8) NOT NULL;
ALTER TABLE ordenadores_particiones ADD cache varchar(500);

INSERT INTO tipospar (codpar,tipopar,clonable) values(6, 'FAT16', 1);
INSERT INTO tipospar (codpar,tipopar,clonable) values(CONV('A5',16,10), 'FREEBSD', 1);
INSERT INTO tipospar (codpar,tipopar,clonable) values(CONV('A6',16,10), 'OPENBSD', 1);
INSERT INTO tipospar (codpar,tipopar,clonable) values(CONV('AF',16,10), 'HFS', 1);
INSERT INTO tipospar (codpar,tipopar,clonable) values(CONV('BE',16,10), 'SOLARIS-BOOT', 1);
INSERT INTO tipospar (codpar,tipopar,clonable) values(CONV('DA',16,10), 'DATA', 1);
INSERT INTO tipospar (codpar,tipopar,clonable) values(CONV('EE',16,10), 'GPT', 0);
INSERT INTO tipospar (codpar,tipopar,clonable) values(CONV('EF',16,10), 'EFI', 0);
INSERT INTO tipospar (codpar,tipopar,clonable) values(CONV('FB',16,10), 'VMFS', 0);
INSERT INTO tipospar (codpar,tipopar,clonable) values(CONV('0700',16,10), 'WINDOWS', 0);

