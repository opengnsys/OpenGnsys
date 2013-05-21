<?php
include_once("../idiomas/php/".$idioma."/pintaParticiones_".$idioma.".php");

/*________________________________________________________________________________________________________
	Crea la tabla de configuraciones y perfiles a crear
________________________________________________________________________________________________________*/
function tablaConfiguracionesIniciarSesion($cmd,$idordenador){
	global $TbMsg;
	global $idcentro;
	$tablaHtml="";
	$cmd->texto="SELECT	ordenadores_particiones.numdisk,ordenadores_particiones.numpar,
				ordenadores_particiones.tamano,
				ordenadores_particiones.idnombreso, nombresos.nombreso,
				tipospar.tipopar, imagenes.descripcion AS imagen,
				perfilessoft.descripcion AS perfilsoft,
				sistemasficheros.descripcion AS sistemafichero
			FROM ordenadores
			INNER JOIN ordenadores_particiones ON ordenadores_particiones.idordenador=ordenadores.idordenador
			LEFT OUTER JOIN nombresos ON nombresos.idnombreso=ordenadores_particiones.idnombreso
			INNER JOIN tipospar ON tipospar.codpar=ordenadores_particiones.codpar
			LEFT OUTER JOIN imagenes ON imagenes.idimagen=ordenadores_particiones.idimagen
			LEFT OUTER JOIN perfilessoft ON perfilessoft.idperfilsoft=ordenadores_particiones.idperfilsoft
			LEFT OUTER JOIN sistemasficheros ON sistemasficheros.idsistemafichero=ordenadores_particiones.idsistemafichero
			WHERE ordenadores.idordenador=".$idordenador."
			  AND tipospar.clonable=1
			  AND nombresos.nombreso!='DATA'
			ORDER BY ordenadores_particiones.numdisk,ordenadores_particiones.numpar";
				
	$rs->Comando=&$cmd; 
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) 
		return($tablaHtml); // Error al abrir recordset
	$rs->Primero();
	$actualDisk = 0;
	$columns = 3;
	while (!$rs->EOF){
		if($actualDisk != $rs->campos["numdisk"]){
			$actualDisk = $rs->campos["numdisk"];
			$tablaHtml.='<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;<strong>'.$TbMsg["DISK"].'&nbsp;'.$actualDisk.'</strong></td>'.chr(13);
		}
		if(!empty($rs->campos["idnombreso"])){
			$tablaHtml.='<TR>'.chr(13);
			$tablaHtml.='<TD ><input type="radio" name="particion"  value='.$rs->campos["numdisk"].";".$rs->campos["numpar"].'></TD>'.chr(13);
			$tablaHtml.='<TD align=center>&nbsp;'.$rs->campos["numpar"].'&nbsp;</TD>'.chr(13);
			$tablaHtml.='<TD>&nbsp;'.$rs->campos["nombreso"].'&nbsp;</TD>'.chr(13);
			$tablaHtml.='</TR>'.chr(13);
		}
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($tablaHtml);
}


