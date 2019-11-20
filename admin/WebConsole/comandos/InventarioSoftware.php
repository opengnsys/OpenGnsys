<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: InventarioSoftware.php
// Descripción : 
//		Implementación del comando "Inventario Software"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/pintaTablaConfiguraciones.php");
include_once("../idiomas/php/".$idioma."/comandos/inventariosoftware_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/pintaParticiones_".$idioma.".php");
include_once("./includes/capturaacciones.php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");

//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
	<TITLE>Administración web de aulas</TITLE>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="./jscripts/InventarioSoftware.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
	<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/inventariosoftware_'.$idioma.'.js"></SCRIPT>'?>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<?php
switch($ambito){
		case $AMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito=$TbMsg[0];
			break;
		case $AMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[1];
			break;
		case $AMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito=$TbMsg[2];
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[3];
			break;
		case $AMBITO_ORDENADORES :
			$urlimg='../images/iconos/ordenador.gif';
			$textambito=$TbMsg[4];
			break;
	}
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[6].': '.$textambito.','.$nombreambito.'</U></span>&nbsp;&nbsp;</span></p>';
?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<P align=center><SPAN class=subcabeceras><?php echo $TbMsg[7] ?></SPAN></p>

		<?php echo tablaConfiguracionesInventarioSoftware($cmd,$idambito); ?>

	<BR>
<?php
//________________________________________________________________________________________________________
	include_once("./includes/formularioacciones.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
	include_once("./includes/opcionesacciones.php");
	//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?php
/*________________________________________________________________________________________________________
	Particiones
________________________________________________________________________________________________________*/
function tabla_configuraciones($cmd,$idordenador)
{
	global $idcentro;
	$tablaHtml="";
	$rs=new Recordset; 
	$rsp=new Recordset; 
	$cmd->texto="SELECT ordenadores_particiones.numpar,nombresos.nombreso
				FROM ordenadores_particiones 
				INNER JOIN tipospar ON tipospar.codpar=ordenadores_particiones.codpar
				INNER JOIN nombresos ON ordenadores_particiones.idnombreso=nombresos.idnombreso
				WHERE ordenadores_particiones.idordenador=$idordenador 
				AND tipospar.clonable>0 AND ordenadores_particiones.idnombreso>0
				ORDER BY ordenadores_particiones.numpar";
	$rs->Comando=&$cmd; 
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
			$tablaHtml.='<TR>'.chr(13);
			$tablaHtml.='<TD ><input type="radio" name="particion"  value='.$rs->campos["numpar"].'></TD>'.chr(13);
			$tablaHtml.='<TD align=center>&nbsp;'.$rs->campos["numpar"].'&nbsp;</TD>'.chr(13);
			$tablaHtml.='<TD>&nbsp;'.$rs->campos["nombreso"].'&nbsp;</TD>'.chr(13);
			$tablaHtml.='</TR>'.chr(13);
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($tablaHtml);
}
?>
