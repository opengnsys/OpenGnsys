# Actualización SQL para crear el comando Eliminar Imagen Cache.
INSERT INTO ogAdmBD.comandos
	SET idcomando=11, descripcion='Eliminar Imagen Cache',
	    pagina='../comandos/EliminarImagenCache.php',
	    gestor='../comandos/gestores/gestor_Comandos.php',
	    funcion='EliminarImagenCache', aplicambito=31,
	    visuparametros='iph;tis;dcr;scp', parametros='nfn;iph;tis;dcr;scp', activo=1;

