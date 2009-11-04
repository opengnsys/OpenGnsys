// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: colasacciones.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero colasacciones.php
// *************************************************************************************************************************************************
	var currentNotTR=null;  
	var currentTR=null;  
	var currentAccion=null;  

	var currentResultado=null;  
	var currentEstado=null;  
	var currentFecha=null;  
	var currentHora=null;  

	var currentIdcmdtskwrk=null;
	var currentCodtipoaccion=null;
	var currentIdambcmdtskwrk=null;
	
	var currentTipoAccion=null;  
	var currentidTipoAccion=null;  
	var currentNombreTipoAccion=null;  

	var currentNotificacion=null;  
	var currentIdNotificador=null;
	var currentResultadoNot=null;
	var currentTipoNotificador=null;

	var op_modificar_resultado=1;
	var op_modificar_estado=2;
	var op_reiniciar_accion=3;
	var op_eliminar_accion=4;
	var op_modificar_resultado_notificacion=5
	var op_reiniciar_notificacion=6;

	var op_eliminar_mulaccion=7;
	var op_modificar_mulresultado=8;
	var op_modificar_mulestado=9;
	var op_reiniciar_mulaccion=10;

	var ACCION_ELIMINADA=-1; // Acción eliminada
	var ACCION_REINICIADA=-2; // Acción reiniciada

	var NOTIFICADOR_ORDENADOR=1;
	var NOTIFICADOR_COMANDO=2;
	var NOTIFICADOR_TAREA=3;

	var currentOp=null;
//____________________________________________________________________________
//
//	Recupera el navegador utilizado
//____________________________________________________________________________
var IE=(navigator.appName=="Microsoft Internet Explorer");
var NS=(navigator.appName=="Netscape");

//________________________________________________________________________________________________________
	function resaltar(o){
		if (o==currentOp) return
		o.style.borderBottomColor="#5a86b5"
		o.style.borderRightColor="#5a86b5"
		o.style.borderTopColor="#5a86b5"
		o.style.borderLeftColor="#5a86b5"
		o.style.color="#5a86b5"
		o.style.fontWeight="bold"

	}
//________________________________________________________________________________________________________
	function desresaltar(o){
		if (o==currentOp) return
		o.style.borderBottomColor="#999999"
		o.style.borderRightColor="#999999"
		o.style.borderTopColor="#999999"
		o.style.borderLeftColor="#999999"
		o.style.color="#999999"
		o.style.fontWeight="normal"
	}
//________________________________________________________________________________________________________
	function eleccion(o,op){
		switch(op){
			case 1: 
					eliminar_mulaccion();
					break;
			case 2:
					reiniciar_mulaccion();
					break;
			case 3:
					modificar_mulestado(ACCION_DETENIDA)
					break;
			case 4:
					modificar_mulestado(ACCION_INICIADA)
					break;
			case 5:
					modificar_mulresultado(ACCION_ABORTADA);
					break;
			case 6:
					modificar_mulresultado(ACCION_TERMINADA);
					break;
		}
	}
//________________________________________________________________________________________________________
	function chgdespleacciones(o){
		var otip="";
		for (var i=0; i< o.options.length; i++){
			if(o.options[i].selected)
				otip+=o.options[i].value+"="+o.options[i].text+";"
		}
		document.fdatos.tiposacciones.value=otip
	}
//________________________________________________________________________________________________________
	function chgdespleestados(o,swevt){
		var otip="";
		for (var i=0; i< o.options.length; i++){
			if(o.options[i].selected)
				otip+=o.options[i].value+"="+o.options[i].text+";"
		}
		document.fdatos.estados.value=otip
	
		if(swevt==null){
			// Implicaciones
			var marca=false
			if (o.options[0].selected || o.options[1].selected)  marca=true;
			marca_resultado(ACCION_SINERRORES,marca);
			marca_resultado(ACCION_CONERRORES,marca);

			marca=false
			if (o.options[2].selected) marca=true;
			marca_resultado(ACCION_EXITOSA,marca);
			marca_resultado(ACCION_FALLIDA,marca);
			marca_resultado(ACCION_TERMINADA,marca);
			marca_resultado(ACCION_ABORTADA,marca);
		}
	}
//________________________________________________________________________________________________________
	function chgdespleresultados(o,swevt){
		var otip="";
		for (var i=0; i< o.options.length; i++){
			if(o.options[i].selected)
				otip+=o.options[i].value+"="+o.options[i].text+";"
		}
		document.fdatos.resultados.value=otip

		if(swevt==null){
			// Implicaciones 
			var marca=false
			if (o.options[0].selected || o.options[1].selected || o.options[2].selected || o.options[3].selected)  marca=true;
			marca_estado(ACCION_FINALIZADA,marca);

			marca=false
			if (o.options[4].selected || o.options[5].selected ) marca=true;
			marca_estado(ACCION_DETENIDA,marca);
			marca_estado(ACCION_INICIADA,marca);
		}
	}
//________________________________________________________________________________________________________
	function marca_accion(tipoaccion,marca){
		var o=document.fdatos.wtiposacciones
		var otip="";
		for (var i=0; i< o.options.length; i++){
			if(o.options[i].value==tipoaccion)
				otip+=o.options[i].selected=marca
		}
		chgdespleacciones(o);
	}
