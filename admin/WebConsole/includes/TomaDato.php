<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon.
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: TomaDato.php
// Descripción :
//			Recupera un dato de una tabla
//	Parametros: 
//		- cmd:Un comando ya operativo (con conexión abierta)  
//		- idcentro:Centro al que pertene el registro donde se encuentra el dato a recuperar, será 0 para no contemplar este dato
//		- nombretabla: Nombre de la tabla origen de los datos
//		- identificador: Valor del campo identificador del registro (cadena separada por comas si hay varios)
//		- nombreid: Nombre del campo identificador del registro (cadena separada por comas si hay varios)
//		- nombreliteral: Nombre del campo que se quiere recuperar
//		- swid: Indica 0= El identificador es tipo alfanumérico	1= EI identificador es tipo numérico ( valor por defecto)
//	 (*) En el caso de haber varios identificadores todos deben ser del mismo tipo  ( numérico o alfanumérico)
//*************************************************************************************************************************************************
function TomaDato($cmd,$idcentro,$nombretabla,$identificador,$nombreid,$nombreliteral,$swid=1){
	$Dato="";
	if (empty($identificador)) return($Dato);

	if($swid==0)	$ch='"';	else $ch=""; // Caracter comillas para campos alfanuméricos
	$auxidentificador=split(";",$identificador);
	$auxnombreid=split(";",$nombreid);

	$clausulaWhere=" WHERE  ".$auxnombreid[0]."=".$ch.$auxidentificador[0].$ch;
	for ($i=1;$i<sizeof($auxidentificador);$i++)
		$clausulaWhere.=" AND ".$auxnombreid[$i]."=".$ch.$auxidentificador[$i].$ch;
	$cmd->texto="SELECT  *  FROM ".$nombretabla.$clausulaWhere;
	if (!empty($idcentro)) 
			$cmd->texto.=" AND idcentro=".$idcentro;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if (!$rs->EOF)
		$Dato.=$rs->campos[$nombreliteral];
	$rs->Cerrar();
	return($Dato);
}
