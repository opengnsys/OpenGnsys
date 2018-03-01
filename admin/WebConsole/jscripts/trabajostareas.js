// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: trabajostareas.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero trabajostareas.php
// *************************************************************************************************************************************************
var currentidtarea=null;
//____________________________________________________________________________
function gestion_tareas(id,o,op){
	var idtrabajo=document.fdatos.idtrabajo.value;
	var oorden=document.getElementById("orden-"+id);
	var orden=oorden.value;
	if(orden=="" || orden<1){
		alert(TbMsg[1]);
		if(op==null) o.checked=false;
		oorden.focus();
		return
	}
	if (o.checked){
		if(op==null)
			var  opcion=op_alta;
		else
			var opcion=op_modificacion;
	}
	else
		var opcion=op_eliminacion;

	var wurl="../gestores/gestor_trabajostareas.php";
	var prm="opcion="+opcion+"&idtrabajo="+idtrabajo+"&idtarea="+id+"&orden="+orden;
	CallPage(wurl,prm,"retornoGestion","POST");
}
//______________________________________________________________________________________________________
function retornoGestion(resul){
	if(resul.length>0){
		eval(resul);
	}
}



//________________________________________________________________________________________________________
function resultado_insertar_trabajostareas(resul,descrierror,resultado_modificar_trabajostareas){
	if (!resul){ // Ha habido algún error en la inserción
		alert(descrierror);
		return
	}
	alert(TbMsg[0]);
	var imgact=document.getElementById("imgact-"+currentidtarea);
	imgact.innerHTML='<TD id="imgact-' + currentidtarea + '"><IMG src="../images/iconos/actualizar.gif" style="cursor:hand" onclick="ActualizarAccion(' + currentidtarea + ')">'
}
//________________________________________________________________________________________________________
function resultado_eliminar_trabajostareas(resul,descrierror,idtt){
	if (!resul){ // Ha habido algún error en la eliminación
		alert(descrierror);
		return
	}
	alert(TbMsg[2]);
	var imgact=document.getElementById("imgact-"+currentidtarea);
	var imgHTML='&nbsp;';
	imgact.innerHTML=imgHTML;
	var oorden=document.getElementById("orden-"+currentidtarea);
	oorden.value="";
}
//________________________________________________________________________________________________________
function resultado_modificar_trabajostareas(resul,descrierror,id){
	if (!resul){ // Ha habido algún error en la eliminación
		alert(descrierror);
		return
	}
	alert(TbMsg[3])
}
//____________________________________________________________________________
function ActualizarAccion(id){
	var ocheckbox=document.getElementById("checkbox-"+id);
	gestion_tareas(id,ocheckbox,op_modificacion)
}
