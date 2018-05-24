<?php
include_once(__DIR__ . "/configfunctions.php");
include_once(__DIR__ . "/pintaParticiones.php");

/**
 * Busca en la configuración JSON los datos de partición para el código hexadecimal correspondiente.
 * @param object $json  datos JSON de configuración
 * @param string $code  código hexadecimal de partición
 * @return array        tipo de partición (string) e indicador de clonable (bool)
 */
function getPartitionData($json, $code) {
    if (isset($json->partitiontables)) {
        foreach ($json->partitiontables as $tab) {
            if (isset($tab->partitions)) {
                foreach ($tab->partitions as $par) {
                    if (hexdec($par->id) == $code) {
                        return [$par->type, $par->clonable];
                    }
                }
            }
        }
    }
    return [$code, true];
}

/**
 * Busca en la configuración JSON los datos de tabla de particiones para el código correspondiente.
 * @param object $json  datos JSON de configuración
 * @param string $code  código de tabla de particiones
 * @return string       tipo de tabla de particiones
 */
function getParttableData($json, $code) {
    if (isset($json->partitiontables)) {
        foreach ($json->partitiontables as $tab) {
            if (hexdec($tab->id) == $code) {
                return $tab->type;
            }
        }
    }
    return "";
}

/*________________________________________________________________________________________________________
	UHU   - 2013/05/14 - Se añade la clave número de disco
	Ramón - 2018/03/09 - Usar fichero de configuración JSON para datos estáticos

	La clave de configuración está formada por una serie de valores separados por ";"
	 
		Ejemplo:1;1;7;30000000;3;3;0;11
		
		Parámetros:
			1) Número de disco
			2) Número de partición
			3) Código de la partición
			4) Tamaño
			5) Identificador del sistema de ficheros instalado en la partición
			6) Identificador del nombre del sistema operativo instalado en la partición
			7) Identificador de la imagen restaurada en la partición
			8) Identificador del perfil software que contiene el S.O. instalado en la partición
			
		Además de este campo, la consulta almacena la descripción de los identificadores que forman parte
		 de esta clave compuesta de manera que el tiempo de acceso para recuperlarlos sean corto
		 ya que están en memoria y no en tablas.
		  
		En el ejempo anterior podríamos tener datos	
			1	1 	 NTFS 	30000000 	Windows NTFS 	Windows XP profesional  	NULL 	Perfil Software (CUR-8, Part:1) 
		Que indica:
			1) Número de disco
			2) Número de partición
			3) Código de la partición
			4) Tamaño
			5) Descripción del sistema de ficheros instalado en la partición
			6) Descripción del nombre del sistema operativo instalado en la partición
			7) Descripción de la imagen restaurada en la partición
			8) Descripción del perfil software que contiene el S.O. instalado en la partición
			
			Estos datos se guardan en la misma tabla de claves que será una matriz asociativa.
			
			Parámetros de la función:
				$cmd: Objeto comando (Operativo)
				$idambito: Identificador del ámbito (identificador del Aula, grupo de ordenador u ordenador)			
				$ambito: Tipo de ambito (Aulas, grupos de ordenadores u ordenadores)
				$sws: Switchs que indican el nivel de agrupamiento de los ordenadores para ser tratados
							Se trata de un octeto de manera que si tiene un "1" en la posición determinada
							indica que se requiere desplegar por ese parámetro:
								00000001- No agrupar por Sistema de ficheros
								00000010- No agrupar por Nombre de sistema Operativo
								00000100- No agrupar por Tamaño de partición
								00001000- No agrupar por Imagen instalada
								00010000- No agrupar por Perfil software contenido
								00100000- No agrupar por Contenido Cache
				$swr: Indica  si se se tiene en cuenta las particiones no clonables (si:true o no:false)	
________________________________________________________________________________________________________*/
function cargaCaves($cmd,$idambito,$ambito,$sws,$swr)
{
	global $tbKeys; // Tabla contenedora de claves de configuración
	global $conKeys; // Contador de claves de configuración
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	global $msk_sysFi;
	global $msk_nombreSO;
	global $msk_tamano;
	global $msk_imagen;
	global $msk_perfil;	
	global $msk_cache;

	// Comprobar modos SQL para hacer que la consulta sea compatible.
	$cmd->texto="SELECT @@sql_mode AS mode";
	$cmd->ejecutar();
	@$mode=$cmd->Recordset->campos["mode"];

	$cmd->texto="SELECT CONCAT_WS(';', LPAD(ordenadores_particiones.numdisk, 3, 0),
				LPAD(ordenadores_particiones.numpar, 3, 0), ";

	if($sws & $msk_tamano)
		$cmd->texto.="	ordenadores_particiones.tamano,";

	if($sws & $msk_sysFi)
		$cmd->texto.="	ordenadores_particiones.idsistemafichero, ";	

	if($sws & $msk_nombreSO)
		$cmd->texto.="	ordenadores_particiones.idnombreso, ";

	if($sws & $msk_imagen)
		$cmd->texto.="	ordenadores_particiones.idimagen, ";

	if($sws & $msk_perfil)
		$cmd->texto.="	ordenadores_particiones.idperfilsoft, ";

	if($sws & $msk_cache)
		$cmd->texto.="	ordenadores_particiones.cache, ";

	$cmd->texto.="		ordenadores_particiones.codpar) AS configuracion,
				ANY_VALUE(ordenadores_particiones.numdisk) AS numdisk,
				ANY_VALUE(ordenadores_particiones.numpar) AS numpar,
				ANY_VALUE(ordenadores_particiones.codpar) AS codpar,
				ANY_VALUE(ordenadores_particiones.tamano) AS tamano,
				ANY_VALUE(ordenadores_particiones.uso) AS uso,
				ANY_VALUE(sistemasficheros.descripcion) AS sistemafichero,
				ANY_VALUE(ordenadores_particiones.idnombreso) AS idnombreso,
				ANY_VALUE(nombresos.nombreso) AS nombreso,
				ANY_VALUE(imagenes.idimagen) AS idimagen,
				ANY_VALUE(imagenes.descripcion) AS imagen,
				(ANY_VALUE(imagenes.revision) - ANY_VALUE(ordenadores_particiones.revision)) AS difimagen,
				ANY_VALUE(imagenes.nombreca) AS nombreca,
				ANY_VALUE(imagenes.idrepositorio) AS repositorio,
				ANY_VALUE(ordenadores_particiones.fechadespliegue) AS fechadespliegue,
				ANY_VALUE(ordenadores_particiones.idperfilsoft) AS idperfilsoft,
				ANY_VALUE(perfilessoft.descripcion) AS perfilsoft
				FROM ordenadores
			  INNER JOIN ordenadores_particiones ON ordenadores_particiones.idordenador=ordenadores.idordenador
			  LEFT OUTER JOIN nombresos ON nombresos.idnombreso=ordenadores_particiones.idnombreso
			  LEFT OUTER JOIN imagenes ON imagenes.idimagen=ordenadores_particiones.idimagen
			  LEFT OUTER JOIN perfilessoft ON perfilessoft.idperfilsoft=ordenadores_particiones.idperfilsoft
			  LEFT OUTER JOIN sistemasficheros ON sistemasficheros.idsistemafichero=ordenadores_particiones.idsistemafichero";

	switch($ambito){
		case $AMBITO_AULAS :
			$cmd->texto.=" INNER JOIN aulas ON aulas.idaula = ordenadores.idaula
					WHERE aulas.idaula=".$idambito;
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto.=" INNER JOIN gruposordenadores ON gruposordenadores.idgrupo = ordenadores.grupoid
					WHERE gruposordenadores.idgrupo=".$idambito;
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto.=" WHERE ordenadores.idordenador=".$idambito;
			break;
	}

	if($swr) // Si se trata de restauración no se tiene en cuenta las partciones no clonables
		$cmd->texto.=" AND ordenadores_particiones.numpar>0 ";

	$cmd->texto.=" GROUP BY configuracion";
	// Comprobar compatiblidad de cláusula GROUP BY.
	if (strpos($mode, 'ONLY_FULL_GROUP_BY') === false) {
		$cmd->texto=preg_replace('/ANY_VALUE/', '', $cmd->texto);
	}
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
	$idx=0; 
	while (!$rs->EOF) {
		$numpar=$rs->campos["numpar"];
		$codpar=$rs->campos["codpar"];
		if ($numpar == 0) {
			// Tipo de tabla de particiones.
			$tipopar = getParttableData($codpar);
			$clonable = false;
		} else {
			// Saltar si no es clonable en restauración.
			list($tipopar, $clonable) = getPartitionData($codpar);
			if ($swr and $clonable == false) {
				continue;
			}
		}
		// Leer datos.
		$tbKeys[$idx]["cfg"]=$rs->campos["configuracion"];
		$tbKeys[$idx]["numdisk"]=$rs->campos["numdisk"];
		$tbKeys[$idx]["numpar"]=$rs->campos["numpar"];
		$tbKeys[$idx]["codpar"]=$codpar;
		$tbKeys[$idx]["tipopar"]=$tipopar;
		$tbKeys[$idx]["clonable"]=$clonable;
		$tbKeys[$idx]["tamano"]=$rs->campos["tamano"];
		$tbKeys[$idx]["uso"]=$rs->campos["uso"];
		$tbKeys[$idx]["sistemafichero"]=$rs->campos["sistemafichero"];
		$tbKeys[$idx]["idnombreso"]=$rs->campos["idnombreso"];
		$tbKeys[$idx]["nombreso"]=$rs->campos["nombreso"];
		$tbKeys[$idx]["idimagen"]=$rs->campos["idimagen"];
		$tbKeys[$idx]["imagen"]=$rs->campos["imagen"];
		$tbKeys[$idx]["nombreca"]=$rs->campos["nombreca"];
		$tbKeys[$idx]["repositorio"]=$rs->campos["repositorio"];
		$tbKeys[$idx]["fechadespliegue"]=$rs->campos["fechadespliegue"];
		$tbKeys[$idx]["difimagen"]=$rs->campos["difimagen"];
		$tbKeys[$idx]["idperfilsoft"]=$rs->campos["idperfilsoft"];
		$tbKeys[$idx]["perfilsoft"]=$rs->campos["perfilsoft"];
		//$tbKeys[$idx]["cache"]=$rs->campos["cache"];
		$idx++;
		$rs->Siguiente();
	}
	$conKeys=$idx; // Guarda contador
	$rs->Cerrar();
	return(true);
}
/*________________________________________________________________________________________________________
			UHU  - 2013/05/14 - Se añade la clave número de disco
			UHU - 2013/06/06 - Se añade un return de las configuraciones detectadas
			Dibuja la tabla de configuración de las particiones de un grupo de ordenadores
			
			Parámetros de la función:
				$cmd: Objeto comando (Operativo)
				$idambito: Identificador del ámbito (identificador del Aula, grupo de ordenador u ordenador)			
				$ambito: Tipo de ambito (Aulas, grupos de ordenadores u ordenadores)
				$sws: Switchs que indican el nivel de agrupamiento (ver comentarios de la función(cargaCaves)
				$swr: Indica  si se se tiene en cuenta las particiones no clonables (true:sólo conables , false:todas)
				
			Especificaciones:
				Esta función llama a pintaParticiones() que es realmente la encargada de mostrar o bien la
				configuración de los ordenadores o la pantalla de los comandos "Configurar" o "RestaurarImagen" 
				para permitir introducir los	datos necesarios.		
________________________________________________________________________________________________________*/
function pintaConfiguraciones($cmd,$idambito,$ambito,$colums,$sws,$swr,$pintaParticionesFunction="pintaParticiones",$tipoid="idordenador")
{
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	global $TbMsg;
	global $msk_sysFi;
	global $msk_nombreSO;
	global $msk_tamano;
	global $msk_imagen;
	global $msk_perfil;	
	global $msk_cache;

	cargaCaves($cmd,$idambito,$ambito,$sws,$swr);
	cargaSistemasFicheros($cmd,$idambito,$ambito);
	cargaPerfiles($cmd,$idambito,$ambito);
	cargaImagenes($cmd,$idambito,$ambito);
	cargaNombresSO($cmd,$idambito,$ambito);
	cargaTamano($cmd,$idambito,$ambito);
	cargaCache($cmd,$idambito,$ambito);
	$cmd->texto="SELECT	COUNT(*) AS con,
				GROUP_CONCAT(CAST( temp2.idordenador AS CHAR(11) )  ORDER BY temp2.idordenador SEPARATOR ',' ) AS idordenadores,
				temp2.configuraciones
				FROM (SELECT 
					temp1.idordenador AS idordenador,
					GROUP_CONCAT(CAST( temp1.configuracion AS CHAR(250) )  ORDER BY temp1.configuracion SEPARATOR '@' ) AS configuraciones
					FROM (SELECT ordenadores_particiones.idordenador,ordenadores_particiones.numdisk,
						ordenadores_particiones.numpar,
						CONCAT_WS(';', LPAD(ordenadores_particiones.numdisk, 3, 0),
						LPAD(ordenadores_particiones.numpar, 3, 0), ";

	if($sws & $msk_tamano)
		$cmd->texto.="	ordenadores_particiones.tamano,";

	if($sws & $msk_sysFi)
		$cmd->texto.="	ordenadores_particiones.idsistemafichero, ";	

	if($sws & $msk_nombreSO)
		$cmd->texto.="	ordenadores_particiones.idnombreso, ";


	if($sws & $msk_imagen)
		$cmd->texto.="	ordenadores_particiones.idimagen, ";	

	if($sws & $msk_perfil)
		$cmd->texto.="	ordenadores_particiones.idperfilsoft, ";
		
	if($sws & $msk_cache)
		$cmd->texto.="	ordenadores_particiones.cache, ";
			
	$cmd->texto.="		ordenadores_particiones.codpar) AS configuracion
						FROM ordenadores
						INNER JOIN ordenadores_particiones ON ordenadores_particiones.idordenador=ordenadores.idordenador
						LEFT OUTER JOIN nombresos ON nombresos.idnombreso=ordenadores_particiones.idnombreso
						LEFT OUTER JOIN imagenes ON imagenes.idimagen=ordenadores_particiones.idimagen
						LEFT OUTER JOIN perfilessoft ON perfilessoft.idperfilsoft=ordenadores_particiones.idperfilsoft";

	switch($ambito){
		case $AMBITO_AULAS :
			$cmd->texto.="	INNER JOIN aulas ON aulas.idaula = ordenadores.idaula WHERE aulas.idaula =".$idambito;
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto.="	INNER JOIN gruposordenadores ON gruposordenadores.idgrupo = ordenadores.grupoid WHERE 											 	gruposordenadores.idgrupo =".$idambito;
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto.="	WHERE ordenadores.idordenador=".$idambito;
			break;
	}

	if ($swr) // Si se trata de restauración no se tiene en cuenta las particiones no clonables
		$cmd->texto.=" AND ordenadores_particiones.numpar>0";

	$cmd->texto.="	ORDER BY ordenadores_particiones.idordenador, ordenadores_particiones.numdisk, ordenadores_particiones.numpar) AS temp1
					GROUP BY temp1.idordenador) AS temp2
					GROUP BY temp2.configuraciones
					ORDER BY con desc,idordenadores";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero();
	$cc=0; // Contador de configuraciones
	$configuraciones = array();
	echo '<table id="tabla_conf" width="95%" class="tabla_listados_sin" align="center" border="0" cellpadding="0" cellspacing="1">';
	while (!$rs->EOF){
		$cc++;
		//Muestra ordenadores
		echo '<tr><td colspan="'.$colums.'" style="background-color: #ffffff;">';
		echo pintaOrdenadores($cmd,$rs->campos["idordenadores"],10,$cc,$tipoid);
		echo '</td></tr>';
		//Muestra particiones y configuración
		$configuraciones[$cc-1] = $rs->campos["configuraciones"];
		echo $pintaParticionesFunction($cmd,$rs->campos["configuraciones"],$rs->campos["idordenadores"],$cc,$ambito,$idambito);
		$rs->Siguiente();
	}
	if ($cc == 0) {
		echo '<tr><th>'.$TbMsg["CONFIG_NOCONFIG"].'</th><tr>';  // Cliente sin configuración.
	}
	echo "</table>";
	$rs->Cerrar();
	
	return $configuraciones;
}
//________________________________________________________________________________________________________
//	Descripción:
//		Muestra una taba html con el icono de ordenadores
//	Parametros:
//		$cmd: Objeto comando (operativo)		
//		$idordenadores: Cadena con los identificadores de los ordenadores separados por ","
//		$maxcontor: Número máximo de ordenadores por fila
//		$cc: Identificador del bloque de configuración
//		$tipoid: define si el "value" de la tabla es una cadena de ip o de id de los equipos.
//			 Valores ipordenador o idordenador (por defecto id).
//	Versión 0.1 - Se incluye parametro tipoid.
//		Fecha 2014-10-23
//		Autora: Irina Gomez, ETSII Universidad de Sevilla
//________________________________________________________________________________________________________
function pintaOrdenadores($cmd,$idordenadores,$maxcontor,$cc,$tipoid='idordenador')
{
	$tablaHtml="";
	$ipordenadores="";
	$contor=0;
	$maxcontor=10; // Número máximo de prodenadores por fila
	$cmd->texto=" SELECT idordenador,nombreordenador,ip FROM ordenadores WHERE idordenador IN (".$idordenadores.") ORDER BY nombreordenador";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	// Cada ordenador es una celda de la tabla.
	while (!$rs->EOF){
		$contor++;
		$tablaHtml.= '<td align="center" style="BACKGROUND-COLOR: #FFFFFF;">
				<img src="../images/iconos/ordenador.gif" >
				<br><span style="FONT-SIZE:9px;	COLOR: #4f4f4f;" >'.$rs->campos["nombreordenador"].'</span></td>';
		if($contor>$maxcontor){
			$contor=0;
			$tablaHtml.='</tr><tr>';
		}
		$ipordenadores.=$rs->campos["ip"].',';
		$rs->Siguiente();
	}
	$tablaHtml.='</tr>';
	$tablaHtml.= '</table>';

	//Quitamos coma final en ipordenadores
	$ipordenadores = trim($ipordenadores, ',');

	// Inicio tabla: el identificador de los ordenadores puede ser las ips o las ids.
	if ($tipoid == 'ipordenador') 
		$inicioTablaHtml='<table align="left" border="0" id="tbOrd_'.$cc.'" value="'.$ipordenadores.'"><tr>';
	else
		$inicioTablaHtml='<table align="left" border="0" id="tbOrd_'.$cc.'" value="'.$idordenadores.'"><tr>';
		
	$tablaHtml=$inicioTablaHtml.$tablaHtml;
	return($tablaHtml);
}
/*________________________________________________________________________________________________________
	
	Selecciona los ordenadores que tienen el mismo sistema de ficheros del ámbito elegido
	UHU 2013/05/17 - Ahora se carga también el numero de disco en la consulta
________________________________________________________________________________________________________*/
function cargaSistemasFicheros($cmd,$idambito,$ambito)
{
	global $tbSysFi; // Tabla contenedora de ordenadores incluidos en la consulta
	global $conSysFi; // Contador de elementos anteriores
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;

	$cmd->texto="SELECT	COUNT(*) AS con,
				ordenadores_particiones.idsistemafichero,
				ordenadores_particiones.numdisk,
				ordenadores_particiones.numpar,
				sistemasficheros.descripcion AS sistemafichero,
				GROUP_CONCAT(CAST(ordenadores_particiones.idordenador AS CHAR(11) ) 
					ORDER BY ordenadores_particiones.idordenador SEPARATOR ',' ) AS ordenadores
			   FROM ordenadores
			   JOIN ordenadores_particiones ON ordenadores_particiones.idordenador=ordenadores.idordenador
			   JOIN sistemasficheros ON sistemasficheros.idsistemafichero=ordenadores_particiones.idsistemafichero";

	switch($ambito){
		case $AMBITO_AULAS :
			$cmd->texto.="	JOIN aulas ON aulas.idaula = ordenadores.idaula
					WHERE aulas.idaula =".$idambito;
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto.="	INNER JOIN gruposordenadores ON gruposordenadores.idgrupo = ordenadores.grupoid
					WHERE gruposordenadores.idgrupo =".$idambito;
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto.="	WHERE ordenadores.idordenador =".$idambito;
			break;
	}	
	$cmd->texto.="		GROUP BY ordenadores_particiones.numdisk,ordenadores_particiones.numpar, ordenadores_particiones.idsistemafichero";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero();
	$idx=0; 
	while (!$rs->EOF){
			$tbSysFi[$idx]["idsistemafichero"]=$rs->campos["idsistemafichero"];
			$tbSysFi[$idx]["numdisk"]=$rs->campos["numdisk"];			
			$tbSysFi[$idx]["numpar"]=$rs->campos["numpar"];			
			$tbSysFi[$idx]["sistemafichero"]=$rs->campos["sistemafichero"];
			$tbSysFi[$idx]["ordenadores"]=$rs->campos["ordenadores"];
			$idx++;
		$rs->Siguiente();
	}
	$conSysFi=$idx; // Guarda contador
	$rs->Cerrar();
}
/*________________________________________________________________________________________________________
	
	Toma sistema de ficheros común a los ordenadores pasados como parámetros
	UHU 2013/05/17 - Ahora se tienen en cuenta el disco, sino se le pasa ningun parametro, se asigna 1

________________________________________________________________________________________________________*/
function tomaSistemasFicheros($numpar,$ordenadores,$sw=false,$numdisk = 1)
{
	global $tbSysFi;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conSysFi; // Contador de elementos anteriores

	for ($k=0; $k<$conSysFi; $k++){
		if ($tbSysFi[$k]["numdisk"] == $numdisk && $tbSysFi[$k]["numpar"] == $numpar) {
			$pcs = explode (",", $ordenadores);
			$intersec = array_intersect (explode(",", $tbSysFi[$k]["ordenadores"]), $pcs);
			if (array_diff ($pcs, $intersec) == NULL) {
				if ($sw) {	// Retonar identificador
					return ($tbSysFi[$k]["idsistemafichero"]);
				} else {
					return ($tbSysFi[$k]["sistemafichero"]);
				}
			}
		}
	}
}
/*________________________________________________________________________________________________________
	
	Selecciona los ordenadores que tienen el mismo perfil software en la misma partición
	UHU 2013/05/17 - Ahora se carga también el numero de disco en la consulta
________________________________________________________________________________________________________*/
function cargaPerfiles($cmd,$idambito,$ambito)
{
	global $tbPerfil;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conPerfil; // Contador de elementos anteriores
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	
	$cmd->texto="SELECT count(*) AS con,
			    ordenadores_particiones.idperfilsoft,
			    ordenadores_particiones.numdisk,
			    ordenadores_particiones.numpar,
			    perfilessoft.descripcion AS perfilsoft,
			    GROUP_CONCAT(CAST(ordenadores_particiones.idordenador AS CHAR(11) ) 
				ORDER BY ordenadores_particiones.idordenador SEPARATOR ',' ) AS ordenadores
		       FROM ordenadores
		       JOIN ordenadores_particiones ON ordenadores_particiones.idordenador=ordenadores.idordenador
		       JOIN perfilessoft ON perfilessoft.idperfilsoft=ordenadores_particiones.idperfilsoft";

	switch ($ambito) {
		case $AMBITO_AULAS :
			$cmd->texto.="	JOIN aulas ON aulas.idaula = ordenadores.idaula
					WHERE aulas.idaula =".$idambito;
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto.="	JOIN gruposordenadores ON gruposordenadores.idgrupo = ordenadores.grupoid
					WHERE gruposordenadores.idgrupo =".$idambito;
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto.="	WHERE ordenadores.idordenador =".$idambito;
			break;
	}	
	$cmd->texto.="			GROUP BY ordenadores_particiones.numdisk,ordenadores_particiones.numpar, ordenadores_particiones.idperfilsoft";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero();
	$idx=0; 
	while (!$rs->EOF){
			$tbPerfil[$idx]["idperfilsoft"]=$rs->campos["idperfilsoft"];
			$tbPerfil[$idx]["perfilsoft"]=$rs->campos["perfilsoft"];
			$tbPerfil[$idx]["numdisk"]=$rs->campos["numdisk"];
			$tbPerfil[$idx]["numpar"]=$rs->campos["numpar"];					
			$tbPerfil[$idx]["ordenadores"]=$rs->campos["ordenadores"];			
			$idx++;
		$rs->Siguiente();
	}
	$conPerfil=$idx; // Guarda contador
	$rs->Cerrar();
}
/*________________________________________________________________________________________________________
	
		Toma perfilsoft común a los ordenadores pasados como parámetros
		UHU 2013/05/17 - Ahora se tienen en cuenta el disco, sino se le pasa ningun parametro, se asigna 1
________________________________________________________________________________________________________*/
function tomaPerfiles($numpar,$ordenadores,$numdisk = 1)
{
	global $tbPerfil;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conPerfil; // Contador de elementos anteriores

	for ($k=0; $k<$conPerfil; $k++){
		if ($tbPerfil[$k]["numdisk"] == $numdisk && $tbPerfil[$k]["numpar"] == $numpar) {
			$pcs = explode (",", $ordenadores);
			$intersec = array_intersect (explode(",", $tbPerfil[$k]["ordenadores"]), $pcs);
			if (array_diff ($pcs, $intersec) == NULL) {
				return ($tbPerfil[$k]["perfilsoft"]);
			}
		}
	}
}
/*________________________________________________________________________________________________________
	
	Selecciona los ordenadores que tienen la misma imagen en la misma partición
		UHU 2013/05/17 - Ahora se carga también el numero de disco en la consulta
________________________________________________________________________________________________________*/
function cargaImagenes($cmd,$idambito,$ambito)
{
	global $tbImg;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conImg; // Contador de elementos anteriores
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	
	$cmd->texto="SELECT	count(*) as con,
				ordenadores_particiones.idimagen,
				ordenadores_particiones.numdisk,
				ordenadores_particiones.numpar,
				imagenes.descripcion as imagen,
				GROUP_CONCAT(CAST(ordenadores_particiones.idordenador AS CHAR(11) )
					ORDER BY ordenadores_particiones.idordenador SEPARATOR ',' ) AS ordenadores
			   FROM ordenadores
			   JOIN ordenadores_particiones ON ordenadores_particiones.idordenador=ordenadores.idordenador
			   JOIN imagenes ON imagenes.idimagen=ordenadores_particiones.idimagen";

	switch($ambito){
		case $AMBITO_AULAS :
			$cmd->texto.="	JOIN aulas ON aulas.idaula = ordenadores.idaula
					WHERE aulas.idaula =".$idambito;
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto.="	JOIN gruposordenadores ON gruposordenadores.idgrupo = ordenadores.grupoid
					WHERE gruposordenadores.idgrupo =".$idambito;
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto.="	WHERE ordenadores.idordenador =".$idambito;
			break;
	}	
	$cmd->texto.="			GROUP BY ordenadores_particiones.numdisk,ordenadores_particiones.numpar, ordenadores_particiones.idimagen";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero();
	$idx=0; 
	while (!$rs->EOF){
			$tbImg[$idx]["idimagen"]=$rs->campos["idimagen"];
			$tbImg[$idx]["imagen"]=$rs->campos["imagen"];
			$tbImg[$idx]["numdisk"]=$rs->campos["numdisk"];			
			$tbImg[$idx]["numpar"]=$rs->campos["numpar"];			
			$tbImg[$idx]["ordenadores"]=$rs->campos["ordenadores"];			
			$idx++;
		$rs->Siguiente();
	}
	$conImg=$idx; // Guarda contador
	$rs->Cerrar();
}
/*________________________________________________________________________________________________________
	
		Toma sistema operativo común a los ordenadores pasados como parámetros
		UHU 2013/05/17 - Ahora se tienen en cuenta el disco, sino se le pasa ningun parametro, se asigna 1
________________________________________________________________________________________________________*/
function tomaImagenes($numpar,$ordenadores, $numdisk = 1)
{
	global $tbImg;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conImg; // Contador de elementos anteriores

	for ($k=0; $k<$conImg; $k++) {
		if ($tbImg[$k]["numdisk"] == $numdisk && $tbImg[$k]["numpar"] == $numpar) {
			$pcs = explode (",", $ordenadores);
			$intersec = array_intersect (explode(",", $tbImg[$k]["ordenadores"]), $pcs);
			if (array_diff ($pcs, $intersec) == NULL) {
				return ($tbImg[$k]["imagen"]);
			}
		}
	}
}
/*________________________________________________________________________________________________________
	
	Selecciona los ordenadores que tienen el mismo sistema de ficheros en la misma partición
	UHU 2013/05/17 - Ahora se carga también el numero de disco en la consulta
________________________________________________________________________________________________________*/
function cargaNombresSO($cmd,$idambito,$ambito)
{
	global $tbSO;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conSO; // Contador de elementos anteriores
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	
	$cmd->texto="SELECT	COUNT(*) AS con,
				ordenadores_particiones.idnombreso,
				ordenadores_particiones.numdisk,ordenadores_particiones.numpar,nombresos.nombreso,
				GROUP_CONCAT(CAST(ordenadores_particiones.idordenador AS CHAR(11) )
					ORDER BY ordenadores_particiones.idordenador SEPARATOR ',' ) AS ordenadores
			   FROM ordenadores
			   JOIN ordenadores_particiones ON ordenadores_particiones.idordenador=ordenadores.idordenador
			   JOIN nombresos ON nombresos.idnombreso=ordenadores_particiones.idnombreso";

	switch($ambito){
		case $AMBITO_AULAS :
			$cmd->texto.="	JOIN aulas ON aulas.idaula = ordenadores.idaula
					WHERE aulas.idaula =".$idambito;
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto.="	JOIN gruposordenadores ON gruposordenadores.idgrupo = ordenadores.grupoid
					WHERE gruposordenadores.idgrupo =".$idambito;
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto.="	WHERE ordenadores.idordenador =".$idambito;
			break;
	}	
	$cmd->texto.="			GROUP BY ordenadores_particiones.numdisk,ordenadores_particiones.numpar, ordenadores_particiones.idnombreso";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero();
	$idx=0; 
	while (!$rs->EOF){
			$tbSO[$idx]["idnombreso"]=$rs->campos["idnombreso"];
			$tbSO[$idx]["nombreso"]=$rs->campos["nombreso"];
			$tbSO[$idx]["numdisk"]=$rs->campos["numdisk"];			
			$tbSO[$idx]["numpar"]=$rs->campos["numpar"];			
			$tbSO[$idx]["ordenadores"]=$rs->campos["ordenadores"];			
			$idx++;
		$rs->Siguiente();
	}
	$conSO=$idx; // Guarda contador
	$rs->Cerrar();
}
/*________________________________________________________________________________________________________
	
		Toma sistema operativo común a los ordenadores pasados como parámetros
		UHU 2013/05/17 - Ahora se tienen en cuenta el disco, sino se le pasa ningun parametro, se asigna 1
________________________________________________________________________________________________________*/
function tomaNombresSO($numpar,$ordenadores,$numdisk = 1)
{
	global $tbSO;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conSO; // Contador de elementos anteriores

	for($k=0; $k<$conSO; $k++) {
		if ($tbSO[$k]["numdisk"] == $numdisk && $tbSO[$k]["numpar"] == $numpar) {
			//$pos = strpos($tbSO[$k]["ordenadores"], $ordenadores);
			//if ($pos !== false) { // Cadena encontrada
			$pcs = explode (",", $ordenadores);
			$intersec = array_intersect (explode(",", $tbSO[$k]["ordenadores"]), $pcs);
			if (array_diff ($pcs, $intersec) == NULL) {
				return ($tbSO[$k]["nombreso"]);
			}
		}
	}
}
/*________________________________________________________________________________________________________
	
	Selecciona los ordenadores que tienen el mismo tamaño para la misma partición
	UHU 2013/05/17 - Ahora se carga también el numero de disco en la consulta
________________________________________________________________________________________________________*/
function cargaTamano($cmd,$idambito,$ambito)
{
	global $tbTam;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conTam; // Contador de elementos anteriores
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	
	// Comprobar modos SQL para hacer que la consulta sea compatible.
	$cmd->texto="SELECT @@sql_mode AS mode";
	$cmd->ejecutar();
	@$mode=$cmd->Recordset->campos["mode"];
	$cmd->texto="SELECT	COUNT(*) AS con,
			   	ANY_VALUE(ordenadores_particiones.tamano) AS tamano,
			   	ANY_VALUE(ordenadores_particiones.uso) AS uso,
				ANY_VALUE(ordenadores_particiones.numdisk) AS numdisk,
				ANY_VALUE(ordenadores_particiones.numpar) AS numpar,
				GROUP_CONCAT(CAST(ordenadores_particiones.idordenador AS CHAR(11) )
					ORDER BY ordenadores_particiones.idordenador SEPARATOR ',' ) AS ordenadores
			   FROM ordenadores
			   JOIN ordenadores_particiones ON ordenadores_particiones.idordenador=ordenadores.idordenador";

	switch($ambito){
		case $AMBITO_AULAS :
			$cmd->texto.="	JOIN aulas ON aulas.idaula = ordenadores.idaula
					WHERE aulas.idaula =".$idambito;
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto.="	JOIN gruposordenadores ON gruposordenadores.idgrupo = ordenadores.grupoid
					WHERE gruposordenadores.idgrupo =".$idambito;
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto.="	WHERE ordenadores.idordenador =".$idambito;
			break;
	}	
	$cmd->texto.="			GROUP BY ordenadores_particiones.numdisk,ordenadores_particiones.numpar, ordenadores_particiones.tamano";
	// Comprobar compatiblidad de cláusula GROUP BY.
	if (strpos($mode, 'ONLY_FULL_GROUP_BY') === false)
		$cmd->texto=preg_replace('/ANY_VALUE/', '', $cmd->texto);

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero();
	$idx=0; 
	while (!$rs->EOF){
		$tbTam[$idx]["tamano"]=$rs->campos["tamano"];
		$tbTam[$idx]["uso"]=$rs->campos["uso"];
		$tbTam[$idx]["numdisk"]=$rs->campos["numdisk"];
		$tbTam[$idx]["numpar"]=$rs->campos["numpar"];			
		$tbTam[$idx]["ordenadores"]=$rs->campos["ordenadores"];
		$idx++;
		$rs->Siguiente();
	}
	$conTam=$idx; // Guarda contador
	$rs->Cerrar();
}
/*________________________________________________________________________________________________________
	
		Toma tamaño de partición común a los ordenadores pasados como parámetros
		UHU 2013/05/17 - Ahora se tienen en cuenta el disco, sino se le pasa ningun parametro, se asigna 1
________________________________________________________________________________________________________*/
function tomaTamano($numpar,$ordenadores,$numdisk = 1)
{
	global $tbTam;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conTam; // Contador de elementos anteriores

	for ($k=0; $k<$conTam; $k++) {
		if ($tbTam[$k]["numdisk"] == $numdisk && $tbTam[$k]["numpar"] == $numpar) {
//			$pos = strpos ($tbTam[$k]["ordenadores"], $ordenadores);
//			if ($pos !== FALSE) { // Cadena encontrada
			$pcs = explode (",", $ordenadores);
			$intersec = array_intersect (explode(",", $tbTam[$k]["ordenadores"]), $pcs);
			if (array_diff ($pcs, $intersec) == NULL) {
				return ($tbTam[$k]["tamano"]);
			}
		}
	}
}
/*________________________________________________________________________________________________________
	
		Toma porcentaje de uso de partición común a los ordenadores pasados como parámetros
________________________________________________________________________________________________________*/
function tomaUso($numpar, $ordenadores, $numdisk=1)
{
	global $tbTam;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conTam; // Contador de elementos anteriores

	for ($k=0; $k<$conTam; $k++) {
		if ($tbTam[$k]["numdisk"] == $numdisk && $tbTam[$k]["numpar"] == $numpar) {
//			$pos = strpos ($tbTam[$k]["ordenadores"], $ordenadores);
//			if ($pos !== FALSE) { // Cadena encontrada
			$pcs = explode (",", $ordenadores);
			$intersec = array_intersect (explode(",", $tbTam[$k]["ordenadores"]), $pcs);
			if (array_diff ($pcs, $intersec) == NULL) {
				return ($tbTam[$k]["uso"]);
			}
		}
	}
}
/*________________________________________________________________________________________________________
	
	Selecciona los ordenadores que tienen el mismo Contenido de Cache para la misma partición
	UHU 2013/05/17 - Ahora se carga también el numero de disco en la consulta
________________________________________________________________________________________________________*/
function cargaCache($cmd,$idambito,$ambito)
{
	global $tbCac;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conCac; // Contador de elementos anteriores
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	
	$cmd->texto="SELECT	COUNT(*) AS con,
			   	ordenadores_particiones.cache,
			   	ordenadores_particiones.numdisk,
			   	ordenadores_particiones.numpar,
				GROUP_CONCAT(CAST(ordenadores_particiones.idordenador AS CHAR(11) )
					ORDER BY ordenadores_particiones.idordenador SEPARATOR ',' ) AS ordenadores
			   FROM ordenadores
			   JOIN ordenadores_particiones ON ordenadores_particiones.idordenador=ordenadores.idordenador";

	switch($ambito){
		case $AMBITO_AULAS :
			$cmd->texto.="	JOIN aulas ON aulas.idaula = ordenadores.idaula
					WHERE aulas.idaula =".$idambito;
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto.="	JOIN gruposordenadores ON gruposordenadores.idgrupo = ordenadores.grupoid
					WHERE gruposordenadores.idgrupo =".$idambito;
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto.="	WHERE ordenadores.idordenador =".$idambito;
			break;
	}	
	$cmd->texto.="			GROUP BY ordenadores_particiones.numdisk,ordenadores_particiones.numpar, ordenadores_particiones.cache";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero();
	$idx=0; 
	while (!$rs->EOF){
			$tbCac[$idx]["cache"]=$rs->campos["cache"];
			$tbCac[$idx]["numdisk"]=$rs->campos["numdisk"];
			$tbCac[$idx]["numpar"]=$rs->campos["numpar"];
			$tbCac[$idx]["ordenadores"]=$rs->campos["ordenadores"];
			$idx++;
		$rs->Siguiente();
	}
	$conCac=$idx; // Guarda contador
	$rs->Cerrar();
}
/*________________________________________________________________________________________________________
	
		Toma tamaño de partición común a los ordenadores pasados como parámetros
		UHU 2013/05/17 - Ahora se tienen en cuenta el disco, sino se le pasa ningun parametro, se asigna 1
________________________________________________________________________________________________________*/
function tomaCache($numpar,$ordenadores,$numdisk = 1)
{
	global $tbCac;  // Tabla contenedora de ordenadores incluidos en la consulta
	global $conCac; // Contador de elementos anteriores

	for ($k=0; $k<$conCac; $k++) {
		if ($tbCac[$k]["numdisk"] == $numdisk && $tbCac[$k]["numpar"] == $numpar) {
			$pcs = explode (",", $ordenadores);
			$intersec = array_intersect (explode(",", $tbCac[$k]["ordenadores"]), $pcs);
			if (array_diff ($pcs, $intersec) == NULL) {
				return ($tbCac[$k]["cache"]);
			}
		}
	}
}
