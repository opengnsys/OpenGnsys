<? 
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: propiedades_usuarios.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un usuario para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/HTMLSELECT.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../idiomas/php/".$idioma."/propiedades_usuarios_".$idioma.".php");
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idusuario=0; 
$usuario="";
$pasguor="";
$nombre="";
$email="";
$identificador=0;
$idambito=0;
$ididioma=0;
$idtipousuario=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros 
if (isset($_GET["idusuario"])) $idusuario=$_GET["idusuario"]; 
if (isset($_GET["idtipousuario"])) $idtipousuario=$_GET["idtipousuario"]; 
if (isset($_GET["identificador"])) $idusuario=$_GET["identificador"]; 
if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 

//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idusuario);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}

switch($idtipousuario){
	case $SUPERADMINISTRADOR:
			$urlimg="../images/iconos/superadministradores.gif";
			break;
	case $ADMINISTRADOR:
			$urlimg="../images/iconos/administradores.gif";
			break;
	case $OPERADOR:
			$urlimg="../images/iconos/operadores.gif";
			break;
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_usuarios.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_usuarios_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden name=idusuario value=<?=$idusuario?>>
	<INPUT type=hidden name=idtipousuario value=<?=$idtipousuario?>>
	<INPUT type=hidden name=idambito value=<?=$idambito?>>
	<?
		if ($opcion==$op_modificacion && $idtipousuario!=$SUPERADMINISTRADOR){
			echo '<INPUT type=hidden name=usuario value='.$usuario.'>';
			echo '<INPUT type=hidden name=pasguor value='.$pasguor.'>';
		}
	?>
	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
<!-------------------------------------------------------------------------------------------------------------------------------------------------->
			<TR>
				<TH>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TH>
				<?if ($opcion==$op_eliminacion || ($opcion==$op_modificacion && $idtipousuario!=$SUPERADMINISTRADOR)){?>
					<TD><?echo $usuario?>&nbsp&nbsp;<IMG src="<? echo $urlimg ?>"></TD>
				<?}else{?>
					<TD><INPUT type=text class=cajatexto maxlength=10 name="usuario"  style="width:100" value="<? echo $usuario?>">
					<IMG src="<? echo $urlimg ?>">

				<?}?>
			</TR>
<!-------------------------------------------------------------------------------------------------------------------------------------------------->
			<TR>
				<TH>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TH>
				<?if ($opcion==$op_eliminacion || ($opcion==$op_modificacion && $idtipousuario!=$SUPERADMINISTRADOR)){?>
					<TD><?echo $pasguor?></TD>
				<?}else{?>
					<TD><INPUT type=text class=cajatexto maxlength=10  name="pasguor"  style="width:100" value="<? echo $pasguor?>">
				<?}?>
			</TR>
<!-------------------------------------------------------------------------------------------------------------------------------------------------->
			<TR>
				<TH>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TH>
				<?if ($opcion==$op_eliminacion){?>
					<TD><?echo $nombre?></TD>
				<?}else{?>
					<TD><INPUT type=text class=cajatexto name="nombre"  style="width:250" value="<? echo $nombre?>">
				<?}?>
			</TR>
<!-------------------------------------------------------------------------------------------------------------------------------------------------->
			<TR>
				<TH>&nbsp;<?echo $TbMsg[8]?>&nbsp;</TH>
				<?if ($opcion==$op_eliminacion){?>
					<TD><?echo $email?></TD>
				<?}else{?>
					<TD><INPUT type=text class=cajatexto name="email"  style="width:250" value="<? echo $email?>">
				<?}?>
			</TR>
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
			<TR>
				<TH>&nbsp;<?echo $TbMsg[10]?>&nbsp;</TH>
				<?if ($opcion==$op_eliminacion){?>
					<TD><? echo TomaDato($cmd,0,"idiomas",$ididioma,"ididioma","descripcion")?></TD> 
				<?}else{?>
					<TD><? echo HTMLSELECT($cmd,0,"idiomas",$ididioma,"ididioma","descripcion",100)?></TD>
				<?}?>
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
//	Recupera los datos de un usuario
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador del usuario
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $usuario;
	global $pasguor;
	global $nombre;
	global $email;
	global $idambito;
	global $ididioma;
	global $idtipousuario;
	
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM usuarios WHERE idusuario=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
			$usuario=$rs->campos["usuario"];
			$pasguor=$rs->campos["pasguor"];
			$nombre=$rs->campos["nombre"];
			$email=$rs->campos["email"];
			$idambito=$rs->campos["idambito"];
			$ididioma=$rs->campos["ididioma"];
			$idtipousuario=$rs->campos["idtipousuario"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>
