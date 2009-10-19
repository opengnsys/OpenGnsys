// ************************************************************************************************
//  Libreria de scripts de Javascript
// Copyright 2003-2005 José Manuel Alonso. Todos los derechos reservados.
// Fichero: ArbolVistaXML.js
// Este fichero implementa las funciones javascript de la clase ArbolVistaXML.php
// *************************************************************************************************
var botonraton=null;					//  Botón presionado
var currentNodo="";					// Nodo actual
var currentpathimg="";				//	Path por defecto de las imágenes de signo
var gLiteralcolor;						// Color nodo vista para restablecer
var gLiteralbackground;			// Fondo color nodo vista para restablecer
//________________________________________________________________________________________________________
// definicion dela clase triada
//________________________________________________________________________________________________________
	function NodoVista(){
		this.nivel=0;									// Profundidad del nodo
		this.imgsigno=null;					// Objeto IMG (Imagen de signo de la celda vista) O SPAN si el nodo vista no tiene hijos
		this.literal=null; 							// Objeto SPAN (Literal de la celda vista)

		this.CeldaVista=null;					// El objeto TABLE que contiene la imagen de signo y el literal)
		this.Nodo=null;							// El nodo en si (Objeto TR que contiene el objeto TABLE celda vista final)
		this.ArbolHijos=null;					// El arbol conteniendo todos los nodos hijos ( Si nulo no tiene hijos)(Objeto TR)
		this.NodoPadre=null;				// El nodo padre (Objeto TR que contiene el objeto TABLE celda vista final, del padre)
		this.ArbolHijosNodoPadre=null;		// El arbol del padre conteniendo los nodos hijos(Objeto TR)
		this.siguienteHermano=null;	// El nodo hermano siguiente ( Si nulo es el último hermano)(Objeto TR)
		//____________________________________________________________________________
		//	
		//	Devuelve el estado del nodo 0: Contraido 1:Desplegado
		//____________________________________________________________________________
		this.estadoNodo= function(){
			var signoimg=this.imgsigno.getAttribute("value");
			var estado;
			switch(signoimg){
				case "menos_t" :
				case "menos_c" :
					estado=1;
					break;
				case "mas_t" :
				case "mas_c" :
					estado=0;
					break;
				default:
					estado=-1;
			}
			return(estado);
		}
		//____________________________________________________________________________
		//	
		//	Devuelve el segundo dato de una cadena con formato xxxxx-xxx que es id del nodo vista
		//____________________________________________________________________________
		this.toma_identificador= function(){
			if(this.literal==null) return(null);
			var cadena=this.literal.getAttribute("id");
			var iditem=cadena.split("-") // Toma el identificador 
			return(iditem[1]);
		}
		//____________________________________________________________________________
		//	
		//	Devuelve el primer dato de una cadena con formato xxxxx-xxx que es sufijo del nodo vista
		//____________________________________________________________________________
		this.toma_sufijo= function(){
			if(this.literal==null) return(null);
			var cadena=this.literal.getAttribute("id");
			var iditem=cadena.split("-") // Toma el identificador 
			return(iditem[0]);
		}
		//____________________________________________________________________________
		//	
		//	Devuelve el literal de un nodo vista
		//____________________________________________________________________________
		this.toma_infonodo= function(){
			if(this.literal==null) return(null);
			return(this.literal.innerHTML);
		}
		//____________________________________________________________________________
		//	
		//	Devuelve el literal de un nodo vista
		//____________________________________________________________________________
		this.pone_infonodo= function(lit){
			this.literal.innerHTML=lit;
		}		
		//____________________________________________________________________________
		//	
		//	 Devuelve true si el nodo tiene hijos,false en caso contrario
		//____________________________________________________________________________
		this.TieneHijos= function(){
			return(this.ArbolHijos!=null);
		}
		//____________________________________________________________________________
		//	
		//	 Devuelve true si el nodo es el último, false en caso contrario
		//____________________________________________________________________________
		this.UltimoHermano= function(){
					return(this.siguienteHermano==null);
		}
		//____________________________________________________________________________
		//	
		//	 Devuelve el nodo vista padre
		//____________________________________________________________________________
		this.PapaNodo= function(){
			if(this.NodoPadre==null) return(null);
			var oTABLE=this.NodoPadre.getElementsByTagName('TABLE')[0];
			return(TomaDatosNodo(oTABLE));
		}
		// Fin de la clase
}
//________________________________________________________________________________________________________
//	
//	Devuelve un nodo vista
//  Parametro:
//		o: Objeto que puede ser la imagen de signo o el literal de una de las lineas del arbolVista
//________________________________________________________________________________________________________
	function TomaDatosNodo(o){
		var nodo=new NodoVista();

		while(o.tagName!="TABLE" )
			o=o.parentNode;
		nodo.CeldaVista=o;	
		var TAnchor=nodo.CeldaVista.getElementsByTagName('A');
		if(TAnchor.length==2){ // Imagen de signo pulsable
			nodo.imgsigno=TAnchor[0].childNodes[0];
			nodo.literal=TAnchor[1].childNodes[0];
		}
		else{
			var TSpan=nodo.CeldaVista.getElementsByTagName('SPAN');
			nodo.imgsigno=TSpan[0].childNodes[0];
			nodo.literal=TAnchor[0].childNodes[0];
		}
       	while(o.tagName!="TR" )
			 o=o.parentNode;   
		nodo.Nodo=o;
		
		while(o.tagName!="TABLE" )
			o=o.parentNode;
		var Mnivel=o.getAttribute("id").split("-") 
		nodo.nivel=Mnivel[1]

       	while(o.tagName!="TR" )
			 o=o.parentNode;   
		nodo.ArbolHijosNodoPadre=o;
		
		if(parseInt(nodo.nivel)>0){
			o=o.previousSibling;
			while(o.nodeType!=1 )
				o=o.previousSibling
		   	nodo.NodoPadre=o;
		}
		else
			nodo.NodoPadre=null; // Es el primer nodo
		var o=nodo.Nodo; 
		var auxsplit=o.getAttribute("id");
		var idTR=auxsplit.split("-") [0];		
        o=o.nextSibling
        while(o!=null && o.nodeType!=1 )
           o=o.nextSibling
		if(o==null){ // Es el último hermano y no tiene hijos
			nodo.ArbolHijos=null;	
			nodo.siguienteHermano=null;
			return(nodo);
		}
		var auxsplit=o.getAttribute("id");
		var idTRhijo=auxsplit.split("-") [0];
		if(idTR==idTRhijo) { // El nodo no tiene hiijos y no es último hermano
			nodo.ArbolHijos=null;	
			nodo.siguienteHermano=o; 
			return(nodo);
		}
		nodo.ArbolHijos=o;	
		o=o.nextSibling
        while(o!=null && o.nodeType!=1)
           o=o.nextSibling
		if(o==null){ // El nodo  tiene hijos y  es ultimo hermano
			nodo.siguienteHermano=null;
			return(nodo);
		}
		nodo.siguienteHermano=o; // El nodo  tiene hijos y  no es último hermano
		return(nodo);
	}
