### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.5 - 1.1.0
#use ogAdmBD

# Nuevos tipos de particiones y de sistemas de ficheros.
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
INSERT INTO sistemasficheros (idsistemafichero, nemonico, descripcion) VALUES
	(19, 'LINUX-SWAP', 'LINUX-SWAP'),
	(20, 'F2FS', 'F2FS'),
	(21, 'NILFS2', 'NILFS2')
	ON DUPLICATE KEY UPDATE
		idsistemafichero=VALUES(idsistemafichero), nemonico=VALUES(nemonico), descripcion=VALUES(descripcion);

# Incluir ordenador modelo y fecha de creación de imagen y
# establecer valores por defecto (ticket #677).
ALTER TABLE imagenes
	MODIFY idrepositorio INT(11) NOT NULL DEFAULT 0,
	MODIFY numdisk SMALLINT NOT NULL DEFAULT 0,
	MODIFY numpar SMALLINT NOT NULL DEFAULT 0,
	MODIFY codpar INT(8) NOT NULL DEFAULT 0,
	ADD idordenador INT(11) NOT NULL DEFAULT 0 AFTER idrepositorio,
	ADD fechacreacion DATETIME DEFAULT NULL;

# Incluir fecha de despliegue/restauración de imagen (ticket #677) y
# correcion en eliminar imagen de cache de cliente (ticket #658).
ALTER TABLE ordenadores_particiones
	ADD fechadespliegue DATETIME NULL AFTER idperfilsoft,
	MODIFY cache TEXT NOT NULL;

# Mostrar protocolo de clonación en la cola de acciones (ticket #672).
UPDATE parametros
	SET tipopa = 0
	WHERE idparametro = 30;

# Mostrar disco en comandos Inventario de software e Iniciar sesión.
UPDATE comandos
	SET visuparametros = 'dsk;par', parametros = 'nfn;iph;mac;dsk;par'
	WHERE idcomando = 7;
UPDATE comandos
	SET visuparametros = 'dsk;par', parametros = 'nfn;iph;dsk;par'
	WHERE idcomando = 9;

# Eliminar campos que ya no se usan y añadir clave de acceso a la API REST del repositorio (tickets #705 y #743).
ALTER TABLE repositorios
	DROP pathrepoconf,
	DROP pathrepod,
	DROP pathpxe,
	ADD apikey VARCHAR(32) NOT NULL DEFAULT '';
ALTER TABLE menus
	DROP coorx,
	DROP coory,
	DROP scoorx,
	DROP scoory;

# Actualizar componentes hardware y añadir nº de serie y clave de acceso a API REST de OGAgent (tickets #713 y #718) 
ALTER TABLE tipohardwares
        DROP pci;
INSERT INTO tipohardwares (idtipohardware, descripcion, urlimg, nemonico) VALUES
        (17, 'Chasis del Sistema', '', 'cha'),
        (18, 'Controladores de almacenamiento', '../images/iconos/almacenamiento.png', 'sto'),
        (19, 'Tipo de proceso de arranque', '../images/iconos/arranque.png', 'boo');
ALTER TABLE ordenadores
	ADD numserie varchar(25) DEFAULT NULL AFTER nombreordenador,
	ADD agentkey VARCHAR(32) DEFAULT NULL,
	ADD KEY idaulaip (idaula ASC, ip ASC);

# Directorios en repo para distintas UO (ticket #678).
ALTER TABLE entidades
	ADD ogunit TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE centros
	ADD directorio VARCHAR(50) DEFAULT '';

# Incluir campo ID sistema operativo en el perfil de software (tickets #738 #713)
ALTER TABLE perfilessoft
        ADD idnombreso SMALLINT UNSIGNED AFTER idperfilsoft;

# Preparar generación de claves de acceso a la API REST para el usuario principal y a la del repositorio principal (tickets #708 y #743).
UPDATE usuarios
	SET apikey = 'APIKEY'
	WHERE idusuario = 1 AND apikey = '';
UPDATE repositorios
	SET apikey = 'REPOKEY'
	WHERE idrepositorio = 1 AND apikey = '';

# Número de puestos del aula permite valores hasta 32768 (ticket #747)
ALTER TABLE  aulas
     MODIFY puestos smallint  DEFAULT NULL;

