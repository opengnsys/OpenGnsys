<?php

include_once("../idiomas/php/".$idioma."/pintaParticiones_".$idioma.".php");

/**
 * Separa las distintas configuraciones de una cadena por disco.
 * Ej. 1;0;1@1;1;7@1;2;131@2;0;1@2;1;7
 * Serian dos configuraciones, para el disco 1 -> 1;0;1@1;1;7@1;2;131 y
 * para el disco 2 -> 2;0;1@2;1;7
 */
function splitConfigurationsByDisk($configuraciones){
	// Recorremos las configuraciones para separalas segun el disco al que pertenezcan
	$diskConfigs = array();
	$configs = explode("@",$configuraciones);
	foreach($configs as $config){
		$parts = explode(";",$config);
		if(!isset($diskConfigs[$parts[0]])){
			$diskConfigs[$parts[0]] = "@";
		}
		else if($diskConfigs[$parts[0]] != ""){
			$diskConfigs[$parts[0]] .= "@";
		}
		
		// Concatenamos la configuracion en el disco que corresponda
		$diskConfigs[$parts[0]] .= $config;
	}
	return $diskConfigs;
}



// *************************************************************************************************************************************************
//	UHU - 2013/15/14 - Se pintan los discos ademas de las particiones
//	Descripción:
//		Crea una taba html con las especificaciones de particiones de un ambito ya sea ordenador,
//		grupo de ordenadores o aula
//	Parametros:
//		$configuraciones: Cadena con las configuraciones de particioners del ámbito. El formato 
//		sería una secuencia de cadenas del tipo "clave de configuración" separados por "@" 
//			Ejemplo:1;7;30000000;3;3;0;@2;130;20000000;5;4;0;@3;131;1000000;0;0;0;0
//________________________________________________________________________________________________________
function pintaParticiones($cmd,$configuraciones,$idordenadores,$cc)
{
	global $tbKeys; // Tabla contenedora de claves de configuración
	global $conKeys; // Contador de claves de configuración
	global $TbMsg;
	$disktable = array();

	// Separamos las configuraciones segun el disco al que pertenezcan
	$diskConfigs = splitConfigurationsByDisk($configuraciones);
	
	$columns=9;
	echo '<tr height="16">';
	echo '<th align="center">&nbsp;'.$TbMsg["DISK"].'&nbsp;</th>'; // Número de  disco
	echo '<th align="center">&nbsp;'.$TbMsg["PARTITION"].'&nbsp;</th>'; // Número de partición
	echo '<th align="center">&nbsp;'.$TbMsg["PARTITION_TYPE"].'&nbsp;</th>'; // Tipo de partición
	echo '<th align="center">&nbsp;'.$TbMsg["FILESYSTEM_SHORT"].'&nbsp;</th>'; // Sistema de ficheros
	echo '<th align="center">&nbsp;'.$TbMsg["INST_SO"].'&nbsp;</th>'; // Sistema Operativo Instalado
	echo '<th align="center">&nbsp;'.$TbMsg["SIZE_KB"].'&nbsp;</th>'; // Tamaño
	echo '<th align="center">&nbsp;'.$TbMsg["IMAGE"].'&nbsp;</th>'; // Imagen instalada
	echo '<th align="center">&nbsp;'.$TbMsg["SOFT_PROFILE"].'&nbsp;</th>'; // Perfil software 
	echo '<th align="center">&nbsp;'.$TbMsg["CACHE_CONTENT"].'&nbsp;</th>';
	echo '</tr>';

	// Recorremos todas las configuraciones encontradas para cada disco
	$aviso="";
	foreach($diskConfigs as $disk => $diskConfig){
		$disk = (int)$disk;
		echo'<tr height="16">'.chr(13);
	        echo '<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;'.$TbMsg["DISK"].'&nbsp;'.$disk.'</td>'.chr(13);


		
		$auxCfg=explode("@",$diskConfig); // Crea lista de particiones
		for($i=0;$i<sizeof($auxCfg);$i++){
			$auxKey=explode(";",$auxCfg[$i]); // Toma clave de configuracion
			for($k=0;$k<$conKeys;$k++){ // Busca los literales para las claves de esa partición
				if($tbKeys[$k]["cfg"]==$auxCfg[$i]){ // Claves encontradas
					if ($tbKeys[$k]["numpar"] == 0) { // Info del disco (umpart=0)
						$disksize[$tbKeys[$k]["numdisk"]] = tomaTamano($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]);
						if (empty ($disksize)) {
							$disksize = '<em>'.$TbMsg["VARIABLE"].'</em>';
						}
						switch ($tbKeys[$k]["codpar"]) {
							case 1:  $disktable[$tbKeys[$k]["numdisk"]] = "MSDOS";
								 break;
							case 2:  $disktable[$tbKeys[$k]["numdisk"]] = "GPT";
								 break;
							case 3:  $disktable[$tbKeys[$k]["numdisk"]] = "LVM";
								 break;
							case 4:  $disktable[$tbKeys[$k]["numdisk"]] = "ZPOOL";
								 break;
							default: $disktable[$tbKeys[$k]["numdisk"]] = "";
						}
					}
					else {  // Información de partición (numpart>0)
						echo'<tr height="16">'.chr(13);
                                	        echo'<td align="center">&nbsp;</td>'.chr(13);
						echo'<td align="center">'.$tbKeys[$k]["numpar"].'</td>'.chr(13);
						if ($disktable[$tbKeys[$k]["numdisk"]] == "LVM" or $disktable[$tbKeys[$k]["numdisk"]] == "ZPOOL") {
							echo '<td></td>'.chr(13);
						}
						else {
							if (is_numeric ($tbKeys[$k]["tipopar"])) {
								echo '<td align="center"><em>'.sprintf("%02X",$tbKeys[$k]["tipopar"]).'</em></td>'.chr(13);
							}
							else {
								echo '<td align="center">'.$tbKeys[$k]["tipopar"].'</td>'.chr(13);
							}
						}
						$filesys=tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores,false,$tbKeys[$k]["numdisk"]);
						echo'<td align="center">&nbsp;'.$filesys.'&nbsp;</td>'.chr(13);
	
						echo '<td align="center">&nbsp;'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</td>'.chr(13);
						// Mostrar uso solo en clientes individuales.
						$uso=tomaUso($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]);
						if ($uso > 0 and strpos($idordenadores, ',') === false) {
							echo '<td style="text-align:right; background-image:url(../images/flotantes/lsu.gif); background-size:'.$uso.'% 100%; background-repeat:no-repeat"><a title="'.$TbMsg["USAGE"].': '.$uso.'%">&nbsp;'.tomaTamano($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</a></td>'.chr(13);
						} else {
							echo '<td style="text-align:right">&nbsp;'.tomaTamano($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</td>'.chr(13);
						}

						// Si es CACHE incluyo campo oculto con el tamaño
						if ($tbKeys[$k]["tipopar"]== "CACHE"){
							echo "<input type='hidden' name='cachesize' value='".tomaTamano($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"])."'/>".chr(13);
						}

						echo'<td align="center">&nbsp;'.tomaImagenes($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</td>'.chr(13);

						echo'<td align="center">&nbsp;'.tomaPerfiles($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</td>'.chr(13);

						if ($filesys == "CACHE") {
							echo '<td align="leght">&nbsp;';
							$campocache = preg_replace("/[\n|\r|\n\r]/i", '', tomaCache($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]));
							$ima=explode(",",$campocache);
							$numero=1;
							for ($x=0;$x<count($ima); $x++) {
								if(substr($ima[$x],-3)==".MB") {
									if ( $ima[$x] == "0.MB" ){
										echo '<font color=red><strong>'.$TbMsg["CACHE_COMPLETE"].': '.$ima[$x].'</strong></font>';
									}else{
										echo '<strong>'.$TbMsg["CACHE_FREESPACE"].':  '.$ima[$x].'</strong>';
									}
								}elseif (! empty($ima[1])){
									// $dir=is_dir('$ima');echo $dir;
									// if ($ima == "directorio"){$dir="si";}
									// Esto para la informacion de la imagen
									if (substr($ima[$x],-5)==".diff"){$info="F";}elseif(substr($ima[$x],-4)==".img"){$info="F";}else{$info="D";}
									// Esto para numerarla
									if(substr($ima[$x],-4)==".img" || substr($ima[$x],-5)==".diff" || substr($ima[$x],-4)=="") {
										echo '<br />('.$info.') &nbsp;'.$numero++.'.-'.$ima[$x];
									} elseif(preg_match("/.sum/",$ima[$x]) or preg_match("/.torrent/",$ima[$x]) or preg_match("/.full.sum/",$ima[$x])) {
										echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ima[$x];
										}else{
											echo '<br /><font color=blue>('.$info.') </font>'.$numero++.'.-<font color=blue>'.$ima[$x]."</font>";
											}
								}
							}
							echo '&nbsp;</td>'.chr(13);

						} else {
							if ($tbKeys[$k]["difimagen"] > 0 ) {
								echo'<td align="center">&nbsp;'.$tbKeys[$k]["fechadespliegue"].' (* '.$tbKeys[$k]["difimagen"].')&nbsp;</td>'.chr(13);
								$aviso=$TbMsg["WARN_DIFFIMAGE"];
							} else {
								echo'<td align="center">&nbsp;'.$tbKeys[$k]["fechadespliegue"].'&nbsp;</td>'.chr(13);
							}
						}
					
						echo'</tr>'.chr(13);
					}
					break;
				}
			}
		}	
		// Mostrar información del disco, si se ha obtenido.
		if (!empty ($disksize)) {
			echo'<tr height="16">'.chr(13);
			echo'<td align="center">&nbsp;</td>'.chr(13);
			echo'<td align="center">&nbsp;'.$disktable[$disk].'&nbsp;</td>'.chr(13);
			echo'<td></td>'.chr(13);
			echo'<td></td>'.chr(13);
			echo'<td></td>'.chr(13);
			echo'<td align="right">&nbsp;<strong>'.(isset($disksize[$disk])?$disksize[$disk]:('<em>'.$TbMsg["VARIABLE"].'</em>')).'</span></strong>&nbsp;</td>'.chr(13);
			// Creamos un campo oculto para guardar información sobre el disco y su tamaño separados por ;
			echo "<input type='hidden' name='disksize_".$disk."' value='".$disksize[$disk]."'/>\n";
			echo'<td></td>'.chr(13);
			echo'<td></td>'.chr(13);
			echo'<td></td>'.chr(13);
                        echo'</tr>'.chr(13);
		}
	}
	if (!empty($aviso)) {
		echo '<tr><th colspan="'.$columns.'">&nbsp;* '.$aviso.'&nbsp;</th></tr>'."\n";
	}
	echo '<tr height="5"><td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</td></tr>';
}


//________________________________________________________________________________________________________
//
//	Descripción:
//		(Esta función es llamada por pintaConfiguraciones que está incluida en ConfiguracionesParticiones.php)
//		Crea una taba html con las especificaciones de particiones de un ambito ya sea ordenador,
//		grupo de ordenadores o aula
//	Parametros:
//		$configuraciones: Cadena con las configuraciones de particioners del ámbito. El formato 
//		sería una secuencia de cadenas del tipo "clave de configuración" separados por "@" 
//			Ejemplo:1;7;30000000;3;3;0;@2;130;20000000;5;4;0;@3;131;1000000;0;0;0;0
//	Devuelve:
//		El código html de la tabla
// version 1.1: cliente con varios repositorios -  HTMLSELECT_imagenes: cambia parametros idordenadores por idambito
// autor: Irina Gomez, Universidad de Sevilla
// fecha 2015-06-17
//________________________________________________________________________________________________________
function pintaParticionesRestaurarImagen($cmd,$configuraciones,$idordenadores,$cc,$ambito,$idambito)
{
	global $tbKeys; // Tabla contenedora de claves de configuración
	global $conKeys; // Contador de claves de configuración
	global $TbMsg;
	global $_SESSION;
	
	// Separamos las configuraciones segun el disco al que pertenezcan
	$diskConfigs = splitConfigurationsByDisk($configuraciones);
	
	$columns=10;
	echo '<TR>';
	echo '<TH align=center>&nbsp;&nbsp;</TH>';
	echo '<th align="center">&nbsp;'.$TbMsg["DISK"].'&nbsp;</th>'; // Número de  disco
	echo '<TH align=center>&nbsp;'.$TbMsg["PARTITION"].'&nbsp;</TH>';
	echo '<th align="center">&nbsp;'.$TbMsg["PARTITION_TYPE"].'&nbsp;</th>'; // Tipo de partición
	echo '<th align="center">&nbsp;'.$TbMsg["INST_SO"].'&nbsp;</th>'; // Sistema Operativo Instalado
	echo '<th align="center">&nbsp;'.$TbMsg["FILESYSTEM_SHORT"].'&nbsp;</th>'; // Sistema de ficheros
	echo '<th align="center">&nbsp;'.$TbMsg["SIZE_KB"].'&nbsp;</th>'; // Tamaño
	echo '<TH align=center>&nbsp;'.$TbMsg["SAMESYSTEM_IMAGE"].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg["DIFFERENTSYSTEM_IMAGE"].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg["RESTORE_METHOD"].'&nbsp;</TH>';
	echo '</TR>';
	
	
	// Recorremos todas las configuraciones encontradas para cada disco
	
	foreach($diskConfigs as $disk => $diskConfig){
		$disk = (int)$disk;
		echo'<tr height="16">'.chr(13);
		echo '<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;'.$TbMsg["DISK"].'&nbsp;'.$disk.'</td>'.chr(13);
	         
		$auxCfg=explode("@",$diskConfig); // Crea lista de particiones
		for($i=0;$i<sizeof($auxCfg);$i++){
			$auxKey=explode(";",$auxCfg[$i]); // Toma clave de configuracion
			for($k=0;$k<$conKeys;$k++){ // Busca los literales para las claves de esa partición
				if($tbKeys[$k]["cfg"]==$auxCfg[$i]){ // Claves encontradas
				    if($tbKeys[$k]["numpar"]!=0){    // No es info. del disco (part. 0)
					$swcc=$tbKeys[$k]["clonable"];
					if($swcc){
						echo '<TR>'.chr(13);
						echo '<TD align=center>&nbsp;&nbsp;</TD>';
						$icp=$cc."_".$tbKeys[$k]["numdisk"]."_".$tbKeys[$k]["numpar"]; // Identificador de la configuración-partición
						echo '<TD ><input type=radio idcfg="'.$cc.'" id="'.$icp.'" name="particion" value='.$tbKeys[$k]["numdisk"].";".$tbKeys[$k]["numpar"].'></TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.$tbKeys[$k]["numpar"].'&nbsp;</TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.$tbKeys[$k]["tipopar"].'&nbsp;</TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</TD>'.chr(13);	
						echo'<TD align=center>&nbsp;'.tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores,false,$tbKeys[$k]["numdisk"]).'&nbsp;</TD>'.chr(13);
						echo'<TD align=center>&nbsp;'.tomaTamano($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</TD>'.chr(13);	
						echo '<TD>'.HTMLSELECT_imagenes($cmd,$tbKeys[$k]["idimagen"],$tbKeys[$k]["numpar"],$tbKeys[$k]["codpar"],$icp,true,$idambito,$ambito).'</TD>';
						echo '<TD>'.HTMLSELECT_imagenes($cmd,$tbKeys[$k]["idimagen"],$tbKeys[$k]["numpar"],$tbKeys[$k]["codpar"],$icp,false,$idambito,$ambito).'</TD>';
	
						//Clonación
						$metodos="UNICAST=UNICAST-CACHE".chr(13);
						$metodos.="UNICAST-DIRECT=UNICAST-DIRECT".chr(13);
						$metodos.="MULTICAST " . mcast_syntax($cmd,$ambito,$idambito) . "=MULTICAST-CACHE".chr(13);
						$metodos.="MULTICAST-DIRECT " . mcast_syntax($cmd,$ambito,$idambito) . "=MULTICAST-DIRECT".chr(13);
						$metodos.="TORRENT " . torrent_syntax($cmd,$ambito,$idambito) . "=TORRENT-CACHE";
	
						$TBmetodos["UNICAST-CACHE"]=1;
						$TBmetodos["UNICAST-DIRECT"]=2;
						$TBmetodos["MULTICAST-CACHE"]=3;
						$TBmetodos["MULTICAST-DIRECT"]=4;
						$TBmetodos["TORRENT-CACHE"]=5;
						$idxc=$_SESSION["protclonacion"];
						if ($idxc == "UNICAST") {
							$idxc = "UNICAST-DIRECT";
						}
						echo '<TD>'.HTMLCTESELECT($metodos,"protoclonacion_".$icp,"estilodesple","",$TBmetodos[$idxc],100).'</TD>';
						echo '</TR>'.chr(13);
					}
				    }
				}
			}
		}
	}
	echo '<TR height=5><TD colspan='.$columns.' style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</TD></TR>';
}

/*________________________________________________________________________________________________________

	Descripción:
		(Esta función es llamada por pintaConfiguraciones que está incluida en ConfiguracionesParticiones.php)
		Crea una taba html con las especificaciones de particiones de un ambito ya sea ordenador,
		grupo de ordenadores o aula
	Parametros:
		$configuraciones: Cadena con las configuraciones de particioners del ámbito. El formato 
						sería una secuencia de cadenas del tipo "clave de configuración" separados por "@" 
						Ejemplo:1;7;30000000;3;3;0;@2;130;20000000;5;4;0;@3;131;1000000;0;0;0;0
		$idordenadores: cadena con los identificadores de los ordenadores que forman parte del bloque 
		$cc: Identificador de la configuración
	Devuelve:
		El código html de la tabla
________________________________________________________________________________________________________*/
function pintaParticionesConfigurar($cmd,$configuraciones,$idordenadores,$cc)
{

	global $tbKeys; // Tabla contenedora de claves de configuración
	global $conKeys; // Contador de claves de configuración
	global $TbMsg;

	$colums=7;
	echo '<TR id="TR_'.$cc.'">';
	echo '<TH align=center>&nbsp;'.$TbMsg['REMOVE'].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg['PARTITION'].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg['PARTITION_TYPE'].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg['FILESYSTEM'].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg['SIZE_KB'].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg['INSTALLED_OS'].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg['REFORMAT'].'&nbsp;</TH>';	
	echo '</TR>';


	$aviso=false;
	$auxCfg=explode("@",$configuraciones); // Crea lista de particiones
	for($i=0;$i<sizeof($auxCfg);$i++){
		$auxKey=explode(";",$auxCfg[$i]); // Toma clave de configuracion
		for($k=1;$k<$conKeys;$k++){ // Busca los literales para las claves de esa partición
			if($tbKeys[$k]["cfg"]==$auxCfg[$i]){ // Claves encontradas
				if($tbKeys[$k]["numdisk"]==1){ // Solo tratar disco 1
					if($tbKeys[$k]["numpar"]>0){ // Solo particiones (número>0)
						$icp=$cc."_".$k; // Identificador de la configuración-partición
						echo '<tr id="TR_'.$icp.'" align="center">';
						echo '<td><input type="checkbox" onclick="eliminaParticion(this,\''.$icp.'\')"></td>';
						echo '<td>'.HTMLSELECT_particiones($tbKeys[$k]["numpar"]).'</td>';
						echo '<td>'.HTMLSELECT_tipospar($cmd,$tbKeys[$k]["tipopar"]).'</td>';
						$sf=tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores,true);
						echo '<td>'.HTMLSELECT_sistemasficheros($cmd,$sf).'</td>';
						$tm=tomaTamano($tbKeys[$k]["numpar"],$idordenadores);
						echo '<td><input type="text" style="width:100" value="'.$tm.'"></td>';		
						echo '<td>'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores).'</td>';					
						echo '<td>'.opeFormatear().'</td>';
						echo '</tr>';
					} else {
						if ($tbKeys[$k]["codpar"]!=1) { // Aviso tabla no MSDOS.
							$aviso=true;
						}
					}
				} else {			// Aviso: más de un disco.
					$aviso=true;
				}
			}
		}
	}
	// Marcar fin de zona de datos de la tabla.
	// Datos del disco
	$tm=tomaTamano(0,$idordenadores);
	echo '<tr id="TRIMG_'.$cc.'" align="center">'.
	     "\n<td></td>\n<td></td>\n<td".' style="font-size: 1em; padding: 1px 0px;  "'.">".$TbMsg["DISK"]."</td>".
     "\n<td></td>\n<td".' style="font-size: 1em; padding: 1px 0px; "> '.(isset($tm)?$tm:("<em>".$TbMsg["VARIABLE"]."</em>"))." <input type='hidden' id='hdsize$cc' name='hdsize$cc' style='width:100' value='".$tm."'></td>".
	     "\n<td></td>\n<td></td>\n</tr>";
	echo '<tr><th colspan="'.$colums.'">&nbsp;'.$TbMsg["WARN_DISKSIZE"].'</th></tr>';
	// Mostrar aviso: solo disco 1 con tabla MSDOS.
	if ($aviso) {
		echo '<tr><th colspan="'.$colums.'">'.$TbMsg["CONFIG_NODISK1MSDOS"].'</th></tr>';
	}
	// Botones de añadir y confirmar.
	if (isset($tm)) {
		echo '<TR height=30><TD style="BACKGROUND-COLOR: #FFFFFF;" colspan='.$colums.' align=center>';
		echo '	<A href="#add" style="text-decoration:none">
						<IMG id="IMG_'.$icp.'" border=0 src="../images/boton_insertar.gif" 
						value="'.$k.'" onclick="addParticion(this,'.$cc.')"></A>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<A href="#add" style="text-decoration:none">
						<IMG border=0 src="../images/boton_aceptar.gif" onclick="Confirmar('.$cc.')"></A></TD>
					</TR>';
	} else {
		echo '<tr><th colspan="'.$colums.'">'.$TbMsg["WARN_DIFFDISKSIZE"].'</th></tr>'."\n";
	}
}

