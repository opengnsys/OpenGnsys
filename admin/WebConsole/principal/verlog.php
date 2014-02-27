<?
// *************************************************************************************************************************************************
// Aplicación WEB: 
// Autor: 
// Fecha Creación: 
// Fecha Última modificación: 
// Nombre del fichero:
// Descripción : 
//		muestra los log del equipo
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/ejecutarscripts_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/consolaremota_".$idioma.".php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//___________________________________________________________________________________________________

$nombreordenador=$_GET["nombreordenador"];

if (isset($_GET["ip"]))
{
$ip=$_GET["ip"];
}
else
{
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM ordenadores WHERE nombreordenador='".$nombreordenador."'";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$ip=$rs->campos["ip"];
		$rs->Cerrar();
     }
}

?>


<HTML>
<HEAD>
<TITLE>Log: <? echo $_GET["nombreordenador"] ?> </TITLE>
<meta charset="utf-8">
<?
#echo "<meta http-equiv='Refresh' content='2;URL=./verlog.php?nombreordenador=". $_GET["nombreordenador"] ."&ip=".$ip ."'";
?>


</HEAD>

<BODY>


<form name="leerficherolog"  action="" method="GET">
<table>
<tr>
<?php
	$nombreordenador=$_GET["nombreordenador"];
	echo "<td> Log  host ". $nombreordenador ."  ip " . $ip ." </td> ";
?>
</tr>
</table>
<TEXTAREA NAME="contenido" ROWS="50" COLS="150"  > 
<?php
	$fp = "/opt/opengnsys/log/clients/" . $ip . ".log";
	$array=file($fp); 
	foreach($array as $line)
	{
    echo($line);
	}
	#lectura del fichero tipo tail
	#for($i=count($array);$i>0;$i--)
	#{
	#	echo "$array[$i]";
	#}
?>
</TEXTAREA>

</form>

</BODY>
</HTML>
