// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2006
// Nombre del fichero: aula.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero aulas.php
// *************************************************************************************************************************************************
var litambito="";
var idambito="";
var nombreambito="";
var currentObj=null;
var wpadre=window.parent; // Toma frame padre
var farbol=wpadre.frames["frame_arbol"];
//________________________________________________________________________________________________________
//	
//	Muestra el formulario de captura de datos para modificación
//________________________________________________________________________________________________________
function modificar(l,t,w,h,pages){
	reset_contextual(-1,-1) // Oculta menu contextual
	var whref=pages+"?opcion="+op_modificacion+"&identificador="+idambito;
	window.open(whref,"frame_contenidos");
}
//________________________________________________________________________________________________________
//	
//	Muestra el formulario de captura de datos para eliminación
//________________________________________________________________________________________________________
function eliminar(l,t,w,h,pages){
	reset_contextual(-1,-1) // Oculta menu contextual
	var whref=pages+"?opcion="+op_eliminacion+"&identificador="+idambito;
	window.open(whref,"frame_contenidos");
}

//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de modificar datos 
//	Parámetros:
//			- resul: resultado de la operación ( true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- lit: Nuevo nombre del grupo
//________________________________________________________________________________________________________
function resultado_modificar(resul,descrierror,lit){
	if (!resul){
		alert(descrierror);
		return;
	}
	alert(CTbMsg[5]);
}
//________________________________________________________________________________________________________
//	
//	Refresca la visualización del estado de los ordenadores(Clientes rembo y clientes Windows o Linux) 
//________________________________________________________________________________________________________
function actualizar_ordenadores(){
	reset_contextual(-1,-1) // Oculta menu contextual
	var resul=window.confirm(TbMsg[1]);
	if (!resul)return
	var whref="actualizar.php?litambito="+litambito+"&idambito="+idambito
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=whref; // LLama a la página gestora
}
//________________________________________________________________________________________________________
//	
//	Conmuta el estado de los ordenadores(Modo Administrado reiniciándolos) 
//________________________________________________________________________________________________________
function conmutar_ordenadores(){
	reset_contextual(-1,-1) // Oculta menu contextual
	var resul=window.confirm(TbMsg[4]);
	if (!resul)return
	var whref="conmutar.php?litambito="+litambito+"&idambito="+idambito
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=whref; // LLama a la página gestora
}
//________________________________________________________________________________________________________
//	
//	Resetea la visualización del estado de los ordenadores(Clientes rembo y clientes Windows o Linux) 
//________________________________________________________________________________________________________
function purgar_ordenadores(){
	reset_contextual(-1,-1) // Oculta menu contextual
	var resul=window.confirm(TbMsg[2]);
	if (!resul)return
	var whref="purgar.php?litambito="+litambito+"&idambito="+idambito
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=whref; // LLama a la página gestora
}
//________________________________________________________________________________________________________
//	
//	Estatus de un aula
//________________________________________________________________________________________________________
function veraulas(o){
	Toma_Datos(o);
	var whref="aula.php?litambito="+litambito+"&idambito="+idambito+"&nombreambito="+nombreambito;
	 window.open(whref,"frame_contenidos")
	farbol.DespliegaNodo(litambito,idambito);
}
//________________________________________________________________________________________________________
function menucontextual(o,idmnctx){
	var menuctx=document.getElementById(idmnctx); // Toma objeto DIV
	muestra_contextual(ClickX,ClickY,menuctx) // muestra menu
	Toma_Datos(o);
	farbol.DespliegaNodo(litambito,idambito);
}
//________________________________________________________________________________________________________
//	
//	Toma datos
//________________________________________________________________________________________________________
function Toma_Datos(o){
	var identificador=o.getAttribute("id");
	litambito=identificador.split("-")[0];
	idambito=identificador.split("-")[1];
	nombreambito=o.getAttribute("value");
	currentObj=o;
}
//________________________________________________________________________________________________________
//	
//  Envía un comando para su ejecución o incorporación a procedimientos o tareas
//________________________________________________________________________________________________________
function confirmarcomando(ambito,idc,interac){
	var identificador=idc // identificador del comando
	var tipotrama='CMD'
	var wurl="../principal/dialogostramas.php?identificador="+identificador+"&tipotrama="+tipotrama+"&ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito
	if(interac==0){
	   ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		ifr.src=wurl; // LLama a la página gestora
	}
	else
		window.open(wurl,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//  Envía un comando para su ejecución o incorporación a procedimientos o tareas
//________________________________________________________________________________________________________
function confirmarprocedimiento(ambito){
	var wurl="../varios/ejecutarprocedimientos.php?ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito
	window.open(wurl,"frame_contenidos")}
//________________________________________________________________________________________________________
//	
//	Muestra la cola de acciones
//________________________________________________________________________________________________________
function cola_acciones(tipoaccion){
	var ambito;
	switch(litambito){
		case LITAMBITO_CENTROS :
			ambito=AMBITO_CENTROS;
			break;
		case LITAMBITO_GRUPOSAULAS :
			ambito=AMBITO_GRUPOSAULAS;
			break;
		case LITAMBITO_AULAS :
			ambito=AMBITO_AULAS;
			break;
		case LITAMBITO_GRUPOSORDENADORES :
			ambito=AMBITO_GRUPOSORDENADORES;
			break;
		case LITAMBITO_ORDENADORES :
			ambito=AMBITO_ORDENADORES;
			break;
	}
	var wurl="../principal/colasacciones.php?ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito+"&tipocola="+tipoaccion
	window.open(wurl,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra la cola de reservas
//________________________________________________________________________________________________________
function cola_reservas(tiporeserva){
	var ambito;
	switch(litambito){
		case LITAMBITO_CENTROS :
			ambito=AMBITO_CENTROS;
			break;
		case LITAMBITO_GRUPOSAULAS :
			ambito=AMBITO_GRUPOSAULAS;
			break;
		case LITAMBITO_AULAS :
			ambito=AMBITO_AULAS;
			break;
		case LITAMBITO_GRUPOSORDENADORES :
			ambito=AMBITO_GRUPOSORDENADORES;
			break;
		case LITAMBITO_ORDENADORES :
			ambito=AMBITO_ORDENADORES;
			break;
	}
	var wurl="../principal/programacionesaulas.php?ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito+"&tipocola="+tiporeserva
	window.open(wurl,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
// Muestra el formulario de captura de datos de un ordenador estandar
//________________________________________________________________________________________________________
function ordenador_estandar(){
	reset_contextual(-1,-1) // Oculta menu contextual
	var whref="../propiedades/propiedades_ordenadorestandar.php?idaula="+idambito+"&nombreaula="+nombreambito
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
function resultado_ordenadorestandar(resul,descrierror){
	if (!resul){ // Ha habido algún error
		alert(descrierror)
		return
	}
	alert(TbMsg[0]);
}
//________________________________________________________________________________________________________
//	
//	Muestra la configuración de los ordenadores
//	Parámetros:
//			- ambito: Ámbito que se quiere investigar
//________________________________________________________________________________________________________
function configuraciones(ambito){
		switch(ambito){
			case AMBITO_AULAS:
					wurl="configuracionaula.php?idaula="+idambito
					 window.open(wurl,"frame_contenidos")
					break;
			case AMBITO_GRUPOSORDENADORES:
					wurl="configuraciongrupoordenador.php?idgrupo="+idambito
					 window.open(wurl,"frame_contenidos")
					break;
			case AMBITO_ORDENADORES:
					wurl="configuracionordenador.php?idordenador="+idambito
					 window.open(wurl,"frame_contenidos")
					break;
		}
}
//___________________________________________________________________________________________________________
//	
//	Muestra formulario para incorporar ordenadores a través de un fichero de configuración de un servidor dhcp
//___________________________________________________________________________________________________________
function incorporarordenador(){
	var whref="../varios/incorporaordenadores.php?idaula="+idambito+"&nombreaula="+nombreambito
	window.open(whref,"frame_contenidos")
}
	