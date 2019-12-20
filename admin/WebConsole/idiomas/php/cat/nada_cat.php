<?php
//________________________________________________________________________________________________________
//
//	Fichero de idiomas php: nada_esp.php 
//	Idioma: Catalán
//________________________________________________________________________________________________________
	$TbMsg=array();
	$TbMsg[0]="DISPOSITIU UTILITZAT PER ACCEDIR A OPENGNSYS";
	$TbMsg[1]="IP - Dispositiu";
	$TbMsg[2]="Tipus - Dispositiu";
	$TbMsg[3]="Sistema Operatiu";
	$TbMsg[4]="Versió Sistema";
	$TbMsg[5]="Navegador";
	$TbMsg[6]="Versió Navegador";
	$TbMsg["TIP"]="Consejo del día";

	// Los mensajes pueden tener imágenes asociadas llamadas images/tipOfDay_N.png
	$TipOfDay=Array();
	$TipOfDay[0]="El cliente de OpenGnsys puede restaurar y crear imágenes en todos los repositorios definidos en la unidad organizativa.";
	$TipOfDay[1]="OpenGnsys permite gestionar equipos UEFI desde la versión 1.1.1 (Espeto).";
	$TipOfDay[2]="<a href='https://opengnsys.es' class='help_menu' target='blank'>Nueva web de OpenGnsys</a> dirigida a los usuarios, donde encontrarán fácilmente:   \n".
		     "<ul>\n".
		     "	<li>Descarga de la última versión de Opengnsys</li>\n".
                     "  <li>Manual de usuario</li>\n".
                     "  <li>Documentación de la instalación</li>\n".
                     "  <li>Casos de éxito</li>\n".
                     "</ul>\n<br>\n";
	$TipOfDay[3]="OpenGnsys permite instalar varios ogLive, pudiendo seleccionar en cada equipo el que mejor reconozca su hardware.";
	$TipOfDay[4]="OpenGnsys permite independizar el alojamiento de las imágenes de distintas unidades organizativas dentro de un mismo repositorio.";
	$TipOfDay[5]="Para facilitar la migración de un servidor existen scripts para exportar e importar los datos de OpenGnsys.";
	$TipOfDay[6]="RemotePC conjuga OpenGnsys con UDS para ofrecer acceso remoto a los equipos de las aulas fuera del horario de docencia.";
	$TipOfDay[7]="<b>Curso Online</b><p>Todos los miembros de organizaciones que estén federadas en el Servicio de Identidad de RedIRIS pueden acceder al curso 'Curso Básico de OpenGnsys 1.1.0' en la <a href='https://docencia-net.cv.uma.es' class='help_menu' target='blank'>Plataforma de Formación del Grupo Docencia-Net.</p>";
	$TipOfDay[8]="El nuevo agente de OpenGnsys para el sistema operativo permite mandar mensajes a los usuarios y ejecutar comandos sobre el equipo.";
	$TipOfDay[9]="En la web de OpenGnsys puedes encontrar <a href='https://opengnsys.es/trac/wiki/EjemploPracticos' class='help_menu' target='blank'>ejemplos prácticos y recetas</a>, como por ejemplo la postconfiguración necesario para la activación de Windows con KMS.";
	$TipOfDay[10]="OpenGnsys soporta discos Nvme, utilizando el ogLive bionic 5.0.";
	$TipOfDay[11]="Para evitar conflictos en la transferencia multicast conviene configurar en cada aula un puerto distinto.";
	$TipOfDay[12]="Puede definir la prioridad de ejecución de torrent en <br>/etc/default/opengnsys. Los valores recomendados son: \n".
		"<ul>\n".
		"  <li> 8 para el Servidor de Administración o un Repositorio sin torrent.</li>\n".
		"  <li> 0 para el Servidor de Administración junto al Repositorio con torrent.</li>\n".
		"  <li>-8 para el repositorio con torrent.</li>\n".
		"</ul>\n<br>\n";
