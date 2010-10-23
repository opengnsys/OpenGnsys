// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2005
// Fecha Última modificación: abril-2005
// Nombre del fichero: ejecutarprocedimientos.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero ejecutarprocedimientos.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
//	
//  Envía un comando para su ejecución o lo incorpora como procedimientos inicial (Autoexec)
//________________________________________________________________________________________________________
function gestion(op)
{
	reset_contextual(-1,-1); // Oculta menu contextual
	var resul=window.confirm(TbMsg[0]);
	if (!resul) return
	var ambito=document.fdatos.ambito.value
	var idambito=document.fdatos.idambito.value
	var idprocedimiento=currentNodo.toma_identificador() // identificador del ambito
	var procedimiento=currentNodo.toma_infonodo() // Nombre del procedimiento
	
	/* LLamada a la gestión */
	var wurl="../gestores/gestor_ejecutaracciones.php";
	var prm="opcion="+op+"&ambito="+ambito+"&idambito="+idambito+"&idprocedimiento="+idprocedimiento+"&descriprocedimiento="+procedimiento;
	CallPage(wurl,prm,"retornoGestion","POST");
}
//______________________________________________________________________________________________________
function retornoGestion(resul)
{
	//alert(resul)
	if(resul.length>0)
		eval(resul);
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de ejecutar un procedimiento sobre un ámbito
//	Parámetros:
//			- resul: resultado de la operación( true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//________________________________________________________________________________________________________
function resultado_gestion_procedimiento(resul,descrierror){
	if (!resul){ // Ha habido algún error en la ejecución
		alert(descrierror)
		return
	}
	alert(TbMsg[1])
	location.href="../nada.php";
}

