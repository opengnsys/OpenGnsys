### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.5 - 1.1.0
#use ogAdmBD

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

# Directorios en repo para distintas UO (ticket #678).
ALTER TABLE entidades
	ADD ogunit TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE centros
	ADD directorio VARCHAR(50) DEFAULT '';

