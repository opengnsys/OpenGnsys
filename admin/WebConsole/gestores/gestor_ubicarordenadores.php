<?php
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/aulas_".$idioma.".php");

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexiÃ³n con servidor B.D.
//________________________________________________________________________________________________________


echo "<html>";
echo "<head>";
echo "<meta http-equiv='Refresh' content='1;URL=../principal/ubicarordenadores.php?idambito=". $_GET['idaula'] ."&nombreambito=" . $_GET['nombreambito'] . "&litambito=" . $_GET['litambito'] . "'>";
echo "<title> gestion de equipos </title>";
echo "<base target='principal'>";
echo "</head>";
echo "<body>";



#echo('litambito con valor:     '. $_GET['litambito']);
#echo ('idambito con valor:      ' . $_GET['idaula']);
#echo ('nombreambito con valor:      ' . $_GET['nombreambito']);
#echo "<br>";

$lista = explode(";",$_POST['listOfItems']);
foreach ($lista as $sublista) {
	$elementos = explode("|",$sublista);
	$hostname=$elementos[1];
	$grupo=$elementos[0];
	ReubicarOrdenador($cmd,$hostname,$grupo);
    #echo $elementos[0] .  $elementos[1];
    #echo "<br>";
}
echo " </body>";
echo " </html> ";

function ReubicarOrdenador($cmd,$hostname,$grupo)
{
global $cmd;
global $hostname;
global $grupo;
if ($grupo == "pxe")
{
	$cmd->CreaParametro("@grupo","0",0);
	$cmd->CreaParametro("@hostname",$hostname,0);
	$cmd->texto="update ordenadores set grupoid=@grupo where nombreordenador=@hostname";
}
else
{
	$cmd->CreaParametro("@grupo",$grupo,0);
	$cmd->CreaParametro("@hostname",$hostname,0);
	#$cmd->texto="update ordenadores set grupoid=(Select idgrupo from gruposordenadores where nombregrupoordenador=@grupo) where nombreordenador=@hostname";
	$cmd->texto="update ordenadores set grupoid=@grupo where nombreordenador=@hostname";
	
}
$cmd->Ejecutar();
#Update ordenadores Set grupoid=(Select idgrupo From gruposordenadores Where nombregrupoordenador="subgrupo1") where nombreordenador="prueba1"
}


#   <iframe src="frame_a.htm" name="frame1"></iframe>
#   <iframe src="frame_b.htm" name="frame2"></iframe>

?>

<html> <head>
   <script language="javascript">
	function actualiza_frame_principal(){
    window.parent.frames[1].location="../principal/aulas.php"
   // window.location="../nada.php"
} 
   </script>
</head>
 <body onunload="actualiza_frame_principal()"> </body>
</html>



