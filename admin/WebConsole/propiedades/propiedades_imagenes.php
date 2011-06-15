<? 
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: propiedades_imagenes.php
// Descripción : 
//		 Presenta el formulario de captura de datos de una imagen para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/HTMLSELECT.php");
include_once("../idiomas/php/".$idioma."/propiedades_imagenes_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idimagen=0; 
$nombreca="";
$descripcion="";
$idperfilsoft=0;
$comentarios="";
$grupoid=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"];  // Recoge parametros
if (isset($_GET["idimagen"])) $idimagen=$_GET["idimagen"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idimagen=$_GET["identificador"];
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idimagen);
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
	<SCRIPT language="javascript" src="../jscripts/validators.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/propiedades_imagenes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_imagenes_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<DIV  align=center>
<FORM  name="fdatos" action="../gestores/gestor_imagenes.php" method="post"> 
	<INPUT type=hidden name=opcion value=<?=$opcion?>>
	<INPUT type=hidden name=idimagen value=<?=$idimagen?>>
	<INPUT type=hidden name=grupoid value=<?=$grupoid?>>
	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
	<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[11]?>&nbsp;</TD>
			<?if ($opcion==$op_eliminacion)
					echo '<TD style="width:150">'.$nombreca.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=nombreca style="width:150" type=text value="'.$nombreca.'"></TD>';?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
			<?if ($opcion==$op_eliminacion)
					echo '<TD style="width:300">'.$descripcion.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=descripcion style="width:300" type=text value="'.$descripcion.'"></TD>';?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD>'.$perfilsoft.'</TD>';
				else
					echo '<TD>'.HTMLSELECT($cmd,$idcentro,'perfilessoft',$idperfilsoft,'idperfilsoft','descripcion',300).'</TD>';
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[8]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD>'.$numpar.'</TD>';
				else
					echo '<TD><INPUT  class="formulariodatos" name=numpar style="width:30" type=text value="'.$numpar.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[9]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD>'.$tipopar.'</TD>';
				else
					echo '<TD>'.HTMLSELECT($cmd,0,'tipospar',$codpar,'codpar','tipopar',100,"","","clonable=1").'</TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[10]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD>'.$nombrerepositorio.'</TD>';
				else
					echo '<TD>'.HTMLSELECT($cmd,$idcentro,'repositorios',$idrepositorio,'idrepositorio','nombrerepositorio',300).'</TD>';
			?>
		</TR>						
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TD>
			<?if ($opcion==$op_eliminacion)
					echo '<TD>'.$comentarios.'</TD>';
				else
					echo '<TD><TEXTAREA   class="formulariodatos" name=comentarios rows=3 cols=55>'.$comentarios.'</TEXTAREA></TD>';
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
</FORM>
<?
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
//	Recupera los datos de una imagen
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador de la imagen
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$idmagen){
	global $nombreca;
	global $descripcion;
	global $comentarios;
	global $idperfilsoft;
	global $numpar;
	global $codpar;
	global $tipopar;
	global $nombrerepositorio;
	global $idrepositorio;
	global $perfilsoft;		
	
	$rs=new Recordset; 
	$cmd->texto="SELECT imagenes.*,tipospar.tipopar,repositorios.nombrerepositorio,perfilessoft.descripcion as perfilsoft FROM imagenes
								LEFT OUTER JOIN tipospar ON tipospar.codpar=imagenes.codpar
								LEFT OUTER JOIN repositorios ON repositorios.idrepositorio=imagenes.idrepositorio
								LEFT OUTER JOIN perfilessoft ON perfilessoft.idperfilsoft=imagenes.idperfilsoft
								 WHERE imagenes.idimagen=".$idmagen;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreca=$rs->campos["nombreca"];
		$descripcion=$rs->campos["descripcion"];		
		$idperfilsoft=$rs->campos["idperfilsoft"];
		$comentarios=$rs->campos["comentarios"];
		$numpar=$rs->campos["numpar"];
		$tipopar=$rs->campos["tipopar"];
		$codpar=$rs->campos["codpar"];
		$idrepositorio=$rs->campos["idrepositorio"];
		$nombrerepositorio=$rs->campos["nombrerepositorio"];
		$perfilsoft=$rs->campos["perfilsoft"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(true);
}
?>
