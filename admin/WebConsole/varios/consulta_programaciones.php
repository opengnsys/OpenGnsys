<?php 
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: consulta_programacion.php
// Descripción :
//		Muestra un calendario para elegir una fecha
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
//_________________________________________________________________________________________________________
 // Toma parametros
 $idprogramacion=0;
 if (isset($_POST["idprogramacion"])) $idprogramacion=$_POST["idprogramacion"];

// Abre conexiones
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$rs=new Recordset; // Recupero acciones anidadas
$cmd->texto="SELECT * FROM programaciones WHERE idprogramacion=".$idprogramacion;
$rs->Comando=&$cmd; 
if ($rs->Abrir()){ // Error al abrir recordset
	$cadena_campos=$rs->campos[0];
	for($i=1;$i<$rs->numerodecampos;$i++)
		$cadena_campos.=";".$rs->campos[$i]; // Usa el caracter ; para delimitar
	echo $cadena_campos;
}
$rs->Cerrar();