/*________________________________________________________________________________________________________
	Crea la tabla de configuraciones y perfiles a crear
________________________________________________________________________________________________________*/
function tablaConfiguracionesCrearImagen($cmd,$idordenador,$idrepositorio)
{
	global $idcentro;
	global $TbMsg;
	$tablaHtml="";
	$rs=new Recordset; 
	$cmd->texto="SELECT ordenadores.ip AS masterip,ordenadores_particiones.numdisk, ordenadores_particiones.numpar,ordenadores_particiones.codpar,ordenadores_particiones.tamano,
				ordenadores_particiones.idnombreso,nombresos.nombreso,tipospar.tipopar,tipospar.clonable,
				imagenes.nombreca,imagenes.descripcion as imagen,perfilessoft.idperfilsoft,
				perfilessoft.descripcion as perfilsoft,sistemasficheros.descripcion as sistemafichero
				FROM ordenadores
				INNER JOIN ordenadores_particiones ON ordenadores_particiones.idordenador=ordenadores.idordenador
				LEFT OUTER JOIN nombresos ON nombresos.idnombreso=ordenadores_particiones.idnombreso
				INNER JOIN tipospar ON tipospar.codpar=ordenadores_particiones.codpar
				LEFT OUTER JOIN imagenes ON imagenes.idimagen=ordenadores_particiones.idimagen
				LEFT OUTER JOIN perfilessoft ON perfilessoft.idperfilsoft=ordenadores_particiones.idperfilsoft
				LEFT OUTER JOIN sistemasficheros ON sistemasficheros.idsistemafichero=ordenadores_particiones.idsistemafichero
				WHERE ordenadores.idordenador=".$idordenador." ORDER BY ordenadores_particiones.numdisk,ordenadores_particiones.numpar";
	//echo 	$cmd->texto;
	$rs->Comando=&$cmd; 
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) 
		return($tablaHtml."</table>"); // Error al abrir recordset
	$rs->Primero();
	$actualDisk = 0;
	$columns = 6;
	while (!$rs->EOF){
		
		if($actualDisk != $rs->campos["numdisk"]){
			$actualDisk = $rs->campos["numdisk"];
			$tablaHtml.='<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;<strong>'.$TbMsg["DISK"].'&nbsp;'.$actualDisk.'</strong></td>'.chr(13);
		}
		
		$swcc=$rs->campos["clonable"] && !empty($rs->campos["idnombreso"]);
		$swc=$rs->campos["idperfilsoft"]>0; // Una partición es clonable si posee un identificador de perfil software		
		$swccc=$swcc && $swcc;
		$tablaHtml.='<TR>'.chr(13);
		if($swccc){
			$tablaHtml.='<TD><input type=radio name="particion" value="'.$rs->campos["numdisk"]."_".$rs->campos["numpar"]."_".$rs->campos["codpar"].'"></TD>'.chr(13);
			$tablaHtml.='<TD align=center>&nbsp;'.$rs->campos["numpar"].'&nbsp;</TD>'.chr(13);
			$tablaHtml.='<TD align=center>&nbsp;'.$rs->campos["tipopar"].'&nbsp;</TD>'.chr(13);
			if(empty($rs->campos["nombreso"]) && !empty($rs->campos["idnombreso"])) // Si el identificador del S.O. no es nulo pero no hay descripción
				$tablaHtml.='<TD align=center>&nbsp;'.'<span style="FONT-SIZE:10px;	COLOR: red;" >'.$TbMsg[12].'</span></TD>'.chr(13);
			else
				$tablaHtml.='<TD>&nbsp;'.$rs->campos["nombreso"].'&nbsp;</TD>'.chr(13);
			$tablaHtml.='<TD>'.HTMLSELECT_imagenes($cmd,$idrepositorio,$rs->campos["idperfilsoft"],$rs->campos["numdisk"],$rs->campos["numpar"],$rs->campos["masterip"]).'</TD>';
			$tablaHtml.='<TD>'.HTMLSELECT_repositorios($cmd,$idcentro,$idrepositorio,$rs->campos["numdisk"],$rs->campos["numpar"],$rs->campos["masterip"]).'</TD>';
			//$tablaHtml.='<TD>&nbsp;</TD>';
		}
		$tablaHtml.='</TR>'.chr(13);	
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$tablaHtml.="</table>";
	return($tablaHtml);
}

