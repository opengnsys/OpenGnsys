#!/usr/bin/php
<?
require($REPO . "admin/config/parametrosaccesoBD.php");


$conexion=mysql_connect(SQL_HOST, SQL_USER, SQL_PASS) or die ('no se ha podido conectar con mysql');
mysql_select_db(DATABASE, $conexion);


$hostname=exec('/bin/hostname', $retval);
echo $hostname;
$IP=system('echo $IP', $retval);
$MAC=system('echo $MAC', $retval);

$insert="insert into equipos (hostname, mac, ip) values ('" . $hostname . "', '" . $MAC . "', '" . $IP . "')";
echo $insert;
$resultado=mysql_query($insert);

$insert="insert into kernelparameters (mac) values ('" . $MAC . "')";
echo $insert;
$resultado=mysql_query($insert);

//busqueda de la startpage y copiarlo a /var/tmp
$peticion="select startpage FROM equipos WHERE hostname='".$hostname."'";
$resultequipos = mysql_query($peticion);
while ($filaequipos = mysql_fetch_array($resultequipos))
  {
//#echo trim($filaequipos[0]);
#echo "bash -c " . REPO . "/admin/startpage/" . trim($filaequipos[0]);
exec("cp  " . REPO . "/admin/startpage/" . trim($filaequipos[0])  . " /var/tmp/");
#exec( REPO . "/admin/startpage/" . trim($filaequipos[0]) . " &>/dev/tty1");
//system("/EAC.files/admin/startpage/" . trim($filaequipos[0]));

}
mysql_close($conexion);
?>


