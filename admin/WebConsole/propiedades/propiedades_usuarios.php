<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
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
$apikey="";
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
			$litusu=$TbMsg[11];
			break;
	case $ADMINISTRADOR:
			$urlimg="../images/iconos/administradores.gif";
			$litusu=$TbMsg[12];
			break;
	case $OPERADOR:
			$urlimg="../images/iconos/operadores.gif";
			$litusu=$TbMsg[13];
			break;
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_usuarios.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_usuarios_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos" action="../gestores/gestor_usuarios.php" method="post"> 
	<INPUT type=hidden name=opcion value=<?=$opcion?>>
	<INPUT type=hidden name=idusuario value=<?=$idusuario?>>
	<INPUT type=hidden name=idtipousuario value=<?=$idtipousuario?>>
	<INPUT type=hidden name=idambito value=<?=$idambito?>>

	<P align=center class=cabeceras><?echo $TbMsg[4]." (".$litusu.")"?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
<!------------------------------------------------	NOMBRE USUARIO	-------------------------------------------------------------------------------------------------->
			<TR>
				<TH>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TH>
				<?if ($opcion==$op_eliminacion || $opcion==$op_modificacion && $idusuario==1){?>
					<TD><INPUT type=hidden class=cajatexto name="usuario"  style="width:100" value="<? echo $usuario?>"></INPUT><?echo $usuario?>&nbsp&nbsp;<IMG src="<? echo $urlimg ?>"></TD>
				<?}else{?>
					<TD><INPUT type=text class=cajatexto name="usuario"  style="width:100" value="<? echo $usuario?>">
					<IMG src="<? echo $urlimg ?>">

				<?}?>
			</TR>
<!----------------------------------------------------	PASSWORD	-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
			<TR>
			<!-- disables autocomplete --><input type="password" style="display:none">
				<TH>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TH>
				<?if ($opcion==$op_eliminacion || $opcion==$op_modificacion && $idusuario==1){?>
					<TD><INPUT type=hidden class=cajatexto  name="pasguor"  style="width:100" value=""></INPUT>****</TD>
				<?}else{?>
					<TD><INPUT type=password class=cajatexto  name="pasguor"  style="width:100" value="">
				<?}?>
			</TR>
<!----------------------------------------------------	CONFIRMAR PASSWORD	---------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
			<TR>
				<TH>&nbsp;<?echo $TbMsg[18]?>&nbsp;</TH>
				<?if ($opcion==$op_eliminacion || $opcion==$op_modificacion && $idusuario==1){?>
					<TD><INPUT type=hidden class=cajatexto  name="confirmarpasguor"  style="width:100" value=""></INPUT>****</TD>
				<?}else{?>
					<TD><INPUT type=password class=cajatexto  name="confirmarpasguor"  style="width:100" value="">
				<?}?>
			</TR>
<!---------------------------------------------------	NOMBRE COMPLETO	----------------------------------------------------------------------------------------------->
			<TR>
				<TH>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TH>
				<?if ($opcion==$op_eliminacion || ($opcion==$op_modificacion && $idusuario==1)){?>
					<TD><INPUT type=hidden class=cajatexto name="nombre"  style="width:250" value="<? echo $nombre?>"></INPUT><?echo $nombre?></TD>
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
                        <?if ($opcion!=$op_eliminacion){?>
                        <TR>
                                <TH>&nbsp;<?echo $TbMsg['APIKEY']?>&nbsp;</TH>
				<?// Opcion nuevo usuario
				if ($opcion == 1) {?>
					<TD><? echo $TbMsg['NEWAPIKEY']?></TD>
				<?} else { ?>
					<TD><? echo $apikey?></TD>
				<?}?>
                        </TR>
                        <?}?>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

	</TABLE>
</FORM>
</DIV>

<?php
if ($idusuario==1){
///*

       echo '<table id="tabla_conf" align="center" border="0" cellPadding="5" cellspacing="1" class="tabla_datos">';
		echo '<tr>';
		echo '<th align="center">&nbsp;'.$TbMsg[14].$TbMsg[15].'<a style="color:white" href="'.$TbMsg[17].'" target="_blank">'.$TbMsg[16].'</a>&nbsp;</th>';
		echo '</tr>';
       echo '</table>';
       echo '<p>';

//*/
}
?>
<?
if ($opcion==$op_eliminacion && $idusuario==1)
{}else{
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
}
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
	global $nombre;
	global $email;
        global $apikey;
	global $ididioma;
	global $idtipousuario;
	
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM usuarios WHERE idusuario=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
			$usuario=$rs->campos["usuario"];
			$nombre=$rs->campos["nombre"];
			$email=$rs->campos["email"];
			$apikey=$rs->campos["apikey"];
			$ididioma=$rs->campos["ididioma"];
			$idtipousuario=$rs->campos["idtipousuario"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>