//________________________________________________________________________________________________________
	function marca_resultado(resultado,marca){
		var o=document.fdatos.wresultados
		var otip="";
		for (var i=0; i< o.options.length; i++){
			if(o.options[i].value==resultado)
				otip+=o.options[i].selected=marca
		}
		chgdespleresultados(o,false);
	}
//________________________________________________________________________________________________________
	function marca_estado(estado,marca){
		var o=document.fdatos.westados
		var otip="";
		for (var i=0; i< o.options.length; i++){
			if(o.options[i].value==estado)
				otip+=o.options[i].selected=marca
		}
		chgdespleestados(o,false);
	}
//________________________________________________________________________________________________________
	function modificar_resultado(resultado){

		var oIMGs=currentTR.getElementsByTagName('IMG')
		var ultimgale=oIMGs.length-1
		var resimg=oIMGs[ultimgale].value
		if(resimg==ACCION_EXITOSA || resimg==ACCION_FALLIDA){
			alert(TbMsg[0]);
			return
		}
		if(resimg==ACCION_TERMINADA && resultado==ACCION_TERMINADA){
			alert(TbMsg[2]);
			return
		}
		if(resimg==ACCION_ABORTADA && resultado==ACCION_ABORTADA){
			alert(TbMsg[3]);
			return
		}
		reset_contextual(-1,-1);
		currentResultado=resultado;
		ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		var gestorcolas="";
		switch(currentTipoAccion){
				case EJECUCION_COMANDO :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case EJECUCION_TAREA :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case EJECUCION_TRABAJO :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
		}
		wurl=gestorcolas+"?opcion="+op_modificar_resultado+"&idaccion="+currentAccion+"&resultado="+resultado
		ifr.src=wurl; // LLama a la página gestora
	}
//________________________________________________________________________________________________________
	function modificar_estado(estado){
		var oIMGs=currentTR.getElementsByTagName('IMG')
		var ultimgale=oIMGs.length-2
		var resimg=oIMGs[ultimgale].value
		if(resimg==ACCION_FINALIZADA){
			alert(TbMsg[1]);
			return
		}
		if(resimg==ACCION_INICIADA && estado==ACCION_INICIADA){
			alert(TbMsg[4]);
			return
		}
		if(resimg==ACCION_DETENIDA && estado==ACCION_DETENIDA){
			alert(TbMsg[5]);
			return
		}
		reset_contextual(-1,-1);
		currentEstado=estado;
		ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		var gestorcolas="";
		switch(currentTipoAccion){
				case EJECUCION_COMANDO :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case EJECUCION_TAREA :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case EJECUCION_TRABAJO :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
		}
		var wurl=gestorcolas+"?opcion="+op_modificar_estado+"&idaccion="+currentAccion+"&estado="+estado
		ifr.src=wurl; // LLama a la página gestora
	}
//________________________________________________________________________________________________________
	function reiniciar_accion(){
		reset_contextual(-1,-1);

		ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		var gestorcolas="";
		switch(currentTipoAccion){
				case EJECUCION_COMANDO :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case EJECUCION_TAREA :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case EJECUCION_TRABAJO :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
		}
		var wurl=gestorcolas+"?opcion="+op_reiniciar_accion+"&idaccion="+currentAccion;
		ifr.src=wurl; // LLama a la página gestora
	}
//________________________________________________________________________________________________________
	function eliminar_accion(){
		reset_contextual(-1,-1);
		ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		var gestorcolas="";
		switch(currentTipoAccion){
				case EJECUCION_COMANDO :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case EJECUCION_TAREA :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case EJECUCION_TRABAJO :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
		}
		var wurl=gestorcolas+"?opcion="+op_eliminar_accion+"&idaccion="+currentAccion
		ifr.src=wurl; // LLama a la página gestora
	}
//________________________________________________________________________________________________________
	function eliminar_mulaccion(){
		reset_contextual(-1,-1);
		if(confirm("ATENCIÓN.-Se van a eliminar todas las acciones que están actualmente seleccionadas. ¿ Está seguro de querer hacerlo ?")){
			var mulaccion=document.getElementById("mulaccion").value; // Toma los identificadores de todas las acciones
			var wurl="../gestores/gestor_colasacciones.php?opcion="+op_eliminar_mulaccion+"&mulaccion="+mulaccion
			ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
			ifr.src=wurl; // LLama a la página gestora
		}
	}
//________________________________________________________________________________________________________
	function reiniciar_mulaccion(){
		reset_contextual(-1,-1);
		if(confirm("ATENCIÓN.-Se van a reiniciar todas las acciones que están actualmente seleccionadas. ¿ Está seguro de querer hacerlo ?")){
			var mulaccion=document.getElementById("mulaccion").value; // Toma los identificadores de todas las acciones
			var wurl="../gestores/gestor_colasacciones.php?opcion="+op_reiniciar_mulaccion+"&mulaccion="+mulaccion
			ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
			ifr.src=wurl; // LLama a la página gestora
		}
	}
//________________________________________________________________________________________________________
	function modificar_mulestado(estado){
		reset_contextual(-1,-1);
		if(estado==ACCION_DETENIDA) msg="ATENCIÓN.-Se van a detener todas las acciones que están actualmente seleccionadas y no hayan finalizado. ¿ Está seguro de querer hacerlo ?"
		if(estado==ACCION_INICIADA) msg="ATENCIÓN.-Van a proseguir todas las acciones que están actualmente seleccionadas y estén detenidas. ¿ Está seguro de querer hacerlo ?"
		if(confirm(msg)){
			var mulaccion=document.getElementById("mulaccion").value; // Toma los identificadores de todas las acciones
			var wurl="../gestores/gestor_colasacciones.php?opcion="+op_modificar_mulestado+"&mulaccion="+mulaccion+"&estado="+estado
			ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
			ifr.src=wurl; // LLama a la página gestora
		}
	}
