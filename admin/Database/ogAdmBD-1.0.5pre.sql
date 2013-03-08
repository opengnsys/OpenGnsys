### Procedimiento para actualización de la base de datos.
# OpenGnSys 1.0.5
#use ogAdmBD

# Procedimiento para actualización condicional de tablas.
delimiter '//'
CREATE PROCEDURE addcols() BEGIN
	# Añadir validación del cliente.
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='validacion' AND TABLE_NAME='aulas' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE aulas
			ADD validacion TINYINT(1) DEFAULT 0,
			ADD paginalogin VARCHAR(100),
			ADD paginavalidacion VARCHAR(100);
	END IF;
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='validacion' AND TABLE_NAME='ordenadores' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores
			ADD validacion TINYINT(1) DEFAULT 0,
			ADD paginalogin VARCHAR(100),
			ADD paginavalidacion VARCHAR(100);
	END IF;
	# Submenú para comandos.
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='submenu' AND TABLE_NAME='comandos' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE comandos
			ADD submenu VARCHAR(50) NOT NULL DEFAULT '';
	END IF;
	# Añadir índice para mnemónicos de parámetros.
	IF NOT EXISTS (SELECT * FROM information_schema.STATISTICS
			WHERE COLUMN_NAME='nemonico' AND TABLE_NAME='parametros' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE parametros
			ADD KEY (nemonico);
	END IF;
	# Añadir imágenes diferenciales.
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='tipo' AND TABLE_NAME='imagenes' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE imagenes
			ADD tipo TINYINT NULL,
			ADD imagenid INT NOT NULL DEFAULT '0',
			ADD ruta VARCHAR(250) NULL;
		UPDATE imagenes SET tipo=1;
		UPDATE grupos SET tipo=70 WHERE tipo=50;
	END IF;
END//
# Ejecutar actualización condicional.
delimiter ';'
CALL addcols();
DROP PROCEDURE addcols;

# Nuevos comandos.
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

# Parámetros para los comandos nuevos.
INSERT INTO parametros (idparametro, nemonico, descripcion, nomidentificador, nomtabla, nomliteral, tipopa, visual) VALUES
	(31, 'idf', 'Imagen Incremental', 'idimagen', 'imagenes', 'descripcion', 1, 1),
	(32, 'ncf', 'Nombre canónico de la Imagen Incremental', '', '', '', 0, 1),
	(33, 'bpi', 'Borrar imagen o partición previamente', '', '', '', 5, 1),
	(34, 'cpc', 'Copiar también en cache', '', '', '', 5, 1),
	(35, 'bpc', 'Borrado previo de la imagen en cache', '', '', '', 5, 1),
	(36, 'rti', 'Ruta de origen', '', '', '', 0, 1),
	(37, 'met', 'Método clonación', ';', '', 'Desde caché; Desde repositorio', 3, 1),
	(38, 'nba', 'No borrar archivos en destino', '', '', '', 0, 1)
	ON DUPLICATE KEY UPDATE
		nemonico=VALUES(nemonico), descripcion=VALUES(descripcion),
		nomidentificador=VALUES(nomidentificador), nomtabla=VALUES(nomtabla),
		nomliteral=VALUES(nomliteral), tipopa=VALUES(tipopa), visual=VALUES(visual);

# Actualizar menús para nuevo parámetro "video" del Kernel, que sustituye a "vga" (ticket #573).
ALTER TABLE menus
     MODIFY resolucion VARCHAR(50) DEFAULT NULL;
UPDATE menus
	SET resolucion = CASE resolucion 
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

# Cambios para NetBoot con ficheros dinámicos (tickets #534 #582).
DROP TABLE IF EXISTS menuboot;
DROP TABLE IF EXISTS itemboot;
DROP TABLE IF EXISTS menuboot_itemboot;
ALTER TABLE ordenadores
	MODIFY arranque VARCHAR(30) NOT NULL DEFAULT '01';
UPDATE ordenadores SET arranque = '01' WHERE arranque = '1';
UPDATE ordenadores SET arranque = '19pxeadmin' WHERE arranque = 'pxeADMIN';

