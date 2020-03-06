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
include_once("../includes/restfunctions.php");
//_________________________________________________________________________________________________________
 // Toma parametros
 $idprogramacion=0;
 if (isset($_POST["idprogramacion"])) $idprogramacion=$_POST["idprogramacion"];

$schedule = get_schedule(null, $idprogramacion);
$task = $schedule['schedule'][0];
$cadena_campos = "null;null;null";
$cadena_campos.= ";".$task[OG_REST_PARAM_NAME];
$cadena_campos.= ";".$task[OG_REST_PARAM_YEARS];
$cadena_campos.= ";".$task[OG_REST_PARAM_MONTHS];
$cadena_campos.= ";".$task[OG_REST_PARAM_DAYS];
$cadena_campos.= ";".$task['week_days'];
$cadena_campos.= ";".$task['weeks'];
$cadena_campos.= ";".$task[OG_REST_PARAM_HOURS];
$cadena_campos.= ";".$task[OG_REST_PARAM_AM_PM];
$cadena_campos.= ";".$task[OG_REST_PARAM_MINUTES];
$cadena_campos.= ";null";
$cadena_campos.= ";null";
$cadena_campos.= ";null";
$cadena_campos.= ";null";
$cadena_campos.= ";null";
$cadena_campos.= ";null";
$cadena_campos.= ";null";

echo $cadena_campos;
