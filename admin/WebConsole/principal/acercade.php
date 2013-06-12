<?php
//********************************************************************
// Descripción : 
//              Pagina de informacion sobre el proyecto OpenGnSys
//********************************************************************
include_once("../idiomas/php/".$idioma."/acercade_".$idioma.".php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title> Administración web de aulas </title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<link rel="stylesheet" type="text/css" href="../estilos.css" />
</head>

<body class="acercade">

<img alt="*" src="../images/acercade.png" align="left" hspace="10em" vspace="10em" />

<h1><img alt="OpenGnSys" src="../images/iconos/logoopengnsys.png" /></h1>

<p>
<?php
// Añadir versión.
$versionfile="../../doc/VERSION.txt";
if (file_exists ($versionfile))
        include ($versionfile);

?>
</p>


<p><strong><?php echo $TbMsg["TITLE"] ?></strong></p>

<p><?php echo $TbMsg["DESCRIPTION"] ?> </p>

<p><?php echo $TbMsg["LICENSE"] ?> <a href="http://www.gnu.org/licenses/gpl.html"  target="_blank" ><img alt="GPL v3"  src="../images/gplv3-88x31.png" height="20em" /></a></p>

<p><strong><?php echo $TbMsg["LINK"] ?> <a href="http://opengnsys.es"  target="_blank" >opengnsys.es</a><strong></p> 
</body>
</html>
