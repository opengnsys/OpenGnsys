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
	(CONV('FB02',16,10), 'VMFS-KRN', 1);
	ON DUPLICATE KEY UPDATE
		codpar=VALUES(codpar), tipopar=VALUES(tipopar), clonable=VALUES(clonable);

# Añadir campo para incluir aulas en proyecto Remote PC (ticket #708).
ALTER TABLE aulas
	ADD inremotepc TINYINT DEFAULT 0:
# Añadir campo para clave de acceso a la API REST (ticket #708).
ALTER TABLE usuarios
	ADD apikey VARCHAR(32) NOT NULL DEFAULT '';

# Añadir campo para porcentaje de uso de sistema de ficheros (ticket #711)
ALTER TABLE ordenadores_particiones
	ADD uso TINYINT NOT NULL DEFAULT 0,

