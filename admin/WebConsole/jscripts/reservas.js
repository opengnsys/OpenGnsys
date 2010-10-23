// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fichero: reservas.js
// Este fichero implementa las funciones javascript del fichero reservas.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
//	
//		Muestra formulario de programaciones para tareas y trabajos 
//________________________________________________________________________________________________________
function programacion(tipoaccion){
	reset_contextual(-1,-1)
	var identificador=currentNodo.toma_identificador()
	var descripcion=currentNodo.toma_infonodo()
	var whref="../varios/programaciones.php?idreserva="+identificador+"&descripcionreserva="+descripcion+"&tipoaccion="+EJECUCION_RESERVA
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra la cola de reservas
//________________________________________________________________________________________________________
function cola_reservas(tiporeserva){
	reset_contextual(-1,-1); // Oculta menu contextual
	var ambito=AMBITO_GRUPOSRESERVAS;
	var idambito=currentNodo.toma_identificador() // identificador del ámbito
	if(idambito==null) idambito=0;
	var  nombreambito=	currentNodo.toma_infonodo() // nombre del ámbito desde página aula.php
	var wurl="../principal/programacionesaulas.php?ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito+"&tipocola="+tiporeserva
	window.open(wurl,"frame_contenidos")
}
