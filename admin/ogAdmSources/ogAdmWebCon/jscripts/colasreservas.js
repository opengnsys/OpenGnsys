// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: colasreservas.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero colasreservas.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
	function chgdesplereservas(o){
		var otip="";
		for (var i=0; i< o.options.length; i++){
			if(o.options[i].selected)
				otip+=o.options[i].value+"="+o.options[i].text+";"
		}
		document.fdatos.estadoreserva.value=otip
	}
//________________________________________________________________________________________________________
	function chgdesplesituacion(o){
		var otip="";
		for (var i=0; i< o.options.length; i++){
			if(o.options[i].selected)
				otip+=o.options[i].value+"="+o.options[i].text+";"
		}
		document.fdatos.situacion.value=otip
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
		url="../varios/horareser_ventana.php?hora="+ohora.value
		window.open(url,"vh","top=200,left=250,height=120,width=160,scrollbars=no")
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
	function sobre(){
}
//________________________________________________________________________________________________________
	function fuera(){
}
//________________________________________________________________________________________________________
	function clic(o){
		var idaula=o.value; 
		var wid=o.getAttribute("id");
		var aux=wid.split("/") // Toma el identificador 
		var onodo=document.getElementById("nodomes-"+idaula+"-"+aux[1]+"/"+aux[2]); 
		desplieganodo(onodo); // Despliega el mes donde está el día
		onodo=document.getElementById("nododia-"+idaula+"-"+wid); 
		desplieganodo(onodo); 
		nwhref="#anododia-"+idaula+"-"+wid
		location.href=nwhref
		resalta(onodo);
}
//________________________________________________________________________________________________________
	function TH_clic(o){
		var idaula=o.value; 
		var onodo=document.getElementById("nodomes-"+idaula+"-"+o.getAttribute("id")); 
		desplieganodo(onodo); 

		nwhref="#anodomes-"+idaula+"-"+o.getAttribute("id")
		location.href=nwhref
		resalta(onodo);
	}
//________________________________________________________________________________________________________
	function desplieganodo(onodo){
		var pathimg='../images/tsignos';
		despliega(onodo,pathimg)
}

