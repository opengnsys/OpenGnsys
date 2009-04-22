<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Diciembre-2003
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: frames.php
// Descripción :Este fichero implementa la distribución en frames de la aplicación
// *************************************************************************************************************************************************
include_once("./includes/ctrlacc.php");
include_once("./includes/constantes.php");

//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<TITLE> Administración web de aulas</TITLE>
</HEAD>
<FRAMESET rows="25,*">
	<FRAME SRC="barramenu.php" frameborder=0  scrolling=no  NAME="frame_menus" >
	<FRAMESET cols="30%,*">
			<? if($idtipousuario!=$SUPERADMINISTRADOR){?>
					<FRAME SRC="./principal/aulas.php" frameborder=1 scrolling=auto NAME="frame_arbol" >
				<? }
						else{
									if($idtipousuario==$SUPERADMINISTRADOR){?>
										<FRAME SRC="./principal/administracion.php" frameborder=1 scrolling=auto NAME="frame_arbol" >
									<?}?>
						<?}?>
		<FRAME SRC="nada.php" frameborder=1  NAME="frame_contenidos">
		</FRAMESET>
	</FRAMESET>	
</FRAMESET>
</HTML>
