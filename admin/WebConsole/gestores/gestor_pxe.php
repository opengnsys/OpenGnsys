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
echo "<meta http-equiv='Refresh' content='1;URL=../principal/boot.php?idambito=". $_GET['idaula'] ."&nombreambito=" . $_GET['nombreambito'] . "&litambito=" . $_GET['litambito'] . "'>";
echo "<title> gestion de equipos </title>";
echo "<base target='principal'>";
echo "</head>";
echo "<body>";



#echo('litambito con valor:     '. $_GET['litambito']);
#echo ('idambito con valor:      ' . $_GET['idaula']);
#echo ('nombreambito con valor:      ' . $_GET['nombreambito']);

$lista = explode(";",$_POST['listOfItems']);
foreach ($lista as $sublista) {
	$elementos = explode("|",$sublista);
	$hostname=$elementos[1];
	$optboot=$elementos[0];
	ogBootServer($cmd,$optboot,$hostname,$idioma);
}
echo " </body>";
echo " </html> ";

function ogBootServer($cmd,$optboot,$hostname,$idioma) 
{	
global $cmd;
global $hostname;
global $optboot;
$cmd->CreaParametro("@optboot",$optboot,0);
$cmd->CreaParametro("@hostname",$hostname,0);
$cmd->texto="update ordenadores set arranque=@optboot where nombreordenador=@hostname";
$cmd->Ejecutar();

$cmd->texto="SELECT ordenadores.ip AS ip, ordenadores.mac AS mac, 
                        ordenadores.netiface AS netiface, aulas.netmask AS netmask,
                        aulas.router AS router, repositorios.ip AS iprepo,
                        aulas.nombreaula AS grupo,
                        menus.resolucion AS vga
                        FROM ordenadores 
                        JOIN aulas ON ordenadores.idaula=aulas.idaula 
                        JOIN repositorios ON ordenadores.idrepositorio=repositorios.idrepositorio 
                        LEFT JOIN menus ON ordenadores.idmenu=menus.idmenu 
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
	$repo=$rs->campos["iprepo"];   			
	$group=cleanString($rs->campos["grupo"]);
        if($rs->campos["vga"]== null)
                $vga="788";
        else
                $vga=$rs->campos["vga"];

$rs->Cerrar();

$cmd->texto="SELECT ipserveradm from entornos";
$rs=new Recordset;
$rs->Comando=&$cmd;
if (!$rs->Abrir()) echo "error";

$rs->Primero();
        $server=$rs->campos["ipserveradm"];
$rs->Cerrar();

switch ($idioma) {
    case eng:
        $idioma=en_GB;
        break;
    case esp:
        $idioma=es_ES;
        break;
    case cat:
        $idioma=ca_ES;
        break;
}


$infohost="vga=$vga ".
          "LANG=$idioma ".
          "ip=$ip:$server:$router:$netmask:$hostname:$netiface:none" .
          " group=$group" .
          " ogrepo=$repo" .
          " oglive=$repo" .
          " oglog=$server" .
          " ogshare=$server";

###################obtenemos las variables de red del aula.

	#02.1 obtenemos nombre fichero mac
	$pxedir="/opt/opengnsys/tftpboot/pxelinux.cfg";
	$mac=  substr($mac,0,2) . ":" . substr($mac,2,2) . ":" . substr($mac,4,2) . ":" . substr($mac,6,2) . ":" . substr($mac,8,2) . ":" . substr($mac,10,2);
	$macfile="$pxedir/01-" . str_replace(":","-",strtolower($mac));	


########## Escribimos el fichero mac
if (!$gestion=fopen($macfile, 'w+')) 
{
	echo "No se puede abrir el archivo ($macfile)";
	return;
}	
# cuales son los parametros del menu
fwrite($gestion, "DEFAULT syslinux/vesamenu.c32 \n");
fwrite($gestion, "MENU TITLE Aplicacion OpenGnsys \n");

$cmd->texto="SELECT itemboot.label, itemboot.kernel, 
			itemboot.append, menuboot.timeout, menuboot.prompt,
			 menuboot.description, menuboot_itemboot.default 
			From itemboot,menuboot_itemboot,menuboot 
			WHERE menuboot_itemboot.labelmenu=menuboot.label 
			AND menuboot_itemboot.labelitem=itemboot.label 
			AND menuboot.label='" . $optboot   . "'";
 
$rs->Comando=&$cmd; 
if (!$rs->Abrir()) echo "error";
$rs->Primero(); 
while (!$rs->EOF)
{ 
		fwrite($gestion, " \n");     
		fwrite($gestion, "LABEL " .  $rs->campos['label'] . " \n");
		fwrite($gestion, "MENU LABEL " . $rs->campos['label'] . " \n");
		if ( $rs->campos["default"] == true)
		{
			fwrite($gestion, "MENU DEFAULT \n");
		}
		fwrite($gestion, $rs->campos["kernel"] . " \n");
		# set netmask cird para ogclient
		$isnfsroot=substr_count($rs->campos["append"] , "boot=oginit");
		if ($isnfsroot > 0)
		{
			$netmask=$netmask;
		}	
		else
		{
			$netmask=netmask2cidr($netmask);
		}

		$iseac=substr_count($rs->campos["append"] , "boot=oginit");
		$isinitrd=substr_count($rs->campos["append"] , "initrd.gz");
		
		// delete vga value (included in the variable infohost)
		// borramos valor vga (incluido en la variable infohost)
		$append=preg_replace('/vga=.../','',$rs->campos["append"]);
		if ($iseac > 0)
		{
			fwrite($gestion, $append . " " . $infohost . " \n ");
		}

		elseif ($isinitrd > 0)
		{
		fwrite($gestion, $append . " ogrepo=" . $repo . " \n");
		}
		else
		{
			fwrite($gestion, $append . " \n"); 
		}

		$prompt=$rs->campos["prompt"];
		$timeout=$rs->campos["timeout"];

		$rs->Siguiente();
}
$rs->Cerrar();


	fwrite($gestion, " \n");  
	fwrite($gestion, "PROMPT " . $prompt ." \n");
	fwrite($gestion, "TIMEOUT " . $timeout . " \n");
	fwrite($gestion, " \n");  
	fclose($gestion); 
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