//________________________________________________________________________________________________________
	function modificar_mulresultado(resultado){
		reset_contextual(-1,-1);
		if(resultado==ACCION_ABORTADA) msg="ATENCIÓN.-Se van a abortar todas las acciones que están actualmente seleccionadas y no hayan finalizado. ¿ Está seguro de querer hacerlo ?"
		if(resultado==ACCION_TERMINADA) msg="ATENCIÓN.-Van a terminar todas las acciones que están actualmente seleccionadas y no hayan finalizado. ¿ Está seguro de querer hacerlo ?"
		if(confirm(msg)){
			var mulaccion=document.getElementById("mulaccion").value; // Toma los identificadores de todas las acciones
			var wurl="../gestores/gestor_colasacciones.php?opcion="+op_modificar_mulresultado+"&mulaccion="+mulaccion+"&resultado="+resultado
			ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
			ifr.src=wurl; // LLama a la página gestora
		}
	}
//________________________________________________________________________________________________________
	function resultado_multipleaccion(resul,descrierror){
		if (!resul){
			alert(descrierror)
			return
		}
		quitar_filtro();
	}
//________________________________________________________________________________________________________
	function resultado_modificar_resultado(resul,descrierror,id){
		if (!resul){
			alert(descrierror)
			return
		}
		var mulaccion=document.getElementById("mulaccion").value;// Toma los identificadores de todas las acciones
		mitriada=new TRIADA;
		toma_triada(mitriada,mulaccion);

		var oIMGs=currentTR.getElementsByTagName('IMG')
		ultimgale=oIMGs.length-1
		switch(currentResultado){
				case ACCION_TERMINADA :
					oIMGs[ultimgale].src="../images/iconos/acTerminada.gif"
					oIMGs[ultimgale].value=ACCION_TERMINADA
					mitriada.resultado=ACCION_TERMINADA
					break;
				case ACCION_ABORTADA :
					oIMGs[ultimgale].src="../images/iconos/acAbortada.gif"
					oIMGs[ultimgale].value=ACCION_ABORTADA
					mitriada.resultado=ACCION_ABORTADA
					break;
		}
		ultimgale=oIMGs.length-2
		oIMGs[ultimgale].src="../images/iconos/acFinalizada.gif"
		oIMGs[ultimgale].value=ACCION_FINALIZADA
		mitriada.estado=ACCION_FINALIZADA

		actualiza_triadas(mitriada,mulaccion);

		alert(TbMsg[7])
	}
//________________________________________________________________________________________________________
	function resultado_modificar_estado(resul,descrierror,id){
		if (!resul){
			alert(descrierror)
			return
		}

		var mulaccion=document.getElementById("mulaccion").value;// Toma los identificadores de todas las acciones
		mitriada=new TRIADA;
		toma_triada(mitriada,mulaccion);

		var oIMGs=currentTR.getElementsByTagName('IMG')
		ultimgale=oIMGs.length-2
		switch(currentEstado){
				case ACCION_DETENIDA :
					oIMGs[ultimgale].src="../images/iconos/acDetenida.gif"
					oIMGs[ultimgale].value=ACCION_DETENIDA
					mitriada.estado=ACCION_TERMINADA

					break;
				case ACCION_INICIADA :
					oIMGs[ultimgale].src="../images/iconos/acIniciada.gif"
					oIMGs[ultimgale].value=ACCION_INICIADA
					mitriada.estado=ACCION_INICIADA
					break;
		}
		actualiza_triadas(mitriada,mulaccion);
		alert(TbMsg[8])
	}
//________________________________________________________________________________________________________
	function resultado_reiniciar_accion(resul,descrierror,id){
		if (!resul){
			alert(descrierror)
			return
		}
		var mulaccion=document.getElementById("mulaccion").value;// Toma los identificadores de todas las acciones
		mitriada=new TRIADA;
		toma_triada(mitriada,mulaccion);

		var oIMGs=currentTR.getElementsByTagName('IMG')
		ultimgale=oIMGs.length-2
		oIMGs[ultimgale].src="../images/iconos/acIniciada.gif"
		oIMGs[ultimgale].value=ACCION_INICIADA
		mitriada.estado=ACCION_INICIADA

		ultimgale=oIMGs.length-1
		oIMGs[ultimgale].src="../images/iconos/acSinErrores.gif"
		oIMGs[ultimgale].value=ACCION_SINERRORES
		mitriada.resultado=ACCION_SINERRORES

		var oTDPORCEN=document.getElementById("PORCEN-"+currentAccion);
		oTDPORCEN.innerHTML="0%";

		CambiaImg_Notificaciones("../images/iconos/reiniciar.gif",ACCION_REINICIADA)
		actualiza_triadas(mitriada,mulaccion);

		alert(TbMsg[9])
	}
