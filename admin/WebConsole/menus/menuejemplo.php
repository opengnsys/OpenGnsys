<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es" xml:lang="es">

<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<title>Men&uacute; de inicio de los equipos OpenGnsys</title>

	<style type="text/css">	
	body { background: #fff; font-size: 0.7em; }
	h1, h2 { font-size: 1.5em; }
	br {font-size: 0.2em; }
	a:link, a:visited { text-decoration: none; color:#900; font-weight: bold; }
	a:hover, a:active { color:#d90; }

	h1 {
		font-size: 1.5em;
		width: 100%;
		vertical-align: bottom;	
		color: #555;
		background: transparent url('images/opengnsys.png')  no-repeat top left;
		padding: 2em 0 1.5em 12em;
		margin-bottom: 1em;
	}

	dl {
		background: transparent url('images/xp_peque.png') no-repeat top left;
		padding: 0 0 1em 5em;
		margin: 2em 10em;
	}

	dl.windows {
		background-image: url('images/xp_peque.png');
	}

	dl.linux {
		background-image: url('images/linux_peque.png');
	}

	dl.apagar {
		background-image: url('images/poweroff.png');
	}

	dt { float: left;}
	dd { margin: 1em 10em 1em 20em; }

	div.admin {
		margin: 1em;
		float; right;
	}
	</style>

</head>

   <body>

	<h1>Men&uacute; de opciones</h1>

	<dl class="windows">
		<dt><a href="command:bootOs 1 1" title="Iniciar sesi&oacute;n de Windows, accesskey: 1" accesskey="1">[1] Arrancar Windows.</a></dt>
			<dd>Arranque normal de Windows sin modificaciones.</dd>
		<dt><a href="commandwithconfirmation:restoreImage REPO windows 1 1" title="Formatear el disco e instalar el sistema operativo Windows, accesskey: 2" accesskey="2">[2] Instalar Windows. </a></dt>
			<dd>El proceso de instalaci&oacute;n tardar&aacute; unos minutos.</dd>
	</dl>

 	<!-- dl class="windows"> 
		<dt><a href="commandwithconfirmation:/opt/opengnsys/interfaceAdm/RestaurarImagenBasica 1 1 WINXPRD2 10.1.15.3 0000 0" title="Sincronizar imagen de Windows, accesskey: 2" accesskey="2">Sincronizar Imagen Window.</a></dt> 
			<dd>Restaurar Imagen Windows con sincronizaci&oacute;n.</dd> 
	</dl --> 

	<dl class="linux">
		<dt><a href="command:bootOs 1 2" title="Iniciar sesi&oacute;n de Ubuntu 12, accesskey: 3" accesskey="3">[3] Arrancar GNU/Linux. </a></dt>
			<dd>Arranque normal de <acronym title="GNU's not Unix">GNU</acronym>/Linux sin modificaciones.</dd>
			
		<dt><a href="commandwithconfirmation:restoreImage REPO linux 1 2" title="Formatear el disco e instalar el sistema operativo GNU/Linux, accesskey: 4" accesskey="4">[4] Instalar GNU/Linux. </a></dt>
			<dd>El proceso de instalaci&oacute;n tardar&aacute; unos minutos.</dd>
	</dl>
	
	<dl class="apagar">
		<dt><a href="command:poweroff" title="Apagar la m&aacute;quina, accesskey: 0" accesskey="0">[0] Apagar. </a></dt>
			<dd>Apagar el ordenador.</dd>

		<dt><a href="command:reboot" title="Reiniciar la m&aacute;quina, accesskey: 6" accesskey="6">[6] Reiniciar. </a></dt>
			<dd>Reiniciar el ordenador.</dd>
	</dl>

<?php	// Acceso a menú privado.
if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}
?>
	<div class="admin"><a href="../varios/acceso_operador.php?iph=<?php echo $ip ?>">Administraci&oacute;n</a></div>

   </body>
</html>

