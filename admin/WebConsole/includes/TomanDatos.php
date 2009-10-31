<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon.
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: TomanDatos.php
// Descripción :
//			Recupera datos de una tabla
//	Parametros: 
//		- cmd:Un comando ya operativo (con conexión abierta)  
//		- idcentro:Centro al que pertene el registro donde se encuentra el dato a recuperar, será  0 para no contemplar este dato
//		- nombretabla: Nombre de la tabla origen de los datos
//		- identificador: Valor del campo identificador del registro
//		- nombreid: Nombre del campo identificador del registro 
//		- nombreliteral: Array asosiativa con los nombres de los campo que se quieren recuperar 
//		- swid: Indica 0= El identificador es tipo alfanumérico	1= EI identificador es tipo numérico (valor por defecto)
// *************************************************************************************************************************************************
function TomanDatos($cmd,$nombretabla,$identificador,$nombreid,$nombreliterales,$swid=1){
	$Dato="";
	if (empty($identificador)) $identificador=0;
	$rs=new Recordset; 
	if($swid==0)
		$cmd->texto="SELECT  *  FROM ".$nombretabla." WHERE ".$nombreid."='".$identificador."'";
	else
		$cmd->texto='SELECT  *  FROM '.$nombretabla.' WHERE '.$nombreid.'='.$identificador;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if (!$rs->EOF){
		for($x=0;$x<sizeof($nombreliterales);$x++){
			$Dato[$nombreliterales[$x]]=$rs->campos[$nombreliterales[$x]];
		}
	}
	$rs->Cerrar();
	return($Dato);
}