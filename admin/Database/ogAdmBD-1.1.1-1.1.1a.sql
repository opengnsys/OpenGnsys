### Fichero de actualización de la base de datos.
# OpenGnsys 1.1.1 - OpenGnsys 1.1.1a
#use ogAdmBD

# Evitar fallo al obtener configuración de ordenador.
ALTER TABLE ordenadores_particiones
	ALTER idperfilsoft SET DEFAULT 0;
