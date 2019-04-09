<?php
//********************************************************************
// Descripción : 
//              Pagina de ayuda
//********************************************************************
include_once("../includes/ctrlacc.php");
include_once("../idiomas/php/".$idioma."/ayuda_".$idioma.".php");

// Añadir versión.
$data = json_decode(@file_get_contents('../../doc/VERSION.json'));
$version=(empty($data->project)) ? "OpenGnsys" : @$data->project.' '.@$data->version.' '.(isset($data->codename) ? '('.$data->codename.') ' : '').@$data->release;;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title> Administración web de aulas </title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<link rel="stylesheet" type="text/css" href="../estilos.css" />
</head>

<body>

<div><p align=center class=cabeceras><img  border=0 nod="aulas-1" value="Sala Virtual" style="cursor:pointer" src="../images/iconos/logocirculos.png" >&nbsp;&nbsp;<?php echo $TbMsg["HELP_TITLE"]; ?></p>
</div>

<div style="margin-left: 20%">
    <p class=subcabeceras><a class="help_menu" href="manual.php"> <?php echo $TbMsg["MANUAL"] ?> </a> </p>
    <p class=subcabeceras><a class="help_menu" href="../api/index.html"> <?php echo $TbMsg["API"] ?> </a></p>
    <p class=subcabeceras><a class="help_menu" href="engine.php"> <?php echo $TbMsg["CFG"] ?> </a> </p>
    <p class=subcabeceras><a class="help_menu" href="../rest/"> <?php echo $TbMsg["REST"] ?> </a></p>
    <p>&nbsp;</p>
    <p class=subcabeceras><a class="help_menu" href="changelog.php"> <?php echo $TbMsg["CHANGELOG"]." ".$version ?>  </a> </p>
    <p class=subcabeceras><a class="help_menu" href="https://listas.unizar.es/cgi-bin/mailman/listinfo/opengnsys-users" target="_blank"> <?php echo $TbMsg["USERMAIL"] ?> </a> </p>
    <p class=subcabeceras><a class="help_menu" href="https://opengnsys.es/" target="_blank"> <?php echo $TbMsg["WEB"] ?> </a> </p>
    <p class=subcabeceras><a class="help_menu" href="acercade.php"> <?php echo  $TbMsg["ABOUT"] ?> </a> </p>
</div>
</body>
</html>
