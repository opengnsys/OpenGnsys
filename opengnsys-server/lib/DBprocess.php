<?php

#no integrado parcialmente. Pendiente de ser llamado desde el EACserver
function RegistryHost ($mac,$ip)
{
	echo $mac . $ip;
	$conexion=mysql_connect($_SERVER['SQL_HOST'], $_SERVER['SQL_USER'], $_SERVER['SQL_PASS']) or die ('no se ha podido conectar con mysql');
	mysql_select_db($_SERVER['DATABASE'], $conexion);
	$insert="insert into infonetequipos (hostname, macaddress, ipaddress, startpage, aula, vga, acpi, pci) values ('" . $_SERVER['HOSTNAME'] . "', '" . $mac . "', '" . $ip . "' ,  'default.sh' , '1', '788', '" . $_SERVER['acpi'] . "', '" . $_SERVER['pci'] . "')";
	$resultado=mysql_query($insert);
	#ethtool -s eth0 wol g autoneg off speed 100 duplex full
	#}
		mysql_close($conexion);
}






function BootServer ($boot,$ip)
{
#/**  @function BootServer: @brief Configura el fichero de arranque remoto para el equipo, y actualiza la base de datos.
#@param  $1 str_LabelBootMenu
#@param  $2 str_IPHost
#@return Mensaje informativo sobre dicho proceso
#@warning  Salidas de errores no determinada
#@attention
#@note	 Notas sin especificar
#@version 1.0       Date: 27/10/2008                 Author Antonio J. Doblas Viso. Universidad de Malaga
#*/



	#actualizamos el menu de arranque para ese equipo
	$query3="update infonetequipos set arranque='" . $boot ."'  where ipaddress='" .$ip . "'";
	$resultado = mysql_query($query3) or die (mysql_error());
############# Consultas SQL para obtener la informacion del ciente. varinfoaula, varinfoequipo, varinfoparameters
## varinfohost= toda la informacion de este equipo almacenado en el array infohost
	$peticion="select * from infonetequipos where ipaddress='".$ip . "'";
	$rsinfohost = mysql_query($peticion);
	$infohost = mysql_fetch_array($rsinfohost);
	$aula= $infohost['aula'];
	#obtenemos los campos del host y los metemos dentro de la variable varinfohost
	$varinfohost=" ";
	$lastparameters=mysql_num_fields($rsinfohost);
	#for ($i=3; $i<$lastparameters; $i++)
	for ($i=0; $i<$lastparameters; $i++)
	{
		if ( mysql_field_name($rsinfohost, $i) == 'startpage' )
		{
			$varinfohost = $varinfohost . " " .  mysql_field_name($rsinfohost, $i) . "=A_id" . $aula . "_" . $infohost[$i] ;
		}
		else
		{
			$varinfohost = $varinfohost . " " .  mysql_field_name($rsinfohost, $i) . "="  . $infohost[$i] ;
			#echo $infohost[$i] . "\n";
		}
	}
	#echo $parameters . "\n" ;
	mysql_free_result($rsinfohost);

## obtenemos el nombre del fichero pxe para este equipo
	$mac=$infohost['macaddress'];
	$macfile="01-" . str_replace(":","-",strtolower($mac));
	$nombre_archivo="/tftpboot/pxelinux.cfg/" . $macfile;


### varinfoaula= toda la informacion del aula al que pertenece el equipo infoaula.
	$peticion="select * from infonetaulas where id_aula='".$aula . "'";
	$rsinfoaula = mysql_query($peticion);
	#$infoaula = mysql_fetch_assoc($rsinfoaula);
	$infoaula = mysql_fetch_array($rsinfoaula);
	$varinfoaula=" ";
		$lastparameters=mysql_num_fields($rsinfoaula);
		#for ($i=3; $i<$lastparameters; $i++)
		for ($i=0; $i<$lastparameters; $i++)
		{
			$varinfoaula = $varinfoaula . " " .  mysql_field_name($rsinfoaula, $i) . "="  . $infoaula[$i] ;
		}
	mysql_free_result($rsinfoaula);

$gateway=$infoaula['gateway'];
	$netmask=$infoaula['netmask'];
	$repo_client=$infoaula['repo_client'];
	$menu=$infohost['arranque'];

### varinfoparameters= toda la informacion generica de los clientes.
	$peticion="select variable, value from infoparametersclients";
	$rsinfoparameters = mysql_query($peticion);
	#$infoparameters = mysql_fetch_assoc($rsinfoparameters);
#	$infoparameters = mysql_fetch_array($rsinfoparameters);
	$varinfoparameters=" ";
#		$lastparameters=mysql_num_fields($rsinfoparameters);
#		#for ($i=3; $i<$lastparameters; $i++)
#		for ($i=0; $i<$lastparameters; $i++)
#		{
#			$varinfoparameters = $varinfoparameters . " " .  mysql_field_name($rsinfoparameters, $i) . "="  . $infoparameters[$i] ;
#		}
	while ($fila = mysql_fetch_row($rsinfoparameters))
	{
#foreach ($fila as $elemento)
#		{
#			$varinfoparameters = $varinfoparameters . "" .  $elemento . "=";
#		}
		$varinfoparameters = $varinfoparameters . " " .  $fila[0] . "=" . $fila[1];
	}

	mysql_free_result($rsinfoparameters);
################################################ Fin de consustar SQL

	if (!$gestion= fopen($nombre_archivo, 'w+'))
	{
		echo "No se puede abrir el archivo ($nombre_archivo)";
		return;
	}
	# cuales son los parametros del menu
	fwrite($gestion, "DEFAULT syslinux/vesamenu.c32 \n");
	fwrite($gestion, "MENU TITLE Aplicacion GNSYS \n");
	# cuales son los elemtos del menu
	$peticion="select itemboot.label, itemboot.kernel, itemboot.append, menuboot.timeout, menuboot.prompt, menuboot.description, menuboot_itemboot.default from itemboot,menuboot_itemboot,menuboot where menuboot_itemboot.labelmenu=menuboot.label and menuboot_itemboot.labelitem=itemboot.label and menuboot.label='" . $menu   . "'";
	#	echo $peticion;
	$rsbootoption = mysql_query($peticion);
	while($row = mysql_fetch_assoc($rsbootoption))
	{
 		fwrite($gestion, " \n");
		fwrite($gestion, "LABEL " .  $row['label'] . " \n");
		fwrite($gestion, "MENU LABEL " . $row['label'] . " \n");
		if ( $row['default'] == true)
		{
			fwrite($gestion, "MENU DEFAULT \n");
		}
		fwrite($gestion, $row['kernel'] . " EACregistred=YES " . $varinfoaula  .  "\n");
		$iseac=substr_count($row['append'] , "og");
		echo $iseaci . " \n";
		if ($iseac > 0)
		{
			$append=str_replace("repo_client", $repo_client, $row['append']);
##	echo $append . "\n";
			fwrite($gestion, $append . " ip=" .  $infohost['ipaddress'] .":" . $repo_client . ":" . $gateway . ":" . $netmask . ":" . $infohost['hostname'] . ":eth0 ro " . " " . $varinfohost  . " ". $varinfoparameters . " \n");
		}
		else
		{
			fwrite($gestion, $row['append'] . " \n");
		}
			$prompt=$row['prompt'];
			$timeout=$row['timeout'];
	}

	fwrite($gestion, " \n");
	fwrite($gestion, "PROMPT " . $prompt ." \n");
	fwrite($gestion, "TIMEOUT " . $timeout . " \n");
	mysql_free_result($rsbootoption);
	fwrite($gestion, " \n");
	fclose($gestion);
	exec("chown www-data:www-data /tftpboot/pxelinux.cfg/". $macfile);
	exec("chmod 777 /tftpboot/pxelinux.cfg/". $macfile);
	#return("ok");
	# actualizando el boot del cliente
	#system(REPO . "admin/procedimientos/Log " . SQL_HOST . "  ' 00:00 '  ' " . $ip . " solicita un SetDefaultBoot ' ' " . $boot . " " . $ip . " con resultado " .  $bootstatus ."'");
}

