### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.5 - 1.0.6
#use ogAdmBD

# Incluir ordenador modelo y fecha de creación de imagen (ticket #677).
ALTER TABLE imagenes
	ADD idordenador INT(11) NOT NULL AFTER idrepositorio,
	ADD fechacreacion DATETIME NULL;

# Incluir fecha de despliegue/restauración de imagen (ticket #677) y
# correcion en eliminar imagen de cache de cliente (ticket #658).
ALTER TABLE ordenadores_particiones
	ADD fechadespliegue DATETIME NULL AFTER idperfilsoft,
	MODIFY cache TEXT NOT NULL;

# Mostrar protocolo de clonación en la cola de acciones (ticket #672).
UPDATE parametros
	SET tipopa = 0
	WHERE idparametro = 30;

