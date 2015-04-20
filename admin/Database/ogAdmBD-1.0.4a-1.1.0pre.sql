### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.4a - 1.1.0
#use ogAdmBD

# Añadir validación del cliente.
ALTER TABLE aulas
	ADD validacion TINYINT(1) DEFAULT 0,
	ADD paginalogin VARCHAR(100),
	ADD paginavalidacion VARCHAR(100);

ALTER TABLE ordenadores
	ADD validacion TINYINT(1) DEFAULT 0,
	ADD paginalogin VARCHAR(100),
	ADD paginavalidacion VARCHAR(100);

# Nuevos comandos.
ALTER TABLE comandos
	ADD submenu VARCHAR(50) NOT NULL DEFAULT '';
INSERT INTO comandos (idcomando, descripcion, pagina, gestor, funcion, urlimg, aplicambito, visuparametros, parametros, comentarios, activo, submenu) VALUES
	(11, 'Eliminar Imagen Cache', '../comandos/EliminarImagenCache.php', '../comandos/gestores/gestor_Comandos.php', 'EliminarImagenCache', '', 31, 'iph;tis;dcr;scp', 'nfn;iph;tis;dcr;scp', '', 1, ''),
	(12, 'Crear Imagen Basica', '../comandos/CrearImagenBasica.php', '../comandos/gestores/gestor_Comandos.php', 'CrearImagenBasica', '', 16, 'dsk;par;cpt;idi;nci;ipr;iph;bpi;cpc;bpc;rti;nba', 'nfn;dsk;par;cpt;idi;nci;ipr;iph;bpi;cpc;bpc;rti;nba', '', 1, 'Sincronizacion'),
	(13, 'Restaurar Imagen Basica', '../comandos/RestaurarImagenBasica.php', '../comandos/gestores/gestor_Comandos.php', 'RestaurarImagenBasica', '', 28, 'dsk;par;idi;nci;ipr;iph;bpi;cpc;bpc;rti;nba;met', 'nfn;dsk;par;idi;nci;ipr;iph;bpi;cpc;bpc;rti;nba;met', '', 1, 'Sincronizacion'),
	(14, 'Crear Software Incremental', '../comandos/CrearSoftIncremental.php', '../comandos/gestores/gestor_Comandos.php', 'CrearSoftIncremental', '', 16, 'dsk;par;idi;nci;ipr;idf;ncf;bpi;cpc;bpc;iph;rti;nba', 'nfn;dsk;par;idi;nci;ipr;idf;ncf;bpi;cpc;bpc;iph;rti;nba', '', 1, 'Sincronizacion'),
	(15, 'Restaurar Software Incremental', '../comandos/RestaurarSoftIncremental.php', '../comandos/gestores/gestor_Comandos.php', 'RestaurarSoftIncremental', '', 28, 'dsk;par;idi;nci;ipr;idf;ncf;bpi;cpc;bpc;iph;rti;met;nba', 'nfn;dsk;par;idi;nci;ipr;idf;ncf;bpi;cpc;bpc;iph;rti;met;nba', '', 1, 'Sincronizacion')
	ON DUPLICATE KEY UPDATE
		descripcion=VALUES(descripcion), pagina=VALUES(pagina),
		gestor=VALUES(gestor), funcion=VALUES(funcion), urlimg=VALUES(urlimg),
		aplicambito=VALUES(aplicambito), visuparametros=VALUES(visuparametros),
		parametros=VALUES(parametros), comentarios=VALUES(comentarios),
		activo=VALUES(activo), submenu=VALUES(submenu);

# Actualización y definición de parámetros para los comandos nuevos.
ALTER TABLE parametros
	ADD KEY (nemonico);
INSERT INTO parametros (idparametro, nemonico, descripcion, nomidentificador, nomtabla, nomliteral, tipopa, visual) VALUES
	(12, 'nci', 'Nombre canónico', '', '', '', 0, 1),
	(21, 'sfi', 'Sistema de fichero', 'nemonico', 'sistemasficheros', 'nemonico', 1, 0),
	(22, 'tam', 'Tamaño', '', '', '', 0, 1),
	(30, 'ptc', 'Protocolo de clonación', ';', '', ';Unicast;Multicast;Torrent', 0, 1),
	(31, 'idf', 'Imagen Incremental', 'idimagen', 'imagenes', 'descripcion', 1, 1),
	(32, 'ncf', 'Nombre canónico de la Imagen Incremental', '', '', '', 0, 1),
	(33, 'bpi', 'Borrar imagen o partición previamente', '', '', '', 5, 1),
	(34, 'cpc', 'Copiar también en cache', '', '', '', 5, 1),
	(35, 'bpc', 'Borrado previo de la imagen en cache', '', '', '', 5, 1),
	(36, 'rti', 'Ruta de origen', '', '', '', 0, 1),
	(37, 'met', 'Método clonación', ';', '', 'Desde caché; Desde repositorio', 3, 1),
	(38, 'nba', 'No borrar archivos en destino', '', '', '', 0, 1);

