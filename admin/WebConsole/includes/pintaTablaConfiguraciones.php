<?php
include_once("../idiomas/php/".$idioma."/pintaParticiones_".$idioma.".php");

/*________________________________________________________________________________________________________
	Crea la tabla de configuraciones y perfiles a crear
// Version 0.1 - En ambito distinto a ordenador muestra los equipos agrupados en configuraciones iguales.
// Fecha: 2014-10-23
// Autora: Irina Gomez, ETSII Universidad de Sevilla

________________________________________________________________________________________________________*/
function tablaConfiguracionesIniciarSesion($cmd,$idambito,$ambito){
        // TODO despues de las pruebas: idnombreso <> 5
        global $TbMsg;
        global $idcentro;

        global $AMBITO_AULAS;
        global $AMBITO_GRUPOSORDENADORES;
        global $AMBITO_ORDENADORES;
        global $msk_nombreSO;
        // array: identificadores y nombres sistemas operativos en BD;
        $sistOperativo= SistemaOperativoBD($cmd);
	// Identificador del "sistema operativo" DATA.
	$sistData= array_search ('DATA', $sistOperativo);

        $tablaHtml='';
        // Incluimos primera linea de la tabla para todos los equipos.
        $inicioTabla='<table id="tabla_conf" class="tabla_datos" border="0" cellpadding="1" cellspacing="1" align="center">'.chr(13);
	// Cabecera información sistemas operativos.
        $cabeceraTabla='<tr>'.chr(13);
        $cabeceraTabla.='<th align="center">&nbsp;&nbsp;</th>'.chr(13);
        $cabeceraTabla.='<th align="center">&nbsp;Partición&nbsp;</th>'.chr(13);
        $cabeceraTabla.='<th align="center">&nbsp;Nombre del S.O.&nbsp;</th>'.chr(13);
        $cabeceraTabla.='</tr>'.chr(13);
	// Mensaje si no existen datos en la base de datos.
        $tablaSinConfiguracion='<table id="tabla_conf" width="95%" class="tabla_listados_sin" align="center" border="0" cellpadding="0" cellspacing="1">'.chr(13);
        $tablaSinConfiguracion.='<tr><th align="center" >'.$TbMsg["CONFIG_NOOS"].'</th><tr>'.chr(13).'</table>'.chr(13);

	// CONSULTA BD: grupo de equipos con iguales sistemas operativos: idordenadores,configuracion
        $cmd->texto="";
        // agrupamos equipos con igual conf de disco.
        $cmd->texto="SELECT GROUP_CONCAT(pcconf.idordenador SEPARATOR ',') AS idordenadores, pcconf.configuraciones FROM (";

        // partconf agrupa la configuracion de todas las part: idordenador | configuracionTodasPart
        $cmd->texto.=" SELECT partconf.idordenador, GROUP_CONCAT(partconf.configuracion ORDER BY partconf.configuracion ASC SEPARATOR '@') AS configuraciones FROM (";

        // particion conf: idordenador, numdisk, configuracion (numdisk;numpar;idnombreso)
        $cmd->texto.="SELECT ordenadores_particiones.idordenador,ordenadores_particiones.numdisk, CONCAT_WS(';',ordenadores_particiones.numdisk, ordenadores_particiones.numpar, ordenadores_particiones.idnombreso) AS configuracion FROM ordenadores_particiones ";

        switch($ambito){
                case $AMBITO_AULAS :
                        $cmd->texto.=" INNER JOIN ordenadores ON ordenadores_particiones.idordenador=ordenadores.idordenador
                                INNER JOIN aulas ON aulas.idaula = ordenadores.idaula
                                WHERE aulas.idaula =".$idambito;
                        break;
                case $AMBITO_GRUPOSORDENADORES :
                        $cmd->texto.=" INNER JOIN ordenadores ON ordenadores_particiones.idordenador=ordenadores.idordenador
                                INNER JOIN gruposordenadores ON gruposordenadores.idgrupo = ordenadores.grupoid
                                WHERE gruposordenadores.idgrupo =".$idambito;
                        break;
                case $AMBITO_ORDENADORES :
                        $cmd->texto.=" WHERE ordenadores_particiones.idordenador =".$idambito;
                        break;
        }

        $cmd->texto.=" AND ordenadores_particiones.idnombreso <> 0 ";
	// Si existen particiones de datos no las mostramos.
	if ($sistData != '') 
        	$cmd->texto.=" AND ordenadores_particiones.idnombreso <> ".$sistData; 
	
	$cmd->texto.=" ORDER BY ordenadores_particiones.idordenador, idordenador,ordenadores_particiones.numdisk, ordenadores_particiones.numpar";
        // fin consulta basica -> partcion conf
        $cmd->texto.=") AS partconf GROUP BY partconf.idordenador";
        // fin consulta  partconf.
        $cmd->texto.=" ) AS pcconf GROUP BY pcconf.configuraciones " ;

	// Muestro datos de la consulta en tabla.
        $rs=new Recordset;
        $rs->Comando=&$cmd;
        if (!$rs->Abrir())
                return($tablaHtml); // Error al abrir recordset
        $rs->Primero();
        $columns = 3;
        $cc=0;
        echo $inicioTabla;
	// Si no hay datos pinto mensaje informativo.
	if($rs->EOF)
		echo $tablaSinConfiguracion;
	// Para cada grupo de pc con iguales Sist. Operativo pinto una tabla.
        while (!$rs->EOF){
                $cc++;
                echo '<tr><td colspan="'.$columns.'" style="background-color: #ffffff;">';
                echo pintaOrdenadores($cmd,$rs->campos["idordenadores"],10,$cc,'ipordenador');
                echo "</td></tr>";
                $configuraciones=explode("@",$rs->campos["configuraciones"]);
                echo $cabeceraTabla;
                // Una fila para cada particion.
                $actualDisk = 0;
                $tablaHtml='';
                foreach ( $configuraciones as $particiones) {
                        $datos= explode (';', $particiones);
                        // Si es inicio de disco
                        if($actualDisk != $datos[0]){
                                $actualDisk = $datos[0];
                                $tablaHtml.='<tr><td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;<strong>'.$TbMsg["DISK"].'&nbsp;'.$actualDisk.'</strong></td><tr>'.chr(13);
                        }
                        $tablaHtml.='<tr><td><input name="particion" idcfg="'.$cc.'" id="'.$cc.'_'.$datos[0].'_'.$datos[1].'" value="'.$datos[0].';'.$datos[1].'" type="radio"></td>'.chr(13);
                        $tablaHtml.='<td align="center">&nbsp;'.$datos[1].'&nbsp;</td>'.chr(13);
                        $tablaHtml.='<td>&nbsp;'.$sistOperativo[$datos[2]].'</td></tr>'.chr(13);

                }
                echo $tablaHtml;


                $rs->Siguiente();
        }
        $rs->Cerrar();
        echo "</table>".chr(13);

}