# no integrado
function Logger ()
{
	#include("/var/EAC/admin/config/EAC.conf");
					# formato de entrada
					# 1IP ; 2tiempo proceso; 3comando; 4parametros
	#$hora=date("H:i:s");
	#$dia=date("Y-m-d");
					#formato del log
					# IPsolicitante ; dia ; hora ; tiempo proceso ; comando; parametros
					#formato del log
					#mensaje en ventana del solicitante.
					#echo($_SERVER['argv'][1]  . " ; " . $dia . " ; " . $hora . " ; "  . $_SERVER['argv'][2] . " ; "  . $_SERVER['argv'][3] . " ; "  . $_SERVER['argv'][4]);
	#$info=$_SERVER['argv'][1]  . " ; " . $dia . " ; " . $hora . " ; "  . $_SERVER['argv'][2] . " ; "  . $_SERVER['argv'][3] . " ; "  . $_SERVER['argv'][4] . "\n";
	#echo($info);
					## escritura en el ficheor /var/EAC/hosts/$IP-Log
	#$fp = fopen(REPO . "hosts/" . $_SERVER['argv'][1] ."-Log", "ab");
	#fwrite($fp, $info);
	#fclose($fp);
					## insertar la informacion en la base de datos
	#$conexion=mysql_connect(SQL_HOST, SQL_USER, SQL_PASS) or die ('no se ha podido conectar con mysql');
	#mysql_select_db(DATABASE, $conexion);
	#$insert="insert into log (ip, dia, hora, tiempo_proceso, comando, parametros) values ('".$_SERVER['argv'][1] . "','" . $dia . "','" .$hora . "','" .$_SERVER['argv'][2]."','".$_SERVER['argv'][3]."','".$_SERVER['argv'][4]."')";
					#echo $insert . "\n";
	#$resultado = mysql_query($insert) or die (mysql_error());
	#mysql_close($conexion);
}


