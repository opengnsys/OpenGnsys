// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2006
// Nombre del fichero: comunes.js
// Descripción : 
//		Este fichero implementa funciones de uso comn a varios fichechos
// *************************************************************************************************************************************************
var corte_currentNodo			// Copia del Nodo actual para mover y colocar
var currentTipo=null;				// Tipo de Nodo
//____________________________________________________________________________
//
//	Recupera el navegador utilizado
//____________________________________________________________________________
var IE=(navigator.appName=="Microsoft Internet Explorer");
var NS=(navigator.appName=="Netscape");
//document.body.addEventListener("contextmenu",killClick,true);
//document.oncontextmenu=function(){	return(false);}
//window.oncontextmenu = function () { alert("pepe"); }

//________________________________________________________________________________________________________
//	
//	Inserta un nuevo grupo 
//________________________________________________________________________________________________________
function insertar_grupos(tipo,literaltipo,swi,idu){
	reset_contextual(-1,-1) // Oculta menu contextual
	var identificador=currentNodo.toma_identificador();
	if(swi!=null && swi==1) identificador=0;
	if(identificador==null) identificador=0

	if(literaltipo==LITAMBITO_AULAS) // Nuevo grupo de ordenador hijo de un aula
		var wurl="../propiedades/propiedades_grupos.php?opcion="+op_alta+"&grupoid=0"+"&idaula="+identificador+"&tipo="+AMBITO_GRUPOSORDENADORES	+"&literaltipo="+LITAMBITO_GRUPOSORDENADORES;
	else
		if(literaltipo==LITAMBITO_GRUPOSORDENADORES) // Nuevo grupo de ordenador hijo de un grupo  de ordenadores
			var wurl="../propiedades/propiedades_grupos.php?opcion="+op_alta+"&grupoid="+identificador+"&idaula=0"+"&tipo="+AMBITO_GRUPOSORDENADORES	+"&literaltipo="+LITAMBITO_GRUPOSORDENADORES;
		else
			var wurl="../propiedades/propiedades_grupos.php?opcion="+op_alta+"&grupoid="+identificador+"&tipo="+tipo	+"&literaltipo="+literaltipo
	if(idu!=null && idu==1) wurl+="&iduniversidad="+idu
	window.open(wurl,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de insertar un nuevo grupo
// Par�etros:
//			- resul: resultado de la operaci�(true si tuvo �ito)
//			- descrierror: Descripción del error en su caso
//			- nwid: Identificador asignado al nuevo registro
//			- tablanodo: Tabla nodo generada para el nuevo registro (árbol de un s�o un elemento)
//________________________________________________________________________________________________________
function resultado_insertar_grupos(resul,descrierror,nwid,tablanodo){
	reset_contextual(-1,-1) // Oculta menu contextual
	if (!resul){ // Ha habido algn error en la inserci�
		alert(descrierror)
		return
	}
	InsertaNodo(currentNodo,tablanodo);
}
//________________________________________________________________________________________________________
//	
//	Modifica el nombre de un grupo
//________________________________________________________________________________________________________
function modificar_grupos(){
	reset_contextual(-1,-1) // Oculta menu contextual
	var identificador=currentNodo.toma_identificador()
	var literaltipo=currentNodo.toma_sufijo()
	wurl="../propiedades/propiedades_grupos.php?opcion="+op_modificacion+"&idgrupo="+identificador+"&literaltipo="+literaltipo
	window.open(wurl,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de modificar el nombre de un grupo de aulas
//	Par�etros:
//			- resul: resultado de la operaci� ( true si tuvo �ito)
//			- descrierror: Descripción del error en su caso
//			- lit: Nuevo nombre del grupo
//________________________________________________________________________________________________________
function resultado_modificar_grupos(resul,descrierror,lit){
	if (!resul){
		alert(descrierror);
		return;
	}
	currentNodo.pone_infonodo(lit);
	alert(CTbMsg[2]);
}
//________________________________________________________________________________________________________
//	
//	Elimina un grupo
//________________________________________________________________________________________________________
function eliminar_grupos(){
	reset_contextual(-1,-1) // Oculta menu contextual
	if (currentNodo.TieneHijos()){
		var resul=window.confirm(CTbMsg[0]);
		if (!resul)return;
	}
	var identificador=currentNodo.toma_identificador();
	var literaltipo=currentNodo.toma_sufijo();
	wurl="../propiedades/propiedades_grupos.php?opcion="+op_eliminacion+"&idgrupo="+identificador+"&literaltipo="+literaltipo;
	window.open(wurl,"frame_contenidos");
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de eliminar un grupo
//	Par�etros:
//			- resul: resultado de la operaci� ( true si tuvo �ito)
//			- descrierror: Descripción del error en su caso
//			- id: Identificador del registro
//________________________________________________________________________________________________________
function resultado_eliminar_grupos(resul,descrierror,id){
	if (!resul){
		alert(descrierror);
		return
	}
	var nvp=currentNodo.PapaNodo();
	var ncel=nvp.CeldaVista;
	EliminaNodo(currentNodo) // Elimina el nodo del árbol
	var nwcurrentNodo=TomaDatosNodo(ncel);
	resalta(nwcurrentNodo);
	alert(CTbMsg[3]);
}
//________________________________________________________________________________________________________
//	
//	Muestra el formulario de captura de datos para insertar
//________________________________________________________________________________________________________
function insertar(l,t,w,h,pages,swi,idu){
	reset_contextual(-1,-1) // Oculta menu contextual
	var identificador=currentNodo.toma_identificador()
	var literaltipo=currentNodo.toma_sufijo()
	if(swi!=null && swi==1) identificador=0; // Nodos directos (sin pertenencia a grupo)
	if(identificador==null) identificador=0;

	if(literaltipo==LITAMBITO_AULAS){ // Nuevo grupo de ordenador hijo de un aula
		if(	pages=="../propiedades/propiedades_ordenadores.php")
			var whref="../propiedades/propiedades_ordenadores.php?opcion="+op_alta+"&grupoid=0"+"&idaula="+identificador;
		else
			var whref=pages+"&opcion="+op_alta+"&idambito="+identificador;;
	}
	else{
		if(literaltipo==LITAMBITO_GRUPOSORDENADORES) // Nuevo grupo de ordendor hijo de un grupo  de ordenadores
			var whref="../propiedades/propiedades_ordenadores.php?opcion="+op_alta+"&grupoid="+identificador+"&idaula=0"
		else{
			var auxsplit= pages.split('?'); // La variable pages lleva parametros
			if(auxsplit[1]!=null)
				var whref=pages+"&";
			else
				var whref=pages+"?";
			whref+="opcion="+op_alta+"&grupoid="+identificador;
		}
	}
	if(idu!=null){
		switch(idu){
			case 1:
				whref+="&iduniversidad="+idu;
				break;
			case 2:
				whref+="&identidad="+identificador;

				break;
			case 3:
				whref+="&idambito="+identificador;
				break;
		}
	}
	window.open(whref,"frame_contenidos");
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de insertar datos
//	Par�etros:
//			- resul: resultado de la operaci�(true si tuvo �ito)
//			- descrierror: Descripción del error en su caso
//			- nwid: Identificador asignado al nuevo registro
//			- tablanodo: Tabla nodo generada para el nuevo registro (árbol de un s�o un elemento)
//________________________________________________________________________________________________________
function resultado_insertar(resul,descrierror,nwid,tablanodo){
	if (!resul){
		alert(descrierror);
	}
	InsertaNodo(currentNodo,tablanodo);
	alert(CTbMsg[4]);
}
//________________________________________________________________________________________________________
//	
//	Muestra el formulario de captura de datos para modificaci�
//________________________________________________________________________________________________________
function modificar(l,t,w,h,pages){
	reset_contextual(-1,-1) // Oculta menu contextual
	var identificador=currentNodo.toma_identificador()
	if (!identificador) identificador=0;
	var whref=pages+"?opcion="+op_modificacion+"&identificador="+identificador;
	window.open(whref,"frame_contenidos");
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de modificar datos 
//	Par�etros:
//			- resul: resultado de la operaci� ( true si tuvo �ito)
//			- descrierror: Descripción del error en su caso
//			- lit: Nuevo nombre del grupo
//________________________________________________________________________________________________________
function resultado_modificar(resul,descrierror,lit){
	if (!resul){
		alert(descrierror);
		return;
	}
	currentNodo.pone_infonodo(lit);
	alert(CTbMsg[5]);
}
//________________________________________________________________________________________________________
//	
//	Muestra el formulario de captura de datos para eliminaci�
//________________________________________________________________________________________________________
function eliminar(l,t,w,h,pages){
	reset_contextual(-1,-1) // Oculta menu contextual
	var identificador=currentNodo.toma_identificador();
	var whref=pages+"?opcion="+op_eliminacion+"&identificador="+identificador;
	window.open(whref,"frame_contenidos");
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de eliminar un grupo
//	Par�etros:
//			- resul: resultado de la operaci� ( true si tuvo �ito)
//			- descrierror: Descripción del error en su caso
//			- id: Identificador del registro
//________________________________________________________________________________________________________
function resultado_eliminar(resul,descrierror,id){
	if (!resul){
		alert(descrierror);
		return
	}
	var nvp=currentNodo.PapaNodo();
	var ncel=nvp.CeldaVista;
	EliminaNodo(currentNodo) // Elimina el nodo del árbol
	var nwcurrentNodo=TomaDatosNodo(ncel);
	resalta(nwcurrentNodo);
	alert(CTbMsg[6]);
}
//________________________________________________________________________________________________________
//	
//		Copia al buffer un nodo para moverlo posteriormente
//________________________________________________________________________________________________________
function mover(tipo){
	reset_contextual(-1,-1)
	corte_currentNodo=currentNodo
	currentTipo=tipo
}
//________________________________________________________________________________________________________
//	
//	Mueve de sitio un nodo desde un grupo a otro o a la raiz
//________________________________________________________________________________________________________
function colocar(pages,tipo){
	reset_contextual(-1,-1);
	if (!corte_currentNodo || tipo!=currentTipo) {
		alert(CTbMsg[7]);
		corte_currentNodo=null
		currentTipo=null;
		return
	}
	var identificadorgrupo=currentNodo.toma_identificador();
	if (!identificadorgrupo) identificadorgrupo=0
	var identificador=corte_currentNodo.toma_identificador();
	if (!identificador) identificador=0 // Se trata de la raiz
	ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
	var wurl=pages+"?opcion="+op_movida+"&grupoid="+identificadorgrupo+"&identificador="+identificador
	ifr.src=wurl; // LLama a la p�ina gestora
}
//________________________________________________________________________________________________________
//	
//	Devuelve el resultado de cambiar un nodo de sitio
//	Par�etros:
//			- resul: resultado de la operaci�( true si tuvo �ito)
//			- descrierror: Descripción del error en su caso
//			- id: Identificador del registro
//________________________________________________________________________________________________________
function resultado_mover(resul,descrierror,id){
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
//	Esta funci� muestra un menu contextual 
//		Los par�etros recibidos son:
//			- o: Objeto TD literal del nodo
//			- idmnctx: Identificador del DIV que contiene el menu contextual
//________________________________________________________________________________________________________
function menu_contextual(o,idmnctx){
	var menuctx=document.getElementById(idmnctx); // Toma objeto DIV
	if(o!=null)
		clickLiteralNodo(o);
	muestra_contextual(ClickX,ClickY,menuctx) // muestra menu
}