//________________________________________________________________________________________________________
	function resultado_eliminar_accion(resul,descrierror,id){
		if (!resul){
			alert(descrierror)
			return
		}
		var oIMGs=currentTR.getElementsByTagName('IMG')
		ultimgale=oIMGs.length-1
		oIMGs[ultimgale].src="../images/iconos/eliminar.gif"
		oIMGs[ultimgale].value="-1"

		CambiaImg_Notificaciones("../images/iconos/eliminar.gif",ACCION_ELIMINADA)

		alert(TbMsg[10])
	}
//________________________________________________________________________________________________________
	function resalta(o,tipac,nombreac){
		var wobj=o
		// Toma el objeto TR de la acción
		while (wobj.tagName!="TR"){
			wobj=wobj.parentNode
		}
		var woIMGs=wobj.getElementsByTagName('IMG')
		var wultimgale=woIMGs.length-1
		var wresimg=woIMGs[wultimgale].value
		if(wresimg==ACCION_ELIMINADA){
			alert(TbMsg[6]);
			event.returnValue=false;
			return
		}
		currentIdcmdtskwrk=o.getAttribute("name"); // Toma el identificador del comando,tarea o trabajo
		currentCodtipoaccion=tipac // Toma el tipo de acción: comando,tarea o trabajo
		currentAccion=o.getAttribute("id")

		currentidTipoAccion=currentIdcmdtskwrk;  
		currentTipoAccion=currentCodtipoaccion;  
		currentNombreTipoAccion=nombreac;  

		reset_seleccion();

		currentTR=wobj;
		currentIdambcmdtskwrk=currentTR.value  // comando,tarea o trabajo
		var oTDs=currentTR.getElementsByTagName('TD')
		for(var i=0;i<oTDs.length;i++){
			oTDs[i].style.backgroundColor="E2007F"; 
			oTDs[i].style.color="#ffffff" 
		}

		switch(currentTipoAccion){
			case EJECUCION_COMANDO:
					menu_contextual(null,'flo_comandos'); 
					break;
			case EJECUCION_TAREA:
					menu_contextual(null,'flo_tareas'); 
					break;
			case EJECUCION_TRABAJO:
					menu_contextual(null,'flo_trabajos'); 
					break;
		}
	}
//________________________________________________________________________________________________________
	function resaltanot(o,tiponot){
		var wobj=o
		// Toma el objeto TR de la acción
		while (wobj.tagName!="TR"){
			wobj=wobj.parentNode
		}
		var woIMGs=wobj.getElementsByTagName('IMG')
		var wultimgale=woIMGs.length-1
		var wresimg=woIMGs[wultimgale].value
		if(wresimg==ACCION_ELIMINADA){
			alert(TbMsg[12]);
			event.returnValue=false;
			return
		}
		if(wresimg==ACCION_REINICIADA){
			alert(TbMsg[13]);
			event.returnValue=false;
			return
		}

		currentAccion=o.getAttribute("id") // Toma el identificador de la acción
		currentNotificacion=o.name //  Toma el identificador de la notificación
		currentIdNotificador=o.value // Toma el identificador del ordenador que notifica
		currentTipoNotificador=tiponot // Toma el tipo de notificador ( ordenador, comando o tarea )

		reset_seleccion();

		currentNotTR=wobj;
		var oTDs=currentNotTR.getElementsByTagName('TD')

		for(var i=0;i<oTDs.length;i++){
			oTDs[i].style.backgroundColor="E2007F"; // Rojo
			oTDs[i].style.color="#ffffff" 
		}
		// Toma el objeto TR de la acción
		var auxSplit=currentNotTR.getAttribute("id").split("_"); // Toma identificación del nodo notificación
		var idTR='ACC_'+auxSplit[1];
		currentTR=document.getElementById(idTR);
		currentTipoAccion=currentTR.name
		menu_contextual(null,'flo_notificaciones'); 
	}
//________________________________________________________________________________________________________
	function ver_notificaciones(o,sw,ida){
		o=o.parentNode
		o.childNodes[sw].style.display="none"
		sw++
		if(sw>1)sw=0
		o.childNodes[sw].style.display="block"

		while (o.tagName!="TBODY"){
			o=o.parentNode
		}
		var oTRs=o.getElementsByTagName('TR')
		for(var i=0;i<oTRs.length;i++){
			if(oTRs[i].getAttribute("id")=='NOT_'+ida || oTRs[i].getAttribute("id")=='PAR_'+ida)
				if (oTRs[i].style.display=="none") oTRs[i].style.display="block"
				else
					oTRs[i].style.display="none"
		}
	}
//________________________________________________________________________________________________________
	function vertabla_calendario(ofecha){
		currentFecha=ofecha;
		url="../varios/calendario_ventana.php?fecha="+ofecha.value
		window.open(url,"vf","top=160,left=250,height=220,width=150,scrollbars=no")
	}
//________________________________________________________________________________________________________
	function vertabla_horario(ohora){
		currentHora=ohora;
		url="../varios/horario_ventana.php?hora="+ohora.value
		window.open(url,"vh","top=120,left=115,height=180,width=580,scrollbars=no")
	}
//________________________________________________________________________________________________________
	function anade_fecha(fecha){
		currentFecha.value=fecha
	}
//________________________________________________________________________________________________________
	function anade_hora(hora){
		currentHora.value=hora
	}
//________________________________________________________________________________________________________
	function filtrar_accion(){
		document.fdatos.idcmdtskwrk.value=currentIdcmdtskwrk
		document.fdatos.codtipoaccion.value=currentCodtipoaccion
		document.fdatos.submit()
	}