#primera integracion
function InsertClassrom ($descripcion,$subred,$netmask,$broadcast,$gateway,$repo_image,$repo_client)
{

	$query="select * from infonetaulas where descripcion='" . $descripcion ."'";
	echo $query;
	$rs=mysql_query($query);
	$num_rows = mysql_num_rows($rs);
	$row = mysql_fetch_assoc($rs);

	if ($num_rows == 0 )
	{
		$insert="insert into infonetaulas (descripcion, subred, netmask, broadcast, gateway, repo_image, repo_client) values ('$descripcion', '$subred', '$netmask', '$broadcast', '$gateway', '$repo_image', '$repo_client')";
		#echo $row['id_aula'];
		echo $insert;
		$resultado = mysql_query($insert) or die (mysql_error());
		mysql_free_result($rs);
		$query="select * from infonetaulas where descripcion='" . $descripcion ."'";
		#echo $query;
		$rs=mysql_query($query);
		$num_rows = mysql_num_rows($rs);
		$row = mysql_fetch_assoc($rs);
		if ($num_rows > 0 )
		{
			echo "creando directorios";
			$dir = REPOSTATIC . "client/etc/startpage/models";
			$dh  = opendir($dir);
			while (false !== ($nombre_archivo = readdir($dh)))
			{
				$archivos[] = $nombre_archivo;
			}
			foreach ($archivos as $directorio)
			{
				if ((strlen($directorio) >= 3) and  ($directorio <> '.svn'))
				{
				#echo "creando " . $directorio;
				exec("touch /opt/opengnsys/client/etc/startpage/A_id". $row['id_aula'] ."_". $directorio);
				}
			}

		}



	}
	#mysql_close($conexion);
}
# no integrado
function InsertDefaultClassrom ($descripcion,$subred,$netmask,$broadcast,$gateway,$repo_image,$repo_client)
{
	#include ("/var/EAC/admin/config/EAC.conf");
	$conexion=mysql_connect(SQL_HOST_LOCAL, SQL_USER, SQL_PASS) or die ('no se ha podido conectar con mysql');
	mysql_select_db(DATABASE, $conexion);
	$query="select * from aulas";
	$rs=mysql_query($query);
	$num_rows = mysql_num_rows($rs);
	echo $num_rows;
	if ($num_rows == 0 )
	{
		$insert="insert into aulas (descripcion, subred, netmask, broadcast, gateway, repo_image, repo_client) values ('$descripcion', '$subred', '$netmask', '$broadcast', '$gateway', '$repo_image', '$repo_client')";
	}
	else
	{
		$insert="update aulas SET descripcion='" . $descripcion . "', subred='". $subred . "', netmask='" . $netmask ."', broadcast='". $broadcast ."', gateway='" . $gateway . "', repo_image='" . $repo_image ."', repo_client='" . $repo_client ."' WHERE id_aula=1";
	}
	echo $insert;
	$resultado = mysql_query($insert) or die (mysql_error());
	mysql_free_result($rs);
	$query="select * from aulas where descripcion='" . $descripcion ."'";
	echo $query;
	$rs=mysql_query($query);
		$num_rows = mysql_num_rows($rs);
	$row = mysql_fetch_assoc($rs);

	if ($num_rows > 0 )
	{
		echo $row['id_aula'];
		echo "creando directorios";
		$dir = REPO . "admin/startpage/models";
		$dh  = opendir($dir);
		while (false !== ($nombre_archivo = readdir($dh)))
		{
			$archivos[] = $nombre_archivo;
		}

		foreach ($archivos as $directorio)
		{
			if ((strlen($directorio) >= 3) and  ($directorio <> '.svn'))
			{
				#echo "creando " . $directorio;
				exec("touch /var/EAC/admin/startpage/A_id". $row['id_aula'] ."_". $directorio);
			}
		}

	}
	mysql_free_result($rs);

	mysql_close($conexion);
}


# no integrado
function InsertItemtoMenu ($menu,$item)
{
	#include ("/var/EAC/admin/config/EAC.conf");
	echo $menu . $item . "\n";
	$conexion=mysql_connect(SQL_HOST_LOCAL, SQL_USER, SQL_PASS) or die ('no se ha podido conectar con mysql');
	mysql_select_db(DATABASE, $conexion);
	$query="select * from menuboot_itemboot where labelmenu='$menu' and labelitem='$item'";
	echo " " . $query . " \n";
	$rs=mysql_query($query);
	$num_rows = mysql_num_rows($rs);
	echo $num_rows;
	if ($num_rows < 1 )
	{
		$insert="insert into menuboot_itemboot  values ('$menu', '$item', 0)";
		echo " ".$insert . " \n";
		$resultado = mysql_query($insert) or die (mysql_error());
		mysql_free_result($rs);
		mysql_close($conexion);
	}

}

  ?>
