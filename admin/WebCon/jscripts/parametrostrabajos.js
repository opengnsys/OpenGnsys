// ************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fichero: parametrostrabajos.js
// ************************************************************************
//____________________________________________________________________________
//	
//	Esta función muestra una tabala de  parametros de comandos
//		Los parámetros recibidos son:
//			- id: identificador de la tarea
//____________________________________________________________________________
function abre_tablaparametros(id){
	var oTabla=document.getElementById("tablaparametros-"+id); // Toma objeto DIV
	if (oTabla.style.display=="block")
		oTabla.style.display="none";
	else
		oTabla.style.display="block"
}
