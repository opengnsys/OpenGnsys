### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.6
#use ogAdmBD

# Mostrar protocolo de clonación en la cola de acciones (ticket #672)
UPDATE parametros
	SET tipopa = 0
	WHERE idparametro = 30;

