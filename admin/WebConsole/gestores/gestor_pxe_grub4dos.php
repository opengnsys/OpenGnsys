<?php
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/MenuContextual.php");
include_once("../clases/SockHidra.php");
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
		ogBootServer($cmd,$optboot,$hostname,$idioma);
	}
}
echo " </body>";
echo " </html> ";


function ogBootServer($cmd,$optboot,$hostname,$idioma) 
{	
global $cmd;
global $hostname;
global $optboot;
global $retrun;
$return="\n";
$cmd->CreaParametro("@optboot",$optboot,0);
$cmd->CreaParametro("@hostname",$hostname,0);
$cmd->texto="UPDATE ordenadores SET arranque=@optboot WHERE nombreordenador=@hostname";
$cmd->Ejecutar();
$cmd->texto="SELECT ordenadores.ip AS ip, ordenadores.mac AS mac, 
			ordenadores.netiface AS netiface, aulas.netmask AS netmask,
			aulas.router AS router, aulas.dns AS dns, aulas.proxy AS proxy,
			aulas.nombreaula AS grupo, repositorios.ip AS iprepo,
			(SELECT ipserveradm FROM entornos LIMIT 1) AS ipserveradm,
			menus.resolucion AS vga, perfileshard.winboot AS winboot
		FROM ordenadores 
		JOIN aulas ON ordenadores.idaula=aulas.idaula 
		JOIN repositorios ON ordenadores.idrepositorio=repositorios.idrepositorio 
		LEFT JOIN menus ON ordenadores.idmenu=menus.idmenu 
		LEFT JOIN perfileshard ON ordenadores.idperfilhard=perfileshard.idperfilhard
		WHERE ordenadores.nombreordenador='". $hostname ."'";

$rs=new Recordset; 
$rs->Comando=&$cmd; 
if (!$rs->Abrir()) echo "error";
$rs->Primero(); 
	$mac=$rs->campos["mac"];
	$netiface=$rs->campos["netiface"];
	$ip=$rs->campos["ip"];
	$router=$rs->campos["router"];
	$netmask=$rs->campos["netmask"];
	$dns=$rs->campos["dns"];
	$proxy=$rs->campos["proxy"];
	$repo=$rs->campos["iprepo"];
	$server=$rs->campos["ipserveradm"];
	$group=cleanString($rs->campos["grupo"]);
	if($rs->campos["vga"]== null || $rs->campos["vga"]== 0)
		$vga="788";
	else
		$vga=$rs->campos["vga"];
	$winboot=$rs->campos["winboot"];

$rs->Cerrar();

switch ($idioma) {
    case "eng":
        $idioma="en_GB";
        break;
    case "esp":
        $idioma="es_ES";
        break;
    case "cat":
        $idioma="ca_ES";
        break;
}

// Comprobar si se usa el parámetro "vga" (número entero) o "video" (cadena).
if (is_int ($vga)) {
	$infohost =" vga=$vga";
} else {
	$infohost =" video=$vga";
}
// Componer otros parámetros del kernel.
$infohost.=" LANG=$idioma".
	   " ip=$ip:$server:$router:$netmask:$hostname:$netiface:none" .
	   " group=$group" .
	   " ogrepo=$repo" .
	   " oglive=$repo" .
	   " oglog=$server" .
	   " ogshare=$server";
// Añadir parámetros opcionales.
if (! empty ($dns))	{ $infohost.=" ogdns=$dns"; }
if (! empty ($proxy))	{ $infohost.=" ogproxy=$proxy"; }
if (! empty ($winboot))	{ $infohost.=" winboot=$winboot"; }

###################obtenemos las variables de red del aula.

#02.1 obtenemos nombre fichero mac
$pxedir="/opt/opengnsys/tftpboot/menu.lst";
$mac=  substr($mac,0,2) . ":" . substr($mac,2,2) . ":" . substr($mac,4,2) . ":" . substr($mac,6,2) . ":" . substr($mac,8,2) . ":" . substr($mac,10,2);
$macfile="$pxedir/01-" . str_replace(":","-",strtoupper($mac));	

#controlar optboot

exec("sed -e 's|vga=...||g' -e 's|INFOHOST|$infohost|g' $pxedir/templates/$optboot > $macfile");
exec("chmod 777 $macfile");
}


function netmask2cidr($netmask) {
          $cidr = 0;
          foreach (explode('.', $netmask) as $number) {
              for (;$number> 0; $number = ($number <<1) % 256) {
                  $cidr++;
               }
           }
           return $cidr;
}

// Sustituye espacio por "_" y quita acentos y tildes.
function cleanString ($cadena) {
	$patron = array ('/ /','/á/','/é/','/í/','/ó/','/ú/','/ñ/','/Á/','/É/','/Í/','/Ó/','/Ú/','/Ñ/');
	$reemplazo = array ('_','a','e','i','o','u','n','A','E','I','O','U','N');
	return  preg_replace($patron,$reemplazo,$cadena);
}

?>

