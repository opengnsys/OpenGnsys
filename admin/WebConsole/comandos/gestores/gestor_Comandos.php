<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_Comandos.php
// Descripción : 
//		Gestor de todos los comandos
// *************************************************************************************************************************************************
include_once("../../includes/ctrlacc.php");
include_once("../../includes/restfunctions.php");
include_once("../../clases/AdoPhp.php");
include_once("../../includes/constantes.php");
include_once("../../includes/comunes.php");
include_once("../../includes/CreaComando.php");
include_once("../../includes/RecopilaIpesMacs.php");
//________________________________________________________________________________________________________
include_once("../includes/capturaacciones.php");
//________________________________________________________________________________________________________

define('OG_CMD_ID_WAKEUP', 1);
define('OG_CMD_ID_POWEROFF', 2);
define('OG_CMD_ID_RESTORE_IMAGE', 3);
define('OG_CMD_ID_CREATE_IMAGE', 4);
define('OG_CMD_ID_REBOOT', 5);
define('OG_CMD_ID_HARDWARE', 6);
define('OG_CMD_ID_SOFTWARE', 7);
define("OG_CMD_ID_SCRIPT", 8);
define('OG_CMD_ID_SESSION', 9);
define('OG_CMD_ID_SETUP', 10);
define('OG_CMD_ID_DELETE_CACHED_IMAGE', 11);
define('OG_CMD_ID_CREATE_BASIC_IMAGE', 12);
define('OG_CMD_ID_RESTORE_BASIC_IMAGE', 13);
define('OG_CMD_ID_CREATE_INCREMENTAL_IMAGE', 14);
define('OG_CMD_ID_RESTORE_INCREMENTAL_IMAGE', 15);
define('OG_CMD_ID_SENDMESSAGE', 16);

function run_command($idcomando, $cadenaip, $cadenamac, $atributos) {
	global $cmd;
	switch ($idcomando) {
		case OG_CMD_ID_WAKEUP:
			include("wakeonlan_repo.php");
			break;
		case OG_CMD_ID_SETUP:
			setup($cadenaip, $atributos);
			break;
		case OG_CMD_ID_SESSION:
			session($cadenaip, $atributos);
			break;
		case OG_CMD_ID_CREATE_BASIC_IMAGE:
			create_basic_image($cadenaip, $atributos);
			break;
		case OG_CMD_ID_CREATE_INCREMENTAL_IMAGE:
			create_incremental_image($cadenaip, $atributos);
			break;
		case OG_CMD_ID_RESTORE_BASIC_IMAGE:
			restore_basic_image($cadenaip, $atributos);
			break;
		case OG_CMD_ID_RESTORE_INCREMENTAL_IMAGE:
			restore_incremental_image($cadenaip, $atributos);
			break;
		case OG_CMD_ID_POWEROFF:
			poweroff($cadenaip);
			break;
		case OG_CMD_ID_CREATE_IMAGE:
			create_image($cadenaip, $atributos);
			break;
		case OG_CMD_ID_RESTORE_IMAGE:
			restore_image($cadenaip, $atributos);
			break;
		case OG_CMD_ID_REBOOT:
			reboot($cadenaip);
			break;
		case OG_CMD_ID_HARDWARE:
			hardware($cadenaip);
			break;
		case OG_CMD_ID_SOFTWARE:
			software($cadenaip, $atributos);
			break;
		case OG_CMD_ID_SCRIPT:
		case OG_CMD_ID_DELETE_CACHED_IMAGE:
			shell(3, $cadenaip, $atributos);
			break;
	}
}

// Recoge parametros de seguimiento
$sw_ejya="";
$sw_seguimiento="";
$sw_ejprg="";
$sw_mkprocedimiento="";
$nombreprocedimiento="";
$idprocedimiento="";
$ordprocedimiento=0;
$avisoRepo=false;

$sw_mktarea="";
$nombretarea="";
$idtarea="";
$ordtarea=0;

if (isset($_POST["sw_ejya"]))	$sw_ejya=$_POST["sw_ejya"]; 
if (isset($_POST["sw_seguimiento"]))	$sw_seguimiento=$_POST["sw_seguimiento"]; 