/*----------------------------------------------------------------------------------------------
	Dibuja una tabla con los datos de particiones y parametros a elegir
	
		Parametros: 
		- idordenador: El identificador del ordenador
----------------------------------------------------------------------------------------------*/
function tablaConfiguracionesSincronizacion1($idordenador)
{
	global $idcentro;
	global $TbMsg;	
	global $cmd;
	
	$tablaHtml="";
	
	$cmd->texto="SELECT DISTINCT ordenadores_particiones.numdisk,ordenadores_particiones.numpar, ordenadores_particiones.idnombreso, nombresos.nombreso,
					ordenadores_particiones.idimagen, ordenadores_particiones.codpar,
					tipospar.clonable, perfilessoft.idperfilsoft,
					nombresos.idnombreso, nombresos.nombreso
					FROM ordenadores_particiones 
					INNER JOIN tipospar ON tipospar.codpar=ordenadores_particiones.codpar		
					LEFT OUTER JOIN nombresos ON nombresos.idnombreso=ordenadores_particiones.idnombreso
					LEFT OUTER JOIN perfilessoft ON perfilessoft.idperfilsoft=ordenadores_particiones.idperfilsoft										
					WHERE ordenadores_particiones.idordenador=".$idordenador."
					ORDER BY ordenadores_particiones.numdisk, ordenadores_particiones.numpar";
	//echo 	$cmd->texto;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) 
		return($tablaHtml); // Error al abrir recordset
	$rs->Primero(); 
	$tbPAR="";
	$actualDisk = 0;
	$columns = 7;
	while (!$rs->EOF){
		if($actualDisk != $rs->campos["numdisk"]){
			$actualDisk = $rs->campos["numdisk"];
			$tablaHtml.='<td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;<strong>'.$TbMsg["DISK"].'&nbsp;'.$actualDisk.'</strong></td>'.chr(13);
		}
		//$swcc=$rs->campos["clonable"] && !empty($rs->campos["idnombreso"]) && !empty($rs->campos["idperfilsoft"]); 
		$sw=$rs->campos["clonable"] && !empty($rs->campos["idnombreso"]); 
		if($sw){// Una partici�n es clonable si es cierta esta variable	
			$tbPAR.=$rs->campos["numpar"].";"; // Cadena con las particiones a procesar	
			$tablaHtml.='<tr id="trPar-'.$rs->campos["numpar"].'">';
			$tablaHtml.='<td align=center><input type=radio name="particion" value="'.$rs->campos["codpar"].'"></td>';
			$tablaHtml.='<td align="center">&nbsp;'.$rs->campos["numpar"].'&nbsp;</td>'; // N�mero de partici�n
			$tablaHtml.='<td align=center>&nbsp;'.$rs->campos["nombreso"].'&nbsp;</td>'; // Nombre sistema operativo
			$tablaHtml.='<td align=center>'.HTMLSELECT_imagenes($rs->campos["idimagen"]).'</td>';	
			$tablaHtml.='<td align=center><input type=checkbox name="whole" id="whl-'.$rs->campos["numpar"].'"></td>';	
			$tablaHtml.='<td align=center><input type=checkbox name="paramb" checked id="eli-'.$rs->campos["numpar"].'"></td>';	
			$tablaHtml.='<td align=center><input type=checkbox name="compres" id="cmp-'.$rs->campos["numpar"].'"></td>';	
			$tablaHtml.='</tr>';			
		}
		$rs->Siguiente();
	}
	$rs->Cerrar();
	echo $tablaHtml;
	return($tbPAR);
}

