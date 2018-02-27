<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: informacion_acciones.php
// Descripción : 
//		Muestra los comandos que forman parte de un procedimiento y sus valores
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../clases/XmlPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/TomaDato.php");	
include_once("../clases/ArbolVistaXML.php");
include_once("../idiomas/php/".$idioma."/informacion_acciones_".$idioma.".php");
//________________________________________________________________________________________________________

$tipoaccion=0;
$idtipoaccion=0; 
$descripcionaccion="";

if (isset($_GET["tipoaccion"])) $tipoaccion=$_GET["tipoaccion"]; 
if (isset($_GET["idtipoaccion"])) $idtipoaccion=$_GET["idtipoaccion"];
if (isset($_GET["descripcionaccion"])) $descripcionaccion=$_GET["descripcionaccion"];
//________________________________________________________________________________________________________

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.

	
$tbParametros=CreaTablaParametros($cmd); // Crea tabla en memmoria para acceder a detalles de comandos 
$cadenaXML="";
CreaArbol($cmd,$tipoaccion,$idtipoaccion,$descripcionaccion); // Crea el arbol XML 
//________________________________________________________________________________________________________

// Creación del árbol
$baseurlimg="../images/tsignos";
$clasedefault="tabla_listados_sin";
$titulotabla=$TbMsg[5];  
$arbol=new ArbolVistaXml($cadenaXML,0,$baseurlimg,$clasedefault,1,20,130,1,$titulotabla);
//________________________________________________________________________________________________________
?>
<HTML>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
</HEAD>
<BODY>
	<?php
		switch($tipoaccion){
			case $AMBITO_PROCEDIMIENTOS:
				$urlimg="../images/iconos/procedimiento.gif";
				$litsub=$TbMsg[2];
				$litcab=$TbMsg[1];
				break;							
			case $AMBITO_TAREAS:
				$urlimg="../images/iconos/tareas.gif";
				$litsub=$TbMsg[4];	
				$litcab=$TbMsg[3];					
				break;					
		}
	?>
	<P align=center class=cabeceras><?php echo $litcab?><BR>
	<SPAN align=center class=subcabeceras><?php echo $litsub?></SPAN>&nbsp;<IMG src="../images/iconos/acciones.gif"><BR>
	<IMG src="<?php echo $urlimg?>"><SPAN class=presentaciones>&nbsp;&nbsp;<?php echo $descripcionaccion?></SPAN></P>
	<?php echo urldecode($arbol->CreaArbolVistaXml()); // Crea arbol de configuraciones?>
</BODY>
</HTML>
<?php
/********************************************************************************************************
	Devuelve una cadena con formato XML de toda la Información de los procedimientos o tareas
	softwares
	Parametros: 
		- cmd: Un comando ya operativo ( con conexiónabierta)  
		- idperfil: El identificador del perfil software
________________________________________________________________________________________________________*/