# Imágenes incrementales, soporte para varios discos y fecha de creación
# (tickets #565, #601 y #677).
ALTER TABLE imagenes
	MODIFY idrepositorio INT(11) NOT NULL DEFAULT 0,
	MODIFY numpar SMALLINT NOT NULL DEFAULT 0,
	MODIFY codpar INT(8) NOT NULL DEFAULT 0,
	ADD idordenador INT(11) NOT NULL DEFAULT 0 AFTER idrepositorio,
	ADD numdisk SMALLINT NOT NULL DEFAULT 0 AFTER idordenador,
	ADD tipo SMALLINT NULL,
	ADD imagenid INT NOT NULL DEFAULT 0,
	ADD ruta VARCHAR(250) NULL,
	ADD fechacreacion DATETIME DEFAULT NULL;
UPDATE imagenes SET tipo=1;

# Cambio de tipo de grupo.
UPDATE grupos SET tipo=70 WHERE tipo=50;

# Actualizar menús para nuevo parámetro "video" del Kernel, que sustituye a "vga" (ticket #573).
ALTER TABLE menus
	MODIFY resolucion VARCHAR(50) DEFAULT NULL;
#UPDATE menus SET resolucion = CASE resolucion
#				   WHEN '355' THEN 'uvesafb:1152x864-16'
#				   WHEN '788' THEN 'uvesafb:800x600-16'
#				   WHEN '789' THEN 'uvesafb:800x600-24'
#				   WHEN '791' THEN 'uvesafb:1024x768-16'
#				   WHEN '792' THEN 'uvesafb:1024x768-24'
#				   WHEN '794' THEN 'uvesafb:1280x1024-16'
#				   WHEN '795' THEN 'uvesafb:1280x1024-24'
#				   WHEN '798' THEN 'uvesafb:1600x1200-16'
#				   WHEN '799' THEN 'uvesafb:1600x1200-24'
#				   WHEN NULL  THEN 'uvesafb:800x600-16'
#				   ELSE resolucion
#			       END;

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
	('XFS', 'XFS'),
	('EXFAT', 'EXFAT')
	ON DUPLICATE KEY UPDATE
		descripcion=VALUES(descripcion), nemonico=VALUES(nemonico);
# Nuevas particiones marcadas como clonables.
INSERT INTO tipospar (codpar, tipopar, clonable) VALUES
	(CONV('EF',16,10), 'EFI', 1),
	(CONV('AB00',16,10), 'HFS-BOOT', 1),
	(CONV('EF00',16,10), 'EFI', 1)
	ON DUPLICATE KEY UPDATE
		codpar=VALUES(codpar), tipopar=VALUES(tipopar), clonable=VALUES(clonable);

# Añadir proxy para aulas.
ALTER TABLE aulas
       ADD proxy VARCHAR(30) AFTER dns;

# Valores por defecto para incorporar ordenadores (ticket #609).
ALTER TABLE ordenadores
	ALTER fotoord SET DEFAULT 'fotoordenador.gif',
	ALTER idproautoexec SET DEFAULT 0;
# Dejar solo nombre del fichero.
UPDATE ordenadores
	SET fotoord = SUBSTRING_INDEX(fotoord, '/', -1);

# Cambio en script genérico de despliegue de imágenes.
UPDATE procedimientos_acciones
	SET parametros = REPLACE (parametros, 'restoreImage%20', 'deployImage%20')
	WHERE idcomando = 8;

# Corregir errata en particiones vacías con número de partición asignado al código de partición.
UPDATE ordenadores_particiones
	SET codpar = 0
	WHERE codpar = numpar AND tamano = 0;

# Incluir fecha de despliegue/restauración (ticket #677) y
# correccion en eliminar imagen de cache de cliente (ticket #658).
ALTER TABLE ordenadores_particiones
	ADD fechadespliegue DATETIME NULL AFTER idperfilsoft,
	MODIFY cache TEXT NOT NULL;

# Mostrar disco en comandos Inventario de software e Iniciar sesión.
UPDATE comandos
	SET visuparametros = 'dsk;par', parametros = 'nfn;iph;mac;dsk;par'
	WHERE idcomando = 7;
UPDATE comandos
	SET visuparametros = 'dsk;par', parametros = 'nfn;iph;dsk;par'
	WHERE idcomando = 9;

# Eliminar campos que ya no se usan (ticket #705).
ALTER TABLE repositorios
	DROP pathrepoconf,
	DROP pathrepod,
	DROP pathpxe;
ALTER TABLE menus
	DROP coorx,
	DROP coory,
	DROP scoorx,
	DROP scoory;