/**
 * La funcion tablaConfiguracionesSincronizacion1 sustituye a las funciones tablaConfiguracionesCrearImagenBasica y 
 * tablaConfiguracionesCrearSoftIncremental que eran llamadas desde comandos/CrearImagenBasica.php y comandos/CrearSoftIncremental.php
 * Ahora en ambos ficheros se llama a la misma función ya que pintaban lo mismo en pantalla
 *

/*----------------------------------------------------------------------------------------------
	Dibuja una tabla con los datos de particiones y parametros a elegir
	
		Parametros: 
		- idordenador: El identificador del ordenador
----------------------------------------------------------------------------------------------*
function tablaConfiguracionesCrearImagenBasica($idordenador)
{
	global $idcentro;
	global $TbMsg;	
	global $cmd;
	
	$tablaHtml="";
	$cmd->texto="SELECT DISTINCT	ordenadores_particiones.numpar, ordenadores_particiones.idnombreso, nombresos.nombreso,
					ordenadores_particiones.idimagen, ordenadores_particiones.codpar,
					tipospar.clonable, perfilessoft.idperfilsoft,
					nombresos.idnombreso, nombresos.nombreso
					FROM ordenadores_particiones 
					INNER JOIN tipospar ON tipospar.codpar=ordenadores_particiones.codpar		
					LEFT OUTER JOIN nombresos ON nombresos.idnombreso=ordenadores_particiones.idnombreso
					LEFT OUTER JOIN perfilessoft ON perfilessoft.idperfilsoft=ordenadores_particiones.idperfilsoft										
					WHERE ordenadores_particiones.idordenador=$idordenador 
					ORDER BY ordenadores_particiones.numpar";
	//echo 	$cmd->texto;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
	$rs->Primero(); 
	$tbPAR="";
	while (!$rs->EOF){
		//$swcc=$rs->campos["clonable"] && !empty($rs->campos["idnombreso"]) && !empty($rs->campos["idperfilsoft"]); 
		$sw=$rs->campos["clonable"] && !empty($rs->campos["idnombreso"]); 
		if($sw){// Una partici�n es clonable si es cierta esta variable	
			$tbPAR.=$rs->campos["numpar"].";"; // Cadena con las particiones a procesar	
			$tablaHtml.='<tr id="trPar-'.$rs->campos["numpar"].'">';
			$tablaHtml.='<td align=center><input type=radio name="particion" value="'.$rs->campos["codpar"].'"></td>';
			$tablaHtml.='<td align="center">&nbsp;'.$rs->campos["numpar"].'&nbsp;</td>'; // N�mero de partici�n
			$tablaHtml.='<td align=center>&nbsp;'.$rs->campos["nombreso"].'&nbsp;</td>'; // Nombre sistema operativo
			$tablaHtml.='<td align=center>'.HTMLSELECT_imagenes($rs->campos["idimagen"]).'</td>';	
			$tablaHtml.='<td align=center><input type=checkbox name="whole" id="whl-'.$rs->campos["numpar"].'"></td>';	
			$tablaHtml.='<td align=center><input type=checkbox name="paramb" checked id="eli-'.$rs->campos["numpar"].'"></td>';	
			$tablaHtml.='<td align=center><input type=checkbox name="compres" id="cmp-'.$rs->campos["numpar"].'"></td>';	
			$tablaHtml.='</tr>';			
		}
		$rs->Siguiente();
	}
	$rs->Cerrar();
	echo $tablaHtml;
	return($tbPAR);
}


/*----------------------------------------------------------------------------------------------
	Dibuja una tabla con los datos de particiones y parametros a elegir
	
		Parametros: 
		- idordenador: El identificador del ordenador
----------------------------------------------------------------------------------------------*
function tablaConfiguracionesCrearSoftIncremental($idordenador)
{
	global $idcentro;
	global $TbMsg;	
	global $cmd;
	
	$tablaHtml="";
	
	$cmd->texto="SELECT DISTINCT ordenadores_particiones.numpar, ordenadores_particiones.idnombreso,
					nombresos.nombreso, ordenadores_particiones.idimagen,
					tipospar.clonable, perfilessoft.idperfilsoft,
					nombresos.idnombreso, nombresos.nombreso
				FROM ordenadores_particiones 
				INNER JOIN tipospar ON tipospar.codpar=ordenadores_particiones.codpar		
				LEFT OUTER JOIN nombresos ON nombresos.idnombreso=ordenadores_particiones.idnombreso
				LEFT OUTER JOIN perfilessoft ON perfilessoft.idperfilsoft=ordenadores_particiones.idperfilsoft																						
				WHERE ordenadores_particiones.idordenador=$idordenador 
				ORDER BY ordenadores_particiones.numpar";
	//echo 	$cmd->texto;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
	$rs->Primero(); 
	$tbPAR="";
	while (!$rs->EOF){
		//$swcc=$rs->campos["clonable"] && !empty($rs->campos["idnombreso"]) && !empty($rs->campos["idperfilsoft"]); 
		$sw=$rs->campos["clonable"] && !empty($rs->campos["idnombreso"]); 
		if($sw){// Una partici�n es clonable si es cierta esta variable	
			$tbPAR.=$rs->campos["numpar"].";"; // Cadena con las particiones a procesar	
			$tablaHtml.='<TR id="trPar-'.$rs->campos["numpar"].'">';
			$tablaHtml.='<td align=center ><input type=radio name="particion" value="'.$rs->campos["numpar"].'"></td>';
			$tablaHtml.='<td align="center">&nbsp;'.$rs->campos["numpar"].'&nbsp;</td>'; // N�mero de partici�n
			$tablaHtml.='<td align=center>&nbsp;'.$rs->campos["nombreso"].'&nbsp;</td>'; // Nombre sistema operativo
			$tablaHtml.='<td align=center>'.HTMLSELECT_imagenes($rs->campos["idimagen"]).'</td>';	
			$tablaHtml.='<td align=center><input type=checkbox name="whole" id="whl-'.$rs->campos["numpar"].'"></td>';	
			$tablaHtml.='<td align=center><input type=checkbox name="paramb" checked id="eli-'.$rs->campos["numpar"].'"></td>';	
			$tablaHtml.='<td align=center><input type=checkbox name="compres" id="cmp-'.$rs->campos["numpar"].'"></td>';				
			$tablaHtml.='</TR>';
		}		
		$rs->Siguiente();
	}
	$rs->Cerrar();
	echo $tablaHtml;
	return($tbPAR);
}
/**/