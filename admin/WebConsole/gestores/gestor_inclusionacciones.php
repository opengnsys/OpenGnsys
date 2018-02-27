<?php
// *********************************************************************************************************
// Aplicaci�n WEB: ogAdmWebCon
// Autor: Jos� Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_inclusionacciones.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de procedimientos_acciones y tareas_acciones
// ********************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
//________________________________________________________________________________________________________

$tipoaccion=0;
$idtipoaccion=0;
$altas=""; 
$bajas=""; 
$modificaciones=""; 

if (isset($_POST["tipoaccion"])) $tipoaccion=$_POST["tipoaccion"];
if (isset($_POST["idtipoaccion"])) $idtipoaccion=$_POST["idtipoaccion"];

if (isset($_POST["altas"])) $altas=$_POST["altas"]; // Recoge parametros
if (isset($_POST["bajas"])) $bajas=$_POST["bajas"];
if (isset($_POST["modificaciones"])) $modificaciones=$_POST["modificaciones"];


$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
$literal="resultado_gestion_inclusionacciones";

if ($resul)
	echo $literal."(1,'".$cmd->DescripUltimoError()."');";
else
	echo $literal."(0,'".$cmd->DescripUltimoError()."');";

// *************************************************************************************************************************************************
function Gestiona()
{
	global $cmd;
	global $tipoaccion; 
	global $idtipoaccion; 
	global $altas; 	
	global $bajas; 
	global $modificaciones; 
	global $AMBITO_PROCEDIMIENTOS;
	global $AMBITO_TAREAS;
	
	switch($tipoaccion){
		case $AMBITO_PROCEDIMIENTOS:
			$cmd->CreaParametro("@idprocedimientoaccion",0,1);
			$cmd->CreaParametro("@idprocedimiento",0,1);
			$cmd->CreaParametro("@orden",0,1);
			$cmd->CreaParametro("@idcomando",0,1);
			$cmd->CreaParametro("@parametros","",0);
			$cmd->CreaParametro("@procedimientoid",0,1);
			break;							
		case $AMBITO_TAREAS:
			$cmd->CreaParametro("@idtareaaccion",0,1);
			$cmd->CreaParametro("@idtarea",0,1);
			$cmd->CreaParametro("@orden",0,1);
			$cmd->CreaParametro("@idprocedimiento",0,1);
			$cmd->CreaParametro("@tareaid",0,1);	
			break;					
	}	

	/* Altas */
	if(!empty($altas)){
		$altas=substr($altas,0,strlen($altas)-1); // Quita el último ";"
		$tbAltas=explode(";",$altas);
		for($i=0;$i<sizeof($tbAltas);$i++){
			/* Toma datos  altas */
			list($identificador,$orden,$ambito)=explode(",",$tbAltas[$i]);
			switch($tipoaccion){
				case $AMBITO_PROCEDIMIENTOS:
					$cmd->ParamSetValor("@idprocedimiento",$idtipoaccion);
					$cmd->ParamSetValor("@orden",$orden);
					$cmd->ParamSetValor("@procedimientoid",$identificador);
					$cmd->texto="INSERT INTO procedimientos_acciones
								(idprocedimiento,orden,idcomando,parametros,procedimientoid)
								VALUES (@idprocedimiento,@orden,@idcomando,@parametros,@procedimientoid)";
					break;							
				case $AMBITO_TAREAS:
					switch($ambito){
						case $AMBITO_TAREAS:
							$cmd->ParamSetValor("@idtarea",$idtipoaccion);
							$cmd->ParamSetValor("@orden",$orden);
							$cmd->ParamSetValor("@tareaid",$identificador);
							$cmd->ParamSetValor("@idprocedimiento",0);
							$cmd->texto="INSERT INTO tareas_acciones
										(idtarea,orden,idprocedimiento,tareaid)
										VALUES (@idtarea,@orden,@idprocedimiento,@tareaid)";
						break;	
						case $AMBITO_PROCEDIMIENTOS:
							$cmd->ParamSetValor("@idtarea",$idtipoaccion);
							$cmd->ParamSetValor("@orden",$orden);
							$cmd->ParamSetValor("@tareaid",0);
							$cmd->ParamSetValor("@idprocedimiento",$identificador);
							$cmd->texto="INSERT INTO tareas_acciones
										(idtarea,orden,idprocedimiento,tareaid)
										VALUES (@idtarea,@orden,@idprocedimiento,@tareaid)";
							break;							
					}	
					break;					
			}	
			$resul=$cmd->Ejecutar();	
			//echo $cmd->texto;
			if(!$resul)
				return(false);		
		}
	}
	
	/* Bajas */
	if(!empty($bajas)){
		$bajas=substr($bajas,0,strlen($bajas)-1); // Quita el último ";"
		$tbBajas=explode(";",$bajas);
		for($i=0;$i<sizeof($tbBajas);$i++){
			switch($tipoaccion){
				case $AMBITO_PROCEDIMIENTOS:
					list($idprocedimientoaccion)=explode(",",$tbBajas[$i]);
					$cmd->ParamSetValor("@idprocedimientoaccion",$idprocedimientoaccion);
					$cmd->texto="DELETE FROM procedimientos_acciones 
								WHERE idprocedimientoaccion=@idprocedimientoaccion";
					break;							
				case $AMBITO_TAREAS:
					list($idtareaaccion)=explode(",",$tbBajas[$i]);
					$cmd->ParamSetValor("@idtareaaccion",$idtareaaccion);
					$cmd->texto="DELETE FROM tareas_acciones 
								WHERE idtareaaccion=@idtareaaccion";				
					break;					
			}	
			$resul=$cmd->Ejecutar();	
			//echo $cmd->texto;
			if(!$resul)
				return(false);		
		}	
	}
	
	/* Modificaciones */
	if(!empty($modificaciones)){
		$modificaciones=substr($modificaciones,0,strlen($modificaciones)-1); // Quita el último ";"
		$tbModificaciones=explode(";",$modificaciones);
		for($i=0;$i<sizeof($tbModificaciones);$i++){
			switch($tipoaccion){
				case $AMBITO_PROCEDIMIENTOS:
					list($idprocedimientoaccion,$orden)=explode(",",$tbModificaciones[$i]);
					$cmd->ParamSetValor("@idprocedimientoaccion",$idprocedimientoaccion);
					$cmd->ParamSetValor("@orden",$orden);
					$cmd->texto="UPDATE procedimientos_acciones SET orden=@orden
								 WHERE idprocedimientoaccion=@idprocedimientoaccion";
					break;							
				case $AMBITO_TAREAS:
					list($idtareaaccion,$orden)=explode(",",$tbModificaciones[$i]);				
					$cmd->ParamSetValor("@idtareaaccion",$idtareaaccion);
					$cmd->ParamSetValor("@orden",$orden);
					$cmd->texto="UPDATE tareas_acciones SET orden=@orden
								 WHERE idtareaaccion=@idtareaaccion";				
					break;					
			}	
			$resul=$cmd->Ejecutar();	
			//echo $cmd->texto;
			if(!$resul)
				return(false);					
		}
	}
	return(true);
}
?>
