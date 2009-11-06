<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: colasacciones.php
// Descripción : 
//		Visualiza las acciones pendientes y finalizadas con los resultados de estatus y horas de inicio y finalización
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/InvFecha.php");
include_once("../clases/XmlPhp.php");
include_once("../includes/HTMLCTEMULSELECT.php");
include_once("../includes/TomanDatos.php");
include_once("../includes/TomaDato.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/colasacciones_".$idioma.".php");
//________________________________________________________________________________________________________
$ambito=""; 
$idambito=0;
$nombreambito="";

$fechainicio="";
$fechafin="";
$horainicio="";
$horafin="";
$tiposacciones="";
$estados="";
$resultados="";
$porcendesde=0;
$porcenhasta=100;
$idcmdtskwrk=""; // Identificador del comando , la tarea o el trabajo
$codtipoaccion=""; // Identificador del tipo de acción: comando , tarea o trabajo 
$idambcmdtskwrk=""; // Identificador del ambito al que se aplica el comando , la tarea o el trabajo 

$accionid=0;
$idTipoAccion=0;
$TipoAccion=0;
$NombreTipoAccion="";

if (isset($_GET["ambito"]))	$ambito=$_GET["ambito"]; 
if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["nombreambito"])) $nombreambito=$_GET["nombreambito"]; 
if (isset($_GET["tipocola"])) $tipocola=$_GET["tipocola"]; 

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
// Si entra por primera vez (criterios por defecto)
if($ambito!="" && $idambito!="" && $nombreambito!="" && $tipocola!=""){ 
	$wfechainicio=mktime(0, 0, 0, date("m")  , date("d")-3, date("Y")); // Acciones desde un mes anterior
	$wfechafin=mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
	$fechainicio=date("d/m/Y",$wfechainicio);
	$fechafin=date("d/m/Y ",$wfechafin);

	// Tipos de acciones default
	switch($tipocola){
		case 0:
			$wtiposacciones[0]=$EJECUCION_COMANDO;
			$wtiposacciones[1]=$EJECUCION_TAREA;
			$wtiposacciones[2]=$EJECUCION_TRABAJO;

			$tiposacciones=$EJECUCION_COMANDO."=".$LITEJECUCION_COMANDO.";";
			$tiposacciones.=$EJECUCION_TAREA."=".$LITEJECUCION_TAREA.";";
			$tiposacciones.=$EJECUCION_TRABAJO."=".$LITEJECUCION_TRABAJO.";";
			break;
		case $EJECUCION_COMANDO:
			$wtiposacciones[0]=$EJECUCION_COMANDO;
			$tiposacciones=$EJECUCION_COMANDO."=".$LITEJECUCION_COMANDO.";";	
			break;
		case $EJECUCION_TAREA:
			$wtiposacciones[0]=$EJECUCION_TAREA;
			$tiposacciones=$EJECUCION_TAREA."=".$LITEJECUCION_TAREA.";";
			break;
		case $EJECUCION_TRABAJO:
			$wtiposacciones[0]=$EJECUCION_TRABAJO;
			$tiposacciones=$EJECUCION_TRABAJO."=".$LITEJECUCION_TRABAJO.";";
			break;
	}
	// Estados default
	$westados[0]=$ACCION_DETENIDA;
	$westados[1]=$ACCION_INICIADA;
	$westados[2]=$ACCION_FINALIZADA;
	$estados=$ACCION_DETENIDA."=".$LITACCION_DETENIDA.";";
	$estados.=$ACCION_INICIADA."=".$LITACCION_INICIADA.";";
	$estados.=$ACCION_FINALIZADA."=".$LITACCION_FINALIZADA.";";

	// Resultados default
	$wresultados[0]=$ACCION_EXITOSA;
	$wresultados[1]=$ACCION_FALLIDA;
	//$resultados[2]=$ACCION_TERMINADA;
	//$resultados[3]=$ACCION_ABORTADA;
	$wresultados[2]=$ACCION_SINERRORES;
	$wresultados[3]=$ACCION_CONERRORES;

	$resultados=$ACCION_EXITOSA."=".$LITACCION_EXITOSA.";";
	$resultados.=$ACCION_FALLIDA."=".$LITACCION_FALLIDA.";";
	$resultados.=$ACCION_SINERRORES."=".$LITACCION_SINERRORES.";";
	$resultados.=$ACCION_CONERRORES."=".$LITACCION_CONERRORES.";";
}
if (isset($_POST["ambito"]))	$ambito=$_POST["ambito"]; 
if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
if (isset($_POST["nombreambito"])) $nombreambito=$_POST["nombreambito"]; 

if (isset($_POST["fechainicio"])) $fechainicio=$_POST["fechainicio"]; 
if (isset($_POST["fechafin"])) $fechafin=$_POST["fechafin"]; 
if (isset($_POST["horainicio"])) $horainicio=$_POST["horainicio"]; 
if (isset($_POST["horafin"])) $horafin=$_POST["horafin"]; 

if (isset($_POST["tiposacciones"])) {
	$tiposacciones=$_POST["tiposacciones"]; 
	$auxP=split(";",$tiposacciones);
	$cont=0;
	for ($i=0;$i<sizeof($auxP)-1;$i++){
		$dualparam=split("=",$auxP[$i]);
		$wtiposacciones[$cont++]=$dualparam[0];
	}
}
if (isset($_POST["estados"])){
	$estados=$_POST["estados"]; 
	$auxP=split(";",$estados);
	$cont=0;
	for ($i=0;$i<sizeof($auxP)-1;$i++){
		$dualparam=split("=",$auxP[$i]);
		$westados[$cont++]=$dualparam[0];
	}
}
if (isset($_POST["resultados"])){
	$resultados=$_POST["resultados"]; 
	$auxP=split(";",$resultados);
	$cont=0;
	for ($i=0;$i<sizeof($auxP)-1;$i++){
		$dualparam=split("=",$auxP[$i]);
		$wresultados[$cont++]=$dualparam[0];
	}
}
if (isset($_POST["porcendesde"])) $porcendesde=$_POST["porcendesde"]; 
if (isset($_POST["porcenhasta"])) $porcenhasta=$_POST["porcenhasta"]; 

if($porcendesde=="") $porcendesde=0;
if($porcenhasta=="") $porcenhasta=100;

if (isset($_POST["idcmdtskwrk"])) $idcmdtskwrk=$_POST["idcmdtskwrk"]; 
if (isset($_POST["codtipoaccion"])) $codtipoaccion=$_POST["codtipoaccion"]; 
if (isset($_POST["idambcmdtskwrk"])) $idambcmdtskwrk=$_POST["idambcmdtskwrk"]; 

if (isset($_POST["accionid"])) $accionid=$_POST["accionid"]; 
if (isset($_POST["idTipoAccion"])) $idTipoAccion=$_POST["idTipoAccion"]; 
if (isset($_POST["TipoAccion"])) $TipoAccion=$_POST["TipoAccion"]; 
if (isset($_POST["NombreTipoAccion"])) $NombreTipoAccion=$_POST["NombreTipoAccion"]; 
//________________________________________________________________________________________________________
// Clausula WHERE ( construcción )
$ClausulaWhere="";

// Cuestion de fechas 
$WhereFechaInicio="";
$WhereFechaFin="";
$WhereFechaReg="";
if($fechainicio!="")
	$WhereFechaInicio="acciones.fechahorareg>='".InvFecha($fechainicio).' ' .$horainicio."'";
if($fechafin!="")
	$WhereFechaFin.=" acciones.fechahorareg<='".InvFecha($fechafin).' ' .$horafin."'";
if($WhereFechaInicio!=""){
	if($WhereFechaFin!="")
		$WhereFechaReg=" (".$WhereFechaInicio." AND ".$WhereFechaFin.") ";
	else
		$WhereFechaReg=" (".$WhereFechaInicio.") ";
}
else{
		if($WhereFechaFin!="")
			$WhereFechaReg=" (".$WhereFechaFin.") ";
}
$ClausulaWhere.=$WhereFechaReg;
//________________________________________________________________________________________________________
// Cuestion tipos de acciones
$WhereTiposAcciones="";
for($i=0;$i<sizeof($wtiposacciones);$i++){
		if (isset($wtiposacciones[$i]))
			$WhereTiposAcciones.=" acciones.tipoaccion=".$wtiposacciones[$i]." OR ";
}
if($WhereTiposAcciones!=""){
	$WhereTiposAcciones=substr($WhereTiposAcciones,0,strlen($WhereTiposAcciones)-3); 
	$ClausulaWhere.=" AND (".$WhereTiposAcciones.")";
}
//________________________________________________________________________________________________________
// Cuestion estados
$WhereEstados="";
for($i=0;$i<sizeof($westados);$i++){
	if (isset($westados[$i]))
		$WhereEstados.=" acciones.estado=".$westados[$i]." OR ";
}
if($WhereEstados!=""){
	$WhereEstados=substr($WhereEstados,0,strlen($WhereEstados)-3); 
	$ClausulaWhere.=" AND (".$WhereEstados.")";
}
//________________________________________________________________________________________________________
// Cuestion resultados
$WhereResultados="";
for($i=0;$i<sizeof($wresultados);$i++){
		if (isset($wresultados[$i]))
			$WhereResultados.=" acciones.resultado=".$wresultados[$i]." OR ";
}
if($WhereResultados!=""){
	$WhereResultados=substr($WhereResultados,0,strlen($WhereResultados)-3); // Quita la coma
	$ClausulaWhere.=" AND (".$WhereResultados.")";
}
//________________________________________________________________________________________________________
// Cuestion identificador del comando la tarea o el trabajo implicado en la acción
$Wherecmdtskwrk="";
if($idcmdtskwrk!="" && $codtipoaccion!="" ){
	$Wherecmdtskwrk='acciones.idtipoaccion='.$idcmdtskwrk.' AND acciones.tipoaccion='.$codtipoaccion;
	$ClausulaWhere.=" AND (".$Wherecmdtskwrk.")";
}
//________________________________________________________________________________________________________
// Cuestion identificador del ambito al que se aplica el comando la tarea o el trabajo implicado en la acción
$Whereambcmdtskwrk="";
if($idambcmdtskwrk!=""){
	$Whereambcmdtskwrk='acciones.ambito='.$idambcmdtskwrk;
	$ClausulaWhere.=" AND (".$Whereambcmdtskwrk.")";
}
//________________________________________________________________________________________________________
// Cuestion identificador del Centro que ha ejecutado la acción
$WhereCentroAccion="";
$WhereCentroAccion='acciones.idcentro='.$idcentro;
$ClausulaWhere.=" AND (".$WhereCentroAccion.")";

