// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: aulas.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero aulas.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
//	
//		Copia al buffer un nodo de ordenador para moverlo posteriormente
//________________________________________________________________________________________________________
function mover_ordenador(){
	reset_contextual(-1,-1)
	corte_currentNodo=currentNodo
}
//________________________________________________________________________________________________________
//	
//		Esta funci� cambia de sitio un ordenador desde un aula a otro aula o bien adentro de un 
//  grupo de ordenadores dentro del mismo aula
//________________________________________________________________________________________________________
function colocar_ordenador(swsufijo){
	reset_contextual(-1,-1)
	if (!corte_currentNodo) {
		alert(CTbMsg[7]);
		return
	}
	var identificador=currentNodo.toma_identificador()
	var sufijonodo=currentNodo.toma_sufijo()
	var identificador_ordenador=corte_currentNodo.toma_identificador()
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	var swsf=parseInt(swsufijo)
	if (swsf==0) // El ordenador se mueve a un grupo de ordenadores
		var wurl="../gestores/gestor_ordenadores.php?opcion="+op_movida+"&grupoid="+identificador+"&idordenador="+identificador_ordenador
	else // El ordenador se mueve a un aula
		var wurl="../gestores/gestor_ordenadores.php?opcion="+op_movida+"&idaula="+identificador+"&idordenador="+identificador_ordenador
	ifr.src=wurl; // LLama a la p�ina para eliminar
}
//________________________________________________________________________________________________________
//	
//		Devuelve el resultado de cambiar un ordenador de sitio
//		Especificaciones:
//		Los par�etros recibidos son:
//			- resul: resultado de la operaci� de eliminaci� ( true si tuvo �ito)
//			- descrierror: Descripción del error en su caso
//			- nwid: Identificador del registro
//________________________________________________________________________________________________________
function resultado_cambiar_ordenadores(resul,descrierror,id){
	if (!resul){
		alert(descrierror)
		return
	}
	var ncel=corte_currentNodo.CeldaVista;
	var celdaHTML=ncel.parentNode.innerHTML; // Recupera celda del nodo
	if(IE)
		var  patron = new RegExp("<TD width=16><SPAN><IMG","gi") 
	else 
			if(NS)
				var  patron = new RegExp("<TD width=\"16\"><SPAN><IMG","gi") 

	var p=celdaHTML.search(patron); 
	if(p<0) return // Ha habido algn problema
	var nwceldaHTML='<TABLE  border="0" cellspacing="0" cellpadding="0"><TBODY><TR height=16><TD width=3></TD>';
	nwceldaHTML+=celdaHTML.substring(p);
	InsertaNodo(currentNodo,nwceldaHTML);
	EliminaNodo(corte_currentNodo) // Elimina el nodo 
	corte_currentNodo=null;
}
//________________________________________________________________________________________________________
//	
//	Refresca la visualizaci� del estado de los ordenadores(Clientes rembo y clientes Windows o Linux) 
//________________________________________________________________________________________________________
function actualizar_ordenadores(){
	reset_contextual(-1,-1) // Oculta menu contextual
	var resul=window.confirm(TbMsg[1]);
	if (!resul)return
	var idambito=currentNodo.toma_identificador()
	var litambito=currentNodo.toma_sufijo()
	var whref="actualizar.php?litambito="+litambito+"&idambito="+idambito
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=whref; // LLama a la p�ina gestora
}
//________________________________________________________________________________________________________
//	
//	Conmuta el estado de los ordenadores(Modo Administrado reinici�dolos) 
//________________________________________________________________________________________________________
function conmutar_ordenadores(){
	reset_contextual(-1,-1) // Oculta menu contextual
	var resul=window.confirm(TbMsg[4]);
	if (!resul)return
	var idambito=currentNodo.toma_identificador()
	var litambito=currentNodo.toma_sufijo()
	var whref="conmutar.php?litambito="+litambito+"&idambito="+idambito
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=whref; // LLama a la p�ina gestora
}
//________________________________________________________________________________________________________
//	
//	Resetea la visualizaci� del estado de los ordenadores(Clientes rembo y clientes Windows o Linux) 
//________________________________________________________________________________________________________
function purgar_ordenadores(){
	reset_contextual(-1,-1) // Oculta menu contextual
	var resul=window.confirm(TbMsg[2]);
	if (!resul)return
	var idambito=currentNodo.toma_identificador()
	var litambito=currentNodo.toma_sufijo()
	var whref="purgar.php?litambito="+litambito+"&idambito="+idambito
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	ifr.src=whref; // LLama a la p�ina gestora
}
//________________________________________________________________________________________________________
//	
//	Muestra estatus de los ordenadores 
//________________________________________________________________________________________________________
function ver_aulas(){
	reset_contextual(-1,-1) // Oculta menu contextual
	var idambito=currentNodo.toma_identificador();
	var litambito=currentNodo.toma_sufijo();
	var nombreambito=currentNodo.toma_infonodo();
	var whref="aula.php?litambito="+litambito+"&idambito="+idambito+"&nombreambito="+nombreambito
	 window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Estatus de un aula
//________________________________________________________________________________________________________
function veraula(o,sw){
	var identificador=o.getAttribute("id");
	var litambito=identificador.split("-")[0];
	var idambito=identificador.split("-")[1];
	var nombreambito=o.getAttribute("value");
	var whref="aula.php?litambito="+litambito+"&idambito="+idambito+"&nombreambito="+nombreambito
	 window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
function menucontextual(o,idmnctx){
	var menuctx=document.getElementById(idmnctx); // Toma objeto DIV
	muestra_contextual(ClickX,ClickY,menuctx) // muestra menu
}
//________________________________________________________________________________________________________
//	
//  Env� un comando para su ejecuci� o incorporaci� a procedimientos o tareas
//________________________________________________________________________________________________________
function confirmarcomando(ambito,idc,interac){
	reset_contextual(-1,-1); // Oculta menu contextual
	var identificador=idc // identificador del comando
	var tipotrama='CMD'
	var idambito=currentNodo.toma_identificador() // identificador del ambito
	var nombreambito=currentNodo.toma_infonodo() // nombre del �bito
	if(nombreambito=="")
		var  nombreambito=currentNodo.value // nombre del �bito desde p�ina aula.php
	var wurl="../principal/dialogostramas.php?identificador="+identificador+"&tipotrama="+tipotrama+"&ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito
	if(interac==0){
	   ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		ifr.src=wurl; // LLama a la p�ina gestora
	}
	else
		window.open(wurl,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//  Env� un comando para su ejecuci� o incorporaci� a procedimientos o tareas
//________________________________________________________________________________________________________
function confirmarprocedimiento(ambito){
	reset_contextual(-1,-1); // Oculta menu contextual
	var idambito=currentNodo.toma_identificador() // identificador del ambito
	var nombreambito=currentNodo.toma_infonodo() // nombre del �bito
	if(nombreambito=="")
		var  nombreambito=currentNodo.value // nombre del �bito desde  p�ina aula.php
	var wurl="../varios/ejecutarprocedimientos.php?ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito
	window.open(wurl,"frame_contenidos")}
//________________________________________________________________________________________________________
//	
//	Muestra la cola de acciones
//________________________________________________________________________________________________________
function cola_acciones(tipoaccion){
	reset_contextual(-1,-1); // Oculta menu contextual
	var ambito;
	var litambito=currentNodo.toma_sufijo() // ambito
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
	var idambito=currentNodo.toma_identificador() // identificador del �bito
	var nombreambito=currentNodo.toma_infonodo() // nombre del ordenador
	if(nombreambito=="")
		var  nombreambito=currentNodo.value // nombre del �bito desde p�ina aula.php
	var wurl="../principal/colasacciones.php?ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito+"&tipocola="+tipoaccion
	window.open(wurl,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra la cola de reservas
//________________________________________________________________________________________________________
function cola_reservas(tiporeserva){
	reset_contextual(-1,-1); // Oculta menu contextual
	var ambito;
	var litambito=currentNodo.toma_sufijo() // ambito

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
	var idambito=currentNodo.toma_identificador() // identificador del �bito
	var nombreambito=currentNodo.toma_infonodo() // nombre del ordenador
	if(nombreambito=="")
		var  nombreambito=currentNodo.value // nombre del �bito desde p�ina aula.php
	var wurl="../principal/programacionesaulas.php?ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito+"&tipocola="+tiporeserva
	window.open(wurl,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
// Muestra el formulario de captura de datos de un ordenador estandar
//________________________________________________________________________________________________________
function ordenador_estandar(){
	reset_contextual(-1,-1) // Oculta menu contextual
	var identificador=currentNodo.toma_identificador()
	var nombreaula=currentNodo.toma_infonodo()
	var whref="../propiedades/propiedades_ordenadorestandar.php?idaula="+identificador+"&nombreaula="+nombreaula
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
function resultado_ordenadorestandar(resul,descrierror){
	if (!resul){ // Ha habido algn error
		alert(descrierror)
		return
	}
	alert(TbMsg[0]);
}
//________________________________________________________________________________________________________
//	
//	Muestra la configuraci� de los ordenadores
//	Par�etros:
//			- ambito: �bito que se quiere investigar
//________________________________________________________________________________________________________
function configuraciones(ambito){
		reset_contextual(-1,-1) // Oculta menu contextual
		var identificador=currentNodo.toma_identificador();
		switch(ambito){
			case AMBITO_AULAS:
					wurl="configuracionaula.php?idaula="+identificador
					 window.open(wurl,"frame_contenidos")
					break;
			case AMBITO_GRUPOSORDENADORES:
					wurl="configuraciongrupoordenador.php?idgrupo="+identificador
					 window.open(wurl,"frame_contenidos")
					break;
			case AMBITO_ORDENADORES:
					wurl="configuracionordenador.php?idordenador="+identificador
					 window.open(wurl,"frame_contenidos")
					break;
		}
}
//___________________________________________________________________________________________________________
//	
//	Muestra formulario para incorporar ordenadores a trav� de un fichero de configuraci� de un servidor dhcp
//___________________________________________________________________________________________________________
function incorporarordenador(){
	reset_contextual(-1,-1)
	var idaula=currentNodo.toma_identificador()
	var nombreaula=currentNodo.toma_infonodo()
	var whref="../varios/incorporaordenadores.php?idaula="+idaula+"&nombreaula="+nombreaula
	window.open(whref,"frame_contenidos")
}
	