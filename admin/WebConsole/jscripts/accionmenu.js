// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: accionmenu.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero accionmenu.php
// *************************************************************************************************************************************************
var altas,bajas,modificaciones;
//________________________________________________________________________________________________________ 
// Devuelve el valor seleccionado de un desplegable cualquiera
//________________________________________________________________________________________________________ 
function tomavalorDesple(desplegable)
{
	var idx=desplegable.selectedIndex; // Indice seleccionado en el desplegable
	var val=desplegable.options[idx].value; // Valor seleccionado en el desplegable
	return(val);
}
//________________________________________________________________________________________________________ 
// 
//	Envía la información para dar de alta, baja o modificar items en un menu
//	Parámetros:
//		$op: Indica si esta página se ejecuta desde "acciones" o desde "menus"
//				 1: desde acciones
//				 2: desde menus
//________________________________________________________________________________________________________ 
function confirmar(op)	
{
	var ELEMENT_NODE=1; // Tipo de nodo
	altas=bajas=modificaciones="";
	
	if(op==1){
		var idtipoaccion=document.fdatos.idtipoaccion.value;
		var tipoaccion=document.fdatos.tipoaccion.value;
	}		
	// Indices de campos a recuperar
	var ichk=0; // Checkbox
	var imenu=1; // Nombre del menu
	var itipo=2; // Tipo de item
	var iurlimg=3; // Url de la imagen
	var iitem=4; // Literal del item
	var iorden=5; // Orden de ejecución
	var iidtipoaccion=6; // Identificación de la acción
	var itipoaccion=7; // Identificación del tipo de acción
	
	var trMnu = document.getElementById("TR_menus"); // Recupera la tabla de menu
	trMnu=trMnu.nextSibling; // Primera fila de particiones
	while(trMnu){
		if(trMnu.nodeType==ELEMENT_NODE){
			var ochk=trMnu.childNodes[ichk].childNodes[0];
			var otipo=trMnu.childNodes[itipo].childNodes[0];
			var ourlimg=trMnu.childNodes[iurlimg].childNodes[0];
			var oitem=trMnu.childNodes[iitem].childNodes[0];
			var oorden=trMnu.childNodes[iorden].childNodes[0];
			
			var idmenu=ochk.id; // Identificador del menu
			var std=ochk.value; // Estado original del checkbox 1=seleccionado 0=No seleccionado
			var tipo=tomavalorDesple(otipo);
			var urlimg=tomavalorDesple(ourlimg);
			var item=oitem.value;
			var orden=oorden.value;		

			if(op==2){
				var idtipoaccion=trMnu.childNodes[iidtipoaccion].innerHTML;
				var tipoaccion=trMnu.childNodes[itipoaccion].innerHTML;
			}				
			/* Control de errores */
			if(ochk.checked){
				if(item==""){ // Descripción del item
					alert(TbMsg[0]);
					oitem.focus();
					return(false);
				}
				if(orden=="" || orden<0 || !IsNumeric(orden)){
					alert(TbMsg[1]);
					oorden.focus();
					return(false);
				}
			}
			/* Compone parametros */
			if(ochk.checked){ // El checbox está seleccionado, alta o modificación
				var prm=idmenu+","+idtipoaccion+","+tipoaccion;
				prm+=","+tipo	+","+urlimg+","+item+","+orden;	
				if(std>0) // Originalmente estaba seleccionado, se trata de una modificación
					modificaciones+=prm+";";
				else
					altas+=prm+";";
			}
			else{ // Baja
				if(std>0){ // Si originariamente estaba marcado se trata de una baja
					var prm=idmenu+","+idtipoaccion+","+tipoaccion;
					bajas+=prm+";";
				}
			}
		}	
		trMnu=trMnu.nextSibling; // Primera fila de particiones								
	}		
	
	/* LLamada a la gestión */
	var wurl="../gestores/gestor_accionmenu.php";
	var prm="altas="+altas+"&bajas="+bajas+"&modificaciones="+modificaciones;
	CallPage(wurl,prm,"retornoGestion","POST");
}
//______________________________________________________________________________________________________
function retornoGestion(resul)
{
		if(resul.length>0){
		eval(resul)
	}
}
//________________________________________________________________________________________________________
function resultado_gestion_accionmenu(resul,descrierror){
	if (!resul){ // Ha habido algún error en la inserción
		alert(descrierror);
		return
	}
	alert(TbMsg[3]);
	location.href="../nada.php";
}