//________________________________________________________________________________________________________
// Cuestion accionid ( acciones que son hijas de otras acciones (Tarea-comando,Trabajo-Tarea)
$WhereAccionid="";
$WhereAccionid='acciones.accionid='.$accionid;
$ClausulaWhere.=" AND (".$WhereAccionid.")";
//________________________________________________________________________________________________________
//echo $ClausulaWhere; 
$mulaccion=""; // Para opciones de multiples acciones

// Captura de parametros de tareas y trabajos
$tsk_ambito="";
$tsk_idambito="";
$tsk_nombreambito="";
$tsk_fechainicio="";
$tsk_fechafin="";
$tsk_horainicio="";
$tsk_horafin="";
$tsk_tiposacciones="";
$tsk_estados="";
$tsk_resultados="";
$tsk_porcendesde="";
$tsk_porcenhasta="";

$tsk_idcmdtskwrk="";
$tsk_codtipoaccion="";
$tsk_idambcmdtskwrk="";

$tsk_accionid="";
$tsk_idTipoAccion="";
$tsk_TipoAccion="";
$tsk_NombreTipoAccion="";

// Trabajos
$wrk_ambito="";
$wrk_idambito="";
$wrk_nombreambito="";
$wrk_fechainicio="";
$wrk_fechafin="";
$wrk_horainicio="";
$wrk_horafin="";
$wrk_tiposacciones="";
$wrk_estados="";
$wrk_resultados="";
$wrk_porcendesde="";
$wrk_porcenhasta="";

$wrk_idcmdtskwrk="";
$wrk_codtipoaccion="";
$wrk_idambcmdtskwrk="";

$wrk_accionid="";
$wrk_idTipoAccion="";
$wrk_TipoAccion="";
$wrk_NombreTipoAccion="";

// Tareas
if (isset($_POST["tsk_ambito"])) $tsk_ambito=$_POST["tsk_ambito"]; 
if (isset($_POST["tsk_idambito"])) $tsk_idambito=$_POST["tsk_idambito"]; 
if (isset($_POST["tsk_nombreambito"])) $tsk_nombreambito=$_POST["tsk_nombreambito"]; 

if (isset($_POST["tsk_fechainicio"])) $tsk_fechainicio=$_POST["tsk_fechainicio"]; 
if (isset($_POST["tsk_fechafin"])) $tsk_fechafin=$_POST["tsk_fechafin"]; 
if (isset($_POST["tsk_horainicio"])) $tsk_horainicio=$_POST["tsk_horainicio"]; 
if (isset($_POST["tsk_horafin"])) $tsk_horafin=$_POST["tsk_horafin"]; 

if (isset($_POST["tsk_tiposacciones"])) $tsk_tiposacciones=$_POST["tsk_tiposacciones"]; 
if (isset($_POST["tsk_estados"])) $tsk_estados=$_POST["tsk_estados"]; 
if (isset($_POST["tsk_resultados"]))	$tsk_resultados=$_POST["tsk_resultados"]; 

if (isset($_POST["tsk_porcendesde"])) $tsk_porcendesde=$_POST["tsk_porcendesde"]; 
if (isset($_POST["tsk_porcenhasta"])) $tsk_porcenhasta=$_POST["tsk_porcenhasta"]; 

if (isset($_POST["tsk_idcmdtskwrk"])) $tsk_idcmdtskwrk=$_POST["tsk_idcmdtskwrk"]; 
if (isset($_POST["tsk_codtipoaccion"])) $tsk_codtipoaccion=$_POST["tsk_codtipoaccion"]; 
if (isset($_POST["tsk_idambcmdtskwrk"])) $tsk_idambcmdtskwrk=$_POST["tsk_idambcmdtskwrk"]; 

if (isset($_POST["tsk_accionid"])) $tsk_accionid=$_POST["tsk_accionid"]; 
if (isset($_POST["tsk_idTipoAccion"])) $tsk_idTipoAccion=$_POST["tsk_idTipoAccion"]; 
if (isset($_POST["tsk_TipoAccion"])) $tsk_TipoAccion=$_POST["tsk_TipoAccion"]; 
if (isset($_POST["tsk_NombreTipoAccion"])) $tsk_NombreTipoAccion=$_POST["tsk_NombreTipoAccion"]; 

// Trabajos
if (isset($_POST["wrk_ambito"])) $wrk_ambito=$_POST["wrk_ambito"]; 
if (isset($_POST["wrk_idambito"])) $wrk_idambito=$_POST["wrk_idambito"]; 
if (isset($_POST["wrk_nombreambito"])) $wrk_nombreambito=$_POST["wrk_nombreambito"]; 

if (isset($_POST["wrk_fechainicio"])) $wrk_fechainicio=$_POST["wrk_fechainicio"]; 
if (isset($_POST["wrk_fechafin"])) $wrk_fechafin=$_POST["wrk_fechafin"]; 
if (isset($_POST["wrk_horainicio"])) $wrk_horainicio=$_POST["wrk_horainicio"]; 
if (isset($_POST["wrk_horafin"])) $wrk_horafin=$_POST["wrk_horafin"]; 

if (isset($_POST["wrk_tiposacciones"])) $wrk_tiposacciones=$_POST["wrk_tiposacciones"]; 
if (isset($_POST["wrk_estados"])) $wrk_estados=$_POST["wrk_estados"]; 
if (isset($_POST["wrk_resultados"])) $wrk_resultados=$_POST["wrk_resultados"]; 

if (isset($_POST["wrk_porcendesde"])) $wrk_porcendesde=$_POST["wrk_porcendesde"]; 
if (isset($_POST["wrk_porcenhasta"])) $wrk_porcenhasta=$_POST["wrk_porcenhasta"]; 

if (isset($_POST["wrk_idcmdtskwrk"])) $wrk_idcmdtskwrk=$_POST["wrk_idcmdtskwrk"]; 
if (isset($_POST["wrk_codtipoaccion"])) $wrk_codtipoaccion=$_POST["wrk_codtipoaccion"]; 
if (isset($_POST["wrk_idambcmdtskwrk"])) $wrk_idambcmdtskwrk=$_POST["wrk_idambcmdtskwrk"]; 

if (isset($_POST["wrk_accionid"])) $wrk_accionid=$_POST["wrk_accionid"]; 
if (isset($_POST["wrk_idTipoAccion"])) $wrk_idTipoAccion=$_POST["wrk_idTipoAccion"]; 
if (isset($_POST["wrk_TipoAccion"])) $wrk_TipoAccion=$_POST["wrk_TipoAccion"]; 
if (isset($_POST["wrk_NombreTipoAccion"])) $wrk_NombreTipoAccion=$_POST["wrk_NombreTipoAccion"]; 
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/colasacciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/colasacciones_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
<?
$flotante=new MenuContextual(); // Crea objeto MenuContextual
$XMLcontextual=ContextualXMLComando(); // Crea contextual de las acciones
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=ContextualXMLTarea(); // Crea contextual de las acciones
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=ContextualXMLTrabajo(); // Crea contextual de las acciones
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=ContextualXMLNotificaciones(); // Crea contextual de las notificaciones
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=ContextualXMLModifAcciones(); // Crea subcontextual de las notificaciones
echo $flotante->CreaMenuContextual($XMLcontextual); 
switch($ambito){
		case $AMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito=$TbMsg[24];
			break;
		case $AMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[25];
			break;
		case $AMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito=$TbMsg[26];
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[27];
			break;
		case $AMBITO_ORDENADORES :
			$urlimg='../images/iconos/ordenador.gif';
			$textambito=$TbMsg[28];
			break;
}
$tabla_ambitosacciones=""; // Tabla  para localizar ambitos  e identificadores con acciones sobre ellos
$cont_ambitosacciones=0; // Contador de la tabla 

$tabla_parametros=""; // Tabla  para localizar parametros
$cont_parametros=0; // Contador de la tabla 
CreaTablaParametros($cmd); // Crea tabla  especificaciones de lparametros en memoria 

echo '<p align=center class=cabeceras><img src="../images/iconos/acciones.gif">&nbsp;'.$TbMsg[0].'&nbsp;</p>';
echo '<FORM name="fdatos" action="colasacciones.php" method="post">'.chr(13);
// Campos ocultos 
echo '<INPUT type=hidden name=ambito value="'.$ambito.'">';
echo '<INPUT type=hidden name=idambito value="'.$idambito.'">';
echo '<INPUT type=hidden name=nombreambito value="'.$nombreambito.'">';

echo '<INPUT type=hidden name=idcmdtskwrk value="'.$idcmdtskwrk.'">';
echo '<INPUT type=hidden name=codtipoaccion value="'.$codtipoaccion.'">';
echo '<INPUT type=hidden name=idambcmdtskwrk value="'.$idambcmdtskwrk.'">';

echo '<INPUT type=hidden name=tiposacciones value="'.$tiposacciones.'">';
echo '<INPUT type=hidden name=estados value="'.$estados.'">';
echo '<INPUT type=hidden name=resultados value="'.$resultados.'">';

echo '<INPUT type=hidden name=accionid value="'.$accionid.'">';
echo '<INPUT type=hidden name=idTipoAccion value="'.$idTipoAccion.'">';
echo '<INPUT type=hidden name=TipoAccion value="'.$TipoAccion.'">';
echo '<INPUT type=hidden name=NombreTipoAccion value="'.$NombreTipoAccion.'">';

