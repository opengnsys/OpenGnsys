<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Agosto-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: controlacceso.php
// Descripción :Este fichero redirecciona a la página principal con un código de error
// *************************************************************************************************************************************************
$herror=0;
if (isset($_GET["herror"])) $herror=$_GET["herror"]; 
//________________________________________________________________________________________________________
?>
<HTML>
	<TITLE> Administración web de aulas</TITLE>
	<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<LINK rel="stylesheet" type="text/css" href="estilos.css">
	</HEAD>
	<BODY>
		<?
			echo '<SCRIPT LANGUAGE="JAVASCRIPT">'.chr(13);
			echo '	var o=window.top;'.chr(13);
			echo '	var ao=o.parent;'.chr(13);
			echo '	while (o!=ao){ // Busca la primera ventana del navegador'.chr(13);
			echo '	 ao=o;'.chr(13);
			echo '	 o=o.parent;';
			echo '	 };'.chr(13);
			echo '	ao.location="./acceso.php?herror='.$herror.'";'.chr(13);
			echo '</SCRIPT>'.chr(13);
		?>
	</BODY>
</HTML>
