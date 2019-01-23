<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_tareas.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de tareas
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/SockHidra.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/tareas_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idtarea=0; 
$descripcion="";
$comentarios="";
$ambito="";
$idambito="";


$grupoid=0; 
$swc=0; // switch de cliente, esta pagina la llama el cliente a través del browser 

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["idtarea"])) $idtarea=$_POST["idtarea"];
if (isset($_POST["descripcion"])) $descripcion=$_POST["descripcion"]; 
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"]; 
if (isset($_POST["ambito"])) $ambito=$_POST["ambito"]; 
if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["identificador"])) $idtarea=$_POST["identificador"];
if (isset($_POST["swc"])) $swc=$_POST["swc"]; 

$tablanodo=""; // Arbol para nodos insertados

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}

$literal="";
switch($opcion){
	case $op_alta :
		$literal="resultado_insertar_tareas";
		break;
	case $op_modificacion:
		$literal="resultado_modificar_tareas";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_tareas";
		break;
	case $op_movida :
		$literal="resultado_mover";
		break;
	case $op_ejecucion :
		$literal="resultado_ejecutar_tareas";
		break;
	default:
		break;
}
if($opcion!=$op_movida){
	echo '<HTML>';
	echo '<HEAD>';
	echo '	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
	echo '<BODY>';
	echo '	<SCRIPT language="javascript" src="../jscripts/propiedades_tareas.js"></SCRIPT>';
	echo '<p><span style="visibility:hidden" id="arbol_nodo">'.$tablanodo.'</span></p>';
	if ($resul){
		if(empty($swc)){
			echo '<SCRIPT language="javascript">'.chr(13);
			echo 'var oHTML'.chr(13);
			echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
			echo 'o=cTBODY.item(1);'.chr(13);
			if ($opcion==$op_alta )
				echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idtarea.",o.innerHTML);".chr(13);
			else{
				echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
			}
			echo '</SCRIPT>';
		}
		else{
			echo '<SCRIPT language="javascript">'.chr(13);
			echo 'alert("El item se ha ejecutado correctamente");'.chr(13);
			echo 'location.href="../varios/menucliente.php?iph='.$_SESSION["ogCliente"].'";'.chr(13);
			echo '</SCRIPT>';
		}		
	}
	else{
		if(empty($swc)){
			echo '<SCRIPT language="javascript">';
			echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idtarea.")";
			echo '</SCRIPT>';
		}
		else{
			echo '<SCRIPT language="javascript">'.chr(13);
			echo 'alert("***ATENCIÓN:El item NO se ha podido ejecutar");'.chr(13);
			echo 'location.href="../varios/menucliente.php?iph='.$_SESSION["ogCliente"].'";'.chr(13);
			echo '</SCRIPT>';
		}
	}
	echo '</BODY>';
	echo '</HTML>';	
}
else{
	if ($resul)
				echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
	else
			echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idtarea.")";
}
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla tareas
________________________________________________________________________________________________________*/
function Gestiona(){
	global $EJECUCION_TAREA;

	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idtarea;
	global	$descripcion;
	global	$comentarios;
	global	$ambito;
	global	$idambito;	
	global	$grupoid;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;
	global	$op_ejecucion;

	global	$tablanodo;

	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@idtarea",$idtarea,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@comentarios",$comentarios,0);
	$cmd->CreaParametro("@ambito",$ambito,1);
	$cmd->CreaParametro("@idambito",$idambito,1);
	$cmd->CreaParametro("@grupoid",$grupoid,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO tareas (descripcion,comentarios,ambito,idambito,idcentro,grupoid)
						VALUES (@descripcion,@comentarios,@ambito,@idambito,@idcentro,@grupoid)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idtarea=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_tareas($idtarea,$descripcion,"");
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE tareas SET descripcion=@descripcion,comentarios=@comentarios,
							ambito=@ambito,idambito=@idambito
							WHERE idtarea=@idtarea";
			$resul=$cmd->Ejecutar();
			//echo $cmd->texto;
			break;
		case $op_eliminacion :
			$resul=EliminaTareas($cmd,$idtarea,"idtarea");	
			break;
		case $op_movida :
			$cmd->texto="UPDATE tareas SET  grupoid=@grupoid WHERE idtarea=@idtarea";
			$resul=$cmd->Ejecutar();
			break;
		case $op_ejecucion :
					$resul=EjecutandoTareas();
			break;
		default:
			break;
	}
	return($resul);
}
/*________________________________________________________________________________________________________
	Crea un arbol XML para el nuevo nodo insertado 
________________________________________________________________________________________________________*/
function SubarbolXML_tareas($idtarea,$descripcion,$urlimg){
		global $LITAMBITO_TAREAS;
		$cadenaXML='<TAREA';
		// Atributos`
		if	($urlimg!="")
			$cadenaXML.=' imagenodo="'.$urlimg;
		else
			$cadenaXML.=' imagenodo="../images/iconos/tareas.gif"';
		$cadenaXML.=' infonodo="'.$descripcion;
		$cadenaXML.=' nodoid='.$LITAMBITO_TAREAS.'-'.$idtarea;
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_TAREAS."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</TAREA>';
		return($cadenaXML);
}
//________________________________________________________________________________________________________
function EjecutandoTareas(){

	global $EJECUCION_COMANDO;
	global $EJECUCION_TAREA;
	global $PROCESOS;
	global $ACCION_INICIADA;
	global $ACCION_SINERRORES; 
	global $idcentro;
	global $servidorhidra;
	global $hidraport;
	global	$idtarea;
	global	$cmd;

	$shidra=new SockHidra($servidorhidra,$hidraport); 

	$ambitarea="";
	$paramtarea="cmd=";

	$tbComandos="";
	$tabla_comandos="";
	$cont_comandos=0;

	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM tareas_comandos WHERE idtarea=".$idtarea;
	$cmd->texto.=" ORDER by tareas_comandos.orden";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	// Recorre tareas-comandos
	while (!$rs->EOF){
			$tbComandos["idcomando"]=$rs->campos["idcomando"];
			$tbComandos["ambito"]=$rs->campos["ambito"];
			$tbComandos["idambito"]=$rs->campos["idambito"];
			$tbComandos["parametros"]=$rs->campos["parametros"];
			$tbComandos["idnotificador"]=$rs->campos["idtareacomando"];
			$tabla_comandos[$cont_comandos]=$tbComandos;
			$cont_comandos++;

			$ambitarea.=$rs->campos["ambito"].":".$rs->campos["idambito"].";";
			$paramtarea.=$rs->campos["idtareacomando"].";";
			
			$rs->Siguiente();
	}
	$rs->Cerrar();

	$ambitarea=substr($ambitarea,0,strlen($ambitarea)-1); // Quita la coma final
	$paramtarea=substr($paramtarea,0,strlen($paramtarea)-1); // Quita la coma final

	//Creación parametros para inserción
	$cmd->CreaParametro("@tipoaccion","",1);
	$cmd->CreaParametro("@idtipoaccion",0,1);
	$cmd->CreaParametro("@cateaccion",$PROCESOS,1);
	$cmd->CreaParametro("@ambito",0,1);
	$cmd->CreaParametro("@idambito",0,1);
	$cmd->CreaParametro("@ambitskwrk","",0);
	$cmd->CreaParametro("@fechahorareg","",0);
	$cmd->CreaParametro("@estado",$ACCION_INICIADA,0);
	$cmd->CreaParametro("@resultado",$ACCION_SINRESULTADO,0);
	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@parametros","",0);	
	$cmd->CreaParametro("@accionid",0,1);	
	$cmd->CreaParametro("@idnotificador",0,1);	

	// Insertar accion:tarea --------------------------------------------------------------------
	$cmd->ParamSetValor("@tipoaccion",$EJECUCION_TAREA);
	$cmd->ParamSetValor("@idtipoaccion",$idtarea);
	$cmd->ParamSetValor("@ambito",0);
	$cmd->ParamSetValor("@idambito",0);
	$cmd->ParamSetValor("@ambitskwrk",$ambitarea);
	$cmd->ParamSetValor("@fechahorareg",date("d/m/y H:i:s"));
	$cmd->ParamSetValor("@parametros",$paramtarea);
	$cmd->texto="INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,ambitskwrk,fechahorareg,estado,resultado,idcentro,parametros,accionid,idnotificador) VALUES (@tipoaccion,@idtipoaccion,@cateaccion,@ambito,@idambito,@ambitskwrk,@fechahorareg,@estado,@resultado,@idcentro,@parametros,0,0)";
	$resul=$cmd->Ejecutar();
	if(!$resul) return(false);

	$accionid=$cmd->Autonumerico(); // Toma identificador dela acción

	// Insertar acciones:comandos
	$shidra=new SockHidra($servidorhidra,$hidraport); 
	for ($i=0;$i<$cont_comandos;$i++){
		$tbComandos=$tabla_comandos[$i];
		$cmd->ParamSetValor("@tipoaccion",$EJECUCION_COMANDO);
		$cmd->ParamSetValor("@idtipoaccion",$tbComandos["idcomando"]);
		$cmd->ParamSetValor("@ambito",$tbComandos["ambito"]);
		$cmd->ParamSetValor("@idambito",$tbComandos["idambito"]);
		$cmd->ParamSetValor("@ambitskwrk","");
		$cmd->ParamSetValor("@fechahorareg",date("d/m/y H:i:s"));
		$cmd->ParamSetValor("@parametros",$tbComandos["parametros"]);
		$cmd->ParamSetValor("@accionid",$accionid);
		$cmd->ParamSetValor("@idnotificador",$tbComandos["idnotificador"]);
		$cmd->texto="INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,ambitskwrk,fechahorareg,estado,resultado,idcentro,parametros,accionid,idnotificador) VALUES (@tipoaccion,@idtipoaccion,@cateaccion,@ambito,@idambito,@ambitskwrk,@fechahorareg,@estado,@resultado,@idcentro,@parametros,@accionid,@idnotificador)";
		$resul=$cmd->Ejecutar();
		if(!$resul) return(false);
		$tbComandos["parametros"].="ids=".$cmd->Autonumerico().chr(13);

		if ($shidra->conectar()){ // Se ha establecido la conexión con el servidor hidra
			$shidra->envia_comando($tbComandos["parametros"]);
			$shidra->desconectar();
		}
	}
	return(true);
}