//-----------------------------------------------------------------------------------------------------------------------
//	 Gestiona el despliegue y contracción de nodovs
//-----------------------------------------------------------------------------------------------------------------------
function clickNodo(nodov,pathimg){
        var signoimg=nodov.imgsigno.getAttribute("value");
		switch(signoimg){
			case "menos_t" :
				nodov.imgsigno.setAttribute("value","mas_t",null);
				nodov.imgsigno.setAttribute("src",pathimg+"/mas_t.gif",null);
				nodov.ArbolHijos.style.display="none"
				break;
			case "menos_c" :
				nodov.imgsigno.setAttribute("value","mas_c",null);
				nodov.imgsigno.setAttribute("src",pathimg+"/mas_c.gif",null);
				if (nodov.nivel==0)
				    nodov.imgsigno.setAttribute("src",pathimg+"/mas_root.gif",null);
				nodov.ArbolHijos.style.display="none"
				break;
			case "mas_t" :
				nodov.imgsigno.setAttribute("value","menos_t",null);
				nodov.imgsigno.setAttribute("src",pathimg+"/menos_t.gif",null);
				nodov.ArbolHijos.style.display="block"
				break;
			case "mas_c" :
                nodov.imgsigno.setAttribute("value","menos_c",null);
				nodov.imgsigno.setAttribute("src",pathimg+"/menos_c.gif",null);
				if (nodov.nivel==0)
				    nodov.imgsigno.setAttribute("src",pathimg+"/menos_root.gif",null);
				nodov.ArbolHijos.style.display="block"
				break;
		}
}
//-----------------------------------------------------------------------------------------------------------------------
//	 Gestiona el despliegue y contracción de nodos a través de la imagen del nodo
//-----------------------------------------------------------------------------------------------------------------------
function clickImagenSigno(oIMG,pathimg){
	currentpathimg=pathimg;
	var nodov=TomaDatosNodo(oIMG);
	clickNodo(nodov,pathimg);
	if (EsAncestro(nodov,currentNodo)) 
		resalta(nodov);
}
//-----------------------------------------------------------------------------------------------------------------------
//	 Gestiona el despliegue y contracción de nodos a través del literal del nodo
//-----------------------------------------------------------------------------------------------------------------------
function clickLiteralNodo(oLIT,pathimg){
	var nodov=TomaDatosNodo(oLIT);
	resalta(nodov);
	if(nodov.imgsigno==null) return;

	if(pathimg==null){
		var signoimg=nodov.imgsigno.getAttribute("src");
		var p=signoimg.lastIndexOf("/");
		var pathimg=signoimg.substring(0,p);
		currentpathimg=pathimg;
	}
	var signoimg=nodov.imgsigno.getAttribute("value");
	var signo=signoimg.split("_")	
	if(botonraton==1){
			if (signo[0]=="mas"  || 	signo[0]=="menos" ) clickNodo(nodov,pathimg);
	}
	else{
			if (signo[0]=="mas" ) clickNodo(nodov,pathimg);
	}
}
//-----------------------------------------------------------------------------------------------------------------------
//	 Resalta el nodo vista seleccionado y lo pone como nodo vista actual
//-----------------------------------------------------------------------------------------------------------------------
function resalta(nodov){
	if(currentNodo==nodov) return;
	if (currentNodo){
		currentNodo.literal.style.color=gLiteralcolor;
		currentNodo.literal.style.backgroundColor=gLiteralbackground;
	}
	gLiteralcolor=nodov.literal.style.color; // Guarda el color del nodo 
	gLiteralbackground=nodov.literal.style.backgroundColor; // Guarda el background del nodo 	
	
	nodov.literal.style.color="#FFFFFF"; // Blanco
	nodov.literal.style.backgroundColor="#0a266a"; // Azul marino
	currentNodo=nodov;
}
//-----------------------------------------------------------------------------------------------------------------------
//	 Deja de resaltar un nodo vista
//____________________________________________________________________________
function desresalta(nodov){
	nodov.literal.style.color=nodov.Literalcolor;
	nodov.literal.style.backgroundColor=nodov.Literalbackground;
}
//-----------------------------------------------------------------------------------------------------------------------
//	 Averigua si el primer nodo vista es ancestro del segundo
//____________________________________________________________________________
function EsAncestro(nodoA,nodoH){
	if(nodoH==null) return(false); 
	var NodoAncestro=nodoA.ArbolHijos;
	var NodoHijo=nodoH.Nodo;
	while(NodoHijo!=null){
		if(NodoHijo==NodoAncestro) return(true);
		NodoHijo=NodoHijo.parentNode;
	}
	return(false);
}
//-----------------------------------------------------------------------------------------------------------------------
// Despliega un nivel el nodo indicado
//-----------------------------------------------------------------------------------------------------------------------
function despliega(o,pathimg){
	var nodov=TomaDatosNodo(o);
	clickNodo(nodov,pathimg);
}
//-----------------------------------------------------------------------------------------------------------------------
// Despliega el nodo indicado ( desde la pagina
//-----------------------------------------------------------------------------------------------------------------------
function DespliegaNodo(lit,id){
	var o=document.getElementById(lit+"-"+id);
	if(o!=null){
		var ancestro=	TomaDatosNodo(o);
		resalta(ancestro);
		while(ancestro!=null){
			if(ancestro.estadoNodo()==0) // Nodo contraido
				clickNodo(ancestro,currentpathimg);
			 ancestro=ancestro.PapaNodo();
		}
	}
}
//____________________________________________________________________________
//	
//	Inserta un nodo en el árbol
// Especificaciones:
//		Los parámetros recibidos son:
//			- nodov: Nodo vista
//			- tablanodo: Tabla nodo generada para la nueva celda vista
//____________________________________________________________________________
function InsertaNodo(nodov,tablanodo){
		var nwceldavista=CreaCeldaVista(nodov,tablanodo);
		var nwTR = document.createElement('TR');
		nwTR.id="TRNodo-0";
		var nwTD = document.createElement('TD');
		nwTD.innerHTML=nwceldavista;
		nwTR.appendChild(nwTD); 

		if(!nodov.TieneHijos()){
			CreaNodoHijo(nodov);
			if(parseInt(nodov.nivel)==0){ // Nodo raiz
                nodov.imgsigno.setAttribute("value","menos_c",null);
			    nodov.imgsigno.setAttribute("src",currentpathimg+"/menos_root.gif",null);
			}
			else{
				if(nodov.UltimoHermano()){
					nodov.imgsigno.setAttribute("value","menos_c",null);
					nodov.imgsigno.setAttribute("src",currentpathimg+"/menos_c.gif",null);
				}
				else{
					nodov.imgsigno.setAttribute("value","menos_t",null);
					nodov.imgsigno.setAttribute("src",currentpathimg+"/menos_t.gif",null);
				}
			}
			var ATTonclick='clickImagenSigno(this,' + "'"+currentpathimg+"'"+','+nodov.nivel+');'; 
			nodov.imgsigno.setAttribute("onclick",ATTonclick,null);
			nodov.imgsigno.setAttribute("border","0",null);
			var oSPAN=nodov.imgsigno.parentNode;
			var htmlIMG=oSPAN.innerHTML;
			TDpadre=oSPAN.parentNode;
			TDpadre.innerHTML='<A href="#">'+htmlIMG+'</A>';	
		}
		var pivoteNodo=nodov.ArbolHijos;
		var nodoTD = pivoteNodo.childNodes[0];;
		var nodoTABLE=nodoTD.childNodes[0];
		var nodoTBODY=nodoTABLE.childNodes[0];
		var nodoTR=nodoTBODY.childNodes[0];
		if(nodoTR!=null)
			nodoTBODY.insertBefore(nwTR,nodoTR);
		else
			nodoTBODY.appendChild(nwTR);
}
//____________________________________________________________________________
//	
//	Monta y devuelve el código HTML de la estructura de una celda vista
//		Los parámetros recibidos son:
//			- pivoteNodo: Nodo vista
//			- tablanodo: Tabla nodo generada para la nueva celda vista
//____________________________________________________________________________
function CreaCeldaVista(nodov,tablanodo){		
		var nodoTD = document.createElement('TD');
		nodoTD.innerHTML=tablanodo;
		var nodoTABLE=nodoTD.childNodes[0];
		var nodoTBODY=nodoTABLE.childNodes[0];
		var nodoTBODYTR=nodoTBODY.childNodes[0];
		var oIMG=nodoTBODYTR.getElementsByTagName('IMG')[0];
		var HTMLinner=nodoTBODYTR.innerHTML;
		
		if(nodov.TieneHijos()){
			var  patron = new RegExp("nada_c","gi") 
			HTMLinner=HTMLinner.replace(patron,"nada_t"); 
		}		
		else{
			var  patron = new RegExp("nada_t","gi") 
			HTMLinner=HTMLinner.replace(patron,"nada_c"); 
		}
		var auxnodo=nodov;
		var nwHTMLinner="";
		var img="";
		while(auxnodo!=null){
			(auxnodo.UltimoHermano())? img="nada.gif" :  img="nada_l.gif"; 
			nwHTMLinner='<TD width="3px"></TD><TD  width="16px"><IMG src="../images/signos/'+img+'" width="16px" height="16px" ></TD>'+nwHTMLinner;
			auxnodo=auxnodo.PapaNodo();
		}
		nwHTMLinner='<TABLE  border=0 cellspacing=0 cellpadding=0><TR height="16px">'+nwHTMLinner+HTMLinner+"</TR></TABLE>"; // Contenido de la tabla del nodo literal
		return(nwHTMLinner);
}		
//____________________________________________________________________________
//	
//	 Crea un nuevo nodo Hijo (objeto TABLE)
//	 Parámetros:
//			- nodov: Un nodo vista
//____________________________________________________________________________
function CreaNodoHijo(nodov){
	var nivel=parseInt(nodov.nivel)+1;
	var nTR=document.createElement('TR');
	nTR.id="TRNodoHijo-0";
	var nTD=document.createElement('TD');
	nTD.innerHTML='<TABLE with="100%"id="tablanivel-'+nivel+'" border="0" cellspacing="0" cellpadding="0"><TBODY></TBODY></TABLE>';
	nTR.appendChild(nTD); 
	
	var pivoteNodo=nodov.Nodo.parentNode;
	if(nodov.UltimoHermano()){
		pivoteNodo.appendChild(nTR); // Para insertar al final
		}
	else{
		pivoteNodo.insertBefore(nTR,nodov.siguienteHermano)
	}
	nodov.ArbolHijos=nTR;	
}
//____________________________________________________________________________
//	
//	Inserta un nodo en el árbol
// Especificaciones:
//		Los parámetros recibidos son:
//			- nodov: Nodo vista
//____________________________________________________________________________
function EliminaNodo(nodov){
	var swuh=nodov.UltimoHermano();
	var pn=nodov.Nodo.parentNode; // Nodo padre
	var papa=nodov.PapaNodo(); // Nodo vista padre

	if(nodov.TieneHijos())
		pn.removeChild(nodov.ArbolHijos); // Elimina arbol hijo
	pn.removeChild(nodov.Nodo);	// Elimina Nodo

	var antHermano=pn.lastChild
	if(antHermano==null){ // El nodo padre no tiene más hijos
			var pn=papa.ArbolHijos.parentNode; // Nodo padre
			pn.removeChild(papa.ArbolHijos); // Elimina arbol hijo
			ChgSignoPadreEliminaNodo(papa.imgsigno); 
	}
	else{
		if(swuh){ // Si era el último hermano ...
			var auxsplit=antHermano.getAttribute("id");
			var idTR=auxsplit.split("-") [0];
			if(idTR=="TRNodoHijo"){
				antHermano=antHermano.previousSibling;
				while(antHermano.nodeType!=1 )
					antHermano=antHermano.previousSibling
				var TAnchor=antHermano.getElementsByTagName('A');
				if(TAnchor.length==2) // Imagen de signo pulsable
					var oIMG=TAnchor[0].childNodes[0];
			}
			else{
				var TSpan=antHermano.getElementsByTagName('SPAN');
				var oIMG=TSpan[0].childNodes[0];
			}	
			var nh=TomaDatosNodo(oIMG);
			ChgSignoEliminaNodo(oIMG);
			if(nh.TieneHijos())
				ChgSignoNivel(nh.ArbolHijos,nh.nivel);
		}
	}
}
//-----------------------------------------------------------------------------------------------------------------------
//	 Cambia la imagen de signo del hermano anterior de un nodo eliminado
//-----------------------------------------------------------------------------------------------------------------------
function ChgSignoEliminaNodo(imgsigno){
        var signoimg=imgsigno.getAttribute("value");
		switch(signoimg){
			case "menos_t" :
				imgsigno.setAttribute("value","menos_c",null);
				imgsigno.setAttribute("src",currentpathimg+"/menos_c.gif",null);
				break;
			case "mas_t" :
				imgsigno.setAttribute("value","mas_c",null);
				imgsigno.setAttribute("src",currentpathimg+"/mas_c.gif",null);
				break;
			case "nada_t" :
                imgsigno.setAttribute("value","nada_c",null);
				imgsigno.setAttribute("src",currentpathimg+"/nada_c.gif",null);
				break;
		}
}
//-----------------------------------------------------------------------------------------------------------------------
//	 Cambia la imagen de signo del nodo padre de un nodo eliminado ( unico hijo)
//-----------------------------------------------------------------------------------------------------------------------
function ChgSignoPadreEliminaNodo(imgsigno){
        var signoimg=imgsigno.getAttribute("value");
		switch(signoimg){
			case "menos_t" :
				imgsigno.setAttribute("value","nada_t",null);
				imgsigno.setAttribute("src",currentpathimg+"/nada_t.gif",null);
				QuitaANCHOR(imgsigno);
				break;
			case "menos_c" :
				imgsigno.setAttribute("value","nada_c",null);
				imgsigno.setAttribute("src",currentpathimg+"/nada_c.gif",null);
				QuitaANCHOR(imgsigno);
				break;
			case "mas_t" :
				imgsigno.setAttribute("value","nada_t",null);
				imgsigno.setAttribute("src",currentpathimg+"/nada_t.gif",null);
				QuitaANCHOR(imgsigno);
				break;
			case "mas_c" :
				imgsigno.setAttribute("value","nada_c",null);
				imgsigno.setAttribute("src",currentpathimg+"/nada_c.gif",null);
				QuitaANCHOR(imgsigno);
				break;
		}
}
//-----------------------------------------------------------------------------------------------------------------------
//	 Cambia la imagen de un determinado nivel 
//-----------------------------------------------------------------------------------------------------------------------
function QuitaANCHOR(oIMG){
	var TAnchor=oIMG.parentNode;
	var oHTML=TAnchor.innerHTML;
	var oTD=TAnchor.parentNode;
	oTD.innerHTML="<SPAN>"+oHTML+"</SPAN>";
}
//-----------------------------------------------------------------------------------------------------------------------
//	 Cambia la imagen de un determinado nivel 
//-----------------------------------------------------------------------------------------------------------------------
function ChgSignoNivel(arbolv,n){
	if(arbolv==null) return
	var nodoTD =arbolv.childNodes[0];
	var nodoTABLE=nodoTD.childNodes[0];
	var nodoTBODY=nodoTABLE.childNodes[0];
	var oTRs=nodoTBODY.childNodes;
	for(var i=0;i<oTRs.length;i++){
		var auxsplit=oTRs[i].getAttribute("id");
		var idTR=auxsplit.split("-") [0];
		if(idTR=="TRNodoHijo"){
			ChgSignoNivel(oTRs[i],n)
		}
		else{
			var oTABLE=oTRs[i].getElementsByTagName('TABLE');
			var oIMGs=oTABLE[0].getElementsByTagName('IMG');
			oIMGs[n].setAttribute("src",currentpathimg+"/nada.gif",null);
		}
	}
}
//____________________________________________________________________________
//
//	Se ejecuta cada vez que se mueve el puntero del ratón. Se usa para desmarca
//	cualquier item de menu contextual que estuviese activo
//____________________________________________________________________________
function click_de_raton_prov(e){
	if(IE){
		botonraton=event.button
		event.returnValue=true;
		return;
	}
	if(NS){
		botonraton=e.which;
		e.returnValue=true;
		return;
	}
}
//____________________________________________________________________________
//
//	Recupera el navegador utilizado
//____________________________________________________________________________
var IE=(navigator.appName=="Microsoft Internet Explorer");
var NS=(navigator.appName=="Netscape");
//____________________________________________________________________________
//
//	Redirecciona el evento onmousedown a la función de usuario especificada. 
//____________________________________________________________________________
document.onmousedown = click_de_raton_prov; // Redefine el evento onmousedown
 if(NS) document.captureEvents(Event.MOUSEDOWN | Event.MOUSEMOVE | Event.MOUSEUP)


