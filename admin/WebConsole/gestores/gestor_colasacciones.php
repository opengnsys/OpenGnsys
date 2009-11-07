<?
include_once("../includes/ctrlacc.php");
include_once("../includes/TomanDatos.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");

$opcion=0; // Inicializa parametros
$resultado="";
$estado="";
$idaccion=0;

$idnotificacion=0;
$resultadoNot="";
$idnotificador=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["resultado"])) $resultado=$_GET["resultado"];
if (isset($_GET["estado"])) $estado=$_GET["estado"];
if (isset($_GET["idaccion"])) $idaccion=$_GET["idaccion"];

if (isset($_GET["idnotificacion"])) $idnotificacion=$_GET["idnotificacion"];
if (isset($_GET["resultadoNot"])) $resultadoNot=$_GET["resultadoNot"];
if (isset($_GET["idnotificador"])) $idnotificador=$_GET["idnotificador"];

$mulaccion="";
if (isset($_GET["mulaccion"])) $mulaccion=$_GET["mulaccion"];

$op_modificar_resultado=1;
$op_modificar_estado=2;
$op_reiniciar_accion=3;
$op_eliminar_accion=4;
$op_modificar_resultado_notificacion=5;
$op_reiniciar_notificacion=6;

$opcion_multiple=0;
$op_eliminar_mulaccion=7;
$op_modificar_mulresultado=8;
$op_modificar_mulestado=9;
$op_reiniciar_mulaccion=10;

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	if(empty($mulaccion))
		$resul=Gestiona($opcion);
	else
		$resul=GestionaMultiple($opcion);
	$cmd->Conexion->Cerrar();
}
// ************************************************************************************************
?>
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<BODY>
<?
	$literal="";
	switch($opcion){
		case $op_modificar_resultado :
			$literal="resultado_modificar_resultado";
			break;
		case $op_modificar_estado:
			$literal="resultado_modificar_estado";
			break;
		case $op_reiniciar_accion :
			$literal="resultado_reiniciar_accion";
			break;
		case $op_eliminar_accion :
			$literal="resultado_eliminar_accion";
			break;
		case $op_modificar_resultado_notificacion :
			$literal="resultado_modificar_resultado_notificacion";
			break;
		case $op_reiniciar_notificacion :
			$literal="resultado_reiniciar_notificacion";
			break;
		default :
			$literal="resultado_multipleaccion";
			break;
	}
if(empty($mulaccion)){
	if ($resul){
		echo '<SCRIPT language="javascript">';
		echo "	window.parent.".$literal."(1,'".$cmd->DescripUltimoError()."',".$idaccion.")";
		echo '</SCRIPT>';
	}
	else{
		echo '<SCRIPT language="javascript">';
		echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idaccion.")";
		echo '</SCRIPT>';
	}
}
else{
	if ($resul){
		echo '<SCRIPT language="javascript">';
		echo "	window.parent.".$literal."(1,'".$cmd->DescripUltimoError()."')";
		echo '</SCRIPT>';
	}
	else{
		echo '<SCRIPT language="javascript">';
		echo "	window.parent.".$literal."(0,' " .$cmd->DescripUltimoError()."')";
		echo '</SCRIPT>';
	}
}

