<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_trabajostareas.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de trabajos_tareas
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
$idtrabajo=0; 
$idtarea=0; 
$orden=0; 

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idtrabajo"])) $idtrabajo=$_GET["idtrabajo"];
if (isset($_GET["idtarea"])) $idtarea=$_GET["idtarea"];
if (isset($_GET["orden"])) $orden=$_GET["orden"];

$idtrabajotarea=0; 
$tablanodo=""; // Arbol para nodos insertados

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<BODY>
<?
	$literal="";
	switch($opcion){
		case $op_alta :
			$literal="resultado_insertar_trabajostareas";
			break;
		case $op_modificacion :
			$literal="resultado_modificar_trabajostareas";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_trabajostareas";
			break;
		default:
			break;
	}
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idtrabajotarea.");".chr(13);
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idtrabajotarea.");".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idtrabajotarea.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
// *************************************************************************************************************************************************
function Gestiona(){
	global	$cmd;
	global	$opcion;
	global	$idtrabajo;
	global	$idtarea;
	global	$idtrabajotarea;
	global   $urlimgth;
	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$tablanodo;
	global	$orden;

	$cmd->CreaParametro("@idtrabajo",$idtrabajo,1);
	$cmd->CreaParametro("@idtarea",$idtarea,1);
	$cmd->CreaParametro("@orden",$orden,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO trabajos_tareas (idtrabajo,idtarea,orden) VALUES (@idtrabajo,@idtarea,@orden)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idtrabajotarea=$cmd->Autonumerico();
				$resul=actualiza_ambitoparametros($idtrabajotarea);
			}
			break;
		case $op_modificacion :
			$cmd->texto='UPDATE trabajos_tareas set orden=@orden WHERE idtrabajo='.$idtrabajo.' AND  idtarea='.$idtarea;
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$idtrabajotarea=toma_idtrabajotarea($cmd,$idtrabajo,$idtarea);
			$cmd->texto='DELETE  FROM trabajos_tareas WHERE idtrabajo='.$idtrabajo.' AND  idtarea='.$idtarea;
			$resul=$cmd->Ejecutar();
			if($resul)
				$resul=actualiza_ambitoparametros($idtrabajotarea);
			break;
		default:
			break;
	}
	return($resul);
}
//________________________________________________________________________________________________________
function toma_idtrabajotarea($cmd,$idtrabajo,$idtarea){
	$rs=new Recordset; 
	$cmd->texto="SELECT idtrabajotarea FROM trabajos_tareas WHERE idtrabajo=".$idtrabajo." AND idtarea=".$idtarea;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	if (!$rs->EOF) return($rs->campos["idtrabajotarea"]);
	return(0);
}
//________________________________________________________________________________________________________
function actualiza_ambitoparametros($idtrabajotarea){
	global $idtrabajo;
	global $cmd;
	$rs=new Recordset; 
	// Recorre trabajos_tareas
	$cmd->texto="SELECT idtrabajotarea,idtarea,ambitskwrk FROM trabajos_tareas WHERE idtrabajo=".$idtrabajo;
	$cmd->texto.=" ORDER BY idtrabajotarea";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if ($rs->EOF) return(true); // No hay registros

	// Recorre trabajos-tareas
	while (!$rs->EOF){
		$resul=tomando_ambito($rs->campos["idtarea"],&$ambitrabajo,&$paramtrabajo);
		if (!$resul) return(false);
		$rs->Siguiente();
	}
	$rs->Cerrar();
	//Creación parametros para inserción
	$cmd->CreaParametro("@ambitskwrk",$ambitrabajo,0);
	$cmd->CreaParametro("@parametros",$paramtrabajo,0);	
	$cmd->texto="UPDATE trabajos_tareas SET ambitskwrk=@ambitskwrk,parametros=@parametros WHERE idtrabajotarea=".$idtrabajotarea;
	$resul=$cmd->Ejecutar();
	return($resul);
}
//________________________________________________________________________________________________________
function tomando_ambito($idtarea,$ambitarea,$paramtarea){
	global	$cmd;
	$ambitarea="";
	$paramtarea="cmd=";
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM tareas_comandos WHERE idtarea=".$idtarea;
	$cmd->texto.=" ORDER by idtareacomando";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	// Recorre tareas-comandos
	while (!$rs->EOF){
			$ambitarea.=$rs->campos["ambito"].":".$rs->campos["idambito"].";";
			$paramtarea.=$rs->campos["idtareacomando"].";";

			$rs->Siguiente();
	}
	$rs->Cerrar();
	$ambitarea=substr($ambitarea,0,strlen($ambitarea)-1); // Quita la coma final
	$paramtarea=substr($paramtarea,0,strlen($paramtarea)-1); // Quita la coma final
	return(true);
}
?>