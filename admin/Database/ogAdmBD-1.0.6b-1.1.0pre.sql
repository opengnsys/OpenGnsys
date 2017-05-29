### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.6 - OpenGnsys 1.1.0
#use ogAdmBD

### NOTA: la configuración de MySQL solo puede modificarla el usuario "root".
# Soportar cláusuloas GROUP BY especiales para configuración de equipos.
#SET GLOBAL sql_mode = TRIM(BOTH ',' FROM REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));
# Activar calendario de eventos de MySQL.
#SET GLOBAL event_scheduler = ON;

# Nuevos tipos de particiones y de sistemas de ficheros (ticket #758).
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
	(CONV('FB02',16,10), 'VMFS-KRN', 1),
	(CONV('10000',16,10), 'LVM-LV', 1),
	(CONV('10010',16,10), 'ZFS-VOL', 1)
	ON DUPLICATE KEY UPDATE
		codpar=VALUES(codpar), tipopar=VALUES(tipopar), clonable=VALUES(clonable);
INSERT INTO sistemasficheros (idsistemafichero, nemonico, descripcion) VALUES
	(20, 'F2FS', 'F2FS'),
	(21, 'NILFS2', 'NILFS2')
	ON DUPLICATE KEY UPDATE
		idsistemafichero=VALUES(idsistemafichero), nemonico=VALUES(nemonico), descripcion=VALUES(descripcion);

# Añadir campos para aulas: servidor NTP e inclusión en proyecto Remote PC (tickets #725 y #708).
ALTER TABLE aulas
	ADD ntp VARCHAR(30) AFTER proxy,
	ADD inremotepc TINYINT DEFAULT 0;
# Añadir campos para nº de revisión de imágenes y su inclusión en proyecto Remote PC (tickets #737 y #708).
ALTER TABLE imagenes
	ADD revision SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER nombreca,
	ADD inremotepc TINYINT DEFAULT 0;

# Adaptar campo para codificar claves de usuarios (ticket #778),
# añadir clave de acceso a la API REST (tickets #708).
ALTER TABLE usuarios
	MODIFY pasguor VARCHAR(56) NOT NULL DEFAULT '',
	ADD apikey VARCHAR(32) NOT NULL DEFAULT '';
# Preparar generación de clave de acceso a la API REST para el usuario principal (ticket #708).
UPDATE usuarios
	SET apikey = 'APIKEY'
	WHERE idusuario = 1 AND apikey = '';
# Codificar claves de usuarios (ticket #)
INSERT INTO usuarios (idusuario, pasguor)
	SELECT idusuario, pasguor FROM usuarios
	ON DUPLICATE KEY UPDATE
		idusuario=VALUES(idusuario), pasguor=SHA2(VALUES(pasguor),224);

# Añadir nº de revisión de imagen restaurada (ticket #737),
# añadir porcentaje de uso de sistema de ficheros (ticket #711),
# evitar errores "TEXT NOT NULL" y "NO_ZERO_DATE" (ticket #730).
ALTER TABLE ordenadores_particiones
	MODIFY cache TEXT,
	ADD revision SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER idimagen,
	ADD uso TINYINT NOT NULL DEFAULT 0;
ALTER TABLE acciones
	MODIFY restrambito TEXT,
	MODIFY fechahorareg DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
	MODIFY fechahorafin DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00';
ALTER TABLE parametros 
	MODIFY descripcion TEXT;
ALTER TABLE tareas
	MODIFY restrambito TEXT;

# Actualizar componentes hardware y añadir nº de serie y clave de acceso a API REST de OGAgent (tickets #713 y #718)
ALTER TABLE tipohardwares
	DROP pci;
INSERT INTO tipohardwares (idtipohardware, descripcion, urlimg, nemonico) VALUES
	(17, 'Chasis del Sistema', '', 'cha'),
	(18, 'Controladores de almacenamiento', '../images/iconos/almacenamiento.png', 'sto'),
	(19, 'Tipo de proceso de arranque', '../images/iconos/arranque.png', 'boo');
ALTER TABLE ordenadores
	ADD numserie varchar(25) DEFAULT NULL AFTER nombreordenador,
	ADD agentkey VARCHAR(32) DEFAULT NULL;

# Directorios en repo para distintas UO (ticket #678).
ALTER TABLE entidades
	ADD ogunit TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE centros
	ADD directorio VARCHAR(50) DEFAULT '';

# Incluir campo ID sistema operativo en el perfil de software (tickets #738 #713)
ALTER TABLE perfilessoft
	ADD idnombreso SMALLINT UNSIGNED AFTER idperfilsoft;

# Añadir campo y generar clave de acceso a la API REST del repositorio (ticket #743).
ALTER TABLE repositorios
	ADD apikey VARCHAR(32) NOT NULL DEFAULT '';
UPDATE repositorios
	SET apikey = 'REPOKEY'
	WHERE idrepositorio = 1 AND apikey = '';

# Número de puestos del aula permite valores hasta 32768 (ticket #747)
ALTER TABLE  aulas
     MODIFY puestos smallint  DEFAULT NULL;

# Nueva tabla para datos del proyecto Remote PC (ticket #708).
CREATE TABLE IF NOT EXISTS remotepc (
	id INT(11) NOT NULL,
	reserved DATETIME DEFAULT NULL,
	urllogin VARCHAR(100),
	urllogout VARCHAR(100),
	PRIMARY KEY (id)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

# Nuevo comando "Enviar mensaje" (ticket #779)
INSERT INTO comandos  (idcomando, descripcion, pagina, gestor, funcion, urlimg,
        aplicambito, visuparametros, parametros, comentarios, activo, submenu) VALUES
        (16, 'Enviar mensaje', '../comandos/EnviarMensaje.php', '../comandos/gestores/gestor_Comandos.php', 'EnviarMensaje', '',
        31, '', '', '', 1, '' );
INSERT INTO parametros (idparametro, nemonico, descripcion, nomidentificador, nomtabla, nomliteral, tipopa, visual) VALUES 
	(39, 'tit', 'Título', '', '', '', 0, 1),
	(40, 'msj', 'Contenido', '', '', '', 0, 1);

# Crear tabla de log para la cola de acciones (ticket #...)
CREATE TABLE IF NOT EXISTS acciones_log LIKE acciones;
ALTER TABLE acciones_log ADD fecha_borrado DATETIME;
DELIMITER //
CREATE TRIGGER registrar_acciones BEFORE DELETE ON acciones FOR EACH ROW BEGIN
	INSERT INTO acciones_log VALUES
		(OLD.idaccion, OLD.tipoaccion, OLD.idtipoaccion, OLD.descriaccion,
		OLD.idordenador, OLD.ip, OLD.sesion, OLD.idcomando, OLD.parametros,
		OLD.fechahorareg, OLD.fechahorafin, OLD.estado, OLD.resultado,
		OLD.descrinotificacion, OLD.ambito, OLD.idambito, OLD.restrambito,
		OLD.idprocedimiento, OLD.idtarea, OLD.idcentro, OLD.idprogramacion, NOW());
END//
DELIMITER ;

