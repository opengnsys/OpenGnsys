#!/usr/bin/php
<?

require_once("/var/EAC/admin/config/parametrosaccesoBD.php");
$conexion=mysql_connect(SQL_HOST, SQL_USER, SQL_PASS) or die ('no se ha podido conectar con mysql');
mysql_select_db(DATABASE, $conexion);
$peticion="select * FROM tareas_servidor WHERE comando='".$_SERVER['argv'][1] . "' and parametros='". $_SERVER['argv'][2] ."' and ip='" . $_SERVER['argv'][3]."'";
$result = mysql_query($peticion);
$num_registros=mysql_num_rows($result);
if ($num_registros == 0)
{
$insert="insert into tareas_servidor (comando, parametros, ip) values ('".$_SERVER['argv'][1]."','".$_SERVER['argv'][2]."','".$_SERVER['argv'][3]."')";
$resultado = mysql_query($insert) or die (mysql_error());
}
mysql_close($conexion);
?>


