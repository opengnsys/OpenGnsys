// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2006
// Nombre del fichero: aula.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero aulas.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________

var cadenaip;
//________________________________________________________________________________________________________

	function NodoAux(){
		this.idambito=0;								
		this.ambito=0;
		this.litambito=null; 
		this.nombreambito=null;

		this.toma_identificador= function(){
			return(idambito);
		}
		this.toma_sufijo= function(){
			return(litambito);
		}
		this.toma_infonodo= function(){
			return(nombreambito);
		}
		// Fin de la clase
}
currentNodo=new NodoAux();
//________________________________________________________________________________________________________
function nwmenucontextual(o,idmnctx){
	var menuctx=document.getElementById(idmnctx); // Toma objeto DIV
	muestra_contextual(ClickX,ClickY,menuctx) // muestra menu
	Toma_Datos(o);
}
//________________________________________________________________________________________________________
//	
//	Toma datos
//________________________________________________________________________________________________________
function Toma_Datos(o){
	var identificador=o.getAttribute("nod");
	litambito=identificador.split("-")[0];
	idambito=identificador.split("-")[1];
	nombreambito=o.getAttribute("value");
	currentNodo.idambito=idambito;
	currentNodo.litambito=litambito;
	currentNodo.nombreambito=nombreambito;
}
//________________________________________________________________________________________________________
function wactualizar_ordenadores(o){
	Toma_Datos(o);
 	actualizar_ordenadores();
}
//________________________________________________________________________________________________________
//	
//	Refresca la visualizaci� del estado de los ordenadores(Clientes rembo y clientes Windows o Linux) 
//________________________________________________________________________________________________________
function Sondeo(ipes){
	cadenaip=ipes;
	reset_contextual(-1,-1) // Oculta menu contextual
	var wurl="../principal/sondeo.php";
	var prm="cadenaip="+cadenaip+"&sw=1"; // La primera vez se manda sondeo a los clientes
	CallPage(wurl,prm,"retornoSondeo","POST");
	setTimeout("respuestaSondeo();",100); 	
}
//______________________________________________________________________________________________________
function retornoSondeo(resul){
	/*
	if(resul==1)
 		alert(TbMsg[11]);
	else
		alert(TbMsg[12]);
*/
}
//________________________________________________________________________________________________________
function respuestaSondeo(){
	var wurl="../principal/sondeo.php";
	var prm="cadenaip="+cadenaip+"&sw=2"; // La primera vez se manda sondeo a los clientes
	CallPage(wurl,prm,"retornorespuestaSondeo","POST");
	setTimeout("respuestaSondeo();",5000); 	
}
//______________________________________________________________________________________________________
function retornorespuestaSondeo(resul){
	if(resul.length>0){
		var ip=""; // Dirección IP del ordenador
		var so=""; // Sistema operativo activo
		var objOrd=null; // Objeto ordenador
		var imgOrd="";
		var cadena=resul.split(";"); // Trocea la cadena devuelta por el servidor de adminsitración
		for (var i=0;i<cadena.length;i++){
			var dual=cadena[i].split("/");
			ip=dual[0];
			so=dual[1];
			objOrd=document.getElementById(ip);
			tbobjOrd=getElementsByAttribute(document.body, "img","ip",ip);
			if(tbobjOrd.length>0){ // Si existe el objeto
				objOrd=tbobjOrd[0];
				imgOrd=soIMG(so); // Toma url de la imagen según su s.o.
				if(objOrd.sondeo!=so){ // Si es distinto al que tiene ...se cambia la imagen
					objOrd.src="../images/"+imgOrd;
					objOrd.sondeo=imgOrd;
				}
			}		
		}
	}
}
//______________________________________________________________________________________________________
function soIMG(so)
{
	var MimgOrdenador="";
	switch(so){
				case 'INI':
								MimgOrdenador="ordenador_INI.gif";  // Cliente ocupado
								break;
				case 'BSY':
								MimgOrdenador="ordenador_BSY.gif";  // Cliente ocupado
								break;
				case 'OPG':
								MimgOrdenador="ordenador_RMB.gif";  // Cliente Rembo
								break;
				case 'RMB':
								MimgOrdenador="ordenador_RMB.gif";  // Cliente Rembo
								break;
				case 'WS2': 
								MimgOrdenador="ordenador_WS2.gif"; // Windows Server 2003
								break;
				case 'W2K':
								MimgOrdenador="ordenador_W2K.gif"; // Windows 2000
								break;
				case 'WXP':
								MimgOrdenador="ordenador_WXP.gif"; // Windows XP
								break;
				case 'WNT':
								MimgOrdenador="ordenador_WNT.gif"; // Windows NT
								break;
				case 'W95':
								MimgOrdenador="ordenador_W95.gif"; // Windows 95
								break;
				case 'W98':
								MimgOrdenador="ordenador_W98.gif"; // Windows 98
								break;
				case 'WML':
								MimgOrdenador="ordenador_WML.gif"; // Windows Millenium
								break;
				case 'LNX':
								MimgOrdenador="ordenador_LNX.gif"; // Linux
				default:
								MimgOrdenador="ordenador_OFF.gif"; // Linux
								break;
	}
	return(MimgOrdenador);
}
//______________________________________________________________________________________________________
//	Copyright Robert Nyman, http://www.robertnyman.com
//	Free to use if this text is included
//______________________________________________________________________________________________________
function getElementsByAttribute(oElm, strTagName, strAttributeName, strAttributeValue){
	var arrElements = (strTagName == "*" && oElm.all)? oElm.all : oElm.getElementsByTagName(strTagName);
	var arrReturnElements = new Array();
	var oAttributeValue = (typeof strAttributeValue != "undefined")? new RegExp("(^|\\s)" + strAttributeValue + "(\\s|$)") : null;
	var oCurrent;
	var oAttribute;
	for(var i=0; i<arrElements.length; i++){
		oCurrent = arrElements[i];
		oAttribute = oCurrent.getAttribute && oCurrent.getAttribute(strAttributeName);
		if(typeof oAttribute == "string" && oAttribute.length > 0){
			if(typeof strAttributeValue == "undefined" || (oAttributeValue && oAttributeValue.test(oAttribute))){
				arrReturnElements.push(oCurrent);
			}
		}
	}
	return arrReturnElements;
}
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
//	Muestra pantalla de Consola remota
//________________________________________________________________________________________________________
function consola_remota(){
	reset_contextual(-1,-1)
	var whref="../principal/consolaremota.php?litambito="+litambito+"&idambito="+idambito+"&nomambito="+nombreambito
	location.href=whref;
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

	
