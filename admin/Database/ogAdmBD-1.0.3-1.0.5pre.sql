### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.3 - 1.0.5
#use ogAdmBD

# Añadir tipo de arranque Windows al perfil hardware.
ALTER TABLE perfileshard ADD winboot enum( 'reboot', 'kexec' ) NOT NULL DEFAULT 'reboot';

# Soportar particiones GPT y añadir información de caché.
ALTER TABLE ordenadores_particiones
	MODIFY codpar int(8) NOT NULL,
	ADD numdisk tinyint(4) NOT NULL DEFAULT 1 AFTER idordenador,
	ADD cache varchar(500),
	DROP INDEX idordenadornumpar,
	ADD UNIQUE idordenadornumdisknumpar(idordenador,numdisk,numpar);

# Nuevos tipos de particiones y particiones GPT.
ALTER TABLE tipospar MODIFY codpar int(8) NOT NULL;
ALTER TABLE imagenes MODIFY codpar int(8) NOT NULL;
ALTER TABLE sistemasficheros MODIFY codpar int(8) NOT NULL;
INSERT INTO tipospar (codpar,tipopar,clonable) VALUES
	(6, 'FAT16', 1),
	(CONV('A5',16,10), 'FREEBSD', 1),
	(CONV('A6',16,10), 'OPENBSD', 1),
	(CONV('AF',16,10), 'HFS', 1),
	(CONV('BE',16,10), 'SOLARIS-BOOT', 1),
	(CONV('DA',16,10), 'DATA', 1),
	(CONV('EE',16,10), 'GPT', 0),
	(CONV('EF',16,10), 'EFI', 0),
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
	(CONV('EF00',16,10), 'EFI', 0),
	(CONV('EF01',16,10), 'MBR', 0),
	(CONV('EF02',16,10), 'BIOS-BOOT', 0),
	(CONV('FD00',16,10), 'LINUX-RAID', 1),
	(CONV('FFFF',16,10), 'UNKNOWN', 1);

# Añadir foto de ordenador.
ALTER TABLE ordenadores ADD fotoord VARCHAR (250) NOT NULL;

# Actualizar localización de foto de aula (eliminar el camino).
UPDATE aulas SET urlfoto = SUBSTRING_INDEX (urlfoto, '/', -1) WHERE urlfoto LIKE '%/%';

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
	(15, 'Restaurar Software Incremental', '../comandos/RestaurarSoftIncremental.php', '../comandos/gestores/gestor_Comandos.php', 'RestaurarSoftIncremental', '', 28, 'dsk;par;idi;nci;ipr;idf;ncf;bpi;cpc;bpc;iph;rti;met;nba', 'nfn;dsk;par;idi;nci;ipr;idf;ncf;bpi;cpc;bpc;iph;rti;met;nba', '', 1, 'Sincronizacion');

# Parámetros para los comandos nuevos.
ALTER TABLE parametros
	ADD KEY (nemonico);
INSERT INTO parametros (idparametro, nemonico, descripcion, nomidentificador, nomtabla, nomliteral, tipopa, visual) VALUES
	(31, 'idf', 'Imagen Incremental', 'idimagen', 'imagenes', 'descripcion', 1, 1),
	(32, 'ncf', 'Nombre canónico de la Imagen Incremental', '', '', '', 0, 1),
	(33, 'bpi', 'Borrar imagen o partición previamente', '', '', '', 5, 1),
	(34, 'cpc', 'Copiar también en cache', '', '', '', 5, 1),
	(35, 'bpc', 'Borrado previo de la imagen en cache', '', '', '', 5, 1),
	(36, 'rti', 'Ruta de origen', '', '', '', 0, 1),
	(37, 'met', 'Método clonación', ';', '', 'Desde caché; Desde repositorio', 3, 1),
	(38, 'nba', 'No borrar archivos en destino', '', '', '', 0, 1);

# Imágenes incrementales.
ALTER TABLE imagenes
	ADD tipo TINYINT NULL,
	ADD imagenid INT NOT NULL DEFAULT '0',
	ADD ruta VARCHAR(250) NULL;
UPDATE imagenes SET tipo=1;

# Cambio de tipo de grupo.
UPDATE grupos SET tipo=70 WHERE tipo=50;

# Actualizar menús para nuevo parámetro "video" del Kernel, que sustituye a "vga" (ticket #573).
ALTER TABLE menus
     MODIFY resolucion VARCHAR(50) DEFAULT NULL;
UPDATE menus SET resolucion = CASE resolucion 
                		   WHEN '355' THEN 'uvesafb:1152x864-16'
				   WHEN '788' THEN 'uvesafb:800x600-16'
        	        	   WHEN '789' THEN 'uvesafb:800x600-24'
				   WHEN '791' THEN 'uvesafb:1024x768-16'
				   WHEN '792' THEN 'uvesafb:1024x768-24'
				   WHEN '794' THEN 'uvesafb:1280x1024-16'
				   WHEN '795' THEN 'uvesafb:1280x1024-24'
				   WHEN '798' THEN 'uvesafb:1600x1200-16'
				   WHEN '799' THEN 'uvesafb:1600x1200-24'
				   WHEN NULL  THEN 'uvesafb:800x600-16'
				   ELSE resolucion
			      END;

