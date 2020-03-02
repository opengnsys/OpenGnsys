<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: gestor_programaciones.php
// Descripción :
//		Gestiona las programaciones de tareas y trabajos
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/CreaComando.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/comunes.php");
include_once("../includes/restfunctions.php");
//________________________________________________________________________________________________________
$op_alta=1;
$op_modificacion=2;
$op_eliminacion=3;
$op_suspension=4;

$pswop=0; // opcion alta,modificación o eliminación
$pidprogramacion=0; // Identificador de la programación si se trata de mofdificación o eliminación
$pidentificador=0; //  Identificador de la tarea o el trabajo que se está programando
$ptipoaccion=0; //  Tipo de acción:tarea o trabajo
$pnombrebloque=""; //  Nombre del bloque de programación
$pannos=""; //  Valor hexadecimal que indica los años elegidos
$pmeses=""; //  Valor hexadecimal que indica los meses elegidos
$pdiario=""; //  Valor hexadecimal que indica los dias  elegidos en modalidad simple ( un sólo mes )
$pdias=""; //  Valor hexadecimal que indica los días elegidos
$psemanas=""; //  Valor hexadecimal que indica las semanas elegidas
$phoras=""; //  Valor hexadecimal que indica las horas elegidas
$pampm=0; //  Valor que indica la  modalidad a.m.=0 p.m.= 1
$pminutos=0; //  Valor decimal que indica los minutos
$psegundos=0; //  Valor decimal que indica los segundos
$phorasini=""; //  Valor hexadecimal que indica las horas hasta elegidas 
$pampmini=0; //  Valor que indica la  modalidad a.m.=0 p.m.= 1 hora hasta
$pminutosini=0; //  Valor decimal que indica los minutos hasa
$phorasfin=""; //  Valor hexadecimal que indica las horas hasta elegidas 
$pampmfin=0; //  Valor que indica la  modalidad a.m.=0 p.m.= 1 hora hasta
$pminutosfin=0; //  Valor decimal que indica los minutos hasa

$wsw_sus=""; //  programación suspendida
$psesion=0; //  Sesión de un comando programado

if (isset($_POST["wswop"])) $pswop=$_POST["wswop"];
if (isset($_POST["widprogramacion"])) $pidprogramacion=$_POST["widprogramacion"];
if (isset($_POST["widentificador"])) $pidentificador=$_POST["widentificador"];
if (isset($_POST["wtipoaccion"])) $ptipoaccion=$_POST["wtipoaccion"];
if (isset($_POST["wnombrebloque"])) $pnombrebloque=$_POST["wnombrebloque"];
if (isset($_POST["wannos"])) $pannos=$_POST["wannos"];
if (isset($_POST["wmeses"])) $pmeses=$_POST["wmeses"];
if (isset($_POST["wdiario"])) $pdiario=$_POST["wdiario"];
if (isset($_POST["wdias"])) $pdias=$_POST["wdias"];
if (isset($_POST["wsemanas"])) $psemanas=$_POST["wsemanas"];
if (isset($_POST["whoras"])) $phoras=$_POST["whoras"];
if (isset($_POST["wampm"])) $pampm=$_POST["wampm"];
if (isset($_POST["wminutos"])) $pminutos=$_POST["wminutos"];
if (isset($_POST["wsegundos"])) $psegundos=$_POST["wsegundos"];
if (isset($_POST["whorasini"])) $phorasini=$_POST["whorasini"];
if (isset($_POST["wampmini"])) $pampmini=$_POST["wampmini"];
if (isset($_POST["wminutosini"])) $pminutosini=$_POST["wminutosini"];
if (isset($_POST["whorasfin"])) $phorasfin=$_POST["whorasfin"];
if (isset($_POST["wampmfin"])) $pampmfin=$_POST["wampmfin"];
if (isset($_POST["wminutosfin"])) $pminutosfin=$_POST["wminutosfin"];

if (isset($_POST["wsw_sus"])) $wsw_sus=$_POST["wsw_sus"];
if (isset($_POST["wsesion"])) $psesion=$_POST["wsesion"];

if(empty($pminutos)) $pminutos=0;
if(empty($psegundos)) $psegundos=0;
if(empty($pminutosini)) $pminutosini=0;
if(empty($pminutosfin)) $pminutosfin=0;

if($wsw_sus=='true') 
	$psw_sus=1;
else
	$psw_sus=0 ;

