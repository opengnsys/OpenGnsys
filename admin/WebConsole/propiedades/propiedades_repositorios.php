<?php
// *********************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaciónn: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_repositorios.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un repositorio para insertar,modificar y eliminar
// **********************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_repositorios_".$idioma.".php"); 
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idrepositorio=0; 
$nombrerepositorio="";
$ip="";
$puertorepo="2002";
$pathrepod="/opt/opengnsys/admin";
$pathpxe="/opt/opengnsys/tftpboot/pxelinux.cfg";
$grupoid=0;
$comentarios="";
$ordenadores=0; // Número de ordenador a los que da servicio

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idrepositorio"])) $idrepositorio=$_GET["idrepositorio"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idrepositorio=$_GET["identificador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con repositorio B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idrepositorio);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci�n de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_repositorios.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_repositorios_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<DIV  align=center>
<FORM  name="fdatos" action="../gestores/gestor_repositorios.php" method="post"> 
	<INPUT type=hidden name=opcion value="<? echo $opcion?>">
	<INPUT type=hidden name=idrepositorio value="<? echo $idrepositorio?>">
	<INPUT type=hidden name=grupoid value="<? echo $grupoid?>">
	<INPUT type=hidden name=ordenadores value="<? echo $ordenadores?>">

	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos >
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
			<?php
				if ($opcion==$op_eliminacion)
					echo '<TD>'.$nombrerepositorio.'</TD>';
				else	
					echo '<TD><INPUT  class="formulariodatos" name=nombrerepositorio style="width:200" type=text value="'.$nombrerepositorio.'"></TD>';
			?>
			<TD valign=top align=left rowspan=3	><CENTER>
				<IMG border=3 style="border-color:#63676b" src="../images/aula.jpg">
				<BR>&nbsp;Ordenadores:&nbsp;<? echo $ordenadores?></CENTER></TD>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TD>
			<?php
			if ($opcion==$op_eliminacion)
				echo '<TD>'.$ip.'</TD>';
			else	
				echo'<TD><INPUT  class="formulariodatos" name=ip type=text style="width:200" value="'.$ip.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[8]?>&nbsp;</TD>
			<?php
			if ($opcion==$op_eliminacion)
				echo '<TD>'.$puertorepo.'</TD>';
			else	
				echo'<TD><INPUT  class="formulariodatos" name=puertorepo type=text style="width:200" value="'.$puertorepo.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[9]?>&nbsp;</TD>
			<?php
			if ($opcion==$op_eliminacion)
				echo '<TD colspan=2>'.$pathrepod.'</TD>';
			else	
				echo'<TD colspan=2><INPUT  class="formulariodatos" name=pathrepod type=text style="width:330" value="'.$pathrepod.'"></TD>';
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[10]?>&nbsp;</TD>
			<?php
			if ($opcion==$op_eliminacion)
				echo '<TD colspan=2>'.$pathpxe.'</TD>';
			else	
				echo'<TD colspan=2><INPUT  class="formulariodatos" name=pathpxe type=text style="width:330" value="'.$pathpxe.'"></TD>';
			?>
		</TR>		
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TD>
			<?php
			if ($opcion==$op_eliminacion)
				echo '<TD colspan=2>'.$comentarios.'</TD>';
			else	
				echo '<TD colspan=2><TEXTAREA   class="formulariodatos" name=comentarios rows=2 cols=50>'.$comentarios.'</TEXTAREA></TD>';
			?>
		</TR>	

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	
	</TABLE>
</FORM>
</DIV>
<?php
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
//	Recupera los datos de un repositorio
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador del repositorio
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $nombrerepositorio;
	global $ip;
	global $comentarios;
	global $puertorepo;
	global $pathrepod;
	global $pathpxe;
	global $ordenadores;

	$cmd->texto="SELECT  repositorios.*, COUNT(ordenadores.idordenador) AS numordenadores
			FROM repositorios 
	 		LEFT OUTER JOIN ordenadores ON ordenadores.idrepositorio=repositorios.idrepositorio
			WHERE repositorios.idrepositorio=".$id;
	$rs=new Recordset;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(true); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombrerepositorio=$rs->campos["nombrerepositorio"];
		$ip=$rs->campos["ip"];
		$comentarios=$rs->campos["comentarios"];
		$puertorepo=$rs->campos["puertorepo"];
		$pathrepod=$rs->campos["pathrepod"];
		$pathpxe=$rs->campos["pathpxe"];
		$ordenadores=$rs->campos["numordenadores"];
	}
	$rs->Cerrar();
	return(true);
}
?>