//________________________________________________________________________________________________________
	function quitar_filtro(){
		document.fdatos.idcmdtskwrk.value=""
		document.fdatos.codtipoaccion.value=""
		document.fdatos.idambcmdtskwrk.value=""
		document.fdatos.submit()
	}
//________________________________________________________________________________________________________
	function filtrar_porambito(){
		document.fdatos.idcmdtskwrk.value=currentIdcmdtskwrk
		document.fdatos.codtipoaccion.value=currentCodtipoaccion
		document.fdatos.idambcmdtskwrk.value=currentIdambcmdtskwrk
		document.fdatos.submit()
	}
//________________________________________________________________________________________________________
	function modificar_resultado_notificacion(resultadoNot){
		var oIMGs=currentNotTR.getElementsByTagName('IMG')
		var ultimgale=oIMGs.length-1
		var resimg=oIMGs[ultimgale].value
		if(resimg==ACCION_EXITOSA && resultadoNot==ACCION_EXITOSA){
			alert(TbMsg[14]);
			return
		}
		if(resimg==ACCION_FALLIDA && resultadoNot==ACCION_FALLIDA){
			alert(TbMsg[15]);
			return
		}
		reset_contextual(-1,-1);

		currentResultadoNot=resultadoNot;
		ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe

		var gestorcolas="";
		switch(currentTipoNotificador){
				case NOTIFICADOR_ORDENADOR :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case NOTIFICADOR_COMANDO :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case NOTIFICADOR_TAREA :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
		}
		wurl=gestorcolas+"?opcion="+op_modificar_resultado_notificacion+"&idaccion="+currentAccion+"&idnotificacion="+currentNotificacion+"&resultadoNot="+resultadoNot
		ifr.src=wurl; // LLama a la página gestora
	}
//________________________________________________________________________________________________________
	function resultado_modificar_resultado_notificacion(resul,descrierror,id){
		if (!resul){
			alert(descrierror)
			return
		}
		// Cambia imagen resultado de la notificación
		var oIMGs=currentNotTR.getElementsByTagName('IMG')
		ultimgale=oIMGs.length-1
		switch(currentResultadoNot){
				case ACCION_EXITOSA :
					oIMGs[ultimgale].src="../images/iconos/acExitosa.gif"
					oIMGs[ultimgale].value=ACCION_EXITOSA
					if(currentTipoNotificador==NOTIFICADOR_ORDENADOR){
						oIMGs[0].src="../images/iconos/ordenadornot_ok.gif"
						var imgordnot=document.getElementById("ORDNOT_"+currentAccion+"_"+currentNotificacion);
						imgordnot.src="../images/iconos/ordenadornot_ok.gif";
					}
					break;
				case ACCION_FALLIDA :
					oIMGs[ultimgale].src="../images/iconos/acFallida.gif"
					oIMGs[ultimgale].value=ACCION_FALLIDA
					if(currentTipoNotificador==NOTIFICADOR_ORDENADOR){
						oIMGs[0].src="../images/iconos/ordenadornot_ko.gif"
						var imgordnot=document.getElementById("ORDNOT_"+currentAccion+"_"+currentNotificacion);
						imgordnot.src="../images/iconos/ordenadornot_ko.gif";
					}
					break;
		}

		// Cambia imagen resultado de la acción
		var oIMGs=currentTR.getElementsByTagName('IMG')
		ultimgale=oIMGs.length-2
		var imgestacc=oIMGs[ultimgale] // Imagen del estado de la acción
		ultimgale=oIMGs.length-1
		var imgresacc=oIMGs[ultimgale] // Imagen del resultado de la acción

		if(currentResultadoNot==ACCION_FALLIDA){ // Si se notificó a Fallida
			if(imgestacc.value==ACCION_FINALIZADA){ // Si estado era Finalizada 
				imgresacc.src="../images/iconos/acFallida.gif"; // queda como fallida
				imgresacc.value=ACCION_FALLIDA; 
			}
			else{ // Si estado era Iniciada 
				imgresacc.src="../images/iconos/acConErrores.gif"; // queda con errores
				imgresacc.value=ACCION_CONERRORES
			}
			alert(TbMsg[16])
			return
		}

		// Si se notificó a Exitosa, depende si hay alguna fallida  ...
		if(AlgunaNotificacionFallidas()){
			alert(TbMsg[16])
			return // Existen más fallidas
		}
		// Actulización de la imagen
		if(imgestacc.value==ACCION_FINALIZADA){ // Si estado era Finalizada 
			imgresacc.src="../images/iconos/acExitosa.gif"; // queda como Exitosa
			imgresacc.value=ACCION_EXITOSA; 
		}
		else {// Si estado era Iniciada 
			imgresacc.src="../images/iconos/acSinErrores.gif"; // queda sinerrores
			imgresacc.value=ACCION_SINERRORES;
		}

		alert(TbMsg[16])

	}
//________________________________________________________________________________________________________
	function reiniciar_notificacion(){
		reset_contextual(-1,-1);

		ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		var gestorcolas="";
		switch(currentTipoNotificador){
				case NOTIFICADOR_ORDENADOR :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case NOTIFICADOR_COMANDO :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
				case NOTIFICADOR_TAREA :
					gestorcolas="../gestores/gestor_colasacciones.php";
					break;
		}
		var wurl=gestorcolas+"?opcion="+op_reiniciar_notificacion+"&idaccion="+currentAccion+"&idnotificacion="+currentNotificacion+"&idnotificador="+currentIdNotificador
		ifr.src=wurl; // LLama a la página gestora
	}
