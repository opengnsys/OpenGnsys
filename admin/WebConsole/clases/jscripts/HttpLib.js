// ********************************************************************************
// Aplicaci�n WEB: ogAdmWebCon
// Autor: Jos� Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaci�n: A�o 2009-2010
// Fecha �ltima modificaci�n: Agosto-2010
// Nombre del fichero: consolaremota.php
// Descripci�n : Clase para llamar p�ginas web usando metodolog�a AJAX
// ********************************************************************************
var _url;
var _fun;
var oXMLHttpRequest;
//____________________________________________________________________________
//	
//	LLama a la p�gina
//
//	Par�metros:
//	
//		url			// Url de la p�gina a la que se llama
//		fun			// Funci�n a la que se llama despues de descargarse la p�gina
//____________________________________________________________________________
function CallPage(url,prm,fun,met){
	 _url=url;
	 _fun=fun;

	if (window.XMLHttpRequest) {
		oXMLHttpRequest= new XMLHttpRequest();
		oXMLHttpRequest.onreadystatechange = procesaoXMLHttpRequest;
		oXMLHttpRequest.open("POST",_url, true);
		oXMLHttpRequest.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
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
			oXMLHttpRequest.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
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

