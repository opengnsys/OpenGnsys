<?
// ********************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Agosto-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: L_Iconos.php
// Descripción :Este fichero implementa  el mantenimiento de la tabla Iconos
// ********************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLCTESELECT.php");
//-------------------------------------------------------------------------------------------------------------------------------------------------
// Captura de parámetros 
//-------------------------------------------------------------------------------------------------------------------------------------------------

$idtipoicono="";

if (isset($_POST["idtipoicono"])) $idtipoicono=$_POST["idtipoicono"];
//-------------------------------------------------------------------------------------------------------------------------------------------------
// Conexion a la base de datos 
//-------------------------------------------------------------------------------------------------------------------------------------------------
$cmd=CreaComando($cadenaconexion);
if (!$cmd) // Fallo conexión con servidor de datos
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//-------------------------------------------------------------------------------------------------------------------------------------------------
// Búsquedas 
//-------------------------------------------------------------------------------------------------------------------------------------------------
$cmd->texto="SELECT * FROM iconos WHERE idicono>0";
if (!empty($idtipoicono))	 // Tipo
		$cmd->texto.=" AND idtipoicono=".$idtipoicono;
$cmd->texto.=" order by idtipoicono,descripcion ";
$rs=new Recordset; 
$rs->Comando=&$cmd; 
if (!$rs->Abrir())
	RedireccionaError("Fallo al abrir la tabla: Iconos");
?>
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript">
var IE=(navigator.appName=="Microsoft Internet Explorer");
var NS=(navigator.appName=="Netscape");
</SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
<SCRIPT language="javascript" src="L_Iconos.js"></SCRIPT>
</HEAD>
<BODY OnContextMenu="return false">
  <P align="center"><SPAN class=textos>____ Opciones de búsqueda ____</SPAN></P>
   <FORM name="fdatos" action="L_Iconos.php" method="post">
	<INPUT type=hidden name=identificador value="0">
	<TABLE align=center class=tabla_busquedas>
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH>&nbsp;Tipo&nbsp;</TD>
			<TD ><?
					$parametros="0=".chr(13);
					$parametros.="1=iconos web".chr(13);
					$parametros.="2=iconos items".chr(13);
					$parametros.="3=Fondo menu";
					echo '<TD>'.HTMLCTESELECT($parametros, "idtipoicono","estilodesple","",$idtipoicono,100).'</TD>';?>
			</TD>
		</TR>
	</TABLE>
	<BR>
	
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
<P align=center><A href="#"><img border=0 src="../images/iconos/busquedas.gif" onclick="document.fdatos.submit()" alt="Buscar"></A></P>
</FORM>
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
<P align="center"><SPAN class=textos>Registros encontrados : <? echo $rs->numeroderegistros?></SPAN></P>
<TABLE align="center" class="tabla_listados">
  <TR>
	<TH align="center">A</TH>
	<TH align="center">&nbsp;<? echo utf8_encode(Descripción)?>&nbsp;</TH>
	<TH align="center">&nbsp;Nombre&nbsp;</TH>
	<TH align="center">&nbsp;</TH>
	<TH align="center">&nbsp;T&nbsp;</TH>

	</TR>
  <?
	$TBtipo[1]="W";
	$TBtipo[2]="I";
	$TBtipo[3]="F";	
  while (!$rs->EOF){?>
	<TR>
		<TD  align=center><IMG  id=<?=$rs->campos["idicono"]?> style="cursor:hand" onclick="menu_contextual(this)" src="../images/iconos/administrar_off.gif"></TD>
		<TD>&nbsp;<? echo basename($rs->campos["descripcion"])?>&nbsp;</TD>
		<TD>&nbsp;<? echo basename($rs->campos["urlicono"])?>&nbsp;</TD>
		<TD align=center>&nbsp;<IMG src="./iconos/<? echo $rs->campos["urlicono"] ?>"

		<? if ($rs->campos["idtipoicono"]==2) //icono item 
			echo " width=64 ";
		else
			echo " width=16 ";
		?>
		>&nbsp;</TD>
		<TD align=center>&nbsp;<? echo $TBtipo[$rs->campos["idtipoicono"]] ?>&nbsp;</TD>
  </TR>
   <?  $rs->Siguiente();}?>
</TABLE>
<? 
//-------------------------------------------------------------------------------------------------------------------------------------------------
// Menu contextual
//-------------------------------------------------------------------------------------------------------------------------------------------------
$flotante=new MenuContextual(); // Crea objeto MenuContextual

$XMLcontextual=CreacontextualXMLMenu(); // Crea contextual de las acciones
echo $flotante->CreaMenuContextual($XMLcontextual); 
?>
</BODY>
</HTML>
<?
//-------------------------------------------------------------------------------------------------------------------------------------------------
//	Menus contextuales
//-------------------------------------------------------------------------------------------------------------------------------------------------
function CreacontextualXMLMenu(){

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' maxanchu=110';
	$layerXML.=' idctx="flo_menu"';
	$layerXML.=' swimg=1';
	$layerXML.=' origen_x=100';
	$layerXML.=' origen_y=300';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="consultar()"';
	$layerXML.=' imgitem="../images/iconos/consultar.gif"';
	$layerXML.=' textoitem="Consultar"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar()"';
	$layerXML.=' textoitem="Modificar"';
	$layerXML.=' imgitem="../images/iconos/modificar.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="borrar()"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem="Eliminar"';
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>
