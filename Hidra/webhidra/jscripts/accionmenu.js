// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: accionmenu.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero accionmenu.php
// *************************************************************************************************************************************************
var  currentedicion=null;
var  currentidmenu=null;
var EDICIONACCION=1;
var EDICIONITEMS=2;
var EDICIONITEM=3;
//________________________________________________________________________________________________________
function gestion_acciones(id,o,op){
	var idtipoaccion=document.fdatos.idtipoaccion.value
	var tipoaccion=document.fdatos.tipoaccion.value
	var otipoitem=document.getElementById("tipositems-"+id);
	var tipoitem=otipoitem.value
	var oTD=document.getElementById("TDurlimagesitems-"+id);
	var idurlimg=oTD.childNodes[0].value
	var odescripitem=document.getElementById("descripitem-"+id)
	var descripitem=odescripitem.value
	var oorden=document.getElementById("orden-"+id)
	var orden=oorden.value
	if(descripitem==""){
		alert(TbMsg[0]);
		if(op==null) o.checked=false
		odescripitem.focus();
		return
	}
	if(orden=="" || orden<0){
		alert(TbMsg[1]);
		if(op==null) o.checked=false
		oorden.focus();
		return
	}
	if (o.checked){
		if(op==null)  op=op_alta;
		var wurl="../gestores/gestor_accionmenu.php?opcion="+op+"&idtipoaccion="+idtipoaccion+"&idmenu="+id+"&tipoaccion="+tipoaccion+"&tipoitem="+tipoitem	+"&idurlimg="+idurlimg+"&descripitem="+descripitem+"&orden="+orden
	}
	else
		var wurl="../gestores/gestor_accionmenu.php?opcion="+op_eliminacion+"&idtipoaccion="+idtipoaccion+"&idmenu="+id+"&tipoaccion="+tipoaccion
	currentidmenu=id // Guarda identificdor del menu
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	currentedicion=document.fdatos.tipoedicion.value
	ifr.src=wurl; // LLama a la página gestora
}
//________________________________________________________________________________________________________
function resultado_insertar_accionmenu(resul,descrierror,nwid,tablanodo){
	if (!resul){ // Ha habido algún error en la inserción
		alert(descrierror)
		return
	}
	alert(TbMsg[2])
	var imgact=document.getElementById("imgact-"+currentidmenu);
	var imgHTML='<TD id="imgact-'+currentidmenu+'"><IMG src="../images/iconos/actualizar.gif" style="cursor:hand" onclick="ActualizarAccion('+currentidmenu+')">';
	imgact.innerHTML=imgHTML
}
//________________________________________________________________________________________________________
function resultado_eliminar_accionmenu(resul,descrierror,idp,idh){
	if (!resul){ // Ha habido algún error en la eliminación
		alert(descrierror)
		return
	}
	alert(TbMsg[3])
	var imgact=document.getElementById("imgact-"+currentidmenu);
	var imgHTML='&nbsp;';
	imgact.innerHTML=imgHTML
	var oorden=document.getElementById("orden-"+currentidmenu)
	oorden.value=""
}
//________________________________________________________________________________________________________
function resultado_modificar_accionmenu(resul,descrierror,idp,idh){
	if (!resul){ // Ha habido algún error en la eliminación
		alert(descrierror)
		return
	}
	alert(TbMsg[4])
	if(	currentedicion==EDICIONITEM) self.close();
}
//________________________________________________________________________________________________________
function ActualizarAccion(id){

	var ocheckbox=document.getElementById("checkbox-"+id);
	gestion_acciones(id,ocheckbox,op_modificacion)
}
//________________________________________________________________________________________________________
function ActualizarItems(tipoaccion,idtipoaccion,id){

	var oTD=document.getElementById("TDurlimagesitems-"+id);
	var idurlimg=oTD.childNodes[0].value
	var odescripitem=document.getElementById("descripitem-"+id)
	var descripitem=odescripitem.value
	var oorden=document.getElementById("orden-"+id)
	var orden=oorden.value
	if(descripitem==""){
		alert(TbMsg[0]);
		odescripitem.focus();
		return
	}
	if(orden=="" || orden<0){
		alert(TbMsg[1]);
		odescripitem.focus();
		return
	}
	var op=op_modificacion;
	var tipoitem=document.fdatos.tipoitem.value
	var idmenu=document.fdatos.idmenu.value
	var wurl="../gestores/gestor_accionmenu.php?opcion="+op+"&idtipoaccion="+idtipoaccion+"&idmenu="+idmenu+"&tipoaccion="+tipoaccion+"&tipoitem="+tipoitem	+"&idurlimg="+idurlimg+"&descripitem="+descripitem+"&orden="+orden
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	currentedicion=document.fdatos.tipoedicion.value
	ifr.src=wurl; // LLama a la página gestora
}
//________________________________________________________________________________________________________
function ActualizarItem(tipoaccion,idtipoaccion,id){
	var otipoitem=document.getElementById("tipositems-"+id);
	var tipoitem=otipoitem.value
	var oTD=document.getElementById("TDurlimagesitems-"+id);
	var idurlimg=oTD.childNodes[0].value
	var odescripitem=document.getElementById("descripitem-"+id)
	var descripitem=odescripitem.value
	var oorden=document.getElementById("orden-"+id)
	var orden=oorden.value
	if(descripitem==""){
		alert(TbMsg[0]);
		odescripitem.focus();
		return
	}
	if(orden=="" || orden<0){
		alert(TbMsg[1]);
		odescripitem.focus();
		return
	}
	var op=parseInt(op_modificacion);
	var idmenu=document.fdatos.idmenu.value
	var wurl="../gestores/gestor_accionmenu.php?opcion="+op+"&idtipoaccion="+idtipoaccion+"&idmenu="+idmenu+"&tipoaccion="+tipoaccion+"&tipoitem="+tipoitem	+"&idurlimg="+idurlimg+"&descripitem="+descripitem+"&orden="+orden
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	currentedicion=document.fdatos.tipoedicion.value
	ifr.src=wurl; // LLama a la página gestora
}
