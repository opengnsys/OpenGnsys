<?php
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/MenuContextual.php");
include_once("../clases/SockHidra.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/tftputils.php");
include_once("../idiomas/php/".$idioma."/aulas_".$idioma.".php");

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexiÃ³n con servidor B.D.
//________________________________________________________________________________________________________


echo "<html>";
echo "<head>";
echo "<meta http-equiv='Refresh' content='1;URL=../principal/boot.php?idambito=". $_GET['idaula'] ."&nombreambito=" . $_GET['nombreambito'] . "&litambito=" . $_GET['litambito'] . "'>";
echo "<title> gestion de equipos </title>";
echo "<base target='principal'>";
echo "</head>";
echo "<body>";

$lista = explode(";",$_POST['listOfItems']);
foreach ($lista as $sublista) {
	if (! empty ($sublista)) {
		$elementos = explode("|",$sublista);
		$hostname=$elementos[1];
		$optboot=$elementos[0];
		createBootMode ($cmd, $optboot, $hostname, $idioma);
	}
}
echo " </body>";
echo " </html> ";


function netmask2cidr($netmask) {
          $cidr = 0;
          foreach (explode('.', $netmask) as $number) {
              for (;$number> 0; $number = ($number <<1) % 256) {
                  $cidr++;
               }
           }
           return $cidr;
}