// Parametros Tarea padre
echo '<INPUT type=hidden name=tsk_ambito  value="'.$tsk_ambito.'">';
echo '<INPUT type=hidden name=tsk_idambito  value="'.$tsk_idambito.'">';
echo '<INPUT type=hidden name=tsk_nombreambito value="'.$tsk_nombreambito.'">';
echo '<INPUT type=hidden name=tsk_fechainicio value="'.$tsk_fechainicio.'">';
echo '<INPUT type=hidden name=tsk_fechafin value="'.$tsk_fechafin.'">';
echo '<INPUT type=hidden name=tsk_horainicio value="'.$tsk_horainicio.'">';
echo '<INPUT type=hidden name=tsk_horafin value="'.$tsk_horafin.'">';
echo '<INPUT type=hidden name=tsk_tiposacciones value="'.$tsk_tiposacciones.'">';
echo '<INPUT type=hidden name=tsk_estados value="'.$tsk_estados.'">';
echo '<INPUT type=hidden name=tsk_resultados value="'.$tsk_resultados.'">';
echo '<INPUT type=hidden name=tsk_porcendesde value="'.$tsk_porcendesde.'">';
echo '<INPUT type=hidden name=tsk_porcenhasta value="'.$tsk_porcenhasta.'">';

echo '<INPUT type=hidden name=tsk_idcmdtskwrk value="'.$tsk_idcmdtskwrk.'">';
echo '<INPUT type=hidden name=tsk_codtipoaccion value="'.$tsk_codtipoaccion.'">';
echo '<INPUT type=hidden name=tsk_idambcmdtskwrk value="'.$tsk_idambcmdtskwrk.'">';

echo '<INPUT type=hidden name=tsk_accionid value="'.$tsk_accionid.'">';
echo '<INPUT type=hidden name=tsk_idTipoAccion value="'.$tsk_idTipoAccion.'">';
echo '<INPUT type=hidden name=tsk_TipoAccion value="'.$tsk_TipoAccion.'">';
echo '<INPUT type=hidden name=tsk_NombreTipoAccion value="'.$tsk_NombreTipoAccion.'">';

// Parametros Trabajo padre
echo '<INPUT type=hidden name=wrk_ambito  value="'.$wrk_ambito.'">';
echo '<INPUT type=hidden name=wrk_idambito  value="'.$wrk_idambito.'">';
echo '<INPUT type=hidden name=wrk_nombreambito value="'.$wrk_nombreambito.'">';

echo '<INPUT type=hidden name=wrk_fechainicio value="'.$wrk_fechainicio.'">';
echo '<INPUT type=hidden name=wrk_fechafin value="'.$wrk_fechafin.'">';
echo '<INPUT type=hidden name=wrk_horainicio value="'.$wrk_horainicio.'">';
echo '<INPUT type=hidden name=wrk_horafin value="'.$wrk_horafin.'">';
echo '<INPUT type=hidden name=wrk_tiposacciones value="'.$wrk_tiposacciones.'">';
echo '<INPUT type=hidden name=wrk_estados value="'.$wrk_estados.'">';
echo '<INPUT type=hidden name=wrk_resultados value="'.$wrk_resultados.'">';
echo '<INPUT type=hidden name=wrk_porcendesde value="'.$wrk_porcendesde.'">';
echo '<INPUT type=hidden name=wrk_porcenhasta value="'.$wrk_porcenhasta.'">';

echo '<INPUT type=hidden name=wrk_idcmdtskwrk value="'.$wrk_idcmdtskwrk.'">';
echo '<INPUT type=hidden name=wrk_codtipoaccion value="'.$wrk_codtipoaccion.'">';
echo '<INPUT type=hidden name=wrk_idambcmdtskwrk value="'.$wrk_idambcmdtskwrk.'">';

