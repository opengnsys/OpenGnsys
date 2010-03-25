// ************************************************************************************************//
// Aplicación WEB: Realtab
//		Descripción: Aplicación web para gestión de Tablaturas y acordes de guitarra
// 	Copyright 2009 José Manuel Alonso. Todos los derechos reservados.
// 	Fecha: Junio 2008
//  Fichero: Clase para llamar páginas web usando metodología AJAX
// *************************************************************************************************//
var _url;
var _fun;
var oXMLHttpRequest;
//____________________________________________________________________________
//	
//	LLama a la página
//
//	Parámetros:
//	
//		url			// Url de la página a la que se llama
//		fun			// Función a la que se llama despues de descargarse la página
//____________________________________________________________________________
function CallPage(url,fun){
	 _url=url;
	 _fun=fun;
	if (window.XMLHttpRequest) {
		oXMLHttpRequest= new XMLHttpRequest();
		oXMLHttpRequest.onreadystatechange = procesaoXMLHttpRequest;
		oXMLHttpRequest.open("GET",_url, true);
		oXMLHttpRequest.send(null);    
	} else if (window.ActiveXObject) {
		isIE = true;
		try {
		  oXMLHttpRequest= new ActiveXObject("Msxml2.XMLHTTP");
		  } catch (e) {
			try {
			  oXMLHttpRequest= new ActiveXObject("Microsoft.XMLHTTP");
			} catch (E) {
			  oXMLHttpRequest= false;
			}
		}
		if (oXMLHttpRequest) {
			oXMLHttpRequest.onreadystatechange =procesaoXMLHttpRequest;
			oXMLHttpRequest.open("GET",_url, true);
			oXMLHttpRequest.send();
		}
	}
}
 //_____________________________________________________________________________________
 function procesaoXMLHttpRequest(){
	if (oXMLHttpRequest.readyState == 4) {
		if (oXMLHttpRequest.status == 200) {
			var fcbk=_fun+"(oXMLHttpRequest.responseText)";
			eval(fcbk)
		 } 
	}
}