//________________________________________________________________________________________________________
	function resultado_reiniciar_notificacion(resul,descrierror,id){
		if (!resul){
				alert(descrierror)
			return
		}
		var oIMGs=currentNotTR.getElementsByTagName('IMG')
		ultimgale=oIMGs.length-1
		oIMGs[ultimgale].src="../images/iconos/reiniciar.gif"
		oIMGs[ultimgale].value=ACCION_REINICIADA

		if(currentTipoNotificador==NOTIFICADOR_ORDENADOR){
			oIMGs[0].src="../images/iconos/ordenadornot.gif"
			var imgordnot=document.getElementById("ORDNOT_"+currentAccion+"_"+currentNotificacion);
			imgordnot.src="../images/iconos/ordenadornot.gif";
		}
		// Cambia imagen resultado de la acción
		var oIMGs=currentTR.getElementsByTagName('IMG')
		ultimgale=oIMGs.length-2
		var imgestacc=oIMGs[ultimgale] // Imagen del estado de la acción
		ultimgale=oIMGs.length-1
		var imgresacc=oIMGs[ultimgale] // Imagen del resultado de la acción

		imgestacc.src="../images/iconos/acIniciada.gif"; // queda como iniciada
		imgestacc.value=ACCION_INICIADA; 

		//  AL eliminar la notificación se consultan las que quedan ...
		if(AlgunaNotificacionFallidas()){
			imgresacc.src="../images/iconos/acConErrores.gif"; // queda conerrores
			imgresacc.value=ACCION_CONERRORES;
		}
		else
			{
			imgresacc.src="../images/iconos/acSinErrores.gif"; // queda sinerrores
			imgresacc.value=ACCION_SINERRORES;
		}

		alert(TbMsg[17])
	}
//________________________________________________________________________________________________________
	function AlgunaNotificacion(){
		var idTR=currentNotTR.getAttribute("id"); // Toma id del TR de notificación
		o=currentNotTR.parentNode
		while (o.tagName!="TBODY"){
			o=o.parentNode
		}
		var oTRs=o.getElementsByTagName('TR') // Toma la colección de TR's
		for(var i=0;i<oTRs.length;i++){
			if(oTRs[i].getAttribute("id")==idTR){ // Si es un TR de la notificación implicada
				var oIMGs=oTRs[i].getElementsByTagName('IMG')
				for(var j=0;j<oIMGs.length;j++){
					var ultimgale=oIMGs.length-1
					var imgresnot=oIMGs[ultimgale] // Imagen del resultado de la notificación
					if(imgresnot.value==ACCION_EXITOSA || imgresnot.value==ACCION_FALLIDA || imgresnot.value==ACCION_REINICIADA ) 
						return(true); // Hay al menos una notificación 
				}
			}
		}
		return(false);
	}
//________________________________________________________________________________________________________
	function AlgunaNotificacionFallidas(){
		var idTR=currentNotTR.getAttribute("id"); // Toma id del TR de notificación
		o=currentNotTR.parentNode
		while (o.tagName!="TBODY"){
			o=o.parentNode
		}
		var oTRs=o.getElementsByTagName('TR') // Toma la colección de TR's
		for(var i=0;i<oTRs.length;i++){
			if(oTRs[i].getAttribute("id")==idTR){ // Si es un TR de la notificación implicada
				var oIMGs=oTRs[i].getElementsByTagName('IMG')
				for(var j=0;j<oIMGs.length;j++){
					var ultimgale=oIMGs.length-1
					var imgresnot=oIMGs[ultimgale] // Imagen del resultado de la notificación
					if(imgresnot.value==ACCION_FALLIDA) 
						return(true); // Hay al menos una notificación con error
				}
			}
		}
		return(false);
	}
//________________________________________________________________________________________________________
	function CambiaImg_Notificaciones(srcimg,vacc){
		o=currentTR;
		while (o.tagName!="TBODY"){
			o=o.parentNode
		}
		var oTRs=o.getElementsByTagName('TR')
		for(var i=0;i<oTRs.length;i++){
			if(oTRs[i].getAttribute("id")=='NOT_'+currentAccion){
				var oIMGs=oTRs[i].getElementsByTagName('IMG')
				var ultimgale=oIMGs.length-1
				if(ultimgale>0){
					var ultimgale=oIMGs.length-1
					oIMGs[ultimgale].src=srcimg
					oIMGs[ultimgale].value=vacc
					if(currentTipoAccion==EJECUCION_COMANDO){
						oIMGs[0].src="../images/iconos/ordenadornot.gif"
						var idnotif=oIMGs[0].name
						var imgordnot=document.getElementById("ORDNOT_"+currentAccion+"_"+idnotif);
						imgordnot.src="../images/iconos/ordenadornot.gif";
					}
				}
			}
		}
	}

