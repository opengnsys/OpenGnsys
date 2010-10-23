<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_tareascomandos.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de tareas_comandos
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
include_once("../clases/SockHidra.php");
include_once("../includes/constantes.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
$idtareacomando=0; 
$orden=0; 

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros
if (isset($_POST["idtareacomando"])) $idtareacomando=$_POST["idtareacomando"];
if (isset($_POST["orden"])) $orden=$_POST["orden"];

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
$literal="";
switch($opcion){
	case $op_eliminacion :
		$literal="resultado_eliminar_tareacomando";
		break;
	case $op_modificacion :
		$literal="resultado_modificar_tareacomando";
		break;
	case $op_ejecucion :
		$literal="resultado_ejecutar_tareacomando";
		break;
	default:
		break;
}
if ($resul){
	echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idtareacomando.");".chr(13);
}
else{
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idtareacomando.")";
}
// *************************************************************************************************************************************************
function Gestiona(){
	global $cmd;
	global $opcion;
	global $op_modificacion;
	global $op_eliminacion;
	global $op_ejecucion;
	global $EJECUCION_COMANDO;
	global $PROCESOS;
	global $ACCION_INICIADA;
	global $ACCION_SINERRORES; 
	global $servidorhidra;
	global $hidraport;
	global $idcentro;
	global $idtareacomando;
	global $orden;

	$cmd->CreaParametro("@orden",$orden,1);

	switch($opcion){
		case $op_modificacion :
			$cmd->texto='UPDATE tareas_comandos set orden=@orden WHERE idtareacomando='.$idtareacomando;
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$cmd->texto='DELETE  FROM tareas_comandos WHERE idtareacomando='.$idtareacomando;
			$resul=$cmd->Ejecutar();
			break;
		case $op_ejecucion :
				$nombreliterales[0]="idcomando";
				$nombreliterales[1]="ambito";
				$nombreliterales[2]="idambito";
				$nombreliterales[3]="parametros";
				$Datos=TomanDatos($cmd,"tareas_comandos",$idtareacomando,"idtareacomando",$nombreliterales);
				if(empty($Datos)) return(false);

				$idtipoaccion=$Datos["idcomando"];
				$ambito=$Datos["ambito"];
				$idambito=$Datos["idambito"];
				$parametros=$Datos["parametros"];

				$resul=true;

				$cmd->CreaParametro("@tipoaccion",$EJECUCION_COMANDO,1);
				$cmd->CreaParametro("@idtipoaccion",$idtipoaccion,1);
				$cmd->CreaParametro("@cateaccion",$PROCESOS,1);
				$cmd->CreaParametro("@ambito",$ambito,1);
				$cmd->CreaParametro("@idambito",$idambito,1);
				$cmd->CreaParametro("@ambitskwrk","",0);
				$cmd->CreaParametro("@fechahorareg",date("y/m/d H:i:s"),0);
				$cmd->CreaParametro("@estado",$ACCION_INICIADA,0);
				$cmd->CreaParametro("@resultado",$ACCION_SINRESULTADO,0);
				$cmd->CreaParametro("@idcentro",$idcentro,1);
				$cmd->CreaParametro("@parametros",$parametros,0);	
				$cmd->texto="INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,ambitskwrk,fechahorareg,estado,resultado,idcentro,parametros,accionid) VALUES (@tipoaccion,@idtipoaccion,@cateaccion,@ambito,@idambito,@ambitskwrk,@fechahorareg,@estado,@resultado,@idcentro,@parametros,0)";
				$resul=$cmd->Ejecutar();
				if($resul){
					$parametros.="ids=".$cmd->Autonumerico().chr(13);
				}
				$shidra=new SockHidra($servidorhidra,$hidraport); 
				if ($shidra->conectar()){ // Se ha establecido la conexión con el servidor hidra
					$shidra->envia_comando($parametros);
					$shidra->desconectar();
				}
				break;
		default:
			break;
	}
	return($resul);
}
?>
