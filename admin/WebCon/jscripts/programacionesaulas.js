// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: programacionesaulas.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero programacionesaulas.php
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
	function chgdesplesituacion(o,swevt){
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
}
//________________________________________________________________________________________________________
	function TH_clic(o){
		currentFecha=o.getAttribute("id");
		document.fdatos.fechainicio.value="1/"+currentFecha
		document.fdatos.fechafin.value="31/"+currentFecha
		document.fdatos.submit();
	}
//________________________________________________________________________________________________________
	function AnnoReserva(anno){
		var wfechainicio=document.fdatos.fechainicio.value.split("/");
		var wfechafin=document.fdatos.fechafin.value.split("/");

		if(wfechainicio[2]==wfechafin[2]){
			document.fdatos.submit();
			return
		}

		if(wfechainicio[2]==anno) // El mismo año que el de inicio
			document.fdatos.fechafin.value="31/12/"+anno;
		else{
				document.fdatos.fechainicio.value="1/1/"+anno
		}

		if(wfechafin[2]==anno) // El mismo año que el de fin
			document.fdatos.fechainicio.value="1/1/"+anno
		else{
			document.fdatos.fechafin.value="31/12/"+anno;
		}

		document.fdatos.submit();
	}

