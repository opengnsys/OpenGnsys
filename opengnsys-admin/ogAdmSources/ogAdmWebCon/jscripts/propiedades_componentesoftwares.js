// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: propiedades_componentesoftwares.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero propiedades_componentesoftwares.php
// *************************************************************************************************************************************************
var wpadre=window.parent; // Toma frame padre
var farbol=wpadre.frames["frame_arbol"];
//________________________________________________________________________________________________________
//	
//	Cancela la edición 
//________________________________________________________________________________________________________
function cancelar(){
	selfclose();
}
//________________________________________________________________________________________________________
// Devuelve el resultado de insertar un registro
// Especificaciones:
//		Los parámetros recibidos son:
//			- resul: resultado de la operación de inserción (true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- nwid: Identificador asignado al nuevo registro
//			- tablanodo: Tabla nodo generada para el nuevo registro (árbol de un sólo un elemento)
//________________________________________________________________________________________________________
function resultado_insertar_componentesoftwares(resul,descrierror,nwid,tablanodo){
	farbol.resultado_insertar(resul,descrierror,nwid,tablanodo);
	selfclose();
}
//________________________________________________________________________________________________________
//	
//		Devuelve el resultado de modificar algún dato de un registro
//		Especificaciones:
//		Los parámetros recibidos son:
//			- resul: resultado de la operación de inserción ( true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- lit: Nuevo nombre del grupo
//________________________________________________________________________________________________________
function resultado_modificar_componentesoftwares(resul,descrierror,lit,uri){
	farbol.resultado_modificar(resul,descrierror,lit,uri);
	selfclose();
}
//________________________________________________________________________________________________________
//	
//		Devuelve el resultado de eliminar un registro
//		Especificaciones:
//		Los parámetros recibidos son:
//			- resul: resultado de la operación de inserción ( true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- id: Identificador del registro que se quiso modificar
//________________________________________________________________________________________________________
function resultado_eliminar_componentesoftwares(resul,descrierror,id){
	farbol.resultado_eliminar(resul,descrierror,id);
	selfclose();
}
//________________________________________________________________________________________________________
function selfclose(){
	document.location.href="../nada.php";
}
//________________________________________________________________________________________________________
//	
//	Confirma la edición 
//________________________________________________________________________________________________________
function confirmar(op){
	var opcion=op;
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	var wurl="../gestores/gestor_componentesoftwares.php?opcion="+opcion
	if (opcion!=op_eliminacion){
		if(!comprobar_datos()) return
		var valor
		var o
		var nit=document.forms[0].elements.length // Prepara la cadena de parámetros metodo get
		for (i=0;i<nit;i++){
			o=document.forms[0].elements[i]
			valor=o.value
			if (valor=="on") {
					if(o.checked ) 
						valor=1; 
					else 
						valor=0
			}
			wurl+="&"+o.name+"="+valor
		}
	}
	else{
		wurl+="&idsoftware="+document.fdatos.idsoftware.value
	}
	ifr.src=wurl; // LLama a la página gestora
}
//________________________________________________________________________________________________________
//	
//	Comprobar_datos 
//________________________________________________________________________________________________________
function comprobar_datos(){
	if (document.fdatos.descripcion.value=="") {
		alert(TbMsg[0]);
		document.fdatos.descripcion.focus();
		return(false);
	}
	var  p=document.fdatos.idtiposoftware.selectedIndex
	if (p==0){  
         alert(TbMsg[1])
         document.forms.fdatos.idtiposoftware.focus()
         return(false)
	}
	else{
		if(p==3){ // Tipo de software: sistema operativo
		 p=document.fdatos.idtiposo.selectedIndex
		 if (p==0){  
			alert(TbMsg[2])
			document.forms.fdatos.idtiposo.focus()
			return(false)
		 }
		}
	}
	return(true);
}
//________________________________________________________________________________________________________
//	
//	Comprobar_datos 
//________________________________________________________________________________________________________
function seleccion(o){
	if(o.name=="idtiposoftware"){
		var otiposo=document.getElementById("tridtiposo"); // Toma objeto Iframe
		if(otiposo!=null){
			if(o.value!=1){
				otiposo.style.display="none"
				document.fdatos.idtiposo.selectedIndex=0
			}
			else
				otiposo.style.display="block"
		}
	}
}
