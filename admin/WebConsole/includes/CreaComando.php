<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon.
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: CreaComando.php
// Descripción :
//		Devuelve una objeto comando totalmente operativo (con la conexión abierta)
//	Parametros: 
//		- cadenaconexion: Una cadena con los datos necesarios para la conexión: nombre del servidor
//		usuario,password,base de datos,etc separados por coma
// *************************************************************************************************************************************************
function CreaComando($cadenaconexion){
	$strcn=split(";",$cadenaconexion);
	$cn=new Conexion; 
	$cmd=new Comando;	
	$cn->CadenaConexion($strcn[0],$strcn[1],$strcn[2],$strcn[3],$strcn[4]);
	if (!$cn->Abrir()) return (false); 
	$cn->SetUtf8();
	$cmd->Conexion=&$cn; 
	return($cmd);
}
?>