if (isset($_POST["sw_ejprg"]))	$sw_ejprg=$_POST["sw_ejprg"]; 

if (isset($_POST["sw_mkprocedimiento"]))	$sw_mkprocedimiento=$_POST["sw_mkprocedimiento"]; 
if (isset($_POST["nombreprocedimiento"]))	$nombreprocedimiento=$_POST["nombreprocedimiento"]; 
if (isset($_POST["idprocedimiento"]))	$idprocedimiento=$_POST["idprocedimiento"]; 
if (isset($_POST["ordprocedimiento"]))	$ordprocedimiento=$_POST["ordprocedimiento"]; 
if(empty($ordprocedimiento)) $ordprocedimiento=0;

if (isset($_POST["sw_mktarea"]))	$sw_mktarea=$_POST["sw_mktarea"]; 
if (isset($_POST["nombretarea"]))	$nombretarea=$_POST["nombretarea"]; 
if (isset($_POST["idtarea"]))	$idtarea=$_POST["idtarea"]; 
if (isset($_POST["ordtarea"]))	$ordtarea=$_POST["ordtarea"]; 
if(empty($ordtarea)) $ordtarea=0;

//__________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//__________________________________________________________________
$funcion="nfn=".$funcion.chr(13); // Nombre de la función que procesa el comando y el script que lo implementa
$aplicacion=""; // Ámbito de aplicación (cadena de ipes separadas por ";" y de identificadores de ordenadores por ","
$acciones=""; // Cadena de identificadores de acciones separadas por ";" para seguimiento



$atributos=str_replace('@',chr(13),$atributos); // Reemplaza caracters
$atributos=str_replace('#',chr(10),$atributos); 
$atributos=str_replace('$',chr(9),$atributos);


//__________________________________________________________________
?>
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<SCRIPT language="javascript" src="../jscripts/comunescomandos.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>

<?php
//##################################################################
/* Recopila identificadore ,ipes y macs para envío de comandos */
$cadenaid="";
$cadenaip="";
$cadenamac="";
$cadenaoga="";	// Clave de acceso a la API REST de OGAgent.

if(!empty($filtro)){ // Ambito restringido a un subconjuto de ordenadores
	if(substr($filtro,strlen($cadenaid)-1,1)==";") // Si el último caracter es una coma
		$filtro=substr($filtro,0,strlen($filtro)-1); // Quita la coma
}
RecopilaIpesMacs($cmd,$ambito,$idambito,$filtro);

/*--------------------------------------------------------------------------------------------------------------------
	Creación de parametros para sentencias SQL
--------------------------------------------------------------------------------------------------------------------*/
$cmd->CreaParametro("@tipoaccion",0,1);
$cmd->CreaParametro("@idtipoaccion",0,1);
$cmd->CreaParametro("@descriaccion","",0);
$cmd->CreaParametro("@idordenador",0,1);
$cmd->CreaParametro("@ip","",0);
$cmd->CreaParametro("@sesion",0,1);
$cmd->CreaParametro("@parametros","",0);
$cmd->CreaParametro("@fechahorareg","",0);
$cmd->CreaParametro("@estado",0,1);
$cmd->CreaParametro("@resultado",0,1);
$cmd->CreaParametro("@idcentro",0,1);
$cmd->CreaParametro("@idprocedimiento",0,1);
$cmd->CreaParametro("@descripcion","",0);
$cmd->CreaParametro("@idcomando",0,1);
$cmd->CreaParametro("@idtarea",0,1);
$cmd->CreaParametro("@ambito",0,1);
$cmd->CreaParametro("@idambito",0,1);
$cmd->CreaParametro("@restrambito","",0);
$cmd->CreaParametro("@ordprocedimiento",0,1);
$cmd->CreaParametro("@ordtarea",0,1);

if($ambito==0){ // Ambito restringido a un subconjuto de ordenadores con formato (idordenador1,idordenador2,etc)
	$cmd->ParamSetValor("@restrambito",$idambito);
	$idambito=0;
}
if(!empty($filtro)){ // Ambito restringido a un subconjuto de ordenadores 
	$cmd->ParamSetValor("@restrambito",$filtro);
}
$resul=true;