//________________________________________________________________________________________________________
	function ver_accion(){
		switch(currentTipoAccion){
				case EJECUCION_COMANDO :
					break;
				case EJECUCION_TAREA :
						document.fdatos.tsk_ambito.value=document.fdatos.ambito.value
						document.fdatos.tsk_idambito.value=document.fdatos.idambito.value
						document.fdatos.tsk_nombreambito.value=document.fdatos.nombreambito.value

						document.fdatos.tsk_fechainicio.value=document.fdatos.fechainicio.value
						document.fdatos.tsk_fechafin.value=document.fdatos.fechafin.value
						document.fdatos.tsk_horainicio.value=document.fdatos.horainicio.value
						document.fdatos.tsk_horafin.value=document.fdatos.horafin.value
						document.fdatos.tsk_tiposacciones.value=document.fdatos.tiposacciones.value
						document.fdatos.tsk_estados.value=document.fdatos.estados.value
						document.fdatos.tsk_resultados.value=document.fdatos.resultados.value
						document.fdatos.tsk_porcendesde.value=document.fdatos.porcendesde.value
						document.fdatos.tsk_porcenhasta.value=document.fdatos.porcenhasta.value

						document.fdatos.tsk_idcmdtskwrk.value=document.fdatos.idcmdtskwrk.value
						document.fdatos.tsk_codtipoaccion.value=document.fdatos.codtipoaccion.value
						document.fdatos.tsk_idambcmdtskwrk.value=document.fdatos.idambcmdtskwrk.value

						document.fdatos.tsk_accionid.value=document.fdatos.accionid.value
						document.fdatos.tsk_idTipoAccion.value=document.fdatos.idTipoAccion.value
						document.fdatos.tsk_TipoAccion.value=document.fdatos.TipoAccion.value
						document.fdatos.tsk_NombreTipoAccion.value=document.fdatos.NombreTipoAccion.value

						marca_accion(EJECUCION_COMANDO,true);
					break;
				case EJECUCION_TRABAJO :
						document.fdatos.wrk_ambito.value=document.fdatos.ambito.value
						document.fdatos.wrk_idambito.value=document.fdatos.idambito.value
						document.fdatos.wrk_nombreambito.value=document.fdatos.nombreambito.value

						document.fdatos.wrk_fechainicio.value=document.fdatos.fechainicio.value
						document.fdatos.wrk_fechafin.value=document.fdatos.fechafin.value
						document.fdatos.wrk_horainicio.value=document.fdatos.horainicio.value
						document.fdatos.wrk_horafin.value=document.fdatos.horafin.value
						document.fdatos.wrk_tiposacciones.value=document.fdatos.tiposacciones.value
						document.fdatos.wrk_estados.value=document.fdatos.estados.value
						document.fdatos.wrk_resultados.value=document.fdatos.resultados.value
						document.fdatos.wrk_porcendesde.value=document.fdatos.porcendesde.value
						document.fdatos.wrk_porcenhasta.value=document.fdatos.porcenhasta.value

						document.fdatos.wrk_idcmdtskwrk.value=document.fdatos.idcmdtskwrk.value
						document.fdatos.wrk_codtipoaccion.value=document.fdatos.codtipoaccion.value
						document.fdatos.wrk_idambcmdtskwrk.value=document.fdatos.idambcmdtskwrk.value

						document.fdatos.wrk_accionid.value=document.fdatos.accionid.value
						document.fdatos.wrk_idTipoAccion.value=document.fdatos.idTipoAccion.value
						document.fdatos.wrk_TipoAccion.value=document.fdatos.TipoAccion.value
						document.fdatos.wrk_NombreTipoAccion.value=document.fdatos.NombreTipoAccion.value

						marca_accion(EJECUCION_TAREA,true);
					break;
		}
		document.fdatos.accionid.value=currentAccion
		document.fdatos.idTipoAccion.value=currentidTipoAccion
		document.fdatos.TipoAccion.value=currentTipoAccion
		document.fdatos.NombreTipoAccion.value=currentNombreTipoAccion

		marca_estado(ACCION_DETENIDA,true);
		marca_estado(ACCION_INICIADA,true);
		marca_estado(ACCION_FINALIZADA,true);
	
		marca_resultado(ACCION_EXITOSA,true);
		marca_resultado(ACCION_FALLIDA,true);
		marca_resultado(ACCION_TERMINADA,false);
		marca_resultado(ACCION_ABORTADA,false);
		marca_resultado(ACCION_SINERRORES,true);
		marca_resultado(ACCION_CONERRORES,true);
		document.fdatos.submit()
	}
