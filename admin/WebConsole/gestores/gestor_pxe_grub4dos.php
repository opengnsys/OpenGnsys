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



#echo('litambito con valor:     '. $_GET['litambito']);
#echo ('idambito con valor:      ' . $_GET['idaula']);
#echo ('nombreambito con valor:      ' . $_GET['nombreambito']);

$lista = explode(";",$_POST['listOfItems']);
foreach ($lista as $sublista) {
	$elementos = explode("|",$sublista);
	$hostname=$elementos[1];
	$optboot=$elementos[0];
	ogBootServer($cmd,$optboot,$hostname);
}
echo " </body>";
echo " </html> ";

function ogBootServer($cmd,$optboot,$hostname) 
{	
global $cmd;
global $hostname;
global $optboot;
global $retrun;
$return="\n";
$cmd->CreaParametro("@optboot",$optboot,0);
$cmd->CreaParametro("@hostname",$hostname,0);
$cmd->texto="update ordenadores set arranque=@optboot where nombreordenador=@hostname";
$cmd->Ejecutar();

$cmd->texto="SELECT ordenadores.ip AS ip, ordenadores.mac AS mac, 
			ordenadores.netiface AS netiface, aulas.netmask AS netmask, aulas.router AS router, 
			repositorios.ip AS iprepo FROM ordenadores 
			join aulas on ordenadores.idaula=aulas.idaula 
			join repositorios on ordenadores.idrepositorio=repositorios.idrepositorio 
			where ordenadores.nombreordenador='". $hostname ."'"; 
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
	$infohost=" IP=" 
			. $ip . ":"
			. $repo .":" 
			. $router . ":" 
			. $netmask .":" 
			. $hostname .":" 
			. $netiface . ":none repo="
			. $repo; 
$rs->Cerrar();

###################obtenemos las variables de red del aula.

	#02.1 obtenemos nombre fichero mac
	$mac=  substr($mac,0,2) . ":" . substr($mac,2,2) . ":" . substr($mac,4,2) . ":" . substr($mac,6,2) . ":" . substr($mac,8,2) . ":" . substr($mac,10,2);
	$macfile="01-" . str_replace(":","-",strtoupper($mac));	
	$nombre_archivo="/var/lib/tftpboot/menu.lst/" . $macfile;


########## Escribimos el fichero mac
if (!$gestion=fopen($nombre_archivo, 'w+')) 
{
	echo "No se puede abrir el archivo ($nombre_archivo)";
	return;
}	
# cuales son los parametros del menu
fwrite($gestion, "color white/blue black/light-gray \n");


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
		#if ( $rs->campos["default"] == true)
		#{
		#	fwrite($gestion, "MENU DEFAULT \n");
		#}
		
		
		# set netmask cird para ogclient
		$isogclient=substr_count($rs->campos["label"] , "og");
		if ($isogclient > 0)
		{
			$netmask=$netmask;
			$kernel=$rs->campos["kernel"];
			$append=$rs->campos["append"];
			fwrite($gestion,"keeppxe \n");
			fwrite($gestion, $rs->campos["kernel"] . " " .  $infohost . " \n");
			fwrite($gestion, $rs->campos["append"] . " \n"); 
			fwrite($gestion,"savedefault \n");
			fwrite($gestion,"boot \n");
					
          #  fwrite($gestion,"APPEND keeppxe --config-file='pxe detect; default 0; timeout 0; hiddenmenu; title cache; fallback 1; find --set-root /boot/ogvmlinuz; kernel /boot/ogvmlinuz ro boot=oginit vga=788 irqpoll acpi=on " . $infohost . " ogprotocol=smb og2nd=sqfs ; initrd /boot/oginitrd.img; boot; title net; kernel (pd)/ogclient/vmlinuz ro boot=oginit vga=788 irqpoll acpi=on " . $infohost . " ogprotocol=smb og2nd=sqfs; initrd (pd)/ogclient/oginitrd.img; boot' \n");
		#	keeppxe
		#	kernel (pd)/ogclient/ogvmlinuz  ro boot=oginit vga=788 irqpoll acpi=on og2nd=sqfs ogprotocol=smb ogactiveadmin=true  IP=172.17.9.204:172.17.9.249:172.17.9.254:255.255.255.0:cte204:eth0:none repo=172.17.9.249
		#	initrd (pd)/ogclient/oginitrd.img
		#	savedault
		#	boot
			
			
		}	
		else
		{
			$netmask=netmask2cidr($netmask);
			fwrite($gestion, $rs->campos["kernel"] . $return );
			fwrite($gestion, $rs->campos["append"] . " \n"); 
			
		}

		

	

	#	$prompt=$rs->campos["prompt"];
	#	$timeout=$rs->campos["timeout"];

		$rs->Siguiente();
}
$rs->Cerrar();

			

	fwrite($gestion, " \n");  
	fwrite($gestion, "PROMPT " . $prompt ." \n");
	fwrite($gestion, "TIMEOUT " . $timeout . " \n");
	fwrite($gestion, " \n");  
	fclose($gestion); 
	exec("chown www-data:www-data /var/lib/tftpboot/pxelinux.cfg/". $macfile);
	exec("chmod 777 /var/lib/tftpboot/pxelinux.cfg/". $macfile);
	



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

?>