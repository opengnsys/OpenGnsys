### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.5 - 1.0.6
#use ogAdmBD

# Correccion en eliminar imagen de cache de cliente (ticket #658).
ALTER TABLE ordenadores_particiones
	MODIFY cache TEXT NOT NULL;

# Mostrar protocolo de clonación en la cola de acciones (ticket #672)
UPDATE parametros
	SET tipopa = 0
	WHERE idparametro = 30;

