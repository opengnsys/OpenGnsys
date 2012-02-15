<?
// *******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Agosto-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: frames.php
// Descripción :Este fichero implementa la distribución en frames de la aplicación
// Cambio en la línea 22 22% (antes 30)
// *******************************************************************************************************
include_once("./includes/ctrlacc.php");
include_once("./includes/constantes.php");
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<TITLE> Administración web de aulas</TITLE>
</HEAD>
<FRAMESET rows="25,*">
	<FRAME SRC="barramenu.php" frameborder=1  scrolling=no  NAME="frame_menus" >
	<FRAMESET cols="22%,*">
			<? 
			if($idtipousuario!=$SUPERADMINISTRADOR)
				echo '<FRAME SRC="./principal/aulas.php" frameborder=0 scrolling=auto NAME="frame_arbol" >';
			else{
				if($idtipousuario==$SUPERADMINISTRADOR)
					echo '<FRAME SRC="./principal/administracion.php" frameborder=0 scrolling=auto NAME="frame_arbol" >';
			}
			?>
		<FRAME SRC="nada.php" frameborder=0  NAME="frame_contenidos">
		</FRAMESET>
	</FRAMESET>	
</FRAMESET>
</HTML>
