<?
// *******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: administradores_centros.php
// Descripción : 
//		Administra los componentes software incluidos en un perfil software
// *******************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../idiomas/php/".$idioma."/administradores_centros_".$idioma.".php");
//________________________________________________________________________________________________________
$idcentro=0; 
$nombrecentro =""; 
if (isset($_GET["idcentro"])) $idcentro=$_GET["idcentro"]; // Recoge parametros
if (isset($_GET["nombrecentro"])) $nombrecentro=$_GET["nombrecentro"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="../jscripts/administradores_centros.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/administradores_centros_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden value="<? echo $idcentro?>" id=idcentro>	
 
	<P align=center class=cabeceras><?echo $nombrecentro?></SPAN>&nbsp;<IMG src="../images/iconos/centros.gif">
	<BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/confisoft.gif"></P>

		<?
			$rs=new Recordset; 
			// Administradores asignados
			$cmd->texto="SELECT usuarios.idusuario,usuarios.nombre ,usuarios.idtipousuario FROM usuarios 
							INNER JOIN administradores_centros ON administradores_centros.idusuario=usuarios.idusuario 
							WHERE administradores_centros.idcentro=".$idcentro." ORDER by usuarios.nombre";

			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){ 
				$usuariosUO="";
				$rs->Primero();
				echo '<DIV align=center id="Layer_componentes">';
				echo '<P><SPAN align=center class=presentaciones><B>'.$TbMsg[2].'</B></SPAN></P></DIV>';	
				echo '<TABLE width="100%" class="tabla_listados" cellspacing=1 cellpadding=0 >';
				echo '<TR><TH>&nbsp</TH><TH>T</TH><TH>'.$TbMsg[3].'</TH></TR>';
				while (!$rs->EOF){
						$usuariosUO.=$rs->campos["idusuario"].",";
						 echo '<TR>';
						 echo '<TD align=center width="10%"><INPUT type=checkbox 
										onclick="gestion_administrador('.$rs->campos["idusuario"].',this)" checked></INPUT></TD>';
						switch($rs->campos["idtipousuario"]){
							case $ADMINISTRADOR:
								echo '<TD align=center width="10%" ><img src="../images/iconos/administradores.gif"></TD>';
								break;
							case $SUPERADMINISTRADOR:
								echo '<TD align=center width="10%" ><img src="../images/iconos/superadministradores.gif"></TD>';
								break;
						}
						echo '<TD  width="80%" >&nbsp;'.$rs->campos["nombre"].'</TD>';
						echo '</TR>';
						$rs->Siguiente();
				}
				echo '</TABLE>';
			}
			$rs->Cerrar();
			// Administradores disponibles
			$usuariosUO.="0";
			$cmd->texto="SELECT usuarios.idusuario,usuarios.nombre,usuarios.idtipousuario FROM usuarios 
							WHERE usuarios.idusuario NOT IN (".$usuariosUO.") ORDER by usuarios.nombre";
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){
				echo '<DIV align=center id="Layer_componentes">';
				echo '<P><SPAN align=center class=presentaciones><B>'.$TbMsg[5].'</B></SPAN></P></DIV>';	
				echo '<TABLE width="100%" class="tabla_listados" cellspacing=1 cellpadding=0 >';
				echo '<TR><TH>&nbsp</TH><TH>T</TH><TH>'.$TbMsg[3].'</TH></TR>';
				$rs->Primero();
				while (!$rs->EOF){
						 echo '<TR>';
						 echo '<TD align=center width="10%"><INPUT type=checkbox
									 onclick="gestion_administrador('.$rs->campos["idusuario"].',this)"></INPUT></TD>';
						switch($rs->campos["idtipousuario"]){
							case $ADMINISTRADOR:
								echo '<TD align=center width="10%" ><img src="../images/iconos/administradores.gif"></TD>';
								break;
							case $SUPERADMINISTRADOR:
								echo '<TD align=center width="10%" ><img src="../images/iconos/superadministradores.gif"></TD>';
								break;
						}
						 echo '<TD width="80%" >&nbsp;'.$rs->campos["nombre"].'</TD>';
						 echo '</TR>';
						$rs->Siguiente();
				}
				echo '</TABLE>';
			}
			$rs->Cerrar();
		?>
		</TABLE>
	<DIV id="Layer_nota" align=center >
		<BR>
		<SPAN align=center class=notas><I><?echo $TbMsg[4]?></I></SPAN>
	</DIV>
</FORM>
</BODY>
</HTML>
