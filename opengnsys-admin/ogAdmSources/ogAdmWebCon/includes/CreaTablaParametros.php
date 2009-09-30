<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon.
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: CreaTablaParametros.php
// Descripción :
//		Crea una tabla en memoria con especificaciones sobre los parámetros de cada comando 
//	Parametros: 
//		- cmd: Un comando ya operativo (con conexión abierta)  
//		- tabla_parametros: Referencia a la tabla donde se guardarán las especificaciones
//		- cont_parametros: Referencia a la variable que contendrá el número de elementos totales de la tabla
// *************************************************************************************************************************************************
function CreaTablaParametros($cmd,$tabla_parametros,$cont_parametros){
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM parametros";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	$cont=0;
	while (!$rs->EOF){
		$auxtabla_parametros="";
		$auxtabla_parametros["nemonico"]=$rs->campos["nemonico"];
		$auxtabla_parametros["descripcion"]=$rs->campos["descripcion"];
		$auxtabla_parametros["nomidentificador"]=$rs->campos["nomidentificador"];
		$auxtabla_parametros["nomtabla"]=$rs->campos["nomtabla"];
		$auxtabla_parametros["nomliteral"]=$rs->campos["nomliteral"];
		$tabla_parametros[$cont][0]=$auxtabla_parametros["nemonico"];
		$tabla_parametros[$cont][1]=$auxtabla_parametros;
		$cont++;
		$rs->Siguiente();
	}
	$auxnemonico="";
	// Ordena según el nemonico
	for ($i=0;$i<$cont-1;$i++){
		for ($j=$i+1;$j<$cont;$j++){
			if($tabla_parametros[$i][0]>$tabla_parametros[$j][0]){
				$auxnemonico=$tabla_parametros[$i][0];
				$tabla_parametros[$i][0]=$tabla_parametros[$j][0];
				$tabla_parametros[$j][0]=$auxnemonico;
				$auxtabla_parametros=$tabla_parametros[$i][1];
				$tabla_parametros[$i][1]=$tabla_parametros[$j][1];
				$tabla_parametros[$j][1]=$auxtabla_parametros;
			}
		}
	}
	$cont_parametros=$cont;
}
?>