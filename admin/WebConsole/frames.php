<?php
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
if (! empty ($_POST['idmicentro'])) {
	$cambiocentro=split(",",$_POST['idmicentro']);
	$_SESSION["widcentro"]=$cambiocentro[0];
	$_SESSION["wnombrecentro"]=$cambiocentro[1];
	}
if (empty ($idioma)) $idioma="esp";
include_once("./idiomas/php/$idioma/acceso_$idioma.php");
//________________________________________________________________________________________________________
?>
<html>
<head>
<title><?php echo $TbMsg["ACCESS_TITLE"];?></title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<link rel="shortcut icon" href="images/iconos/logocirculos.png" type="image/png" />
</head>
<FRAMESET rows="25,*">
	<FRAME SRC="barramenu.php" frameborder=1  scrolling=no  NAME="frame_menus" >
	<FRAMESET cols="22%,*">
		<?php	if($idtipousuario!=$SUPERADMINISTRADOR)
				echo '<FRAME SRC="./principal/aulas.php" frameborder=1 scrolling=auto NAME="frame_arbol" >';
			else{
				if($idtipousuario==$SUPERADMINISTRADOR)
					echo '<FRAME SRC="./principal/administracion.php" frameborder=1 scrolling=auto NAME="frame_arbol" >';
			}
		?>
		<FRAME SRC="nada.php" frameborder=0  NAME="frame_contenidos">
	</FRAMESET>	
	<noframes>
		<body>
			<p><strong><?php echo $TbMsg["ACCESS_NOFRAMES"];?></strong></p>
		</body>
	</noframes>
</FRAMESET>
</html>

