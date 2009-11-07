<?  
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: propiedades_perfilsoftwares.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un pefil software para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_perfilsoftwares_".$idioma.".php"); 
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idperfilsoft=0; 
$descripcion="";
$comentarios="";
$grupoid=0;
$imagenes=0; // Número de imagenes que tienen este perfil

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idperfilsoft"])) $idperfilsoft=$_GET["idperfilsoft"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idperfilsoft=$_GET["identificador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idperfilsoft);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_perfilsoftwares.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_perfilsoftwares_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<DIV  align=center>
<FORM  name="fdatos"> 
	<INPUT type=hidden name=idperfilsoft value=<?=$idperfilsoft?>>
	<INPUT type=hidden name=imagenes value=<?=$imagenes?>>
	<INPUT type=hidden name=grupoid value=<?=$grupoid?>>
	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
					echo '<TD style="width:215">'.$descripcion.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=descripcion style="width:215" type=text value="'.$descripcion.'"></TD>';?>
			<TD align=left rowspan=2><IMG border=3 style="border-color:#63676b" src="../images/aula.jpg"><br><center>&nbsp;Images:&nbsp;<? echo $imagenes?></center></TD>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TH>
			<?if ($opcion==$op_eliminacion)
					echo '<TD>'.$comentarios.'</TD>';
				else
					echo '<TD><TEXTAREA   class="formulariodatos" name=comentarios rows=3 cols=40>'.$comentarios.'</TEXTAREA></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
</FORM>
</DIV>
<?
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
//	Recupera los datos de un perfil software
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador del perfil software
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $descripcion;
	global $comentarios;

	global $imagenes;

	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM perfilessoft WHERE idperfilsoft=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$descripcion=$rs->campos["descripcion"];
		$comentarios=$rs->campos["comentarios"];
		$rs->Cerrar();
		$cmd->texto="SELECT count(*) as numimagenes FROM imagenes WHERE idperfilsoft=".$id;
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(0); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF)
			$imagenes=$rs->campos["numimagenes"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>