function tablaConfiguracionesInventarioSoftware($cmd,$idordenador){
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
			  AND nombresos.nombreso!='DATA'
			ORDER BY ordenadores_particiones.numdisk,ordenadores_particiones.numpar";
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

        if ( $tablaHtml == "" ) {
                // Equipo sin configuracion en base de datos.
		$tablaHtml='<table id="tabla_conf" width="95%" class="tabla_listados_sin" align="center" border="0" cellpadding="0" cellspacing="1">'.chr(13);
		$tablaHtml.='<tr><th align="center" >'.$TbMsg["CONFIG_NOOS"].'</th><tr>'.chr(13);
        }
       else
        {
                // Equipo con configuracion en BD
                // Incluimos primera linea de la tabla.
		$inicioTabla='<TABLE  id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>'.chr(13);
		$inicioTabla.='        <TR>'.chr(13);
		$inicioTabla.='                <TH align=center>&nbsp;&nbsp;</TH>'.chr(13);
		$inicioTabla.='                <TH align=center>&nbsp;'. $TbMsg["PARTITION"] .'&nbsp;</TH>'.chr(13);
		$inicioTabla.='                <TH align=center>&nbsp;'. $TbMsg["SO_NAME"] .'&nbsp;</TH>'.chr(13);
		$inicioTabla.='        </TR>'.chr(13);
		$tablaHtml=$inicioTabla.$tablaHtml;
        }

	$tablaHtml.="</table>".chr(13);

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
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) 
		return($tablaHtml."</table>"); // Error al abrir recordset
	$rs->Primero();
	$actualDisk = 0;
	$columns = 5;
	while (!$rs->EOF){
		
		if($actualDisk != $rs->campos["numdisk"]){
			$actualDisk = $rs->campos["numdisk"];
			$tablaHtml.='<TR><td colspan="'.$columns.'" style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #D4D0C8;">&nbsp;<strong>'.$TbMsg["DISK"].'&nbsp;'.$actualDisk.'</strong></td></TR>'.chr(13);
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
		}
		$tablaHtml.='</TR>'.chr(13);	
		$rs->Siguiente();
	}
	$rs->Cerrar();
        if ( $tablaHtml == "" ) {
                // Equipo sin configuracion en base de datos.
                $tablaHtml='<table id="tabla_conf" width="95%" class="tabla_listados_sin" align="center" border="0" cellpadding="0" cellspacing="1">'.chr(13);
                $tablaHtml.='<tr><th align="center" >'.$TbMsg["CONFIG_NOCONFIG"].'</th><tr>'.chr(13);
        }
        else
        {
                // Equipo con configuracion en BD
                // Incluimos primera linea de la tabla.
                $inicioTabla='<TABLE  id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>'.chr(13);
                $inicioTabla.='        <TR>'.chr(13);
                $inicioTabla.='                <TH align=center>&nbsp;&nbsp;</TH>'.chr(13);
                $inicioTabla.='                <TH align=center>&nbsp;'. $TbMsg["PARTITION"] .'&nbsp;</TH>'.chr(13);
                $inicioTabla.='                <TH align=center>&nbsp;'. $TbMsg["PARTITION_TYPE"] .'&nbsp;</TH>'.chr(13);
                $inicioTabla.='                <TH align=center>&nbsp;'. $TbMsg["SO_NAME"] .'&nbsp;</TH>'.chr(13);
                $inicioTabla.='                <TH align=center>&nbsp;'. $TbMsg["IMAGE_TO_CREATE"].' -- '.$TbMsg["DESTINATION_REPOSITORY"] .'&nbsp;</TH>'.chr(13);
                $inicioTabla.='        </TR>'.chr(13);

                $tablaHtml=$inicioTabla.$tablaHtml;

        }


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
	$columns = 9;
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
			
			$metodos="SYNC0="." ".chr(13);			
			$metodos.="SYNC1=".$TbMsg["SYNC1_DIR"].chr(13);						
			$metodos.="SYNC2=".$TbMsg["SYNC2_FILE"];		
			$tablaHtml.= '<TD align=center>'.HTMLCTESELECT($metodos,"desplesync_".$rs->campos["numpar"],"estilodesple","",1,100).'</TD>';			
					
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
<<<<<<< .mine
/**/


// Devuelve un Array nombres de los sistemas operativos en BD con sus identificadores.
function SistemaOperativoBD ($cmd) {
        $idSistOperativo = array(); // Array nombres de los sistemas operativos

        $cmd->texto="select idnombreso, nombreso from nombresos";
        $rs=new Recordset;
        $rs->Comando=&$cmd;
        if (!$rs->Abrir()) return; // Error al abrir recordset
        $rs->Primero();
        while (!$rs->EOF){
                $idSistOperativo[ $rs->campos["idnombreso"] ] = $rs->campos["nombreso"];
                $rs->Siguiente();
        }

        return $idSistOperativo;

}

