<?
// *******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla

// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: administradores_usuarios.php
// Descripción : 
//		Administra los componentes software incluidos en un perfil software
// *******************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../idiomas/php/".$idioma."/administradores_usuarios_".$idioma.".php");
//________________________________________________________________________________________________________
$idusuario=0; 
$nombre =""; 
if (isset($_GET["idusuario"])) $idusuario=$_GET["idusuario"]; // Recoge parametros
if (isset($_GET["nombre"])) $nombre=$_GET["nombre"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="../jscripts/administradores_usuarios.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>


<SCRIPT language="javascript">

</SCRIPT>



<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/administradores_usuarios_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden value="<? echo $idusuario?>" id=idusuario>	
 
	<P align=center class=cabeceras><?echo $nombre?></SPAN>&nbsp;<IMG src="../images/iconos/administradores.gif">
	<BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/confisoft.gif"></P>

		<?
			$rs=new Recordset; 
			// Usuarios asignados
			$cmd->texto="SELECT centros.idcentro,centros.nombrecentro, centros.identidad FROM centros 
							INNER JOIN administradores_centros ON administradores_centros.idcentro=centros.idcentro 
							WHERE administradores_centros.idusuario=".$idusuario." ORDER by centros.nombrecentro";

			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){ 
				$centrosUO="";
				$rs->Primero();
				echo '<DIV align=center id="Layer_componentes">';
				echo '<P><SPAN align=center class=presentaciones><B>'.$TbMsg[2].'</B></SPAN></P></DIV>';	
				echo '<TABLE width="100%" class="tabla_listados" cellspacing=1 cellpadding=0 >';
				echo '<TR><TH>&nbsp</TH><TH>T</TH><TH>'.$TbMsg[3].'</TH></TR>';
				while (!$rs->EOF){
						$centrosUO.=$rs->campos["idcentro"].",";
						 echo '<TR>';
						 echo '<TD align=center width="10%"><INPUT type=checkbox 
										onclick="gestion_administrador('.$rs->campos["idcentro"].',this)" checked></INPUT></TD>';
						
						echo '<TD align=center width="10%" ><img src="../images/iconos/centros.gif"></TD>';
						
						echo '<TD  width="80%" >&nbsp;'.$rs->campos["nombrecentro"].'</TD>';
						echo '</TR>';
						$rs->Siguiente();
				}
				echo '</TABLE>';
			}
			$rs->Cerrar();
			// Usuarios disponibles
			$centrosUO.="0";
			$cmd->texto="SELECT centros.idcentro,centros.nombrecentro,centros.identidad FROM centros 
							WHERE centros.idcentro NOT IN (".$centrosUO.") ORDER by centros.nombrecentro";
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
									 onclick="gestion_administrador('.$rs->campos["idcentro"].',this)"></INPUT></TD>';
						

						 echo '<TD align=center width="10%" ><img src="../images/iconos/centros.gif"></TD>';

						 echo '<TD width="80%" >&nbsp;'.$rs->campos["nombrecentro"].'</TD>';
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
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