/*
//
//	Descripcion:
//		(Esta funci�n es llamada por pintaConfiguraciones que est� incluida en ConfiguracionesParticiones.php)
//		Crea una taba html con las especificaciones de particiones de un ambito ya sea ordenador,
//		grupo de ordenadores o aula
//	Parametros:
//		$configuraciones: Cadena con las configuraciones de particioners del �mbito. El formato 
//		ser�a una secuencia de cadenas del tipo "clave de configuraci�n" separados por "@" 
//			Ejemplo:1;7;30000000;3;3;0;@2;130;20000000;5;4;0;@3;131;1000000;0;0;0;0
//	Devuelve:
//		El c�digo html de la tabla
//________________________________________________________________________________________________________
//
//
*/
function pintaParticionesRestaurarImagenSincronizacion1($cmd,$configuraciones,$idordenadores,$cc,$ambito,$idambito)
{
	global $tbKeys; // Tabla contenedora de claves de configuraci�n
	global $conKeys; // Contador de claves de configuraci�n
	global $TbMsg;
	global $_SESSION;
	
	// Separamos las configuraciones segun el disco al que pertenezcan
	$diskConfigs = splitConfigurationsByDisk($configuraciones);
	
	$columns=14;
	echo '<TR>';
	echo '<TH align=center>&nbsp;&nbsp;</TH>';
	echo '<th align="center">&nbsp;'.$TbMsg["DISK"].'&nbsp;</th>'; // Número de  disco
	echo '<TH align=center>&nbsp;'.$TbMsg["PARTITION"].'&nbsp;</TH>';
	echo '<th align="center">&nbsp;'.$TbMsg["PARTITION_TYPE"].'&nbsp;</th>'; // Tipo de partición
	echo '<th align="center">&nbsp;'.$TbMsg["INST_SO"].'&nbsp;</th>'; // Sistema Operativo Instalado
	echo '<th align="center">&nbsp;'.$TbMsg["FILESYSTEM_SHORT"].'&nbsp;</th>'; // Sistema de ficheros
	echo '<th align="center">&nbsp;'.$TbMsg["SIZE_KB"].'&nbsp;</th>'; // Tamaño
	echo '<TH align=center>&nbsp;'.$TbMsg[10].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[16].'&nbsp;</TH>';	
	echo '<TH align=center>&nbsp;'.$TbMsg["SYNC_METHOD"].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg["SEND"].'&nbsp;</TH>';
	echo '  <TH align=center>&nbsp;<dfn  title="'.$TbMsg["TITLE_W"].'">W</dfn> &nbsp;</TH>';
	echo '  <TH align=center>&nbsp;<dfn  title="'.$TbMsg["TITLE_E"].'">E</dfn> &nbsp;</TH>';
	echo '  <TH align=center>&nbsp;<dfn  title="'.$TbMsg["TITLE_C"].'">C</dfn> &nbsp;</TH>';
	echo '</TR>';

	
	// Recorremos todas las configuraciones encontradas para cada disco
	
	foreach($diskConfigs as $disk => $diskConfig){
		$disk = (int)$disk;
		echo'<tr height="16">'.chr(13);
		echo '<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;'.$TbMsg["DISK"].'&nbsp;'.$disk.'</td>'.chr(13);
	     
		$auxCfg=explode("@",$diskConfig); // Crea lista de particiones
		for($i=0;$i<sizeof($auxCfg);$i++){
			$auxKey=explode(";",$auxCfg[$i]); // Toma clave de configuracion
			for($k=0;$k<$conKeys;$k++){ // Busca los literales para las claves de esa partici�n
				if($tbKeys[$k]["cfg"]==$auxCfg[$i]){ // Claves encontradas
					$swcc=$tbKeys[$k]["clonable"];
					echo '<TR>'.chr(13);
					echo '<TD align=center>&nbsp;&nbsp;</TD>';
					if($swcc){
						$icp=$cc."_".$tbKeys[$k]["numdisk"]."_".$tbKeys[$k]["numpar"]; // Identificador de la configuraci�n-partici�n
						echo '<TD align=center><input type=radio idcfg="'.$cc.'" id="'.$icp.'" name="particion" value='.$tbKeys[$k]["numdisk"].";".$tbKeys[$k]["numpar"].'></TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.$tbKeys[$k]["numpar"].'&nbsp;</TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.$tbKeys[$k]["tipopar"].'&nbsp;</TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</TD>'.chr(13);	
						echo'<TD align=center>&nbsp;'.tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores,false,$tbKeys[$k]["numdisk"]).'&nbsp;</TD>'.chr(13);
						echo'<TD align=center>&nbsp;'.tomaTamano($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</TD>'.chr(13);	
						echo '<TD align=center>'.HTMLSELECT_imagenes($cmd,$tbKeys[$k]["idimagen"],$tbKeys[$k]["numpar"],$tbKeys[$k]["codpar"],$icp,true,$idordenadores,$ambito).'</TD>';
						$metodos="CACHE=".$TbMsg[13].chr(13);
						$metodos.="REPO=".$TbMsg[9];		
						echo '<TD align=center>'.HTMLCTESELECT($metodos,"desplemet_".$icp,"estilodesple","",1,100).'</TD>';
						
						$metodos="SYNC0="."  ".chr(13);
						$metodos.="SYNC1=".$TbMsg["SYNC1_DIR"].chr(13);						
						$metodos.="SYNC2=".$TbMsg["SYNC2_FILE"];		
						echo '<TD align=center>'.HTMLCTESELECT($metodos,"desplesync_".$icp,"estilodesple","",1,100).'</TD>';								
							
						$metodos="UNICAST="."Unicast".chr(13);						
						$metodos.="MULTICAST_". mcast_syntax($cmd,$ambito,$idambito) ."="."Multicast".chr(13);		
						$metodos.="TORRENT_". torrent_syntax($cmd,$ambito,$idambito) ."="."Torrent".chr(13);
						$metodos.="RSYNC=Rsync";
						echo '<TD align=center>'.HTMLCTESELECT($metodos,"despletpt_".$icp,"estilodesple","",1,100).'</TD>';								
						
						echo '<td align=center><input type=checkbox name="whole" id="whl-'.$icp.'"></td>';	
						echo '<td align=center><input type=checkbox name="paramb" checked id="eli-'.$icp.'"></td>';	
						echo '<td align=center><input type=checkbox name="compres" id="cmp-'.$icp.'"></td>';	
										
					}
					echo '</TR>'.chr(13);
				}
			}
		}
	}	

	echo '<TR height=5><TD colspan='.$columns.' style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</TD></TR>';
	echo '<tr><th colspan="14">'.$TbMsg["WARN_PROTOCOL"].'</th></tr>';
}
/**
 * Las funcion pintaParticionesRestaurarImagenSincronizacion1 sustituye a las funciones 
 * pintaParticionesRestaurarSoftIncremental y pintaParticionesRestaurarImagenBasica
 * para volver a usarlas tan sólo hay que ir al fichero comandos/RestaurarImagenBasica o comandos/RestaurarSoftIncremental y cambiar la
 * llamada a la función que queramos en el parametro de pintaConfiguraciones.
 * Actualmente en ambos ficheros llaman a la función pintaParticionesRestaurarImagenSincronizacion1 ya que pintan
 * exactamente lo mismo.
 *

//*********************************************************************************************
//	FUNCIONES
//*********************************************************************************************
//
//	Descripci�n:
//		(Esta funci�n es llamada por pintaConfiguraciones que est� incluida en ConfiguracionesParticiones.php)
//		Crea una taba html con las especificaciones de particiones de un ambito ya sea ordenador,
//		grupo de ordenadores o aula
//	Parametros:
//		$configuraciones: Cadena con las configuraciones de particioners del �mbito. El formato 
//		ser�a una secuencia de cadenas del tipo "clave de configuraci�n" separados por "@" 
//			Ejemplo:1;7;30000000;3;3;0;@2;130;20000000;5;4;0;@3;131;1000000;0;0;0;0
//	Devuelve:
//		El c�digo html de la tabla
//________________________________________________________________________________________________________
//
//
function pintaParticionesRestaurarSoftIncremental($cmd,$configuraciones,$idordenadores,$cc,$ambito,$idambito)
{
	global $tbKeys; // Tabla contenedora de claves de configuraci�n
	global $conKeys; // Contador de claves de configuraci�n
	global $TbMsg;
	global $_SESSION;
	
	// Separamos las configuraciones segun el disco al que pertenezcan
	$diskConfigs = splitConfigurationsByDisk($configuraciones);
	
	$columns=9;
	echo '<TR>';
	echo '<TH align=center>&nbsp;&nbsp;</TH>';
	echo '<th align="center">&nbsp;'.$TbMsg["DISK"].'&nbsp;</th>'; // Número de  disco
	echo '<TH align=center>&nbsp;'.$TbMsg["PARTITION"].'&nbsp;</TH>';
	echo '<th align="center">&nbsp;'.$TbMsg["PARTITION_TYPE"].'&nbsp;</th>'; // Tipo de partición
	echo '<th align="center">&nbsp;'.$TbMsg["INST_SO"].'&nbsp;</th>'; // Sistema Operativo Instalado
	echo '<th align="center">&nbsp;'.$TbMsg["FILESYSTEM_SHORT"].'&nbsp;</th>'; // Sistema de ficheros
	echo '<th align="center">&nbsp;'.$TbMsg["SIZE_KB"].'&nbsp;</th>'; // Tamaño
	echo '<TH align=center>&nbsp;'.$TbMsg[10].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[16].'&nbsp;</TH>';	
	echo '</TR>';

	
	// Recorremos todas las configuraciones encontradas para cada disco
	
	foreach($diskConfigs as $disk => $diskConfig){
		$disk = (int)$disk;
		echo'<tr height="16">'.chr(13);
		echo '<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;'.$TbMsg["DISK"].'&nbsp;'.$disk.'</td>'.chr(13);
	     
		$auxCfg=explode("@",$diskConfig); // Crea lista de particiones
		for($i=0;$i<sizeof($auxCfg);$i++){
			$auxKey=explode(";",$auxCfg[$i]); // Toma clave de configuracion
			for($k=0;$k<$conKeys;$k++){ // Busca los literales para las claves de esa partici�n
				if($tbKeys[$k]["cfg"]==$auxCfg[$i]){ // Claves encontradas
					$swcc=$tbKeys[$k]["clonable"];
					echo '<TR>'.chr(13);
					echo '<TD align=center>&nbsp;&nbsp;</TD>';
					if($swcc){
						$icp=$cc."_".$tbKeys[$k]["numpar"]; // Identificador de la configuraci�n-partici�n
						echo '<TD align=center><input type=radio idcfg="'.$cc.'" id="'.$icp.'" name="particion" value='.$tbKeys[$k]["numpar"].'></TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.$tbKeys[$k]["numpar"].'&nbsp;</TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.$tbKeys[$k]["tipopar"].'&nbsp;</TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);	
						echo'<TD align=center>&nbsp;'.tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);
						echo'<TD align=center>&nbsp;'.tomaTamano($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);	
						echo '<TD align=center>'.HTMLSELECT_imagenes($cmd,$tbKeys[$k]["idimagen"],$tbKeys[$k]["numpar"],$tbKeys[$k]["codpar"],$icp,true,$idordenadores,$ambito).'</TD>';
						$metodos="CACHE=".$TbMsg[13].chr(13);
						$metodos.="REPO=".$TbMsg[9];		
						echo '<TD align=center>'.HTMLCTESELECT($metodos,"desplemet_".$icp,"estilodesple","",1,100).'</TD>';
							
					}
					echo '</TR>'.chr(13);
				}
			}
		}
	}	
	echo '<TR height=5><TD colspan='.$columns.' style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</TD></TR>';
}

//*********************************************************************************************
//	FUNCIONES
//*********************************************************************************************
//
//	Descripci�n:
//		(Esta funci�n es llamada por pintaConfiguraciones que est� incluida en ConfiguracionesParticiones.php)
//		Crea una taba html con las especificaciones de particiones de un ambito ya sea ordenador,
//		grupo de ordenadores o aula
//	Parametros:
//		$configuraciones: Cadena con las configuraciones de particioners del �mbito. El formato 
//		ser�a una secuencia de cadenas del tipo "clave de configuraci�n" separados por "@" 
//			Ejemplo:1;7;30000000;3;3;0;@2;130;20000000;5;4;0;@3;131;1000000;0;0;0;0
//	Devuelve:
//		El c�digo html de la tabla
//________________________________________________________________________________________________________
//
//
function pintaParticionesRestaurarImagenBasica($cmd,$configuraciones,$idordenadores,$cc,$ambito,$idambito)
{
	global $tbKeys; // Tabla contenedora de claves de configuraci�n
	global $conKeys; // Contador de claves de configuraci�n
	global $TbMsg;
	global $_SESSION;
	
	// Separamos las configuraciones segun el disco al que pertenezcan
	$diskConfigs = splitConfigurationsByDisk($configuraciones);
	
	$columns=9;
	echo '<TR>';
	echo '<TH align=center>&nbsp;&nbsp;</TH>';
	echo '<th align="center">&nbsp;'.$TbMsg["DISK"].'&nbsp;</th>'; // Número de  disco
	echo '<TH align=center>&nbsp;'.$TbMsg["PARTITION"].'&nbsp;</TH>';
	echo '<th align="center">&nbsp;'.$TbMsg["PARTITION_TYPE"].'&nbsp;</th>'; // Tipo de partición
	echo '<th align="center">&nbsp;'.$TbMsg["INST_SO"].'&nbsp;</th>'; // Sistema Operativo Instalado
	echo '<th align="center">&nbsp;'.$TbMsg["FILESYSTEM_SHORT"].'&nbsp;</th>'; // Sistema de ficheros
	echo '<th align="center">&nbsp;'.$TbMsg["SIZE_KB"].'&nbsp;</th>'; // Tamaño
	echo '<TH align=center>&nbsp;'.$TbMsg[10].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[16].'&nbsp;</TH>';
	echo '</TR>';

	// Recorremos todas las configuraciones encontradas para cada disco
	
	foreach($diskConfigs as $disk => $diskConfig){
		$disk = (int)$disk;
		echo'<tr height="16">'.chr(13);
		echo '<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;'.$TbMsg["DISK"].'&nbsp;'.$disk.'</td>'.chr(13);
	     
		$auxCfg=explode("@",$diskConfig); // Crea lista de particiones
		for($i=0;$i<sizeof($auxCfg);$i++){
			$auxKey=explode(";",$auxCfg[$i]); // Toma clave de configuracion
			for($k=0;$k<$conKeys;$k++){ // Busca los literales para las claves de esa partici�n
				if($tbKeys[$k]["cfg"]==$auxCfg[$i]){ // Claves encontradas
					$swcc=$tbKeys[$k]["clonable"];
					if($swcc){
						echo '<TR>'.chr(13);
						echo '<TD align=center>&nbsp;&nbsp;</TD>';
						$icp=$cc."_".$tbKeys[$k]["numpar"]; // Identificador de la configuraci�n-partici�n
						echo '<TD align=center><input type=radio idcfg="'.$cc.'" id="'.$icp.'" name="particion" value='.$tbKeys[$k]["numpar"].'></TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.$tbKeys[$k]["numpar"].'&nbsp;</TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.$tbKeys[$k]["tipopar"].'&nbsp;</TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);	
						echo'<TD align=center>&nbsp;'.tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);
						echo'<TD align=center>&nbsp;'.tomaTamano($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);	
						echo '<TD align=center>'.HTMLSELECT_imagenes($cmd,$tbKeys[$k]["idimagen"],$tbKeys[$k]["numpar"],$tbKeys[$k]["codpar"],$icp,true,$idordenadores,$ambito).'</TD>';
						$metodos="CACHE=".$TbMsg[13].chr(13);
						$metodos.="REPO=".$TbMsg[9];		
						echo '<TD align=center>'.HTMLCTESELECT($metodos,"desplemet_".$icp,"estilodesple","",1,100).'</TD>';
					}
				}
			}
		}
	}	
	echo '<TR height=5><TD colspan='.$columns.' style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</TD></TR>';
}
**/

