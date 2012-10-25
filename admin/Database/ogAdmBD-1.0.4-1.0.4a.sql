# Internacionalización correcta de los asistentes.
UPDATE ogAdmBD.asistentes
	SET descripcion = 'Asistente Deploy de Imagenes' WHERE descripcion = 'Asistente "Deploy" de Imagenes';
UPDATE ogAdmBD.asistentes
	SET descripcion = 'Asistente UpdateCache con Imagenes' WHERE descripcion = 'Asistente "UpdateCache" con Imagenes';
# Mejorar el rendimiento en acceso a la cola de acciones.
ALTER TABLE ogAdmBD.acciones
	ADD KEY (idordenador),
	ADD KEY (idprocedimiento),
	ADD KEY (idtarea),
	ADD KEY (idprogramacion);