?>
</BODY>
</HTML>	
<?
/* -------------------------------------------------------------------------------------------
	Inserta, modifica o elimina un grupo de servidores dhcp de la base de datos
---------------------------------------------------------------------------------------------*/
function GestionaMultiple($opcion){

	global  $idaccion;
	global  $mulaccion;
	global  $estado;
	global  $resultado;

	global $op_modificar_resultado;
	global $op_modificar_estado;
	global $op_reiniciar_accion;
	global $op_eliminar_accion;
	global $opcion_multiple;
	global $op_modificar_mulresultado;
	global $op_modificar_mulestado;
	global $op_reiniciar_mulaccion;
	global $op_eliminar_mulaccion; 
	
	global $ACCION_DETENIDA;
	global $ACCION_INICIADA;
	global $ACCION_FINALIZADA;

	global $ACCION_TERMINADA; // Finalizada manualmente con indicacion de exito 
	global $ACCION_ABORTADA; // Finalizada manualmente con indicacion de errores 

	$resul=true;
	$auxsplit=split(";",$mulaccion);
	for ($i=0;$i<sizeof($auxsplit)-1;$i++){
		$triada=split(":",$auxsplit[$i]);
		$idaccion=$triada[0];
	
		switch($opcion){
				case $op_modificar_mulresultado:
						$acestado=$triada[1];
						$acresultado=$triada[2];
						if($acestado==$ACCION_INICIADA || $acestado==$ACCION_DETENIDA)
								$resul=Gestiona($op_modificar_resultado);
						/*if($acestado==$ACCION_FINALIZADA){
							if($acresultado==$ACCION_TERMINADA && $resultado==$ACCION_ABORTADA)
								$resul=Gestiona($op_modificar_resultado);
							if($acresultado==$ACCION_ABORTADA && $resultado==$ACCION_TERMINADA)
								$resul=Gestiona($op_modificar_resultado);
						}
						*/
						break;
				case $op_modificar_mulestado:
						$acestado=$triada[1];
						if($acestado==$ACCION_INICIADA && $estado==$ACCION_DETENIDA)
							$resul=Gestiona($op_modificar_estado);
						if($acestado==$ACCION_DETENIDA && $estado==$ACCION_INICIADA)
							$resul=Gestiona($op_modificar_estado);
						break;
				case $op_reiniciar_mulaccion :
						$resul=Gestiona($op_reiniciar_accion);
						break;
				case $op_eliminar_mulaccion :
						$resul=Gestiona($op_eliminar_accion);
						break;
		}
		if(!$resul) return(false);
	}
	$opcion=$opcion_multiple;
	return(true);
}
/* -------------------------------------------------------------------------------------------
	Inserta, modifica o elimina un grupo de servidores dhcp de la base de datos
---------------------------------------------------------------------------------------------*/
function Gestiona($opcion){

	global $ACCION_EXITOSA; // Finalizada con exito
	global $ACCION_FALLIDA; // Finalizada con errores
	global $ACCION_TERMINADA; // Finalizada manualmente con indicacion de exito 
	global $ACCION_ABORTADA; // Finalizada manualmente con indicacion de errores 
	global $ACCION_SINERRORES; // Activa y con algún error
	global $ACCION_CONERRORES; // Activa y sin error

	global $ACCION_DETENIDA;
	global $ACCION_INICIADA;
	global $ACCION_FINALIZADA;

	global	$cmd;
	global	$idaccion;
	global	$resultado;
	global	$estado;

	global	$idnotificacion;
	global	$resultadoNot;

	global $op_modificar_resultado;
	global $op_modificar_estado;
	global $op_reiniciar_accion;
	global $op_eliminar_accion;

	global $op_modificar_resultado_notificacion;
	global $op_reiniciar_notificacion;

	$cmd->CreaParametro("@idaccion",$idaccion,1);
	$cmd->CreaParametro("@idnotificacion",$idnotificacion,1);

	switch($opcion){

		case $op_modificar_resultado:
				$resul=modificar_resultado($cmd,$resultado,$idaccion);
				break;
		case $op_modificar_estado:
				$resul=modificar_estado($cmd,$estado,$idaccion);
				break;
		case $op_reiniciar_accion :
				$resul=reinicia_notificaciones($cmd,$idaccion); // Actualizaciones hacia abajo
				if($resul)
					$resul=reinicia_notificadores($cmd,$idaccion,0); // Actualizaciones hacia arriba
				break;
		case $op_eliminar_accion :
				$resul=delete_notificaciones($cmd,$idaccion); // Eliminaciones hacia abajo
				if ($resul){
					$resul=reinicia_notificadores($cmd,$idaccion,0); // Actualizaciones hacia arriba
					if($resul){
						$cmd->texto="DELETE  FROM  acciones WHERE idaccion=".$idaccion;
						$resul=$cmd->Ejecutar();
					}
				}
				break;
		case $op_modificar_resultado_notificacion:
				$cmd->texto="UPDATE notificaciones SET resultado=".$resultadoNot." WHERE idnotificacion=".$idnotificacion;
				$resul=$cmd->Ejecutar();
				if($resul){
					$resul=modificar_resultado_notificacion($cmd,$idaccion); // Actualizaciones hacia arriba
					if ($resul)
						$resul=modificar_resultado_notificadores($cmd,$resultadoNot,$idnotificacion); // Actualizaciones hacia abajo
				}
			break;
		case $op_reiniciar_notificacion:
				$nwidaccion=TomaDato($cmd,0,'notificaciones',$idnotificacion,'idnotificacion','idaccion');
				if(!empty($nwidaccion)){
					$resul=reinicia_notificaciones($cmd,$nwidaccion); // Actualizaciones hacia abajo
					if($resul)
						$resul=reinicia_notificadores($cmd,$nwidaccion,0); // Actualizaciones hacia arriba
				}
				else{
					$resul=reinicia_notificadores($cmd,0,$idnotificacion); // Actualizaciones hacia arriba
				}
				break;
	}
	return($resul);
}
/* -------------------------------------------------------------------------------------------
	Consecuencias de modificar el resultado de una notificación a Exitosa
		Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
---------------------------------------------------------------------------------------------*/
function modificar_resultado($cmd,$resultado,$idaccion){

	global $ACCION_FINALIZADA;
	global $ACCION_TERMINADA; 
	global $ACCION_ABORTADA; 

	$nombreliterales[0]="estado";
	$nombreliterales[1]="resultado";
	$nombreliterales[2]="idnotificador";
	$nombreliterales[3]="accionid";
	$Datos=TomanDatos($cmd,"acciones",$idaccion,"idaccion",$nombreliterales);
	$nwestado=$Datos["estado"];
	$nwresultado=$Datos["resultado"];
	$nwidnotificador=$Datos["idnotificador"];
	$nwaccionid=$Datos["accionid"];

	if($nwestado<>$ACCION_FINALIZADA || $nwresultado==$ACCION_TERMINADA || $nwresultado==$ACCION_ABORTADA){
		$cmd->texto="UPDATE acciones SET resultado='".$resultado."',estado='".$ACCION_FINALIZADA."' ,fechahorafin='".date("y/m/d h:i:s")."' WHERE idaccion=".$idaccion; // Actualiza resultado y estado de la acción
		$resul=$cmd->Ejecutar();
		if($resul && $nwaccionid>0)
			$resul=cuestion_raizernotificacion($cmd,$idaccion,$nwidnotificador,$nwaccionid,$resultado);
	}
	else
		$resul=false;
	if(!$resul) return(false);

	$rs=new Recordset; // Recupero acciones anidadas
	$cmd->texto="SELECT idaccion FROM acciones WHERE accionid=".$idaccion." AND 	(estado<>'".$ACCION_FINALIZADA."' OR resultado='".$ACCION_TERMINADA."' OR resultado='".$ACCION_ABORTADA."')";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	while (!$rs->EOF){
		$resul=modificar_resultado($cmd,$resultado,$rs->campos["idaccion"]);
		if(!$resul) return(false);
		$rs->Siguiente();
	}
	return(true);
}
/* -------------------------------------------------------------------------------------------
	Consecuencias de modificar el resultado de una notificación a Exitosa
		Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
---------------------------------------------------------------------------------------------*/
function cuestion_raizernotificacion($cmd,$idaccion,$idnotificador,$accionid,$resultado){

	global $ACCION_EXITOSA;
	global $ACCION_FALLIDA; 
	global $ACCION_TERMINADA; 
	global $ACCION_ABORTADA; 

	$nombreliterales[0]="idnotificacion";
	$Datos=TomanDatos($cmd,"notificaciones",$idaccion,"idaccion",$nombreliterales);

	if (empty($Datos)) // No existe notificación 
		$resul=InsertaNotificaciones($cmd,$idaccion,$idnotificador,$accionid,$resultado);
	else{ // Existe modificacion y hay que modificar su resultado
		$LITTERMINADA="¡¡ Acción terminada manualmente !!";
		$LITABORTADA="¡¡ Acción abortada manualmente !!";

		if($resultado==$ACCION_TERMINADA){
			$nwresultado=$ACCION_EXITOSA;
			$nwdescrinotificacion=$LITTERMINADA;
		}
		else{
			$nwresultado=$ACCION_FALLIDA;
			$nwdescrinotificacion=$LITABORTADA;
		}
		$cmd->texto="UPDATE notificaciones SET resultado=".$nwresultado.",descrinotificacion='".$nwdescrinotificacion."'  WHERE idaccion=".$idaccion;
		$resul=$cmd->Ejecutar();
	}
	if($resul)
		$resul=comprueba_resultados($cmd,$accionid,$resultado);
	
	return($resul);
}
/* -------------------------------------------------------------------------------------------
	Consecuencias de modificar el resultado de una notificación a Exitosa
		Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
---------------------------------------------------------------------------------------------*/
function InsertaNotificaciones($cmd,$idaccion,$idnotificador,$accionid,$resultado){

	global $ACCION_EXITOSA;
	global $ACCION_FALLIDA; 
	global $ACCION_TERMINADA; 
	global $ACCION_ABORTADA; 

	$LITTERMINADA="¡¡ Acción terminada manualmente !!";
	$LITABORTADA="¡¡ Acción abortada manualmente !!";

	if($resultado==$ACCION_TERMINADA){
		$nwresultado=$ACCION_EXITOSA;
		$nwdescrinotificacion=$LITTERMINADA;
	}
	else{
		$nwresultado=$ACCION_FALLIDA;
		$nwdescrinotificacion=$LITABORTADA;
	}

	$ntaccionid=$accionid;
	$ntidnotificador=$idnotificador;
	$ntfechahorareg=date("y/m/d h:i:s");
	$ntresultado=$nwresultado;
	$ntdescrinotificacion=$nwdescrinotificacion;
	$ntidaccion=$idaccion;

	$cmd->texto="INSERT INTO notificaciones (accionid,idnotificador,fechahorareg,resultado,descrinotificacion,idaccion) VALUES (";
	$cmd->texto.=$ntaccionid.",".$ntidnotificador.",'".$ntfechahorareg."','".$ntresultado."','".$ntdescrinotificacion."',".$ntidaccion;
	$cmd->texto.=")";

	$resul=$cmd->Ejecutar();
	return($resul);
}
/* -------------------------------------------------------------------------------------------
	Consecuencias de eliminar una notificación de una Acción
	Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
---------------------------------------------------------------------------------------------*/
function comprueba_resultados($cmd,$idaccion,$resultado){
	
	global $ACCION_FINALIZADA;
	global $ACCION_EXITOSA; 
	global $ACCION_FALLIDA;
	global $ACCION_SINERRORES; 
	global $ACCION_CONERRORES; 

	//if($idaccion==0) return(true); // Se ha llegado a la raiz
	$rs=new Recordset; 
	$cmd->texto="SELECT COUNT(*) as numfallidas FROM notificaciones WHERE resultado='".$ACCION_FALLIDA."' AND accionid=".$idaccion;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if($rs->EOF) return(false);
	$numfallidas=$rs->campos["numfallidas"];

	$nombreliterales[0]="estado";
	$nombreliterales[1]="resultado";
	$nombreliterales[2]="accionid";
	$Datos=TomanDatos($cmd,"acciones",$idaccion,"idaccion",$nombreliterales);
	$nwestado=$Datos["estado"];
	$nwresultado=$Datos["resultado"];
	$nwaccionid=$Datos["accionid"];

	// Si el nuevo resultado es el mismo y la acción había finalizado ya, el evento se corta aquí
	if($nwresultado==$resultado && $nwestado==$ACCION_FINALIZADA) return(true);
		
	if($nwestado==$ACCION_FINALIZADA){ // La acción había finalizado
		if($numfallidas>0)
			$finalaccion=$ACCION_FALLIDA;
		else
			$finalaccion=$ACCION_EXITOSA;
	}
	else{ // La acción NO había finalizado luego se convierte en sinerrores
		if($numfallidas>0)
			$finalaccion=$ACCION_CONERRORES;
		else
			$finalaccion=$ACCION_SINERRORES;
	}

	// Actualiza acción
	$cmd->texto="UPDATE acciones SET resultado='".$finalaccion."' WHERE idaccion=".$idaccion;
	$resul=$cmd->Ejecutar();
	if (!$resul) return(false);

	// Si ya existía notificación, se modifica su estado
	if($nwestado==$ACCION_FINALIZADA){
		if($numfallidas>0)
			$cmd->texto="UPDATE notificaciones SET resultado='".$ACCION_FALLIDA."' WHERE idaccion=".$idaccion;
		else
			$cmd->texto="UPDATE notificaciones SET resultado='".$ACCION_EXITOSA."' WHERE idaccion=".$idaccion;
		$resul=$cmd->Ejecutar();
		if($resul && $nwaccionid>0 )
			return(comprueba_resultados($cmd,$nwaccionid,$resultado));
	}
	else{
		// Comprueba si ha finalizado esta acción e inserta su notificador correspondiente
		$resul=comprueba_finalizada($cmd,$idaccion,$nwaccionid,$resultado);
	}
	return($resul);
}
/* -------------------------------------------------------------------------------------------
	Consecuencias de modificar el resultado de una notificación a Exitosa
		Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
---------------------------------------------------------------------------------------------*/
function comprueba_finalizada($cmd,$idaccion,$accionid,$resultado){

	global $EJECUCION_COMANDO;
	global $EJECUCION_TAREA;
	global $EJECUCION_TRABAJO;

	global $ACCION_FINALIZADA;
	global $ACCION_EXITOSA; 
	global $ACCION_FALLIDA;
	global $ACCION_TERMINADA;
	global $ACCION_ABORTADA; 
	global $ACCION_SINERRORES; 
	global $ACCION_CONERRORES;

	$rs=new Recordset; 
	$cmd->texto="SELECT COUNT(*) as numnotificaciones FROM notificaciones WHERE accionid=".$idaccion;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if($rs->EOF) return(false);
	$numnotificaciones=$rs->campos["numnotificaciones"];

	$nombreliterales[0]="tipoaccion";
	$nombreliterales[1]="parametros";
	$nombreliterales[2]="idnotificador";
	$Datos=TomanDatos($cmd,"acciones",$idaccion,"idaccion",$nombreliterales);
	$nwtipoaccion=$Datos["tipoaccion"];
	$nwparametros=$Datos["parametros"];
	$nwidnotificador=$Datos["idnotificador"];

	$ValorParametros=extrae_parametros($nwparametros,chr(13),'=');
	switch($nwtipoaccion){
		case $EJECUCION_COMANDO :
			$cadenanot=$ValorParametros["iph"]; 
			break;
		case $EJECUCION_TAREA :
			$cadenanot=$ValorParametros["cmd"]; 
			break;
		case $EJECUCION_TRABAJO :
			$cadenanot=$ValorParametros["tsk"]; 
			break;
		default:
			return(false);
	}
	$cont=1;
	for($i=0;$i<strlen($cadenanot);$i++){
		if(substr($cadenanot,$i,1)==';') $cont++;
	}

	if($numnotificaciones==$cont){
		if($resultado==$ACCION_ABORTADA)
			$cmd->texto="UPDATE acciones SET resultado='".$ACCION_FALLIDA."', estado='".$ACCION_FINALIZADA."' ,fechahorafin='".date("y/m/d h:i:s")."'  WHERE idaccion=".$idaccion;
		else
			$cmd->texto="UPDATE acciones SET resultado='".$ACCION_EXITOSA."', estado='".$ACCION_FINALIZADA."' ,fechahorafin='".date("y/m/d h:i:s")."'  WHERE idaccion=".$idaccion;

		$resul=$cmd->Ejecutar();
		if ($resul){
			if($accionid>0){
				$resul=InsertaNotificaciones($cmd,$idaccion,$nwidnotificador,$accionid,$resultado);
				if($resul)
					return(comprueba_resultados($cmd,$accionid,$resultado));
			}
		}
	}
	else
		$resul=true;

	return($resul);
}
/* -------------------------------------------------------------------------------------------
	Consecuencias de modificar el resultado de una notificación a Exitosa
		Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
---------------------------------------------------------------------------------------------*/
function modificar_estado($cmd,$estado,$idaccion){

	global $ACCION_FINALIZADA;

	$cmd->texto="UPDATE acciones SET estado='".$estado."' WHERE idaccion=".$idaccion." AND estado<>'".$ACCION_FINALIZADA."'"; // Actualiza estado de la acción
	$resul=$cmd->Ejecutar();
	if(!$resul) return(false);

	$rs=new Recordset; // Recupero acciones anidadas
	$cmd->texto="SELECT idaccion FROM acciones WHERE accionid=".$idaccion." AND estado<>'".$ACCION_FINALIZADA."'";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	while (!$rs->EOF){
		$resul=modificar_estado($cmd,$estado,$rs->campos["idaccion"]);
		if(!$resul) return(false);
		$rs->Siguiente();
	}
	return(true);
}
/* -------------------------------------------------------------------------------------------
	Consecuencias de eliminar una notificación de una Acción
	Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
---------------------------------------------------------------------------------------------*/
function reinicia_notificaciones($cmd,$idaccion){

	global $ACCION_SINERRORES;
	global $ACCION_INICIADA;

	$cmd->texto="DELETE  FROM notificaciones WHERE accionid=".$idaccion; // Elimina notificación
	$resul=$cmd->Ejecutar();
	if($resul){
		$cmd->texto="UPDATE acciones SET resultado=".$ACCION_SINERRORES.",estado=".$ACCION_INICIADA." ,fechahorafin=null WHERE idaccion=".$idaccion; // Actualiza resultado y estado de la acción como consecuencia de la eliminación de la notificación
		$resul=$cmd->Ejecutar();
	}
	if(!$resul) return(false);

	$rs=new Recordset; 
	$cmd->texto="SELECT idaccion FROM acciones WHERE accionid=".$idaccion;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	while (!$rs->EOF){
		$resul=reinicia_notificaciones($cmd,$rs->campos["idaccion"]); // Eliminación recursiva
		if(!$resul) return($resul);
		$rs->Siguiente();
	}
	return(true);
}
/* -------------------------------------------------------------------------------------------
	Consecuencias de eliminar una notificación de una Acción
	Parametros: 
		- cmd:Un comando ya operativo (con conexión abierta)  
---------------------------------------------------------------------------------------------*/
function reinicia_notificadores($cmd,$idaccion,$idnotificacion){
	
	global $ACCION_INICIADA;
	global $ACCION_SINERRORES;
	global $ACCION_CONERRORES;
	global $ACCION_FALLIDA;

	if($idaccion>0){
		$cmd->texto="DELETE FROM notificaciones WHERE idaccion=".$idaccion;
		$resul=$cmd->Ejecutar();
		if(!$resul) return(false);
		$nwidaccion=TomaDato($cmd,0,'acciones',$idaccion,'idaccion','accionid');
	}
	else{	
		$nwidaccion=TomaDato($cmd,0,'notificaciones',$idnotificacion,'idnotificacion','accionid');
		$cmd->texto="DELETE FROM notificaciones WHERE idnotificacion=".$idnotificacion;
		$resul=$cmd->Ejecutar();
		if(!$resul) return(false);
	}
	if (empty($nwidaccion)) return(true);
	$rs=new Recordset; 
	$cmd->texto="SELECT COUNT(*) as numfallidas FROM notificaciones WHERE resultado='".$ACCION_FALLIDA."' AND accionid=".$nwidaccion;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if($rs->EOF) return(true);
	if($rs->campos["numfallidas"]>0) 
		$nwresultado=$ACCION_CONERRORES;
	else
		$nwresultado=$ACCION_SINERRORES;
	$rs->Cerrar();
	$cmd->texto="UPDATE acciones SET resultado='".$nwresultado."',estado='".$ACCION_INICIADA."' ,fechahorafin=null WHERE idaccion=".$nwidaccion;
	$resul=$cmd->Ejecutar();
	if (!$resul) return(false);

	return(reinicia_notificadores($cmd,$nwidaccion,0));
}
/* -------------------------------------------------------------------------------------------
	Consecuencias de eliminar una notificación de una Acción
	Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
---------------------------------------------------------------------------------------------*/
function delete_notificaciones($cmd,$idaccion){

	global $ACCION_SINERRORES;
	global $ACCION_INICIADA;

	$cmd->texto="DELETE  FROM  notificaciones WHERE accionid=".$idaccion; // Elimina notificación
	$resul=$cmd->Ejecutar();
	if(!$resul) return(false);

	$rs=new Recordset; 
	$cmd->texto="SELECT idaccion FROM acciones WHERE accionid=".$idaccion;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if($rs->EOF) return(true);

	while (!$rs->EOF){
		$resul=delete_notificaciones($cmd,$rs->campos["idaccion"]); // Eliminación recursiva
		if(!$resul) return($resul);
		$rs->Siguiente();
	}
	if($resul){
			$cmd->texto="DELETE FROM acciones WHERE accionid=".$idaccion; // Elimina acciones
			$resul=$cmd->Ejecutar();
	}
	return($resul);
}
/* -------------------------------------------------------------------------------------------
	Consecuencias de eliminar una notificación de una Acción
	Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
---------------------------------------------------------------------------------------------*/
function modificar_resultado_notificacion($cmd,$idaccion){

	global $ACCION_FINALIZADA;
	global $ACCION_EXITOSA; 
	global $ACCION_FALLIDA;
	global $ACCION_SINERRORES; 
	global $ACCION_CONERRORES; 

	$rs=new Recordset; 
	$cmd->texto="SELECT COUNT(*) as numfallidas FROM notificaciones WHERE resultado='".$ACCION_FALLIDA."' AND accionid=".$idaccion;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if($rs->EOF) return(true);
	$numfallidas=$rs->campos["numfallidas"];

	$nombreliterales[0]="estado";
	$nombreliterales[1]="accionid";
	$Datos=TomanDatos($cmd,"acciones",$idaccion,"idaccion",$nombreliterales);
	$nwestado=$Datos["estado"];
	$nwaccionid=$Datos["accionid"];

	if($nwestado==$ACCION_FINALIZADA){ // La acción había finalizado
		if($numfallidas>0)
			$cmd->texto="UPDATE acciones SET resultado='".$ACCION_FALLIDA."' WHERE idaccion=".$idaccion;
		else
			$cmd->texto="UPDATE acciones SET resultado='".$ACCION_EXITOSA."' WHERE idaccion=".$idaccion;
	}
	else{ // La acción NO había finalizado luego se convierte en sinerrores
		if($numfallidas>0)
			$cmd->texto="UPDATE acciones SET resultado='".$ACCION_CONERRORES."' WHERE idaccion=".$idaccion;
		else
			$cmd->texto="UPDATE acciones SET resultado='".$ACCION_SINERRORES."' WHERE idaccion=".$idaccion;
	}
	$resul=$cmd->Ejecutar();
	if (!$resul) return(false);

	if($nwestado==$ACCION_FINALIZADA){
		if($numfallidas>0)
			$cmd->texto="UPDATE notificaciones SET resultado='".$ACCION_FALLIDA."' ,fechahorareg='".date("y/m/d h:i:s")."' WHERE idaccion=".$idaccion;
		else
			$cmd->texto="UPDATE notificaciones SET resultado='".$ACCION_EXITOSA."' ,fechahorareg='".date("y/m/d h:i:s")."' WHERE idaccion=".$idaccion;
		$resul=modificar_resultado_notificacion($cmd,$nwaccionid);
	}
	return($resul);
}
/* -------------------------------------------------------------------------------------------
	Consecuencias de eliminar una notificación de una Acción
	Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
---------------------------------------------------------------------------------------------*/
function modificar_resultado_notificadores($cmd,$resultadoNot,$idnotificacion){

	global $ACCION_EXITOSA; 
	global $ACCION_TERMINADA;
	global $ACCION_ABORTADA; 

	if($resultadoNot==$ACCION_EXITOSA)
		$resultado=$ACCION_TERMINADA;
	else
		$resultado=$ACCION_ABORTADA;

	$nwidaccion=TomaDato($cmd,0,'notificaciones',$idnotificacion,'idnotificacion','idaccion');
	if (!empty($nwidaccion)) 
		return(modificar_resultado($cmd,$resultado,$nwidaccion));

	return(true);

}
?>