//________________________________________________________________________________________________________
	function ver_accionpadre(tipoaccion){
				switch(tipoaccion){
				case EJECUCION_COMANDO :
					break;
				case EJECUCION_TAREA :
						document.fdatos.ambito.value=document.fdatos.tsk_ambito.value
						document.fdatos.idambito.value=document.fdatos.tsk_idambito.value
						document.fdatos.nombreambito.value=document.fdatos.tsk_nombreambito.value

						document.fdatos.fechainicio.value=document.fdatos.tsk_fechainicio.value
						document.fdatos.fechafin.value=document.fdatos.tsk_fechafin.value
						document.fdatos.horainicio.value=document.fdatos.tsk_horainicio.value
						document.fdatos.horafin.value=document.fdatos.tsk_horafin.value
						document.fdatos.tiposacciones.value=document.fdatos.tsk_tiposacciones.value
						document.fdatos.estados.value=document.fdatos.tsk_estados.value
						document.fdatos.resultados.value=document.fdatos.tsk_resultados.value
						document.fdatos.porcendesde.value=document.fdatos.tsk_porcendesde.value
						document.fdatos.porcenhasta.value=document.fdatos.tsk_porcenhasta.value

						document.fdatos.idcmdtskwrk.value=document.fdatos.tsk_idcmdtskwrk.value
						document.fdatos.codtipoaccion.value=document.fdatos.tsk_codtipoaccion.value
						document.fdatos.idambcmdtskwrk.value=document.fdatos.tsk_idambcmdtskwrk.value

						document.fdatos.accionid.value=document.fdatos.tsk_accionid.value
						document.fdatos.idTipoAccion.value=document.fdatos.tsk_idTipoAccion.value
						document.fdatos.TipoAccion.value=document.fdatos.tsk_TipoAccion.value
						document.fdatos.NombreTipoAccion.value=document.fdatos.tsk_NombreTipoAccion.value
					break;
				case EJECUCION_TRABAJO :
						document.fdatos.ambito.value=document.fdatos.wrk_ambito.value
						document.fdatos.idambito.value=document.fdatos.wrk_idambito.value
						document.fdatos.nombreambito.value=document.fdatos.wrk_nombreambito.value

						document.fdatos.fechainicio.value=document.fdatos.wrk_fechainicio.value
						document.fdatos.fechafin.value=document.fdatos.wrk_fechafin.value
						document.fdatos.horainicio.value=document.fdatos.wrk_horainicio.value
						document.fdatos.horafin.value=document.fdatos.wrk_horafin.value
						document.fdatos.tiposacciones.value=document.fdatos.wrk_tiposacciones.value
						document.fdatos.estados.value=document.fdatos.wrk_estados.value
						document.fdatos.resultados.value=document.fdatos.wrk_resultados.value
						document.fdatos.porcendesde.value=document.fdatos.wrk_porcendesde.value
						document.fdatos.porcenhasta.value=document.fdatos.wrk_porcenhasta.value

						document.fdatos.idcmdtskwrk.value=document.fdatos.wrk_idcmdtskwrk.value
						document.fdatos.codtipoaccion.value=document.fdatos.wrk_codtipoaccion.value
						document.fdatos.idambcmdtskwrk.value=document.fdatos.wrk_idambcmdtskwrk.value

						document.fdatos.accionid.value=document.fdatos.wrk_accionid.value
						document.fdatos.idTipoAccion.value=document.fdatos.wrk_idTipoAccion.value
						document.fdatos.TipoAccion.value=document.fdatos.wrk_TipoAccion.value
						document.fdatos.NombreTipoAccion.value=document.fdatos.wrk_NombreTipoAccion.value
					break;
		}
		document.fdatos.submit()

	}
//________________________________________________________________________________________________________
	function reset_seleccion(){
		if(currentTR!=null){
			var oTDs=currentTR.getElementsByTagName('TD')
			for(var i=0;i<oTDs.length;i++){
				oTDs[i].style.backgroundColor="#EEEECC" 
				oTDs[i].style.color="#003300" 
			}
			currentTR=null;
		}
		if(currentNotTR!=null){
			var oTDs=currentNotTR.getElementsByTagName('TD')
			oTDs[0].style.backgroundColor="#EEEECC" 
			for(var i=1;i<oTDs.length;i++){
				oTDs[i].style.backgroundColor="#E3D8C6" 
				oTDs[i].style.color="#003300" 
			}
			currentNotTR=null;
		}
	}
//________________________________________________________________________________________________________
// Captura la triada idaccion,estado,resultado para lactualizaciones de operaciones de acciones multiples
//________________________________________________________________________________________________________
	function toma_triada(oTriada,wmulaccion){
		patron=";"+currentAccion+":";
		var re = new RegExp (";"+currentAccion+":", 'gi') ;
		var pos=wmulaccion.search(re)
		if(pos<0){ // Comprueba si está el primero
			patron=currentAccion+":";
			var re = new RegExp (";"+currentAccion+":", 'gi') ;
			var pos=wmulaccion.search(re)
			if(pos>0) pos=-1; // No  está el primero , asi que no está
		}
		if(pos<0) oTriada.swexst=false; // No  está el primero , asi que no está
		pos++;
		var posa=pos;
		while(pos<wmulaccion.length){
			if(wmulaccion.charAt(pos)==";") break;
			pos++;
		}
		var posb=pos;
		var triada=wmulaccion.substr(posa,posb-posa)
		var auxsplit=triada.split(":");
		oTriada.posini=posa
		oTriada.posifi=posb
		oTriada.idaccion=auxsplit[0];
		oTriada.estado=auxsplit[1];
		oTriada.resultado=auxsplit[2];
		oTriada.swexst=true;
	}
//________________________________________________________________________________________________________
	function actualiza_triadas(oTriada,wmulaccion){
		var nwtriada=oTriada.idaccion+":"+oTriada.estado+":"+oTriada.resultado
		var lon=wmulaccion.length;
		var string1=wmulaccion.substr(0,mitriada.posini) // Primera parte de la cadena
		var string2=wmulaccion.substr(mitriada.posifi,lon) // Primera parte de la cadena
		var oMulaccion=document.getElementById("mulaccion");// Toma los identificadores de todas las acciones
		oMulaccion.value=string1+nwtriada+string2;
	}
//________________________________________________________________________________________________________
// definicion dela clase triada
//________________________________________________________________________________________________________
	function TRIADA(){
		this.posini=0;
		this.posifi=0;
		this.idaccion;
		this.estado;
		this.resultado;
		this.swexst;
	}