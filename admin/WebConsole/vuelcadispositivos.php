<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Copyright 2003-2005 Jos�Manuel Alonso. Todos los derechos reservados.
// Vuelca archivo de fabricantes PCI
// *************************************************************************************************************************************************
include_once("./clases/AdoPhp.php");

//========================================================================================================
// Variables de sessi� de configuraci� de servidor y base de datos( Modificar aqu�para cambio global) 
$cnx="localhost;usuhidra;passusuhidra;bdhidra;sqlserver"; // Cadena de conexióna la base de datos
$ips="192.168.2.15"; // IP del servidor hidra
$prt="2008"; // Puerto de comunicaci� con el servidor
$wer="http://192.168.2.15/webhidra/pagerror.php"; // P�ina de redireccionamiento de errores
$wac="http://192.168.2.15/webhidra/acceso.php"; // P�ina de login de la Aplicación
//========================================================================================================
$cmd=CreaComando($cnx); // Crea objeto comando
if (!$cmd)  die("Error de conexion");

$cmd->texto="DELETE FROM fabricantes";
$cmd->Ejecutar();
$cmd->texto="DELETE FROM pcifabricantes";
$cmd->Ejecutar();

// Lectura del archivo de dispositivos
$fileparam="dispositivospci";
$fp = fopen($fileparam,"r");
$bufer= fread ($fp, filesize ($fileparam));
fclose($fp);

$modelo="";
$nombremodelo="";
$lineas=split("\n",$bufer);
for($i=0;$i<sizeof($lineas);$i++){
	$pch=substr($lineas[$i],0,1); // Primer caracter
	if($pch=="#" )
		continue;
	else{
		if($pch>="0" &&  $pch<="9"){ // Si es un número ...
			$fabricante=substr($lineas[$i],0,4);
			$nombrefabricante=substr($lineas[$i],4);
			if($fabricante!="" &&  $nombrefabricante!=""){
				$cmd->texto="INSERT INTO fabricantes (codigo,nombre) VALUES (0x".$fabricante.",'".$nombrefabricante."')";
				$cmd->Ejecutar();
				echo "<br>insert:".$cmd->texto;
				//echo "<br>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modelo:$modelo, Nombremodelo:$nombremodelo";
			}
		}
		else{
			if($pch==chr(9)){ // Si el primer caracter es un tabulador ...
				$pch2=substr($lineas[$i],1,1); // Segundo caracter
				if($pch2>="0" &&  $pch2<="9"){ // Si es un número ...
					$modelo=substr($lineas[$i],1,4);
					$nombremodelo=substr($lineas[$i],5);
					$cmd->texto="INSERT INTO pcifabricantes(codigo1,codigo2,descripcion) VALUES (0x".$fabricante.",0x".$modelo.",'".$nombremodelo."')";
					$cmd->Ejecutar();
					echo "<br>insert:".$cmd->texto;
					//echo "<br>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modelo:$modelo, Nombremodelo:$nombremodelo";
				}
			}
		}
	}
}
// *************************************************************************************************************************************************
//	Devuelve una objeto comando totalmente operativo (con la conexiónabierta)
//	Parametros: 
//		- cadenaconexion: Una cadena con los datos necesarios para la conexi�: nombre del servidor
//		usuario,password,base de datos,etc separados por coma
//________________________________________________________________________________________________________
function CreaComando($cadenaconexion){
	$strcn=split(";",$cadenaconexion);
	$cn=new Conexion; 
	$cmd=new Comando;	
	$cn->CadenaConexion($strcn[0],$strcn[1],$strcn[2],$strcn[3],$strcn[4]);
	if (!$cn->Abrir()) return (false); 
	$cmd->Conexion=&$cn; 
	return($cmd);
}
?>
