<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: gestor_programaciones.php
// Descripción :
//		Gestiona las programaciones de tareas y trabajos
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/comunes.php");
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

if (isset($_GET["wswop"]))								$pswop=$_GET["wswop"];
if (isset($_GET["widprogramacion"]))			$pidprogramacion=$_GET["widprogramacion"];
if (isset($_GET["widentificador"]))				$pidentificador=$_GET["widentificador"];
if (isset($_GET["wtipoaccion"]))					$ptipoaccion=$_GET["wtipoaccion"];
if (isset($_GET["wnombrebloque"]))			$pnombrebloque=$_GET["wnombrebloque"];
if (isset($_GET["wannos"]))							$pannos=$_GET["wannos"];
if (isset($_GET["wmeses"]))							$pmeses=$_GET["wmeses"];
if (isset($_GET["wdiario"]))							$pdiario=$_GET["wdiario"];
if (isset($_GET["wdias"]))								$pdias=$_GET["wdias"];
if (isset($_GET["wsemanas"]))						$psemanas=$_GET["wsemanas"];
if (isset($_GET["whoras"]))							$phoras=$_GET["whoras"];
if (isset($_GET["wampm"]))							$pampm=$_GET["wampm"];
if (isset($_GET["wminutos"]))						$pminutos=$_GET["wminutos"];
if (isset($_GET["wsegundos"]))					$psegundos=$_GET["wsegundos"];
if (isset($_GET["whorasini"]))						$phorasini=$_GET["whorasini"];
if (isset($_GET["wampmini"]))						$pampmini=$_GET["wampmini"];
if (isset($_GET["wminutosini"]))					$pminutosini=$_GET["wminutosini"];
if (isset($_GET["whorasfin"]))						$phorasfin=$_GET["whorasfin"];
if (isset($_GET["wampmfin"]))						$pampmfin=$_GET["wampmfin"];
if (isset($_GET["wminutosfin"]))					$pminutosfin=$_GET["wminutosfin"];

if (isset($_GET["wsw_sus"]))						$wsw_sus=$_GET["wsw_sus"];

if(empty($pminutos)) $pminutos=0;
if(empty($psegundos)) $psegundos=0;
if(empty($pminutosini)) $pminutosini=0;
if(empty($pminutosfin)) $pminutosfin=0;

if($wsw_sus=='true') 
	$psw_sus=1;
else
	$psw_sus=0 ;

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
// *************************************************************************************************************************************************
?>
<HTML>
<HEAD>
<BODY>
<?
if($pswop!=$op_suspension){
	if (!$resul){ // Ha ocurrido algún error
		$reporerr=$cmd->UltimoError();
		$repordes=$cmd->DescripUltimoError();
		echo '<SCRIPT language="javascript">';
		echo '		window.parent.error_programacion('.$reporerr.',"'.$repordes.'")';
		echo '</SCRIPT>';
	}
	else{ // programacion registrada correctamente
		switch($pswop){
			case $op_modificacion :
				$idr=$pidprogramacion; // Identificador de la programacion modificada
				$swop=$op_modificacion;
				break;
			case $op_alta:		
				$idr=$pidprogramacion; // Identificador de la programacion nueva
				$swop=$op_alta;
				break;
			case $op_eliminacion :
				$idr=$pidprogramacion; // Identificador de la programacion eliminada
				$swop=$op_eliminacion;
				break;
			default:
				break;
		}
		echo '<SCRIPT language="javascript">';
		echo '		window.parent.registro_programacion('.$idr.',"'.$pnombrebloque.'",'.$swop.')';
		echo '</SCRIPT>';
	}
}
else{

	if (!$resul){ // Ha ocurrido algún error
		$reporerr=0;
		$repordes="Error al suspender la programación";
		echo '<SCRIPT language="javascript">';
		echo '		window.parent.error_programacion('.$reporerr.',"'.$repordes.'")';
		echo '</SCRIPT>';
	}
	else{ // programacion registrada correctamente
		echo '<SCRIPT language="javascript">';
		echo '		window.parent.resultado_suspender_programacion()';
		echo '</SCRIPT>';
	}
}
?>
</BODY>
</HTML>	
<?
// *************************************************************************************************************************************************
function Gestiona(){
	global	$cmd;
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

	switch($pswop){
		case $op_alta :
			$cmd->texto="INSERT INTO programaciones(tipoaccion,identificador,nombrebloque,annos,meses,diario,dias,semanas,horas,ampm,minutos,segundos,horasini,ampmini,minutosini,horasfin,ampmfin,minutosfin,suspendida) VALUES (@tipoaccion,@identificador,@nombrebloque,@annos,@meses,@diario,@dias,@semanas,@horas,@ampm,@minutos,@segundos,@inihoras,@iniampm,@iniminutos,@finhoras,@finampm,@finminutos,@suspendida)";
			$resul=$cmd->Ejecutar();
			if($resul)
				$pidprogramacion=$cmd->Autonumerico();
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE programaciones SET nombrebloque=@nombrebloque,annos=@annos,meses=@meses,diario=@diario,dias=@dias,semanas=@semanas,horas=@horas,ampm=@ampm,minutos=@minutos,segundos=@segundos,horasini=@inihoras,ampmini=@iniampm,minutosini=@iniminutos,horasfin=@finhoras,ampmfin=@finampm,minutosfin=@finminutos,suspendida=@suspendida WHERE idprogramacion=@idprogramacion";
			$cmd->Traduce();
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$cmd->texto="DELETE  FROM  programaciones WHERE idprogramacion=".$pidprogramacion;
			$resul=$cmd->Ejecutar();
			break;
		case $op_suspension :
			$cmd->texto="UPDATE programaciones SET suspendida=@suspendida WHERE identificador=@identificador AND tipoaccion=@tipoaccion";
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
// *************************************************************************************************************************************************
//	Devuelve una objeto comando totalmente operativo (con la conexión abierta)
//	Parametros: 
//		- cadenaconexion: Una cadena con los datos necesarios para la conexión: nombre del servidor
//		usuario,password,base de datos,etc separados por coma
//________________________________________________________________________________________________________
function CreaComando($cadenaconexion){
	$strcn=split(";",$cadenaconexion);
	$cn=new Conexion; 
	$cmd=new Comando;	
	$cn->CadenaConexion($strcn[0],$strcn[1],$strcn[2],$strcn[3],$strcn[4]);
	if (!$cn->Abrir()) return (false); 
	$cmd->Conexion=&$cn; 
	return($cmd);
}
?>