/*--------------------------------------------------------------------------------------------------------------------
	Switch de ejecución inmediata y de seguimiento
--------------------------------------------------------------------------------------------------------------------*/
if($sw_ejya=='on' || $sw_ejprg=="on" ){ 
	$parametros=$funcion.$atributos;
	$aplicacion=chr(13)."ido=".$cadenaid.chr(13)."mac=".$cadenamac.chr(13)."iph=".$cadenaip.chr(13);
	if($sw_seguimiento==1 || $sw_ejprg=="on"){ // Switch de ejecución con seguimiento o comando programado
		$sesion=time();
		$cmd->ParamSetValor("@tipoaccion",$EJECUCION_COMANDO);
		$cmd->ParamSetValor("@idtipoaccion",$idcomando);
		$cmd->ParamSetValor("@descriaccion",$descricomando);
		$cmd->ParamSetValor("@sesion",$sesion);
		$cmd->ParamSetValor("@idcomando",$idcomando);
		$cmd->ParamSetValor("@parametros",$parametros);
		$cmd->ParamSetValor("@fechahorareg",date("y/m/d H:i:s"));
		if($sw_ejprg=="on") // Switch de ejecución con programación (se para el comando tarea para lanzarlo posteriormente)
			$cmd->ParamSetValor("@estado",$ACCION_DETENIDA);
		else
			$cmd->ParamSetValor("@estado",$ACCION_INICIADA);
		$cmd->ParamSetValor("@resultado",$ACCION_SINRESULTADO);
		$cmd->ParamSetValor("@ambito",$ambito);
		$cmd->ParamSetValor("@idambito",$idambito);			
		$cmd->ParamSetValor("@idcentro",$idcentro);
		$auxID=explode(",",$cadenaid);
		$auxIP=explode(";",$cadenaip);
		for ($i=0;$i<sizeof($auxID);$i++){
			$cmd->ParamSetValor("@idordenador",$auxID[$i]);
			$cmd->ParamSetValor("@ip",$auxIP[$i]);
			$cmd->texto="INSERT INTO acciones (idordenador,tipoaccion,idtipoaccion,descriaccion,ip,
						sesion,idcomando,parametros,fechahorareg,estado,resultado,ambito,idambito,restrambito,idcentro)
						VALUES (@idordenador,@tipoaccion,@idtipoaccion,@descriaccion,@ip,
						@sesion,@idcomando,@parametros,@fechahorareg,@estado,@resultado,@ambito,@idambito,@restrambito,@idcentro)";
			$resul=$cmd->Ejecutar();
		}
		$acciones=chr(13)."ids=".$sesion.chr(13); // Para seguimiento
	}
	if (!$resul){
		echo '<SCRIPT language="javascript">';
		echo 'resultado_comando(7);'.chr(13);
		echo '</SCRIPT>';
	}
	else{
		$ValorParametros=extrae_parametros($parametros,chr(13),'=');
		$script=@urldecode($ValorParametros["scp"]);
		if($sw_ejya=='on'){
			if (($sw_seguimiento == 1 || $sw_ejprg == "on") &&
			    $idcomando != OG_CMD_ID_WAKEUP)
				run_schedule($cadenaip);
			else
				run_command($idcomando, $cadenaip, $cadenamac, $atributos);

			// En agente nuevo devuelvo siempre correcto
			$resulhidra = 1;

			// Comprobamos si el comando es soportado por el nuevo OGAgent
			$numip=0;
			$ogAgentNuevo = false;
			switch ($idcomando) {
				case OG_CMD_ID_POWEROFF:
 					// Apagar
					$urlcomando = 'poweroff';
					$ogAgentNuevo = true;
					break;
				case OG_CMD_ID_REBOOT:
					// Reiniciar
					$urlcomando = 'reboot';
					$ogAgentNuevo = true;
					break;
				case OG_CMD_ID_SCRIPT:
					// Ejecutar script 
					$urlcomando = 'script';
					$ogAgentNuevo = true;
					$client = (isset ($_POST['modoejecucion']) && $_POST['modoejecucion'] != '' ) ? $_POST['modoejecucion'] : 'true';
					$paramsPost = '{"script":"'.base64_encode($script).'","client":"'.$client.'"}';
					break;
				case OG_CMD_ID_SENDMESSAGE:
					// Enviar mensaje
					$urlcomando = 'popup';
					$ogAgentNuevo = true;
					$paramsPost = '{"title":"'.$_POST['titulo'].'","message":"'.$_POST['mensaje'].'"}';
					break;
			}

			// Se envía acción al nuevo ogAgent
			if ( $ogAgentNuevo ) {
				// Send REST requests to new OGAgent clients.
				$urls = array();
				$ipsuccess = '';
				// Compose array of REST URLs.
				$auxIp = explode(';', $cadenaip);
				$auxKey = explode(";", $cadenaoga);
				$i = 0;
				foreach ($auxIp as $ip) {
					$urls[$ip]['url'] = "https://$ip:8000/opengnsys/$urlcomando";
					if (isset($auxKey[$i]))  $urls[$ip]['header'] = Array("Authorization: ".$auxKey[$i]);
					if (isset($paramsPost))  $urls[$ip]['post'] = $paramsPost;
					$i++;
				}
				// Launch concurrent requests.
				$responses = multiRequest($urls);
				// Process responses array (IP as array index).
				foreach ($responses as $ip => $resp) {
					// Check if response code is OK (200).
					if ($resp['code'] == 200) {
						$ipsuccess .= "'".$ip."',";
						$numip++;
					}
				}
				// quitamos último carácter ','
				$ipsuccess=substr($ipsuccess, 0, -1);

				// Actualizamos la cola de acciones con los que no dan error
				if ( $numip >> 0 ) {
					$fin= date ("Y-m-d H:i:s");
					$cmd->texto="UPDATE acciones SET resultado='1', estado='3', ".
						" descrinotificacion='', fechahorafin='".$fin."' ".
						" WHERE ip IN  ($ipsuccess) AND idcomando='$idcomando' ".
						" ORDER BY idaccion DESC LIMIT $numip";
					$resul=$cmd->Ejecutar();
				}
			}
			// Mostramos mensaje con resultado
			if (!$resulhidra && $numip == 0){
				echo '<SCRIPT language="javascript">';
				echo 'resultado_comando(1);'.chr(13);
				echo '</SCRIPT>';
			}
			else{
				echo '<SCRIPT language="javascript">'.chr(13);
				if ($avisoRepo) {
					echo 'resultado_comando(17);'.chr(13);
				} else {
					echo 'resultado_comando(2);'.chr(13);
				}
				echo '</SCRIPT>'.chr(13);
			}		
		}
	}
}
/*--------------------------------------------------------------------------------------------------------------------
	Switch de creación o inclusión en procedimiento
--------------------------------------------------------------------------------------------------------------------*/
if($sw_mkprocedimiento=='on' || $sw_mktarea=='on'){ 
	$resul=false;
	if($idprocedimiento==0 || $sw_mktarea=='on'){ // Nuevo procedimiento o Tarea
		if($sw_mktarea=='on' && empty($nombreprocedimiento)){ // Si tarea con inclusión de procedimiento...
			if(!empty($nombretarea))
				$nombreprocedimiento="Proc($nombretarea)";	// .. tarea nueva
			else
				$nombreprocedimiento="Proc($idtarea)";	// .. inclusión en tarea
		}
		$cmd->ParamSetValor("@descripcion",$nombreprocedimiento);
		$cmd->ParamSetValor("@idcentro",$idcentro);
		$cmd->texto="INSERT INTO procedimientos(descripcion,idcentro) VALUES (@descripcion,@idcentro)";
		$resul=$cmd->Ejecutar();
		if($resul){
			if($idprocedimiento==0) // Cambia el identificador sólo si es nuevo procedimiento 
				$idprocedimiento=$cmd->Autonumerico();
			if($sw_mktarea=='on')
				$idprocedimientotarea=$cmd->Autonumerico(); // Identificador para la tarea;	
		}
	}
	if( $idprocedimiento>0 || $sw_mktarea=='on'){ //  inclusión en procedimiento existente 
		$cmd->ParamSetValor("@idprocedimiento",$idprocedimiento);
		$cmd->ParamSetValor("@idcomando",$idcomando);
		$cmd->ParamSetValor("@ordprocedimiento",$ordprocedimiento);
		if ($idcomando == OG_CMD_ID_SCRIPT)
			$parametros=$funcion.substr(rawurldecode($atributos), 0, -2);
		else
			$parametros=$funcion.$atributos;
		$cmd->ParamSetValor("@parametros",$parametros);
		$cmd->texto="INSERT INTO procedimientos_acciones(idprocedimiento,orden,idcomando,parametros) 
				    VALUES (@idprocedimiento,@ordprocedimiento,@idcomando,@parametros)";
		$resul=$cmd->Ejecutar();
		if($sw_mktarea=='on' && $idprocedimiento!=$idprocedimientotarea){ // Si es tarea se graba para su procedimiento independiente aunque los parametros sean los mismos
			$cmd->ParamSetValor("@idprocedimiento",$idprocedimientotarea);		
			$cmd->texto="INSERT INTO procedimientos_acciones(idprocedimiento,orden,idcomando,parametros) 
					    VALUES (@idprocedimiento,@ordprocedimiento,@idcomando,@parametros)";
			$resul=$cmd->Ejecutar();
		}
	}
	if (!$resul){
		echo '<SCRIPT language="javascript">';
		echo 'resultado_comando(3);'.chr(13);
		echo '</SCRIPT>';
	}
	else{
		if($sw_mkprocedimiento=='on'){
			echo '<SCRIPT language="javascript">'.chr(13);
			echo 'resultado_comando(4);'.chr(13);
			echo '</SCRIPT>'.chr(13);
		}
	}
}	
/*--------------------------------------------------------------------------------------------------------------------
	Switch de creación o inclusión en tarea 
--------------------------------------------------------------------------------------------------------------------*/
if($sw_mktarea=='on'){ 
	$resul=false;
	if($idtarea==0){ // Nueva tarea
		$cmd->ParamSetValor("@descripcion",$nombretarea);
		$cmd->ParamSetValor("@idcentro",$idcentro);
		$cmd->ParamSetValor("@ambito",$ambito);
		$cmd->ParamSetValor("@idambito",$idambito);		
		$cmd->texto="INSERT INTO tareas(descripcion,idcentro,ambito,idambito,restrambito)
					VALUES (@descripcion,@idcentro,@ambito,@idambito,@restrambito)";
		$resul=$cmd->Ejecutar();
		if($resul)
			$idtarea=$cmd->Autonumerico();
	}
	if($idtarea>0){ //  inclusión en tarea existente 
		$cmd->ParamSetValor("@idtarea",$idtarea);
		$cmd->ParamSetValor("@idprocedimiento",$idprocedimientotarea);
		$cmd->ParamSetValor("@ordtarea",$ordtarea);
		$cmd->texto="INSERT INTO tareas_acciones(idtarea,orden,idprocedimiento) 
							VALUES (@idtarea,@ordtarea,@idprocedimiento)";
		$resul=$cmd->Ejecutar();
		//echo $cmd->texto;
	}
	if (!$resul){
		echo '<SCRIPT language="javascript">'.chr(13);
		echo 'resultado_comando(5);'.chr(13);
		echo '</SCRIPT>'.chr(13);
	}
	else{
		echo '<SCRIPT language="javascript">'.chr(13);
		echo 'resultado_comando(6);'.chr(13);
		echo '</SCRIPT>'.chr(13);
	}
}
/* Programación del comando */
if ($resul){
	if($sw_ejprg=="on" ){ 	
		echo '<SCRIPT language="javascript">'.chr(13);
		echo 'var whref="../../varios/programaciones.php?idcomando='.$idcomando.'";'.chr(13);
		echo 'whref+="&sesion='.$sesion.'&descripcioncomando='.UrlEncode($descricomando).'&tipoaccion='.$EJECUCION_COMANDO.'";'.chr(13);
		echo 'location.href=whref;';
		echo '</SCRIPT>';
	}
}
?>
</HEAD>
<BODY>
</BODY>
</HTML>	