if($pswop!=$op_suspension){
	$result;
	$idr=$pidprogramacion;
	switch($pswop){
		case $op_alta:
			$result = create_schedule($pidentificador,
				$pnombrebloque, $pannos, $pmeses, $pdiario,
				$phoras, $pampm, $pminutos);
			$swop=$op_alta;
			break;
		case $op_eliminacion:
			$result = delete_schedule($pidprogramacion);
			$swop=$op_eliminacion;
			break;
		case $op_modificacion:
			$result = update_schedule($pidprogramacion,
				$pidentificador, $pnombrebloque, $pannos,
				$pmeses, $pdiario, $phoras, $pampm, $pminutos);
			$swop = $op_modificacion;
			break;
		default:
			break;
	}
	echo 'registro_programacion('.$idr.',"'.$pnombrebloque.'",'.$swop.')';
}
else{
	if ($resul) { // Ha ocurrido algún error
		$reporerr=0;
		$repordes="Error al suspender la programación";
		echo 'error_programacion()';
	}
	else{ // programacion registrada correctamente
		echo 'resultado_suspender_programacion()';
	}
}
// ***************************************************************************************************
function Gestiona()
{
	global $cmd;
	global $pswop;
	global $pidprogramacion;
	global $pidentificador;
	global $ptipoaccion;
	global $pnombrebloque;
	global $pannos;
	global $pmeses;
	global $pdiario;
	global $pdias;
	global $psemanas;
	global $phoras;
	global $pampm;
	global $pminutos;
	global $psegundos;
	global $phorasini;
	global $pampmini;
	global $pminutosini;
	global $phorasfin;
	global $pampmfin;
	global $pminutosfin;
	global $psw_sus;
	global $psesion;
	global $op_alta;
	global $op_modificacion;
	global $op_eliminacion;
	global $op_suspension;

	$cmd->CreaParametro("@idprogramacion",$pidprogramacion,1);

	$cmd->CreaParametro("@tipoaccion",$ptipoaccion,1);
	$cmd->CreaParametro("@identificador",$pidentificador,1);
	$cmd->CreaParametro("@nombrebloque",$pnombrebloque,0);
	$cmd->CreaParametro("@annos",$pannos,1);
	$cmd->CreaParametro("@meses",$pmeses,1);
	$cmd->CreaParametro("@diario",$pdiario,1);
	$cmd->CreaParametro("@dias",$pdias,1);
	$cmd->CreaParametro("@semanas",$psemanas,1);
	$cmd->CreaParametro("@horas",$phoras,1);
	$cmd->CreaParametro("@ampm",$pampm,1);
	$cmd->CreaParametro("@minutos",$pminutos,1);
	$cmd->CreaParametro("@segundos",$psegundos,1);
	$cmd->CreaParametro("@inihoras",$phorasini,1);
	$cmd->CreaParametro("@iniampm",$pampmini,1);
	$cmd->CreaParametro("@iniminutos",$pminutosini,1);
	$cmd->CreaParametro("@finhoras",$phorasfin,1);
	$cmd->CreaParametro("@finampm",$pampmfin,1);
	$cmd->CreaParametro("@finminutos",$pminutosfin,1);
	$cmd->CreaParametro("@suspendida",$psw_sus,1);
	$cmd->CreaParametro("@sesion",$psesion,1);
	switch($pswop){
		case $op_alta :
			$cmd->texto="INSERT INTO programaciones(tipoaccion,identificador,nombrebloque,annos,meses,diario,
						dias,semanas,horas,ampm,minutos,segundos,horasini,ampmini,minutosini,horasfin,
						ampmfin,minutosfin,suspendida,sesion)
						VALUES (@tipoaccion,@identificador,@nombrebloque,@annos,@meses,@diario,@dias,
						@semanas,@horas,@ampm,@minutos,@segundos,@inihoras,@iniampm,@iniminutos,@finhoras,
						@finampm,@finminutos,@suspendida,@sesion)";
			$resul=$cmd->Ejecutar();
			if($resul)
				$pidprogramacion=$cmd->Autonumerico();
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE programaciones SET nombrebloque=@nombrebloque,annos=@annos,meses=@meses,diario=@diario,
						dias=@dias,semanas=@semanas,horas=@horas,ampm=@ampm,minutos=@minutos,segundos=@segundos,
						horasini=@inihoras,ampmini=@iniampm,minutosini=@iniminutos,horasfin=@finhoras,ampmfin=@finampm,
						minutosfin=@finminutos,suspendida=@suspendida WHERE idprogramacion=@idprogramacion";
			$cmd->Traduce();
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$cmd->texto="DELETE  FROM  programaciones WHERE idprogramacion=".$pidprogramacion;
			$resul=$cmd->Ejecutar();
			break;
		case $op_suspension :
			$cmd->texto="UPDATE programaciones SET suspendida=@suspendida 
					WHERE identificador=@identificador AND tipoaccion=@tipoaccion";
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}



