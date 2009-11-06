<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_trabajos.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de trabajos
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/SockHidra.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/trabajos_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idtrabajo=0; 
$descripcion="";
$comentarios="";
$grupoid=0; 
$swc=0; // switch de cliente, esta pagina la llama el cliente a través del browser 

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["idtrabajo"])) $idtrabajo=$_GET["idtrabajo"];
if (isset($_GET["descripcion"])) $descripcion=$_GET["descripcion"]; 
if (isset($_GET["comentarios"])) $comentarios=$_GET["comentarios"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["identificador"])) $idtrabajo=$_GET["identificador"];
if (isset($_GET["swc"])) $swc=$_GET["swc"]; 

$tablanodo=""; // Arbol para nodos insertados

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
	$literal="";
	switch($opcion){
		case $op_alta :
			$literal="resultado_insertar_trabajos";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_trabajos";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_trabajos";
			break;
		case $op_movida :
			$literal="resultado_mover";
			break;
		case $op_ejecucion :
			$literal="resultado_ejecutar_trabajos";
		default:
			break;
	}
echo '<p><span id="arbol_nodo">'.$tablanodo.'</span></p>';
if ($resul){
	if(empty($swc)){
		echo '<SCRIPT language="javascript">'.chr(13);
		echo 'var oHTML'.chr(13);
		echo 'var cTBODY=document.all.tags("TBODY");'.chr(13);
		echo 'o=cTBODY.item(1);'.chr(13);
		if ($opcion==$op_alta )
			echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idtrabajo.",o.innerHTML);".chr(13);
		else
			echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idtrabajo.");".chr(13);
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
		echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idtrabajo.")";
		echo '</SCRIPT>';
	}
	else{
		echo '<SCRIPT language="javascript">'.chr(13);
		echo 'alert("***ATENCIÓN:El item NO se ha podido ejecutar");'.chr(13);
		echo 'location.href="../varios/menucliente.php?iph='.$_SESSION["ogCliente"].'";'.chr(13);
		echo '</SCRIPT>';
	}
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla tareas
________________________________________________________________________________________________________*/
function Gestiona(){

	global $EJECUCION_TRABAJO;

	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idtrabajo;
	global	$descripcion;
	global	$comentarios;
	global	$grupoid;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;
	global	$op_ejecucion;

	global	$tablanodo;

	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@idtrabajo",$idtrabajo,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@comentarios",$comentarios,0);
	$cmd->CreaParametro("@grupoid",$grupoid,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO trabajos (descripcion,comentarios,idcentro,grupoid) VALUES (@descripcion,@comentarios,@idcentro,@grupoid)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idtrabajo=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_trabajos($idtrabajo,$descripcion,"");
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE trabajos SET descripcion=@descripcion,comentarios=@comentarios WHERE idtrabajo=@idtrabajo";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaTrabajos($cmd,$idtrabajo,"idtrabajo");
			break;
		case $op_movida :
			$cmd->texto="UPDATE trabajos SET  grupoid=@grupoid WHERE idtrabajo=@idtrabajo";
			$resul=$cmd->Ejecutar();
			break;
		case $op_ejecucion :
			$resul=ejecutando_trabajos();
			break;
		default:
			break;
	}
	return($resul);
}
/*________________________________________________________________________________________________________
	Crea un arbol XML para el nuevo nodo insertado 
________________________________________________________________________________________________________*/
function SubarbolXML_trabajos($idtrabajo,$descripcion,$urlimg){
		global $LITAMBITO_TRABAJOS;
		$cadenaXML='<TRABAJO';
		// Atributos
		if	($urlimg!="")
			$cadenaXML.=' imagenodo="'.$urlimg;
		else
			$cadenaXML.=' imagenodo="../images/iconos/trabajos.gif"';
		$cadenaXML.=' infonodo="'.$descripcion;
		$cadenaXML.=' nodoid='.$LITAMBITO_TRABAJOS.'-'.$idtrabajo;
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_TRABAJOS."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</TRABAJO>';
		return($cadenaXML);
}
//________________________________________________________________________________________________________
function ejecutando_trabajos(){
	global $EJECUCION_COMANDO;
	global $EJECUCION_TAREA;
	global $EJECUCION_TRABAJO;
	
	global $PROCESOS;

	global $ACCION_INICIADA;
	global $ACCION_SINERRORES; 

	global $idcentro;
	global $idtrabajo;
	global $cmd;

	$ambitrabajo="";
	$paramtrabajo="tsk=";

	$tbTareas="";
	$tabla_tareas="";
	$cont_tareas=0;

	$rs=new Recordset; 
	// Recorre trabajos_tareas
	$cmd->texto="SELECT trabajos_tareas.idtrabajotarea,trabajos_tareas.idtarea,trabajos_tareas.ambitskwrk FROM trabajos_tareas INNER JOIN tareas ON  trabajos_tareas.idtarea=tareas.idtarea WHERE trabajos_tareas.idtrabajo=".$idtrabajo;
	$cmd->texto.=" ORDER BY trabajos_tareas.orden";

	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if ($rs->EOF) return(true); // Error al abrir recordset
	$rs->Primero(); 
	// Recorre trabajos-tareas
	while (!$rs->EOF){
			$tbTareas["idnotificador"]=$rs->campos["idtrabajotarea"];
			$tbTareas["idtarea"]=$rs->campos["idtarea"];
			$tabla_tareas[$cont_tareas]=$tbTareas;
			$cont_tareas++;

			$ambitrabajo.=$rs->campos["ambitskwrk"].";";
			$paramtrabajo.=$rs->campos["idtrabajotarea"].";";

			$rs->Siguiente();
	}
	$rs->Cerrar();

	$ambitrabajo=substr($ambitrabajo,0,strlen($ambitrabajo)-1); // Quita la coma final
	$paramtrabajo=substr($paramtrabajo,0,strlen($paramtrabajo)-1); // Quita la coma final

	//Creación parametros para inserción  --------------------------------------------------------------------
	$cmd->CreaParametro("@tipoaccion","",1);
	$cmd->CreaParametro("@idtipoaccion",0,1);
	$cmd->CreaParametro("@cateaccion",$PROCESOS,1);
	$cmd->CreaParametro("@ambito",0,1);
	$cmd->CreaParametro("@idambito",0,1);
	$cmd->CreaParametro("@ambitskwrk","",0);
	$cmd->CreaParametro("@fechahorareg","",0);
	$cmd->CreaParametro("@estado",$ACCION_INICIADA,0);
	$cmd->CreaParametro("@resultado",$ACCION_SINERRORES,0);
	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@parametros","",0);	
	$cmd->CreaParametro("@accionid",0,1);	
	$cmd->CreaParametro("@idnotificador",0,1);	

	// Insertar accion:trabajo --------------------------------------------------------------------
	$cmd->ParamSetValor("@tipoaccion",$EJECUCION_TRABAJO);
	$cmd->ParamSetValor("@idtipoaccion",$idtrabajo);
	$cmd->ParamSetValor("@ambito",0);
	$cmd->ParamSetValor("@idambito",0);
	$cmd->ParamSetValor("@ambitskwrk",$ambitrabajo);
	$cmd->ParamSetValor("@fechahorareg",date("d/m/y H:i:s"));
	$cmd->ParamSetValor("@parametros",$paramtrabajo);
	$cmd->texto="INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,ambitskwrk,fechahorareg,estado,resultado,idcentro,parametros,accionid,idnotificador) VALUES (@tipoaccion,@idtipoaccion,@cateaccion,@ambito,@idambito,@ambitskwrk,@fechahorareg,@estado,@resultado,@idcentro,@parametros,0,0)";
	$resul=$cmd->Ejecutar();
	if(!$resul) return(false);

	$accionid=$cmd->Autonumerico(); // Toma identificador dela acción

	// Insertar acciones:tareas --------------------------------------------------------------------
	for ($i=0;$i<$cont_tareas;$i++){
		$tbTareas=$tabla_tareas[$i];
		$resul=EjecutandoTareas($tbTareas["idtarea"],$accionid,$tbTareas["idnotificador"]);
		if(!$resul) return(false);
	}
	return(true);
}
//________________________________________________________________________________________________________
function EjecutandoTareas($idtarea,$accionid,$idnotificador){

	global $EJECUCION_COMANDO;
	global $EJECUCION_TAREA;
	global $PROCESOS;

	global $ACCION_INICIADA;
	global $ACCION_SINERRORES; 

	global $idcentro;
	global $servidorhidra;
	global $hidraport;
	global $cmd;

	$shidra=new SockHidra($servidorhidra,$hidraport); 

	$ambitarea="";
	$paramtarea="cmd=";

	$tbComandos="";
	$tabla_comandos="";
	$cont_comandos=0;

	// Recorre tareas-comandos
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM tareas_comandos WHERE idtarea=".$idtarea;
	$cmd->texto.=" ORDER by tareas_comandos.orden";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
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

	// Insertar accion:tarea --------------------------------------------------------------------
	$cmd->ParamSetValor("@tipoaccion",$EJECUCION_TAREA);
	$cmd->ParamSetValor("@idtipoaccion",$idtarea);
	$cmd->ParamSetValor("@ambito",0);
	$cmd->ParamSetValor("@idambito",0);
	$cmd->ParamSetValor("@ambitskwrk",$ambitarea);
	$cmd->ParamSetValor("@fechahorareg",date("d/m/y H:i:s"));
	$cmd->ParamSetValor("@parametros",$paramtarea);
	$cmd->ParamSetValor("@accionid",$accionid);
	$cmd->ParamSetValor("@idnotificador",$idnotificador);

	$cmd->texto="INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,ambitskwrk,fechahorareg,estado,resultado,idcentro,parametros,accionid,idnotificador) VALUES (@tipoaccion,@idtipoaccion,@cateaccion,@ambito,@idambito,@ambitskwrk,@fechahorareg,@estado,@resultado,@idcentro,@parametros,@accionid,@idnotificador)";
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
?>