echo '<INPUT type=hidden name=wrk_accionid value="'.$wrk_accionid.'">';
echo '<INPUT type=hidden name=wrk_idTipoAccion value="'.$wrk_idTipoAccion.'">';
echo '<INPUT type=hidden name=wrk_TipoAccion value="'.$wrk_TipoAccion.'">';
echo '<INPUT type=hidden name=wrk_NombreTipoAccion value="'.$wrk_NombreTipoAccion.'">';
//________________________________________________________________________________________________________
echo CriteriosBusquedas();
echo '</FORM>'.chr(13);
echo '<DIV align=center>';
if($accionid>0){
	switch($TipoAccion){
			case $EJECUCION_COMANDO :
				$textoaccion=$LITEJECUCION_COMANDO;
				$urlimg='../images/iconos/comandos.gif';
				break;
			case $EJECUCION_TAREA :
				$textoaccion=$LITEJECUCION_TAREA;
				$urlimg='../images/iconos/tareas.gif';
				break;
			case $EJECUCION_TRABAJO :
				$textoaccion=$LITEJECUCION_TRABAJO;
				$urlimg='../images/iconos/trabajos.gif';
				break;
	}
	echo '<span align=center class=subcabeceras>'.$textoaccion.':'.$NombreTipoAccion.'</span>&nbsp;&nbsp;<IMG src="'.$urlimg.'">&nbsp;&nbsp;&nbsp;<span class=notas><A href="javascript:ver_accionpadre('.$TipoAccion.');">Volver >></A></span>';
}
else{
	echo '<span align=center class=subcabeceras><U>'.$TbMsg[11].':'.$textambito.'</U>,&nbsp'.$nombreambito.'</span>&nbsp;&nbsp;<IMG src="'.$urlimg.'"></span>';
}
?>
<BR><BR>
<? if($accionid==0){?>
		<TABLE  align=center class=filtros border=0 align=left cellPadding=2 cellSpacing=5 >
		  <TR height=20 width=450 valign=baseline>
			<TD width=70 onclick=eleccion(this,1)  onmouseout=desresaltar(this) onmouseover=resaltar(this) >
				&nbsp;<IMG src="../images/iconos/eliminar.gif"'>&nbsp;<?echo $TbMsg[12]?>&nbsp;</TD>
			<TD width=85 onclick=eleccion(this,2) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>		
				&nbsp;<IMG src="../images/iconos/reiniciar.gif"'>&nbsp;<?echo $TbMsg[13]?>&nbsp;</TD>
			<TD width=75 onclick=eleccion(this,3) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>
					&nbsp;<IMG src="../images/iconos/acDetenida.gif"'>&nbsp;<?echo $TbMsg[14]?>&nbsp;</TD>
			<TD width=80 onclick=eleccion(this,4) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>
					&nbsp;<IMG src="../images/iconos/acIniciada.gif"'>&nbsp;<?echo $TbMsg[15]?>&nbsp;</TD>
			<TD width=75  onclick=eleccion(this,5) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>
					&nbsp;<IMG src="../images/iconos/acAbortada.gif"';>&nbsp;<?echo $TbMsg[16]?>&nbsp;</TD>
			<TD width=75 onclick=eleccion(this,6) onmouseout=desresaltar(this) onmouseover=resaltar(this) align=middle>			&nbsp;<IMG src="../images/iconos/acTerminada.gif"'>&nbsp;<?echo $TbMsg[17]?>&nbsp;</TD>
		   </TR>
		 </TABLE>
<?}
ListaAcciones($cmd);
echo '</DIV>';
echo '<INPUT type=hidden id=mulaccion value="'.$mulaccion.'">';
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
/**************************************************************************************************************************************************
	Dibuja la tabla de acciones y notificaciones aplicadas a los distintos ambitos
________________________________________________________________________________________________________*/
function ListaAcciones($cmd){
	global $TbMsg;
	global $AMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	global $idcmdtskwrk;
	global $codtipoaccion;
	global $idambcmdtskwrk;
	global $tabla_ambitosacciones;
	global $cont_ambitosacciones;
	global $ambito; 
	global $idambito;

	echo '<TABLE  border=0 class="tabla_listados" cellspacing=1 cellpadding=0 >'.chr(13);
	echo '<TR height=20>'.chr(13);
	echo '<TH>&nbsp;</TH>'.chr(13);
	if($idcmdtskwrk=="" && $codtipoaccion=="" && $idambcmdtskwrk=="") // Sin filtro
		echo '<TH>A</TH>'.chr(13);
	else
		echo '<TH>A*</TH>'.chr(13);
	echo '<TH>&nbsp;'.$TbMsg[18].'&nbsp;</TH>'.chr(13);
	echo '<TH>&nbsp;'.$TbMsg[19].'&nbsp;</TH>'.chr(13);
	echo '<TH>&nbsp;'.$TbMsg[20].'&nbsp;</TH>'.chr(13);
	echo '<TH>&nbsp;'.$TbMsg[21].'&nbsp;</TH>'.chr(13);
	echo '<TH>&nbsp;'.$TbMsg[22].'&nbsp;</TH>'.chr(13);
	echo '<TH>&nbsp;</TH>'.chr(13);
	echo '<TH>&nbsp;'.$TbMsg[23].'&nbsp;</TH>'.chr(13);
	echo '<TH>S</TH>'.chr(13);
	echo '<TH>%</TH>'.chr(13);
	echo '<TH>R</TH>'.chr(13);
	echo '</TR>'.chr(13);

	switch($ambito){
		case $AMBITO_CENTROS :
			$cmd->texto="SELECT idcentro,nombrecentro FROM centros WHERE idcentro=".$idambito;
 			RecorreCentro($cmd);
			break;
		case $AMBITO_GRUPOSAULAS :
			$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE idgrupo=".$idambito." AND tipo=".$AMBITO_GRUPOSAULAS;
			RecorreGruposAulas($cmd);
			break;
		case $AMBITO_AULAS :
			$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE idaula=".$idambito;
			RecorreAulas($cmd);
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposordenadores WHERE idgrupo=".$idambito;
			RecorreGruposOrdenadores($cmd);
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto="SELECT idordenador,nombreordenador FROM ordenadores WHERE idordenador=".$idambito;
			RecorreOrdenadores($cmd);
			break;
	}
	// Ordena
	$cont=$cont_ambitosacciones;
	$auxambitoaccion="";
	for ($i=0;$i<$cont-1;$i++){
		for ($j=$i+1;$j<$cont;$j++){
			if($tabla_ambitosacciones[$i][0]>$tabla_ambitosacciones[$j][0]){
				$auxambitoaccion=$tabla_ambitosacciones[$i][0];
				$tabla_ambitosacciones[$i][0]=$tabla_ambitosacciones[$j][0];
				$tabla_ambitosacciones[$j][0]=$auxambitoaccion;

				$auxtabla_ambitosacciones=$tabla_ambitosacciones[$i][1];
				$tabla_ambitosacciones[$i][1]=$tabla_ambitosacciones[$j][1];
				$tabla_ambitosacciones[$j][1]=$auxtabla_ambitosacciones;
			}
		}
	}
	ListandoAcciones($cmd);
	echo '</TABLE>';
}
//________________________________________________________________________________________________________
function ListandoAcciones($cmd){
	global $AMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;

	global $EJECUCION_COMANDO;
	global $EJECUCION_TAREA;
	global $EJECUCION_TRABAJO;

	global $ACCION_EXITOSA; 
	global $ACCION_FALLIDA; 
	global $ACCION_TERMINADA; 
	global $ACCION_ABORTADA; 
	global $ACCION_SINERRORES; 
	global $ACCION_CONERRORES;

	global $ACCION_DETENIDA;
	global $ACCION_INICIADA;
	global $ACCION_FINALIZADA;

	global $porcendesde;
	global $porcenhasta;
	global $tabla_ambitosacciones;
	global $cont_ambitosacciones;
	global $ClausulaWhere;
	global $mulaccion;

	global $PROCESOS;
	global $NOTIFICACIONES;

	// Selecciona acciones 
	$rs=new Recordset; 
	$cmd->texto="SELECT acciones.* FROM acciones";
	if($ClausulaWhere!="") 	$cmd->texto.=" WHERE  (".$ClausulaWhere.")";
	$cmd->texto.=" ORDER BY acciones.idaccion desc ";

	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 

	// Recorre acciones
	while (!$rs->EOF){
		$HTMLparametros="";
		$HTMLnotificaciones="";
		if($rs->campos["tipoaccion"]==$EJECUCION_TAREA || $rs->campos["tipoaccion"]==$EJECUCION_TRABAJO ){
			$auxP=split(";",$rs->campos["ambitskwrk"]); // Recorre los ambitos de la tarea o trabajo
			$i=0;
			do{
				$dualparam=split(":",$auxP[$i]);
				$datobinario=$dualparam[0]."-".$dualparam[1];
				$posp=busca_indicebinariodual($datobinario,$tabla_ambitosacciones,$cont_ambitosacciones); // Busca ambito e id. 
				$i++;
			}while($posp<0 && $i<sizeof($auxP));
		}
		else{
			$datobinario=$rs->campos["ambito"]."-".$rs->campos["idambito"];
			$posp=busca_indicebinariodual($datobinario,$tabla_ambitosacciones,$cont_ambitosacciones); // Busca datos en la tabla 
		}
		if ($posp>=0){
				$auxtabla_ambitosacciones=$tabla_ambitosacciones[$posp][1];
				$ambito=$auxtabla_ambitosacciones["ambito"];
				$nombreambito=$auxtabla_ambitosacciones["nombreambito"];
				switch($ambito){
					case $AMBITO_CENTROS :
						$urlimg='../images/iconos/centros.gif';
						$textambito="Centros";
						$bgcolor="#ff5566";
						break;
					case $AMBITO_GRUPOSAULAS :
						$urlimg='../images/iconos/carpeta.gif';
						$textambito="Grupos de aulas";
						$bgcolor="#FFCC55";
						break;
					case $AMBITO_AULAS :
						$urlimg='../images/iconos/aula.gif';
						$textambito="Aulas";
						$bgcolor="#D4D4D4";
						break;
					case $AMBITO_GRUPOSORDENADORES :
						$urlimg='../images/iconos/carpeta.gif';
						$textambito="Grupos de ordenadores";
						$bgcolor="#FF00CC";
						break;
					case $AMBITO_ORDENADORES :
						$urlimg='../images/iconos/ordenador.gif';
						$bgcolor="#FFFF68";
						$textambito="Ordenadores";
						break;
				}
				$ipesnotificadas=""; // Almacena las ipes de los prdenadores que ya han notificado
				$HTMLnotificaciones=notificaciones($cmd,$rs->campos["idaccion"],&$numnot,$rs->campos["tipoaccion"], $rs->campos["parametros"],&$ipesnotificadas );
				$nottotales=NotificacionesEsperadas($rs->campos["parametros"],$rs->campos["tipoaccion"]);
				if($nottotales>0) 
					$porcen=round(($numnot/$nottotales)*100,1);
				else
					$porcen=0;
	
				if($porcen>=$porcendesde && $porcen<=$porcenhasta){
						$mulaccion.=$rs->campos["idaccion"].":"; // Formato idaccion:estado:resultado;
						echo '<TR id="ACC_'.$rs->campos["idaccion"].'" name='.$rs->campos["tipoaccion"].' value='.$rs->campos["ambito"].' height=20>'.chr(13);
						echo '<TD  align=center><IMG onclick=ver_notificaciones(this,0,'.$rs->campos["idaccion"].'); style="cursor:hand;display:block" src="../images/tsignos/contra.gif">';
						echo '<IMG onclick=ver_notificaciones(this,1,'.$rs->campos["idaccion"].'); style="cursor:hand;display:none" src="../images/tsignos/desple.gif">';
						echo '</TD>'.chr(13);

						switch($rs->campos["tipoaccion"]){
							case $EJECUCION_COMANDO :
								$nombreliterales[0]="descripcion";
								$nombreliterales[1]="visuparametros";
								$Datos=TomanDatos($cmd,"comandos",$rs->campos["idtipoaccion"],"idcomando",$nombreliterales);
								$nombreaccion=$Datos["descripcion"];
							  //  Visualización de los parametros de un comando
								$HTMLparametros=infoparametros($cmd,$rs->campos["idaccion"],$rs->campos["parametros"],$Datos["visuparametros"],$ipesnotificadas);
								echo '<TD align=center><IMG name="'.$rs->campos["idtipoaccion"].'" id='.$rs->campos["idaccion"].' src="../images/iconos/comandos.gif" style="cursor:hand" oncontextmenu="resalta(this,'.$EJECUCION_COMANDO.','."'".$nombreaccion.".'".')"></TD>'.chr(13);
								break;
							case $EJECUCION_TAREA :
								$HTMLparametros=infoparametrosTskWrk($cmd,$rs->campos["idaccion"],$rs->campos["parametros"]);
								$nombreaccion=TomaDato($cmd,0,'tareas',$rs->campos["idtipoaccion"],'idtarea','descripcion');
								echo '<TD align=center><IMG name="'.$rs->campos["idtipoaccion"].'" id='.$rs->campos["idaccion"].' src="../images/iconos/tareas.gif" style="cursor:hand" oncontextmenu="resalta(this,'.$EJECUCION_TAREA.','."'".$nombreaccion.".'".')"></TD>'.chr(13);
								break;
							case $EJECUCION_TRABAJO :
								$HTMLparametros=infoparametrosTskWrk($cmd,$rs->campos["idaccion"],$rs->campos["parametros"]);
								$nombreaccion=TomaDato($cmd,0,'trabajos',$rs->campos["idtipoaccion"],'idtrabajo','descripcion');
								echo '<TD align=center><IMG name="'.$rs->campos["idtipoaccion"].'" id='.$rs->campos["idaccion"].' src="../images/iconos/trabajos.gif" style="cursor:hand" oncontextmenu="resalta(this,'.$EJECUCION_TRABAJO.','."'".$nombreaccion.".'".')"></TD>'.chr(13);
								break;
						}
						echo '<TD align=center>&nbsp;'.$nombreaccion.'&nbsp;</TD>'.chr(13);

						$fechahorareg=$rs->campos["fechahorafin"];
						$wfecha=substr($fechahorareg,0,strpos($fechahorareg,' '));
						$whora =substr (strrchr ($fechahorareg, " "), 1);
						$fecha=trim($wfecha);
						$hora=trim($whora);
						if ($fecha=="0000-00-00") $hora="";
						echo '<TD align=center>&nbsp;'.InvFecha($fecha).'&nbsp;</TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.$hora.'&nbsp;</TD>'.chr(13);

						$fechahorareg=$rs->campos["fechahorareg"];
						$wfecha=substr($fechahorareg,0,strpos($fechahorareg,' '));
						$whora =substr (strrchr ($fechahorareg, " "), 1);
						$fecha=trim($wfecha);
						$hora=trim($whora);
						if ($fecha=="0000-00-00") $hora="";
						echo '<TD align=center>&nbsp;'.InvFecha($fecha).'&nbsp;</TD>'.chr(13);
						echo '<TD align=center>&nbsp;'.$hora.'&nbsp;</TD>'.chr(13);

						if($rs->campos["tipoaccion"]==$EJECUCION_TAREA || $rs->campos["tipoaccion"]==$EJECUCION_TRABAJO ){
							echo '<TD align=center>&nbsp;</TD>'.chr(13);
							echo '<TD align=center>&nbsp;</TD>'.chr(13);
						}
						else{
							echo '<TD align=center><IMG src="'.$urlimg.'"></TD>'.chr(13);
							echo '<TD align=center>&nbsp;'.$nombreambito.'&nbsp;</TD>'.chr(13);
						}
						$mulaccion.=$rs->campos["estado"].":"; // Formato idaccion:estado:resultado;
						switch($rs->campos["estado"]){
								case $ACCION_DETENIDA:
									echo '<TD align=center><IMG value="'.$ACCION_DETENIDA.'" src="../images/iconos/acDetenida.gif" width=16 height=16></TD>'.chr(13);
									break;
								case $ACCION_INICIADA:
									echo '<TD align=center><IMG value="'.$ACCION_INICIADA.'" src="../images/iconos/acIniciada.gif" width=16 height=16></TD>'.chr(13);
									break;
								case $ACCION_FINALIZADA:
									echo '<TD align=center><IMG value="'.$ACCION_FINALIZADA.'" src="../images/iconos/acFinalizada.gif" width=16 height=16></TD>'.chr(13);
									break;
						}
						echo '<TD id="PORCEN-'.$rs->campos["idaccion"].'" align=center >&nbsp;'.$porcen.'%&nbsp;</TD>';
						$mulaccion.=$rs->campos["resultado"].";"; // Formato idaccion:estado:resultado;
						switch($rs->campos["resultado"]){
							case $ACCION_EXITOSA:
								echo '<TD align=center><IMG value="'.$ACCION_EXITOSA.'" src="../images/iconos/acExitosa.gif" width=16 height=16></TD>'.chr(13);
								break;
							case $ACCION_FALLIDA:
								echo '<TD align=center><IMG value="'.$ACCION_FALLIDA.'" src="../images/iconos/acFallida.gif" width=16 height=16></TD>'.chr(13);
								break;
							case $ACCION_SINERRORES:
								echo '<TD align=center><IMG value="'.$ACCION_SINERRORES.'" src="../images/iconos/acSinErrores.gif" width=16 height=16></TD>'.chr(13);
								break;
							case $ACCION_CONERRORES:
								echo '<TD align=center><IMG value="'.$ACCION_CONERRORES.'" src="../images/iconos/acConErrores.gif" width=16 height=16></TD>'.chr(13);
								break;
							case $ACCION_TERMINADA:
								echo '<TD align=center><IMG value="'.$ACCION_TERMINADA.'" src="../images/iconos/acTerminada.gif" width=16 height=16></TD>'.chr(13);
								break;
							case $ACCION_ABORTADA:
								echo '<TD align=center><IMG value="'.$ACCION_ABORTADA.'" src="../images/iconos/acAbortada.gif" width=16 height=16></TD>'.chr(13);
								break;
							default:
								echo '<TD >&nbsp;</TD>';
						}
						echo '</TR>'.chr(13);
						echo $HTMLparametros;
						echo $HTMLnotificaciones;
				}
		}
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
/*________________________________________________________________________________________________________
	Dibuja la tabla de parametros de una tarea o un trabajo
________________________________________________________________________________________________________*/
function infoparametrosTskWrk($cmd,$idaccion,$parametros){
	$HTMLparametros="";
	$HTMLparametros.='<TR id="PAR_'.$idaccion.'" style="display:none">'.chr(13);
	$HTMLparametros.= '<TD>&nbsp;</TD>'.chr(13);
	$HTMLparametros.=  '<TH align=center style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4; " >Nº</TH>'.chr(13);
	$HTMLparametros.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;"  colspan=10>Acción</TH>'.chr(13);
	$HTMLparametros.=  '</TR>'.chr(13);
	
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	$dualparam=split("=",$parametros);
	$auxC=split(";",$dualparam[1]); // Recorre valores delimitado por comas
	for ($j=0;$j<sizeof($auxC);$j++){
		if ($dualparam[0]=="cmd")
			$cmd->texto="SELECT comandos.descripcion FROM comandos INNER JOIN tareas_comandos ON tareas_comandos.idcomando=comandos.idcomando WHERE tareas_comandos.idtareacomando=".$auxC[$j] ;
		else
			$cmd->texto="SELECT tareas.descripcion FROM tareas INNER JOIN trabajos_tareas ON trabajos_tareas.idtarea=tareas.idtarea WHERE trabajos_tareas.idtrabajotarea=".$auxC[$j] ;
		if (!$rs->Abrir()) return(""); // Error al abrir recordset
		if($rs->EOF) return("");
		$valor=$rs->campos["descripcion"];
		$rs->Cerrar();
		$HTMLparametros.='<TR  id="PAR_'.$idaccion.'" style="display:none">'.chr(13);
		$HTMLparametros.= '<TD>&nbsp;</TD>'.chr(13);
		$HTMLparametros.=  '<TD align=center style="BACKGROUND-COLOR: #b5daad;" >'.($j+1).'</TD>'.chr(13);
		$HTMLparametros.=  '<TD  style="BACKGROUND-COLOR: #b5daad;" colspan=10>'.$valor.'</TD>'.chr(13);
		$HTMLparametros.=  '</TR>'.chr(13);
	}
	return($HTMLparametros);
}
/*________________________________________________________________________________________________________
	Dibuja la tabla de parametros de un comando
________________________________________________________________________________________________________*/
function infoparametros($cmd,$idaccion,$parametros,$visuparametros,$ipesnotificadas){
	global  $tabla_parametros;
	global  $cont_parametros;
	global  $MAXLONVISUSCRIPT; // longitud Maxima de visualización del script

	$HTMLparametros="";
	$HTMLparametros.='<TR  id="PAR_'.$idaccion.'" style="display:none">'.chr(13);
	$HTMLparametros.= '<TD>&nbsp;</TD>'.chr(13);
	$HTMLparametros.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4; " colspan=3>Parameter</TH>'.chr(13);
	$HTMLparametros.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;"  colspan=8>Value</TH>'.chr(13);
	$HTMLparametros.=  '</TR>'.chr(13);
	
	$auxVP=split(";",$visuparametros); // Parametros visualizables
	$auxP=split(chr(13),$parametros); // Recorre parametros para visualizar los que así sean
	for ($i=0;$i<sizeof($auxP);$i++){
		$dualparam=split("=",$auxP[$i]);
		for ($k=0;$k<sizeof($auxVP);$k++){
			 if($auxVP[$k]==$dualparam[0]){
				$posp=busca_indicebinariodual($dualparam[0],$tabla_parametros,$cont_parametros); // Busca datos del parámetro en la tabla cargada previamentre con todos los parámetros
				if ($posp>=0){
					$auxtabla_parametros=$tabla_parametros[$posp][1];
					$HTMLparametros.='<TR  id="PAR_'.$idaccion.'" style="display:none">'.chr(13);
					$HTMLparametros.= '<TD>&nbsp;</TD>'.chr(13);
					$HTMLparametros.=  '<TD style="BACKGROUND-COLOR: #b5daad;" colspan=3>&nbsp;'.$auxtabla_parametros["descripcion"].'</TD>'.chr(13);
					if($auxtabla_parametros["tipopa"]==1){
					$valor=TomaDato($cmd,0,$auxtabla_parametros["nomtabla"],$dualparam[1],$auxtabla_parametros["nomidentificador"],$auxtabla_parametros["nomliteral"]);
					}else
						$valor=$dualparam[1];
					
					switch($dualparam[0]){
						case "iph": // Si el parametro es la s Ipes de los ordenadores se pintan
								$tablaipes=PintaOrdenadores($cmd,$valor,$ipesnotificadas,$idaccion);
								$HTMLparametros.=  '<TD  style="BACKGROUND-COLOR: #b5daad;" colspan=8>'.$tablaipes.'</TD>'.chr(13);
								break;
						default:
								$HTMLparametros.=  '<TD  style="BACKGROUND-COLOR: #b5daad;" colspan=8>&nbsp;'.Urldecode($valor).'</TD>'.chr(13);
					}
					$HTMLparametros.=  '</TR>'.chr(13);
				}
			}
		}
	}
	return($HTMLparametros);
}
/*________________________________________________________________________________________________________
	Dibuja la tabla de notificaciones
________________________________________________________________________________________________________*/
function notificaciones($cmd,$idaccion,$numnot,$TipoAccion,$parametros,$ipesnotificadas){
	global $TbMsg;
	global $EJECUCION_COMANDO;
	global $EJECUCION_TAREA;
	global $EJECUCION_TRABAJO;
	global $ACCION_EXITOSA; 
	global $ACCION_FALLIDA; 
	global $NOTIFICADOR_ORDENADOR;
	global $NOTIFICADOR_COMANDO;
	global $NOTIFICADOR_TAREA;

	$HTMLnotificaciones="";
	$numnot=0;
	$rs=new Recordset; 
	switch($TipoAccion){
		case $EJECUCION_COMANDO :
			$TipoNotificador=$NOTIFICADOR_ORDENADOR;
			$urlimg='../images/iconos/comandos.gif';
			$cmd->texto='SELECT notificaciones.*,ordenadores.idordenador as identificadornot,ordenadores.nombreordenador as nombreidentificadornot,ordenadores.ip FROM notificaciones';
			$cmd->texto.=" INNER JOIN  ordenadores ON notificaciones.idnotificador=ordenadores.idordenador";
			$cmd->texto.=" WHERE notificaciones.accionid=".$idaccion." ORDER BY notificaciones.fechahorareg desc";
			break;
		case $EJECUCION_TAREA :
			$TipoNotificador=$NOTIFICADOR_COMANDO;
			$urlimg='../images/iconos/tareas.gif';
			$cmd->texto='SELECT notificaciones.*,tareas_comandos.idtareacomando as identificadornot,comandos.descripcion as nombreidentificadornot FROM notificaciones';
			$cmd->texto.=" INNER JOIN  tareas_comandos ON notificaciones.idnotificador=tareas_comandos.idtareacomando";
			$cmd->texto.=" INNER JOIN  comandos ON comandos.idcomando=tareas_comandos.idcomando";
			$cmd->texto.=" WHERE notificaciones.accionid=".$idaccion." ORDER BY notificaciones.fechahorareg desc ";
			$urlimg= '../images/iconos/comandos.gif>';
			break;
		case $EJECUCION_TRABAJO :
			$TipoNotificador=$NOTIFICADOR_TAREA;
			$urlimg='../images/iconos/trabajos.gif';
			$cmd->texto='SELECT notificaciones.*,trabajos_tareas.idtrabajotarea  as identificadornot,tareas.descripcion as nombreidentificadornot FROM notificaciones';
			$cmd->texto.=" INNER JOIN  trabajos_tareas ON notificaciones.idnotificador=trabajos_tareas.idtrabajotarea";
			$cmd->texto.=" INNER JOIN  tareas ON tareas.idtarea=trabajos_tareas.idtarea";
			$cmd->texto.=" WHERE notificaciones.accionid=".$idaccion." ORDER BY notificaciones.fechahorareg desc";
			$urlimg= '../images/iconos/tareas.gif>';
			break;
	}
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$numreg=0;
	$rs->Primero(); 
	while (!$rs->EOF){
			$numnot++;
			if($numreg==0){
				$HTMLnotificaciones.='<TR  id="NOT_'.$idaccion.'" style="display:none" >'.chr(13);
				$HTMLnotificaciones.= '<TD>&nbsp;</TD>'.chr(13);
				$HTMLnotificaciones.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;">&nbsp;</TH>'.chr(13);
				$HTMLnotificaciones.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;">'.$TbMsg[23].'</TH>'.chr(13);
				$HTMLnotificaciones.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;">'.$TbMsg[19].'</TH>'.chr(13);
				$HTMLnotificaciones.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;">'.$TbMsg[20].'</TH>'.chr(13);
				$HTMLnotificaciones.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;" colspan=6>'.$TbMsg[57].'</TH>'.chr(13);
				$HTMLnotificaciones.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;">R</TH>'.chr(13);
				$HTMLnotificaciones.=  '</TR>'.chr(13);
				$numreg++;
			}
			if($TipoAccion==$EJECUCION_COMANDO){
				switch($rs->campos["resultado"]){
					case $ACCION_EXITOSA:
						$urlimg= '../images/iconos/ordenadornot_ok.gif>';
						break;
					case $ACCION_FALLIDA:
						$urlimg= '../images/iconos/ordenadornot_ko.gif>';
						break;
				}
			}
			$HTMLnotificaciones.='<TR id="NOT_'.$idaccion.'" style="display:none" height=20 value="'.$idaccion.'">'.chr(13);
			$HTMLnotificaciones.='<TD>&nbsp;</TD>'.chr(13);
			$HTMLnotificaciones.='<TD  style="BACKGROUND-COLOR: #E3D8C6;" align=center>';
			$HTMLnotificaciones.='<IMG id="'.$rs->campos["accionid"].'" name="'.$rs->campos["idnotificacion"].'" value="'.$rs->campos["identificadornot"].'" oncontextmenu="resaltanot(this,'.$TipoNotificador.');" src='.$urlimg.'</TD>'.chr(13);
			$HTMLnotificaciones.=  '<TD  style="BACKGROUND-COLOR: #E3D8C6;" align=center>'.$rs->campos["nombreidentificadornot"].'</TD>'.chr(13);
			$fechahorareg=$rs->campos["fechahorareg"];
			$wfecha=substr($fechahorareg,0,strpos($fechahorareg,' '));
			$whora =substr (strrchr ($fechahorareg, " "), 1);
			$fecha=trim($wfecha);
			$hora=trim($whora);
			if ($fecha=="0000-00-00") $hora="";
			$HTMLnotificaciones.=  '<TD  style="BACKGROUND-COLOR: #E3D8C6;" align=center>&nbsp;'.InvFecha($fecha).'&nbsp;</TD>'.chr(13);
			$HTMLnotificaciones.=  '<TD  style="BACKGROUND-COLOR: #E3D8C6;"align=center>&nbsp;'.$hora.'&nbsp;</TD>'.chr(13);
			$HTMLnotificaciones.=  '<TD  style="BACKGROUND-COLOR: #E3D8C6;" colspan=6 align=center>'.$rs->campos["descrinotificacion"].'&nbsp;</TD>'.chr(13);
			switch($rs->campos["resultado"]){
				case $ACCION_EXITOSA:
					$HTMLnotificaciones.=  '<TD  style="BACKGROUND-COLOR: #E3D8C6;" align=center><IMG value="'.$ACCION_EXITOSA.'" src="../images/iconos/acExitosa.gif" width=16 height=16></TD>'.chr(13);
					if($TipoNotificador==$NOTIFICADOR_ORDENADOR){
						$ipesnotificadas.=$rs->campos["ip"]."=".$ACCION_EXITOSA."=".$rs->campos["idnotificacion"].";";
					}
					break;
				case $ACCION_FALLIDA:
					$HTMLnotificaciones.=  '<TD  style="BACKGROUND-COLOR: #E3D8C6;" align=center><IMG value="'.$ACCION_FALLIDA.'" src="../images/iconos/acFallida.gif" width=16 height=16></TD>'.chr(13);
					if($TipoNotificador==$NOTIFICADOR_ORDENADOR){
						$ipesnotificadas.=$rs->campos["ip"]."=".$ACCION_FALLIDA."=".$rs->campos["idnotificacion"].";";
					}
					break;
			}
			$HTMLnotificaciones.='</TR>'.chr(13);
			$rs->Siguiente();
	}
	return($HTMLnotificaciones);
}
/*________________________________________________________________________________________________________
	Recorrea loa distintod ambitos
________________________________________________________________________________________________________*/
function RecorreCentro($cmd){
	global $AMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $tabla_ambitosacciones;
	global $cont_ambitosacciones;

	$auxtabla_ambitosacciones="";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	if(!$rs->EOF){
		$idcentro=$rs->campos["idcentro"];
		$tabla_ambitosacciones[$cont_ambitosacciones][0]=$AMBITO_CENTROS."-".$idcentro;
		$auxtabla_ambitosacciones["ambito"]=$AMBITO_CENTROS;
		$auxtabla_ambitosacciones["nombreambito"]=$rs->campos["nombrecentro"];
		$tabla_ambitosacciones[$cont_ambitosacciones++][1]=$auxtabla_ambitosacciones;
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE idcentro=".$idcentro." AND grupoid=0  AND tipo=".$AMBITO_GRUPOSAULAS;
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE idcentro=".$idcentro." AND grupoid=0";
		RecorreAulas($cmd);
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreGruposAulas($cmd){
	global $AMBITO_GRUPOSAULAS;
	global $tabla_ambitosacciones;
	global $cont_ambitosacciones;

	$auxtabla_ambitosacciones="";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 

	while (!$rs->EOF){
		$idgrupo=$rs->campos["idgrupo"];
		$tabla_ambitosacciones[$cont_ambitosacciones][0]=$AMBITO_GRUPOSAULAS."-".$idgrupo;
		$auxtabla_ambitosacciones["ambito"]=$AMBITO_GRUPOSAULAS;
		$auxtabla_ambitosacciones["nombreambito"]=$rs->campos["nombregrupo"];
		$tabla_ambitosacciones[$cont_ambitosacciones++][1]=$auxtabla_ambitosacciones;
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE grupoid=".$idgrupo ." AND tipo=".$AMBITO_GRUPOSAULAS;
		RecorreGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE  grupoid=".$idgrupo;
		RecorreAulas($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreAulas($cmd){
	global $AMBITO_AULAS;
	global $tabla_ambitosacciones;
	global $cont_ambitosacciones;

	$auxtabla_ambitosacciones="";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 

	while (!$rs->EOF){
		$idaula=$rs->campos["idaula"];
		$tabla_ambitosacciones[$cont_ambitosacciones][0]=$AMBITO_AULAS."-".$idaula;
		$auxtabla_ambitosacciones["ambito"]=$AMBITO_AULAS;
		$auxtabla_ambitosacciones["nombreambito"]=$rs->campos["nombreaula"];
		$tabla_ambitosacciones[$cont_ambitosacciones++][1]=$auxtabla_ambitosacciones;
		$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposOrdenadores WHERE idaula=".$idaula." AND grupoid=0";
		RecorreGruposOrdenadores($cmd);
		$cmd->texto="SELECT idordenador,nombreordenador FROM ordenadores WHERE  idaula=".$idaula." AND grupoid=0";
		RecorreOrdenadores($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreGruposOrdenadores($cmd){
	global $AMBITO_GRUPOSORDENADORES;
	global $tabla_ambitosacciones;
	global $cont_ambitosacciones;

	$auxtabla_ambitosacciones="";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idgrupo=$rs->campos["idgrupo"];
		$tabla_ambitosacciones[$cont_ambitosacciones][0]=$AMBITO_GRUPOSORDENADORES."-".$idgrupo;
		$auxtabla_ambitosacciones["ambito"]=$AMBITO_GRUPOSORDENADORES;
		$auxtabla_ambitosacciones["nombreambito"]=$rs->campos["nombregrupoordenador"];
		$tabla_ambitosacciones[$cont_ambitosacciones++][1]=$auxtabla_ambitosacciones;
		$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposOrdenadores WHERE grupoid=".$idgrupo;
		RecorreGruposOrdenadores($cmd);
		$cmd->texto="SELECT idordenador,nombreordenador FROM ordenadores WHERE  grupoid=".$idgrupo;
		RecorreOrdenadores($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function RecorreOrdenadores($cmd){
	global $AMBITO_ORDENADORES;
	global $tabla_ambitosacciones;
	global $cont_ambitosacciones;

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 

	while (!$rs->EOF){
		$idordenador=$rs->campos["idordenador"];
		$tabla_ambitosacciones[$cont_ambitosacciones][0]=$AMBITO_ORDENADORES."-".$idordenador;
		$auxtabla_ambitosacciones["ambito"]=$AMBITO_ORDENADORES;
		$auxtabla_ambitosacciones["nombreambito"]=$rs->campos["nombreordenador"];
		$tabla_ambitosacciones[$cont_ambitosacciones++][1]=$auxtabla_ambitosacciones;
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
/*________________________________________________________________________________________________________
	Cuenta el numero de ordenadores a los que afecta la acción
________________________________________________________________________________________________________*/
function NotificacionesEsperadas($parametros,$TipoAccion){
	global $EJECUCION_COMANDO;
	global $EJECUCION_TAREA;
	global $EJECUCION_TRABAJO;

	switch($TipoAccion){
		case $EJECUCION_COMANDO :
			$cadenanot=extrae_parametro($parametros,chr(13),'=',"iph");
			break;
		case $EJECUCION_TAREA :
			$cadenanot=extrae_parametro($parametros,chr(13),'=',"cmd");
			break;
		case $EJECUCION_TRABAJO :
			$cadenanot=extrae_parametro($parametros,chr(13),'=',"tsk");
			break;
	}
	$cont=1;
	for($i=0;$i<strlen($cadenanot);$i++){
		if(substr($cadenanot,$i,1)==';') $cont++;
	}
	return($cont);
}
//________________________________________________________________________________________________________
function CriteriosBusquedas(){
	global $idcentro;
	global $TbMsg;
	global $EJECUCION_COMANDO;
	global $EJECUCION_TAREA;
	global $EJECUCION_TRABAJO;

	global $LITEJECUCION_COMANDO;
	global $LITEJECUCION_TAREA;
	global $LITEJECUCION_TRABAJO;

	global $ACCION_DETENIDA;
	global $ACCION_INICIADA;
	global $ACCION_FINALIZADA;

	global $LITACCION_DETENIDA;
	global $LITACCION_INICIADA;
	global $LITACCION_FINALIZADA;

	global $ACCION_EXITOSA; 
	global $ACCION_FALLIDA; 
	global $ACCION_TERMINADA; 
	global $ACCION_ABORTADA; 
	global $ACCION_SINERRORES; 
	global $ACCION_CONERRORES; 

	global $LITACCION_EXITOSA;
	global $LITACCION_FALLIDA;
	global $LITACCION_TERMINADA;
	global $LITACCION_ABORTADA;
	global $LITACCION_SINERRORES;
	global $LITACCION_CONERRORES;

	global $fechainicio;
	global $fechafin;
	global $horainicio;
	global $horafin;
	
	global $wtiposacciones;
	global $westados;
	global $wresultados;
	global $porcendesde;
	global $porcenhasta;

	$HTMLCriterios="";
	$HTMLCriterios.='<TABLE class=tabla_busquedas align=center border="0">'.chr(13);
	$HTMLCriterios.='<TR HEIGHT=30>'.chr(13);
	$HTMLCriterios.='<TD style="	BORDER-BOTTOM:#5a86b5 1px solid;"colspan=2 align="center" >'.chr(13);
	$HTMLCriterios.='<SPAN style="FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; FONT-SIZE: 11px;COLOR:#5a86b5;FONT-WEIGHT: 700;">____ '.$TbMsg[1].'____</SPAN>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);
	$HTMLCriterios.='</TD>'.chr(13);

	$HTMLCriterios.='<TR>'.chr(13);
	$HTMLCriterios.='<TD>'.chr(13);

	// Desplegable con los tipos de acciones
	$HTMLCriterios.='<TABLE class=tabla_standar align=center border="0">'.chr(13);
	$HTMLCriterios.='<TR>'.chr(13);
	$HTMLCriterios.='	<TH align=center>&nbsp;'.$TbMsg[2].'&nbsp;</TH>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);
	$HTMLCriterios.='<TR>'.chr(13);
	$parametros=$EJECUCION_COMANDO."=".$LITEJECUCION_COMANDO.chr(13);
	$parametros.=$EJECUCION_TAREA."=".$LITEJECUCION_TAREA.chr(13);
	$parametros.=$EJECUCION_TRABAJO."=".$LITEJECUCION_TRABAJO;
	$HTMLCriterios.='<TD colspan=3>'.HTMLCTEMULSELECT($parametros,"wtiposacciones",$wtiposacciones,"estilodesple","chgdespleacciones",100,3).'</TD>';
	$HTMLCriterios.='</TR>'.chr(13);
	$HTMLCriterios.='</TABLE>'.chr(13);

	// Desplegable con los distintos estados
	$HTMLCriterios.='<TABLE class=tabla_standar align=center border="0">'.chr(13);
	$HTMLCriterios.='<TR>'.chr(13);
	$HTMLCriterios.='	<TH align=center>&nbsp;'.$TbMsg[4].'&nbsp;</TH>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);
	$HTMLCriterios.='<TR>'.chr(13);
	$parametros=$ACCION_DETENIDA."=".$LITACCION_DETENIDA.chr(13);
	$parametros.=$ACCION_INICIADA."=".$LITACCION_INICIADA.chr(13);
	$parametros.=$ACCION_FINALIZADA."=".$LITACCION_FINALIZADA;
	$HTMLCriterios.='<TD colspan=3>'.HTMLCTEMULSELECT($parametros,"westados",$westados,"estilodesple","chgdespleestados",100,3).'</TD>';
	$HTMLCriterios.='</TR>'.chr(13);
	$HTMLCriterios.='</TABLE>'.chr(13);

	$HTMLCriterios.='</TD>'.chr(13);
	$HTMLCriterios.='<TD valign=top>'.chr(13);
	// Desplegable con los distintos resultados 
	$HTMLCriterios.='<TABLE class=tabla_standar align=center border="0">'.chr(13);
	$HTMLCriterios.='<TR>'.chr(13);
	$HTMLCriterios.='	<TH align=center>&nbsp;'.$TbMsg[3].'&nbsp;</TH>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);
	$HTMLCriterios.='<TR>'.chr(13);
	$parametros=$ACCION_EXITOSA."=".$LITACCION_EXITOSA.chr(13);
	$parametros.=$ACCION_FALLIDA."=".$LITACCION_FALLIDA.chr(13);
	$parametros.=$ACCION_TERMINADA."=".$LITACCION_TERMINADA.chr(13);
	$parametros.=$ACCION_ABORTADA."=".$LITACCION_ABORTADA.chr(13);
	$parametros.=$ACCION_SINERRORES."=".$LITACCION_SINERRORES.chr(13);
	$parametros.=$ACCION_CONERRORES."=".$LITACCION_CONERRORES;
	$HTMLCriterios.='<TD colspan=3>'.HTMLCTEMULSELECT($parametros,"wresultados",$wresultados,"estilodesple","chgdespleresultados",250,6).'</TD>';
	$HTMLCriterios.='</TR>'.chr(13);

	// Porcentajes
	$HTMLCriterios.='<TR>'.chr(13);
	$HTMLCriterios.='<TH>&nbsp;'.$TbMsg[5].':&nbsp;<INPUT size=1 name="porcendesde" value="'.$porcendesde.'">&nbsp;'.$TbMsg[6].':&nbsp;<INPUT size =1 name="porcenhasta" value="'.$porcenhasta.'"></TH>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);
	$HTMLCriterios.='</TABLE>'.chr(13);

	$HTMLCriterios.='<TR>'.chr(13);
	$HTMLCriterios.='<TD  style="BORDER-BOTTOM:#5a86b5 1px solid;" COLSPAN=2>'.chr(13);
	// Fechas
	$HTMLCriterios.='<TABLE WIDTH=100% class=tabla_standar align=center border="0">'.chr(13);
	$HTMLCriterios.='<TR>'.chr(13);
	$HTMLCriterios.='<TH>&nbsp;'.$TbMsg[7].':&nbsp;</TH>'.chr(13);
	$HTMLCriterios.='<TD><INPUT class="cajatexto" onclick="vertabla_calendario(this)" style="WIDTH:80" name="fechainicio" value="'.$fechainicio.'"></TD>'.chr(13);
	$HTMLCriterios.='<TH align=right>&nbsp;'.$TbMsg[8].':&nbsp;&nbsp;</TH>'.chr(13);
	$HTMLCriterios.='<TD align=right><INPUT class="cajatexto" onclick="vertabla_calendario(this)" style="WIDTH:80" name="fechafin" value="'.$fechafin.'"></TD>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);
	$HTMLCriterios.='<TR>'.chr(13);
	$HTMLCriterios.='<TH>&nbsp;'.$TbMsg[9].':&nbsp;</TH>'.chr(13);
	$HTMLCriterios.='<TD><INPUT class="cajatexto" onclick="vertabla_horario(this)" style="WIDTH:80" name="horainicio" value="'.$horainicio.'"></TD>'.chr(13);
	$HTMLCriterios.='<TH align=right>&nbsp;'.$TbMsg[10].':&nbsp;&nbsp;</TH>'.chr(13);
	$HTMLCriterios.='<TD align=right><INPUT class="cajatexto" onclick="vertabla_horario(this)" style="WIDTH:80" name="horafin" value="'.$horafin.'"></TD>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);
	$HTMLCriterios.='</TABLE>'.chr(13);
	
	// Fechas
	$HTMLCriterios.='</TD>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);
	
	$HTMLCriterios.='</TD>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);
	$HTMLCriterios.='</TABLE>'.chr(13);

	$HTMLCriterios.='<TABLE class=tabla_busquedas align=center border="0">'.chr(13);
	$HTMLCriterios.='<TR>'.chr(13);
	$HTMLCriterios.='<TD>';
	// Lupa
	$HTMLCriterios.='<IMG src="../images/iconos/busquedas.gif" onclick="javascript:fdatos.submit()" style="cursor:hand" alt="Buscar">';
	$HTMLCriterios.='</TD>';
	$HTMLCriterios.='<TD>';
		
	$HTMLCriterios.='</TD>'.chr(13);
	$HTMLCriterios.='</TR>'.chr(13);
	$HTMLCriterios.='</TABLE>';

  return($HTMLCriterios);
}
/*________________________________________________________________________________________________________
	Crea la tabla de ordenadores ( iconos pequeños )
________________________________________________________________________________________________________*/
function PintaOrdenadores($cmd,$cadenaip,$ipesnotificadas,$idaccion){
	global $ACCION_EXITOSA; 
	global $ACCION_FALLIDA; 
	
	if(!empty($ipesnotificadas)){
		$ipesnotificadas=substr($ipesnotificadas,0,strlen($ipesnotificadas)-1); // Quita la coma
		$auxN=split(";",$ipesnotificadas);
		$cont=sizeof($auxN);
		$tbipes="";
		$tbresipes="";
		$tbnotif="";
		for ($i=0;$i<$cont;$i++){
			$dualvalor=split("=",$auxN[$i]);
			$tbipes[$i]=$dualvalor[0];
			$tbresipes[$i]=$dualvalor[1];
			$tbnotif[$i]=$dualvalor[2];
		}
		$auxtbipes="";
		$auxtbresipes="";
		$auxtbnotif="";
		// Ordena según la ip
		for ($i=0;$i<$cont-1;$i++){
			for ($j=$i+1;$j<$cont;$j++){
				if($tbipes[$i]>$tbipes[$j]){
					$auxtbipes=$tbipes[$i];
					$tbipes[$i]=$tbipes[$j];
					$tbipes[$j]=$auxtbipes;

					$auxtbresipes=$tbresipes[$i];
					$tbresipes[$i]=$tbresipes[$j];
					$tbresipes[$j]=$auxtbresipes;

					$auxtbnotif=$tbnotif[$i];
					$tbnotif[$i]=$tbnotif[$j];
					$tbnotif[$j]=$auxtbnotif;
				}
			}
		}
	}
	$auxP=split(";",$cadenaip); 
	if(sizeof($auxP)<1) return("");

	$clauslaIN="'".$auxP[0]."'";
	for ($i=1;$i<sizeof($auxP);$i++)
		$clauslaIN.=",'".$auxP[$i]."'";

	$rs=new Recordset; 
	$contor=0;
	$maxord=5; // Máximos ordenadores por linea
	$cmd->texto=" SELECT nombreordenador,ip FROM ordenadores  INNER JOIN aulas ON aulas.idaula=ordenadores.idaula WHERE ip IN(".$clauslaIN.") ORDER by nombreaula,nombreordenador";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	$tablaHtml='<TABLE align=left border=0><TR>';
	while (!$rs->EOF){
		$contor++;
		$tablaHtml.= '<TD align=center style="BACKGROUND-COLOR: #b5daad;FONT-FAMILY: Arial, Helvetica, sans-serif;	BORDER-BOTTOM:#000000 none;FONT-SIZE: 8px">';
		if(!empty($ipesnotificadas)){
			$datobinario=$rs->campos["ip"];
			$posp=busca_indicebinario($datobinario,$tbipes,$cont); // Busca ip
		}
		else
			$posp=-1;
		if ($posp>=0){
			if($tbresipes[$posp]==$ACCION_EXITOSA)
				$tablaHtml.='<IMG id="ORDNOT_'.$idaccion."_".$tbnotif[$posp].'" src="../images/iconos/ordenadornot_ok.gif">';
			else
				$tablaHtml.='<IMG id="ORDNOT_'.$idaccion."_".$tbnotif[$posp].'" src="../images/iconos/ordenadornot_ko.gif">';
		}
		else // No ha notificado
			$tablaHtml.='<IMG src="../images/iconos/ordenadornot.gif">';
		$tablaHtml.='<br><span style="FONT-SIZE:9px" >'.$rs->campos["nombreordenador"].'</TD>';
		if($contor>$maxord){
			$contor=0;
			$tablaHtml.='</TR><TR>';
		}
		$rs->Siguiente();
}
	$tablaHtml.='</TR>';
	$tablaHtml.= '</TR></TABLE>';
	return($tablaHtml);
}
//________________________________________________________________________________________________________
function ContextualXMLComando(){
	global $TbMsg;
	global $idcmdtskwrk;
	global $codtipoaccion;
	global $accionid;
	global $EJECUCION_TAREA;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_comandos"';
	$layerXML.=' maxanchu=130';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	if($idcmdtskwrk=="" && $codtipoaccion==""){
		$layerXML.='<ITEM';
		$layerXML.=' alpulsar="filtrar_accion()"';
		$layerXML.=' imgitem="../images/iconos/filtroaccion.gif"';
		$layerXML.=' textoitem='.$TbMsg[41];
		$layerXML.='></ITEM>';

		$layerXML.='<ITEM';
		$layerXML.=' alpulsar="filtrar_porambito()"';
		$layerXML.=' imgitem="../images/iconos/filtroambito.gif"';
		$layerXML.=' textoitem='.$TbMsg[42];
		$layerXML.='></ITEM>';
	}
	else{
		$layerXML.='<ITEM';
		$layerXML.=' alpulsar="quitar_filtro()"';
		$layerXML.=' imgitem="../images/iconos/filtro_off.gif"';
		$layerXML.=' textoitem='.$TbMsg[43];
		$layerXML.='></ITEM>';
	}

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_modifacciones"';
	$layerXML.=' textoitem='.$TbMsg[44];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="reiniciar_accion()"';
	$layerXML.=' imgitem="../images/iconos/reiniciar.gif"';
	$layerXML.=' textoitem='.$TbMsg[45];
	$layerXML.='></ITEM>';

	if($accionid==0){
		$layerXML.='<ITEM';
		$layerXML.=' alpulsar="eliminar_accion()"';
		$layerXML.=' imgitem="../images/iconos/Eliminar.gif"';
		$layerXML.=' textoitem='.$TbMsg[46];
		$layerXML.='></ITEM>';
	}
	
	if($accionid>0){
		$layerXML.='<SEPARADOR>';
		$layerXML.='</SEPARADOR>';

		$layerXML.='<ITEM';
		$layerXML.=' alpulsar="ver_accionpadre('.$EJECUCION_TAREA.')"';
		$layerXML.=' imgitem="../images/iconos/tareas.gif"';
		$layerXML.=' textoitem='.$TbMsg[47];
		$layerXML.='></ITEM>';
	}
	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLTarea(){	
	global $TbMsg;
	global $idcmdtskwrk;
	global $codtipoaccion;
	global $accionid;
	global $EJECUCION_TRABAJO;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_tareas"';
	$layerXML.=' maxanchu=120';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	if($idcmdtskwrk=="" && $codtipoaccion==""){
		$layerXML.='<ITEM';
		$layerXML.=' alpulsar="filtrar_accion()"';
		$layerXML.=' imgitem="../images/iconos/filtroaccion.gif"';
		$layerXML.=' textoitem='.$TbMsg[41];
		$layerXML.='></ITEM>';
	}
	else{
		$layerXML.='<ITEM';
		$layerXML.=' alpulsar="quitar_filtro()"';
		$layerXML.=' imgitem="../images/iconos/filtro_off.gif"';
		$layerXML.=' textoitem='.$TbMsg[43];
		$layerXML.='></ITEM>';
	}

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_modifacciones"';
	$layerXML.=' textoitem='.$TbMsg[44];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="reiniciar_accion()"';
	$layerXML.=' imgitem="../images/iconos/reiniciar.gif"';
	$layerXML.=' textoitem='.$TbMsg[45];
	$layerXML.='></ITEM>';

	if($accionid==0){
		$layerXML.='<ITEM';
		$layerXML.=' alpulsar="eliminar_accion()"';
		$layerXML.=' imgitem="../images/iconos/Eliminar.gif"';
		$layerXML.=' textoitem='.$TbMsg[46];
		$layerXML.='></ITEM>';
	}

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	if($accionid>0){
		$layerXML.='<ITEM';
		$layerXML.=' alpulsar="ver_accionpadre('.$EJECUCION_TRABAJO.')"';
		$layerXML.=' imgitem="../images/iconos/trabajos.gif"';
		$layerXML.=' textoitem='.$TbMsg[48];
		$layerXML.='></ITEM>';
	}
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_accion()"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[49];
	$layerXML.='></ITEM>';
	
	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLTrabajo(){
	global $TbMsg;
	global $idcmdtskwrk;
	global $codtipoaccion;
	global $accionid;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_trabajos"';
	$layerXML.=' maxanchu=120';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	if($idcmdtskwrk=="" && $codtipoaccion==""){
		$layerXML.='<ITEM';
		$layerXML.=' alpulsar="filtrar_accion()"';
		$layerXML.=' imgitem="../images/iconos/filtroaccion.gif"';
		$layerXML.=' textoitem='.$TbMsg[41];
		$layerXML.='></ITEM>';
	}
	else{
		$layerXML.='<ITEM';
		$layerXML.=' alpulsar="quitar_filtro()"';
		$layerXML.=' imgitem="../images/iconos/filtro_off.gif"';
		$layerXML.=' textoitem='.$TbMsg[43];
		$layerXML.='></ITEM>';
	}

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_modifacciones"';
	$layerXML.=' textoitem='.$TbMsg[44];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="reiniciar_accion()"';
	$layerXML.=' imgitem="../images/iconos/reiniciar.gif"';
	$layerXML.=' textoitem='.$TbMsg[45];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar_accion()"';
	$layerXML.=' imgitem="../images/iconos/Eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[46];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_accion()"';
	$layerXML.=' imgitem="../images/iconos/tareas.gif"';
	$layerXML.=' textoitem='.$TbMsg[50];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLModifAcciones(){
	global $TbMsg;
	global $ACCION_TERMINADA; // Finalizada manualmente con indicacion de exito 
	global $ACCION_ABORTADA; // Finalizada manualmente con indicacion de errores 
	global $ACCION_DETENIDA;
	global $ACCION_INICIADA;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_modifacciones"';
	$layerXML.=' maxanchu=120';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_estado('.$ACCION_DETENIDA.')"';
	$layerXML.=' imgitem="../images/iconos/acDetenida.gif"';
	$layerXML.=' textoitem='.$TbMsg[51];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_estado('.$ACCION_INICIADA.')"';
	$layerXML.=' imgitem="../images/iconos/acIniciada.gif"';
	$layerXML.=' textoitem='.$TbMsg[52];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_resultado('.$ACCION_ABORTADA.')"';
	$layerXML.=' imgitem="../images/iconos/acAbortada.gif"';
	$layerXML.=' textoitem='.$TbMsg[53];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_resultado('.$ACCION_TERMINADA.')"';
	$layerXML.=' imgitem="../images/iconos/acTerminada.gif"';
	$layerXML.=' textoitem='.$TbMsg[54];
	$layerXML.='></ITEM>';

	
	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLNotificaciones(){
	global $TbMsg;
	global $ACCION_EXITOSA; // Finalizada con exito
	global $ACCION_FALLIDA; // Finalizada con errores

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_notificaciones"';
	$layerXML.=' maxanchu=135';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_resultado_notificacion('.$ACCION_EXITOSA.')"';
	$layerXML.=' imgitem="../images/iconos/acExitosa.gif"';
	$layerXML.=' textoitem='.$TbMsg[55];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_resultado_notificacion('.$ACCION_FALLIDA.')"';
	$layerXML.=' imgitem="../images/iconos/acFallida.gif"';
	$layerXML.=' textoitem='.$TbMsg[56];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="reiniciar_notificacion()"';
	$layerXML.=' imgitem="../images/iconos/reiniciar.gif"';
	$layerXML.=' textoitem='.$TbMsg[45];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>