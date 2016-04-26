### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.6 - 1.1.0
#use ogAdmBD

# Nuevos tipos de particiones.
INSERT INTO tipospar (codpar, tipopar, clonable) VALUES
	(CONV('A9',16,10), 'NETBSD', 1),
	(CONV('2700',16,10), 'WIN-RECOV', 1),
	(CONV('8302',16,10), 'LINUX', 1),
	(CONV('A504',16,10), 'FREEBSD', 1),
	(CONV('A901',16,10), 'NETBSD-SWAP', 0),
	(CONV('A902',16,10), 'NETBSD', 1),
	(CONV('A903',16,10), 'NETBSD', 1),
	(CONV('A904',16,10), 'NETBSD', 1),
	(CONV('A905',16,10), 'NETBSD', 1),
	(CONV('A906',16,10), 'NETBSD-RAID', 1),
	(CONV('AF02',16,10), 'HFS-RAID', 1),
	(CONV('FB00',16,10), 'VMFS', 1),
	(CONV('FB01',16,10), 'VMFS-RESERV', 1),
	(CONV('FB02',16,10), 'VMFS-KRN', 1)
	ON DUPLICATE KEY UPDATE
		codpar=VALUES(codpar), tipopar=VALUES(tipopar), clonable=VALUES(clonable);

# Añadir campos para aulas: servidor NTP e inclusión en proyecto Remote PC (tickets #725 y #708).
ALTER TABLE aulas
	ADD ntp VARCHAR(30) AFTER proxy,
	ADD inremotepc TINYINT DEFAULT 0;
# Añadir campos para nº de revisión de imágenes y su inclusión en proyecto Remote PC (tickets #737 y #708).
ALTER TABLE imagenes
	ADD revision SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER nombreca,
	ADD inremotepc TINYINT DEFAULT 0;
# Añadir campo para clave de acceso a la API REST (ticket #708).
ALTER TABLE usuarios
	ADD apikey VARCHAR(32) NOT NULL DEFAULT '';
# Preparar generación de clave de acceso a la API REST para el usuario principal (ticket #708).
UPDATE usuarios
	SET apikey = 'APIKEY'
	WHERE idusuario = 1 AND apikey = '';

# Añadir nº de revisión de imagen restaurada y porcentaje de uso de sistema de ficheros (tickets #737 y #711)
ALTER TABLE ordenadores_particiones
	ADD revision SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER idimagen,
	ADD uso TINYINT NOT NULL DEFAULT 0;

# Eliminar campo sin uso, nuevos componentes hardware y nº de serie (ticket #713)
ALTER TABLE tipohardwares
	DROP pci;
INSERT INTO tipohardwares (idtipohardware, descripcion, urlimg, nemonico) VALUES
	(17, 'Chasis del Sistema', '', 'cha'),
	(18, 'Controladores de almacenamiento', '../images/iconos/almacenamiento.png', 'sto'),
	(19, 'Tipo de proceso de arranque', '../images/iconos/arranque.png', 'boo');
ALTER TABLE ordenadores
	ADD numserie varchar(25) DEFAULT NULL AFTER nombreordenador;

# Directorios en repo para distintas UO (ticket #678).
ALTER TABLE entidades
	ADD ogunit TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE centros
	ADD directorio VARCHAR(50) DEFAULT '';

# Incluir campo ID sistema operativo en el perfil de software (tickets #738 #713)
ALTER TABLE perfilessoft
	ADD idnombreso SMALLINT UNSIGNED AFTER idperfilsoft;
