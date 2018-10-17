<?php
//********************************************************************
// Descripción : 
//              Pagina de ayuda
//********************************************************************
include_once("../includes/ctrlacc.php");
include_once("../idiomas/php/".$idioma."/ayuda_".$idioma.".php");

// Añadir versión.
$versionfile="../../doc/VERSION.txt";
if (file_exists ($versionfile))
    $version=@file_get_contents($versionfile);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title> Administración web de aulas </title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<link rel="stylesheet" type="text/css" href="../estilos.css" />
</head>

<body>

<div><p align=center class=cabeceras><img  border=0 nod="aulas-1" value="Sala Virtual" style="cursor:pointer" src="../images/iconos/logocirculos.png" >&nbsp;&nbsp;<?php echo $TbMsg["AYUDA_TITULO"]; ?></p>
</div>

<div style="margin-left: 20%">
    <p><a class="nounderline" href="manual.php"> <span class=subcabeceras> <?php echo $TbMsg["MANUAL"] ?> </span></a> </p>
    <p><a class="nounderline" href="../api/index.html"><span class=subcabeceras>  <?php echo $TbMsg["API"] ?> </span></a></p>
    <p><a class="nounderline" href="engine.php"><span class=subcabeceras> <?php echo $TbMsg["CFG"] ?></span></a> </p>
    <p>&nbsp;</p>
    <p><a class="nounderline" href="changelog.php"><span class=subcabeceras> <?php echo $TbMsg["CHANGELOG"]." ".$version ?>  </span></a> </p>
    <p><a class="nounderline" href="https://listas.unizar.es/cgi-bin/mailman/listinfo/opengnsys-users" target="_blank"><span class=subcabeceras> <?php echo $TbMsg["USERMAIL"] ?>  </span></a> </p>
    <p><a class="nounderline" href="https://opengnsys.es/" target="_blank"><span class=subcabeceras> <?php echo $TbMsg["WEB"] ?> </span></a> </p>
    <p><a class="nounderline" href="acercade.php"><span class=subcabeceras> <?php echo  $TbMsg["ABOUT"] ?> </span></a> </p>
</div>
</body>
</html>
