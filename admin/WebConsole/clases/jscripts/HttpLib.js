// ********************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: consolaremota.php
// Descripción : Clase para llamar páginas web usando metodología AJAX
// ********************************************************************************
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
function CallPage(url,prm,fun,met){
	 _url=url;
	 _fun=fun;

	if (window.XMLHttpRequest) {
		oXMLHttpRequest= new XMLHttpRequest();
		oXMLHttpRequest.onreadystatechange = procesaoXMLHttpRequest;
		oXMLHttpRequest.open("POST",_url, true);
		oXMLHttpRequest.setRequestHeader('Content-Type','application/x-www-form-urlencoded')
		oXMLHttpRequest.send(prm);    
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
			oXMLHttpRequest.open("POST",_url, true);
			oXMLHttpRequest.setRequestHeader('Content-Type','application/x-www-form-urlencoded')
			oXMLHttpRequest.send(prm);
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

