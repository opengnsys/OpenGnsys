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
<?
echo "<meta http-equiv='Refresh' content='2;URL=http://".$ip."/cgi-bin/httpd-log.sh'";
?>
<meta charset="utf-8">

</HEAD>

<BODY>


<form name="leerficherolog"  action="" method="GET">
<table>
<tr>
<?php
	$nombreordenador=$_GET["nombreordenador"];
	echo "<td> Log del equipo ". $nombreordenador ."  con ip " . $ip ." </td> ";
?>
</tr>
</table>
<TEXTAREA NAME="contenido" ROWS="50" COLS="150"  > 
<?php
	$fp = "/opt/opengnsys/log/clients/" . $ip . ".log";
	#echo exec('tail -n 50 ' . $fp);
	$handle = popen("tail " . $fp ." 2>&1", 'r');
    while(!feof($handle)) {
    $buffer = fgets($handle);
     echo "$buffer";
    ob_flush();
    flush();
}
pclose($handle);
	
	
	
	
?>
</TEXTAREA>

</form>

</BODY>
</HTML>