function CreaArbol($cmd,$tipoaccion,$idtipoaccion,$descripcionaccion)
{
	global $AMBITO_PROCEDIMIENTOS;
	global $AMBITO_TAREAS;

	switch($tipoaccion){
		case $AMBITO_PROCEDIMIENTOS:
			SubarbolXML_procedimientos($cmd,$idtipoaccion);
			break;							
		case $AMBITO_TAREAS:
			SubarbolXML_Tareas($cmd,$idtipoaccion);
			break;					
	}
}
//________________________________________________________________________________________________________
function SubarbolXML_Tareas($cmd,$idtarea)
{
	global $cadenaXML;

	$cmd->texto="SELECT tareas.descripcion as descritarea,procedimientos.descripcion as descriprocedimiento,
				tareas_acciones.orden,tareas_acciones.idprocedimiento,tareas_acciones.tareaid,
				tareas.ambito,tareas.idambito,tareas.restrambito
				FROM tareas
				INNER JOIN tareas_acciones ON tareas_acciones.idtarea=tareas.idtarea
				LEFT OUTER JOIN procedimientos ON procedimientos.idprocedimiento=tareas_acciones.idprocedimiento				 
				WHERE tareas_acciones.idtarea=".$idtarea." 
				ORDER BY tareas_acciones.orden";					
	//echo $cmd->texto;						 			
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$cadenaXML.='<TAREA';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/tareas.gif"';
	$cadenaXML.=' infonodo="'.$rs->campos["descritarea"].'"';
	$cadenaXML.='>';	
	while (!$rs->EOF){
		if($rs->campos["tareaid"]>0) // Tarea recursiva
			SubarbolXML_Tareas($cmd,$rs->campos["tareaid"]);
		else{
			SubarbolXML_procedimientos($cmd,$rs->campos["idprocedimiento"]);
		}
		$rs->Siguiente();
	}
	$cadenaXML.='</TAREA>';	
}
//________________________________________________________________________________________________________
function SubarbolXML_procedimientos($cmd,$idprocedimiento)
{
	global $cadenaXML;

	$cmd->texto="SELECT procedimientos.descripcion as descriprocedimiento,procedimientos_acciones.idcomando,
				comandos.descripcion as descricomando,comandos.visuparametros,procedimientos_acciones.procedimientoid,
				procedimientos_acciones.parametros
				FROM procedimientos
				INNER JOIN procedimientos_acciones ON procedimientos_acciones.idprocedimiento=procedimientos.idprocedimiento
				LEFT OUTER JOIN comandos ON comandos.idcomando=procedimientos_acciones.idcomando
				WHERE procedimientos.idprocedimiento=".$idprocedimiento." 
				ORDER BY orden";	
	//echo $cmd->texto;						 			
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$cadenaXML.='<PROCEDIMIENTO';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/procedimiento.gif"';
	$cadenaXML.=' infonodo="'.$rs->campos["descriprocedimiento"].'"';
	$cadenaXML.='>';	
	while (!$rs->EOF){
		if($rs->campos["procedimientoid"]>0) // Procedimiento recursivo
			SubarbolXML_procedimientos($cmd,$rs->campos["procedimientoid"]);
		else{
			SubarbolXML_comandos($rs->campos["descricomando"],$rs->campos["parametros"],$rs->campos["visuparametros"]);
		}
	
		$rs->Siguiente();
	}
	$cadenaXML.='</PROCEDIMIENTO>';	
}
//________________________________________________________________________________________________________
function SubarbolXML_comandos($descricomando,$parametros,$visuparametros)
{
	global $cadenaXML;

	$cadenaXML.='<COMANDO';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/comandos.gif"';
	$cadenaXML.=' infonodo="'.$descricomando.'"';
	$cadenaXML.='>';
	escribeParametros($parametros,$visuparametros);
	$cadenaXML.='</COMANDO>';	
}
	//________________________________________________________________________________________________________

	function escribeParametros($parametros,$visuparametros)
	{	
		global $cmd;
		global $cadenaXML;

		$tbParametrosValor=array();
		ParametrosValor($cmd,$parametros,$tbParametrosValor); // Toma valores de cada parámetro
		$visuprm=explode(";",$visuparametros);
		for($i=0;$i<sizeof($visuprm);$i++){
			$nemo=$visuprm[$i];
			if(isset($tbParametrosValor[$nemo])){
				for($j=0;$j<sizeof($tbParametrosValor[$nemo])-1;$j++){
					$descripcion=$tbParametrosValor[$nemo]["descripcion"];
					if(sizeof($tbParametrosValor[$nemo])>2)
						$valor=$tbParametrosValor[$nemo][$j]["valor"];
					else
						$valor=$tbParametrosValor[$nemo]["valor"];
					escribiendoParametros($descripcion,$valor);
				}	
			}	
		}	
	}
	//________________________________________________________________________________________________________

	function escribiendoParametros($descripcion,$valor)
	{
		global $cadenaXML;

		$cadenaXML.='<PARAMETRO';
		// Atributos
		$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
		$litprm=$descripcion.': <B>'.$valor.'</B>';
		$cadenaXML.=' infonodo="'.urlencode($litprm).'"';
		$cadenaXML.='>';
		$cadenaXML.='</PARAMETRO>';		
	}

?>
