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

# Actualización SQL para crear el comando Eliminar Imagen Cache.
INSERT INTO ogAdmBD.comandos
	SET idcomando=11, descripcion='Eliminar Imagen Cache',
	    pagina='../comandos/EliminarImagenCache.php',
	    gestor='../comandos/gestores/gestor_Comandos.php',
	    funcion='EliminarImagenCache', aplicambito=31,
	    visuparametros='iph;tis;dcr;scp', parametros='nfn;iph;tis;dcr;scp', activo=1;

