// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005  Jos�Manuel Alonso. Todos los derechos reservados.
// Fecha Creaci�:2003-2004
// Fecha �tima modificaci�: Marzo-2005
// Nombre del fichero: informacion_menus.js
// Descripci� : 
//		Este fichero implementa las funciones javascript del fichero informacion_menus.php
// *************************************************************************************************************************************************
var currentItem=null // Item elegido
var ITEM_PUBLICO=1;
var ITEM_PRIVADO=2;
//____________________________________________________________________________
//
//	Recupera el navegador utilizado
//____________________________________________________________________________
var IE=(navigator.appName=="Microsoft Internet Explorer");
var NS=(navigator.appName=="Netscape");
//________________________________________________________________________________________________________
function eliminar_item(){
	reset_contextual(-1,-1)
	var resul=window.confirm(TbMsg[0]);
	if (!resul)return
	var idaccionmenu=currentNodo.toma_identificador()
	var wurl="../gestores/gestor_accionmenu.php?opcion="+op_eliminacion+"&idaccionmenu="+idaccionmenu
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=wurl; // LLama a la p�ina gestora
}
//________________________________________________________________________________________________________
function resultado_eliminar_accionmenu(resul,descrierror,idp,idh){
	if (!resul){ // Ha habido algn error en la eliminaci�
		alert(descrierror)
		return
	}
	alert(TbMsg[1])
	eliminar_nodo(currentNodo)  // Elimina el nodo del �bol
}
//________________________________________________________________________________________________________
function modificar_items(){
	reset_contextual(-1,-1)
	var tipoitem=currentNodo.toma_identificador()
	var idmenu=document.fdatos.idmenu.value; 
	if(tipoitem==ITEM_PUBLICO)
		var contitem=document.fdatos.contitempub.value; 
	else
		var contitem=document.fdatos.contitempri.value; 
	var alto=230+contitem*32
	if (alto>600) alto=600
	var descripcionaccion=currentNodo.toma_infonodo() // nombre del ordenador
	var whref="../varios/accionmenu.php?tipoitem="+tipoitem+"&idmenu="+idmenu+"&descripcionaccion="+descripcionaccion
	var opciones = "dialogWidth:600px;dialogHeight:"+alto+"px;"
    opciones += "resizable:yes;scroll:no;status:no;";
    opciones += "dialogLeft:170px;dialogTop:150px";
	retorno=window.showModalDialog(whref,"",opciones);
	if (retorno!=null){
		var splitRetorno=retorno.split("\t") 
		var resul=splitRetorno[0] 
		var descrierror=splitRetorno[1]
		if (!resul){ // Ha habido algn error en la inserci�
			alert(descrierror)
			return
		}
	}
}
//________________________________________________________________________________________________________
function modificar_item(){
	reset_contextual(-1,-1)
	var idaccionmenu=currentNodo.toma_identificador()
	var idmenu=document.fdatos.idmenu.value; 
	var descripcionaccion=currentNodo.toma_infonodo() // nombre del ordenador
	var whref="../varios/accionmenu.php?idaccionmenu="+idaccionmenu+"&idmenu="+idmenu+"&descripcionaccion="+descripcionaccion
	var opciones = "dialogWidth:600px;dialogHeight :300px;"
    opciones += "resizable:yes;scroll:no;status:no;";
    opciones += "dialogLeft:170px;dialogTop:150px";
	retorno=window.showModalDialog(whref,"",opciones);
	if (retorno!=null){
		var splitRetorno=retorno.split("\t") 
		var resul=splitRetorno[0] 
		var descrierror=splitRetorno[1]
		if (!resul){ // Ha habido algn error en la inserci�
			alert(descrierror)
			return
		}
	}
}
