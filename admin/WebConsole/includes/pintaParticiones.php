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
	$configs = split("@",$configuraciones);
	foreach($configs as $config){
		$parts = split(";",$config);
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
	echo '</TR>';

	echo '</tr>';

	// Recorremos todas las configuraciones encontradas para cada disco
	
	foreach($diskConfigs as $disk => $diskConfig){
		 echo'<tr height="16">'.chr(13);
	         echo '<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;'.$TbMsg["DISK"].'&nbsp;'.$disk.'</td>'.chr(13);


		
		$auxCfg=split("@",$diskConfig); // Crea lista de particiones
		for($i=0;$i<sizeof($auxCfg);$i++){
			$auxKey=split(";",$auxCfg[$i]); // Toma clave de configuracion
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
							default: $disktable[$tbKeys[$k]["numdisk"]] = "";
						}
					}
					else {  // Información de partición (numpart>0)
						echo'<tr height="16">'.chr(13);
                                	        echo'<td align="center">&nbsp;</td>'.chr(13);
						echo'<td align="center">'.$tbKeys[$k]["numpar"].'</td>'.chr(13);
						if (is_numeric ($tbKeys[$k]["tipopar"])) {
							echo '<td align="center"><em>'.sprintf("%02X",$tbKeys[$k]["tipopar"]).'</em></td>'.chr(13);
						}
						else {
							echo '<td align="center">'.$tbKeys[$k]["tipopar"].'</td>'.chr(13);
						}
						echo'<td align="center">&nbsp;'.tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores,false,$tbKeys[$k]["numdisk"]).'&nbsp;</td>'.chr(13);
	
						echo '<td align="center">&nbsp;'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</td>'.chr(13);					
	
						echo'<td align="right">&nbsp;'.tomaTamano($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</td>'.chr(13);
	
						echo'<td align="center">&nbsp;'.tomaImagenes($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</td>'.chr(13);
						
						echo'<td align="center">&nbsp;'.tomaPerfiles($tbKeys[$k]["numpar"],$idordenadores,$tbKeys[$k]["numdisk"]).'&nbsp;</td>'.chr(13);
	
						if ($tbKeys[$k]["numpar"] == "4") {
							$rs=new Recordset; 
							$cmd->texto="SELECT * FROM  ordenadores_particiones WHERE idordenador='".$idordenadores."' AND numpar=4 AND numdisk = ".$tbKeys[$k]["numdisk"];
							$rs->Comando=&$cmd; 
							if (!$rs->Abrir()) return(false); // Error al abrir recordset
							$rs->Primero(); 
							if (!$rs->EOF){
								$campocache=$rs->campos["cache"];
							}
							$rs->Cerrar();
							echo '<td align="leght">&nbsp;';
							$ima=split(",",$campocache);
							$numero=1;
							for ($x=0;$x<count($ima); $x++) {
								if(substr($ima[$x],-3)==".MB") {
									echo '<strong>'.$TbMsg["CACHE_FREESPACE"].':  '.$ima[$x].'</strong>';
								} else {
									if(substr($ima[$x],-4)==".img") {
										echo '<br />'.$numero++.'.-'.$ima[$x];
									} else {
										echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;'.$ima[$x];
									}
								}
							}
							echo '&nbsp;</td>'.chr(13);
						} else {
							echo'<td align="center">&nbsp;&nbsp;</td>'.chr(13);
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
                        echo'<td align="right">&nbsp;<strong>'.$disksize[$disk].'</strong>&nbsp;</td>'.chr(13);
                        echo'<td></td>'.chr(13);
	                echo'<td></td>'.chr(13);
			echo'<td></td>'.chr(13);
                        echo'</tr>'.chr(13);

			/*
			foreach($disksize as $disk=>$size){
				echo'<tr height="16">'.chr(13);
				echo'<td align="center">&nbsp;'.$TbMsg[35].'&nbsp;'.$disk.'</td>'.chr(13);
				echo'<td align="center">&nbsp;'.$disktable[$disk].'&nbsp;</td>'.chr(13);
				echo'<td></td>'.chr(13);
				echo'<td></td>'.chr(13);
				echo'<td align="right">&nbsp;'.$size.'&nbsp;</td>'.chr(13);
				echo'<td></td>'.chr(13);
				echo'<td></td>'.chr(13);
				echo'<td></td>'.chr(13);
				echo'<td></td>'.chr(13);
				echo'</tr>'.chr(13);
			}
			*/
		}
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
		 echo'<tr height="16">'.chr(13);
	     echo '<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;'.$TbMsg["DISK"].'&nbsp;'.$disk.'</td>'.chr(13);
	         
		$auxCfg=split("@",$diskConfig); // Crea lista de particiones
		for($i=0;$i<sizeof($auxCfg);$i++){
			$auxKey=split(";",$auxCfg[$i]); // Toma clave de configuracion
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
						echo '<TD>'.HTMLSELECT_imagenes($cmd,$tbKeys[$k]["idimagen"],$tbKeys[$k]["numpar"],$tbKeys[$k]["codpar"],$icp,true,$idordenadores,$ambito).'</TD>';
						echo '<TD>'.HTMLSELECT_imagenes($cmd,$tbKeys[$k]["idimagen"],$tbKeys[$k]["numpar"],$tbKeys[$k]["codpar"],$icp,false,$idordenadores,$ambito).'</TD>';
	
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
	echo '<TH align=center><IMG src="../images/iconos/eliminar.gif"></TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[8].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[24].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[27].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[22].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[21].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[14].'&nbsp;</TH>';	
	echo '</TR>';

	$auxCfg=split("@",$configuraciones); // Crea lista de particiones
	for($i=0;$i<sizeof($auxCfg);$i++){
		$auxKey=split(";",$auxCfg[$i]); // Toma clave de configuracion
		for($k=1;$k<$conKeys;$k++){ // Busca los literales para las claves de esa partición
			if($tbKeys[$k]["cfg"]==$auxCfg[$i]){ // Claves encontradas
				$icp=$cc."_".$k; // Identificador de la configuración-partición
				echo '<TR id="TR_'.$icp.'">';
				echo '<TD align=center><input type=checkbox onclick="eliminaParticion(this,\''.$icp.'\')"></TD>';
			
				echo '<TD align=center>'.HTMLSELECT_particiones($tbKeys[$k]["numpar"]).'</TD>';
				echo '<TD align=center>'.HTMLSELECT_tipospar($cmd,$tbKeys[$k]["tipopar"]).'</TD>';
				
				$sf=tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores,true);	
				echo'<TD align=center>'.HTMLSELECT_sistemasficheros($cmd,$sf).'</TD>';

				$tm=tomaTamano($tbKeys[$k]["numpar"],$idordenadores);
				echo'<TD align=center><INPUT type="text" style="width:100" value="'.$tm.'"></TD>';		
					
				echo '<TD align=center>'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores).'</TD>';					
			
				echo '<TD align=center>'.opeFormatear().'</TD>';
				echo '</TR>';
			}
		}
	}
	/* Botones de añadir y confirmar */
	echo '<TR id="TRIMG_'.$cc.'" height=5><TD colspan='.$colums.' style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</TD></TR>';
	echo '<TR height=30><TD style="BACKGROUND-COLOR: #FFFFFF;" colspan='.$colums.' align=center>';
	echo '	<A href="#add" style="text-decoration:none">
						<IMG id="IMG_'.$icp.'" border=0 src="../images/boton_insertar.gif" 
						value="'.$k.'" onclick="addParticion(this,'.$cc.')"></A>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<A href="#add" style="text-decoration:none">
						<IMG border=0 src="../images/boton_aceptar.gif" onclick="Confirmar('.$cc.')"></A></TD>
					</TR>';
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
	
	$columns=13;
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
	echo '<TH align=center>&nbsp;'.$TbMsg[39].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;W&nbsp;</TH>';
	echo '<TH align=center>&nbsp;E&nbsp;</TH>';
	echo '<TH align=center>&nbsp;C&nbsp;</TH>';

	echo '</TR>';

	
	// Recorremos todas las configuraciones encontradas para cada disco
	
	foreach($diskConfigs as $disk => $diskConfig){
		 echo'<tr height="16">'.chr(13);
	     echo '<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;'.$TbMsg["DISK"].'&nbsp;'.$disk.'</td>'.chr(13);
	     
		$auxCfg=split("@",$diskConfig); // Crea lista de particiones
		for($i=0;$i<sizeof($auxCfg);$i++){
			$auxKey=split(";",$auxCfg[$i]); // Toma clave de configuracion
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
						$tipotran="0=".$TbMsg[40].chr(13);
						$tipotran.="1=".$TbMsg[41];	
						echo '<TD align=center>'.HTMLCTESELECT($tipotran,"despletipotran_".$icp,"estilodesple","",1,100).'</TD>';
						echo'<td align=center><input type=checkbox name="whole" id="whl-'.$tbKeys[$k]["numpar"].'"></td>';	
						echo '<td align=center><input type=checkbox name="paramb" checked id="eli-'.$tbKeys[$k]["numpar"].'"></td>';	
						echo '<td align=center><input type=checkbox name="compres" id="cmp-'.$tbKeys[$k]["numpar"].'"></td>';								
					}
					echo '</TR>'.chr(13);
				}
			}
		}
	}	
	echo '<TR height=5><TD colspan='.$columns.' style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</TD></TR>';
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
		 echo'<tr height="16">'.chr(13);
	     echo '<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;'.$TbMsg["DISK"].'&nbsp;'.$disk.'</td>'.chr(13);
	     
		$auxCfg=split("@",$diskConfig); // Crea lista de particiones
		for($i=0;$i<sizeof($auxCfg);$i++){
			$auxKey=split(";",$auxCfg[$i]); // Toma clave de configuracion
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
		 echo'<tr height="16">'.chr(13);
	     echo '<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;'.$TbMsg["DISK"].'&nbsp;'.$disk.'</td>'.chr(13);
	     
		$auxCfg=split("@",$diskConfig); // Crea lista de particiones
		for($i=0;$i<sizeof($auxCfg);$i++){
			$auxKey=split(";",$auxCfg[$i]); // Toma clave de configuracion
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
/**/
