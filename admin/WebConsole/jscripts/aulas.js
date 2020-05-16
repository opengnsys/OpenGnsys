// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: aulas.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero aulas.php
// *************************************************************************************************************************************************
var TBcon=new Array(); // Array para eco de consolas
var Ambito=null;
var IdAmbito=null;
//________________________________________________________________________________________________________
//	
//		Copia al buffer un nodo de ordenador para moverlo posteriormente
//________________________________________________________________________________________________________
function mover_ordenador(){
	reset_contextual(-1,-1);
	corte_currentNodo=currentNodo
}
//________________________________________________________________________________________________________
//	
//		Esta función cambia de sitio un ordenador desde un aula a otro aula o bien adentro de un 
//  grupo de ordenadores dentro del mismo aula
//________________________________________________________________________________________________________
function colocar_ordenador(swsufijo){
	reset_contextual(-1,-1);
	if (!corte_currentNodo) {
		alert(CTbMsg[7]);
		return
	}
	var identificador=currentNodo.toma_identificador();
	var sufijonodo=currentNodo.toma_sufijo();
	var identificador_ordenador=corte_currentNodo.toma_identificador();
	var swsf=parseInt(swsufijo);
	var colo='s';
	if (swsf==0) // El ordenador se mueve a un grupo de ordenadores
		var prm="opcion="+op_movida+"&grupoid="+identificador+"&idordenador="+identificador_ordenador+"&coloc="+colo;
	else // El ordenador se mueve a un aula
		var prm="opcion="+op_movida+"&idaula="+identificador+"&idordenador="+identificador_ordenador+"&coloc="+colo;

	var wurl="../gestores/gestor_ordenadores.php";
	CallPage(wurl,prm,"retornoColocar","POST");
}
//______________________________________________________________________________________________________
function retornoColocar(iHTML){
	if(iHTML.length>0){
		eval(iHTML)
	}
}
//________________________________________________________________________________________________________
//	
//		Devuelve el resultado de cambiar un ordenador de sitio
//		Especificaciones:
//		Los parámetros recibidos son:
//			- resul: resultado de la operación de eliminación ( true si tuvo éxito)
//			- descrierror: Descripción del error en su caso
//			- nwid: Identificador del registro
//________________________________________________________________________________________________________
function resultado_cambiar_ordenadores(resul,descrierror,id){
	if (!resul){
		alert(descrierror);
		return
	}
	var ncel=corte_currentNodo.CeldaVista;

	var celdaHTML=ncel.parentNode.innerHTML; // Recupera celda del nodo
	if(IE)
		var  patron = new RegExp("<TD width=16><SPAN><IMG","gi"); 
	else 
		if(NS)
			var  patron = new RegExp("<TD width=\"16px\"><SPAN><IMG","gi"); 

	var p=celdaHTML.search(patron); 
	if(p<0) return; // Ha habido algn problema
	var nwceldaHTML='<TABLE  border="0" cellspacing="0" cellpadding="0"><TBODY><TR height=16><TD width=3></TD>';
	nwceldaHTML+=celdaHTML.substring(p);
	InsertaNodo(currentNodo,nwceldaHTML);
	EliminaNodo(corte_currentNodo); // Elimina el nodo 
	corte_currentNodo=null;
}
//________________________________________________________________________________________________________
//	
//	Refresca la visualización del estado de los ordenadores(Clientes rembo y clientes Windows o Linux) 
//________________________________________________________________________________________________________
function actualizar_ordenadores(){
	reset_contextual(-1,-1); // Oculta menu contextual
	var resul=window.confirm(TbMsg[1]);
	if (!resul)return;
	var idambito=currentNodo.toma_identificador();
	var ambito=calAmbito(currentNodo.toma_sufijo());
	var wurl="actualizar.php";
	var prm="idambito="+idambito+"&ambito="+ambito;
	CallPage(wurl,prm,"retornoActualizar","POST");
}
//________________________________________________________________________________________________________
//	
//	Detiene el programa que corre en el cliente 
//________________________________________________________________________________________________________
function purgar_ordenadores(){
	reset_contextual(-1,-1); // Oculta menu contextual
	var resul=window.confirm(TbMsg[4]);
	if (!resul)return;
	var idambito=currentNodo.toma_identificador();
	var ambito=calAmbito(currentNodo.toma_sufijo());
	var wurl="purgar.php";
	var prm="idambito="+idambito+"&ambito="+ambito;
	CallPage(wurl,prm,"retornoPurgar","POST");
}
//______________________________________________________________________________________________________
function retornoActualizar(resul){
	if(resul==1)
 		alert(TbMsg[7]);
	else
		alert(TbMsg[8]);
}
//______________________________________________________________________________________________________
function retornoPurgar(resul){
	if(resul==1)
 		alert(TbMsg[9]);
	else
		alert(TbMsg[10]);
}
//________________________________________________________________________________________________________
//	
//	Conmuta el estado de los ordenadores(Modo Administrado reiniciándolos) 
//________________________________________________________________________________________________________
function consola_remota(ambito){
	reset_contextual(-1,-1);

	Ambito=ambito;
	consola_eco(1);
}

//________________________________________________________________________________________________________
//	
//	Abre una ventana independiente, para ver el log del ordenador cliente
//________________________________________________________________________________________________________


function ver_log(ambito){
	var nombre_ordenador=currentNodo.toma_infonodo();
	var whref="../principal/verlog.php?nombreordenador="+nombre_ordenador;
	window.open(whref,"","width=1024,height=870,scrollbars=YES,resizable=YES")
}

//________________________________________________________________________________________________________
//	
//	Abre una ventana independiente, para ver el log del ordenador cliente
//________________________________________________________________________________________________________


function ver_log_seguimiento(ambito){
	var nombre_ordenador=currentNodo.toma_infonodo();
	var whref="../principal/verlogseguimiento.php?nombreordenador="+nombre_ordenador;
	window.open(whref,"","width=1024,height=870,scrollbars=YES,resizable=YES")
}

//________________________________________________________________________________________________________
//	
//	Abre una ventana para  mostrar el eco de una consola
//________________________________________________________________________________________________________
function eco_remoto(lit)
{
	reset_contextual(-1,-1);
 	if(Ambito==null){
		alert(TbMsg[6]);
	}
	else{
		if(Ambito==AMBITO_ORDENADORES)
			consola_eco(1);
		else
			consola_eco(2);
	}		
}
//________________________________________________________________________________________________________
function consola_eco(sw)
{
	var idambito=currentNodo.toma_identificador();
	if(TBcon[idambito])
		TBcon[idambito].close();
	
	if(TBcon[idambito])
		TBcon[idambito].close();		
	var litambito=currentNodo.toma_sufijo();
	var nomambito=currentNodo.toma_infonodo();
	var whref="../principal/consolaremota.php?litambito="+litambito+"&idambito="+idambito+"&nomambito="+nomambito+"&sw="+sw;
	if(sw==1){
		IdAmbito=idambito;
		window.open(whref,"frame_contenidos")
	}
	else{
		var nomw="w_"+litambito+"_"+idambito;	
		TBcon[idambito] = window.open(whref,nomw,"width=720,height=640");
	}
}
//________________________________________________________________________________________________________
//	
//	Muestra estatus de los ordenadores 
//________________________________________________________________________________________________________
function ver_aulas(){
	reset_contextual(-1,-1); // Oculta menu contextual
	var idambito=currentNodo.toma_identificador();
	var litambito=currentNodo.toma_sufijo();
	var nombreambito=currentNodo.toma_infonodo();
	var whref="aula.php?litambito="+litambito+"&idambito="+idambito+"&nombreambito="+nombreambito;
	 window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Estatus de un aula
//________________________________________________________________________________________________________
function veraula(o,sw){
	var identificador=o.getAttribute("id");
	var litambito=identificador.split("-")[0];
	var idambito=identificador.split("-")[1];
	var nombreambito=o.getAttribute("value");
	var whref="aula.php?litambito="+litambito+"&idambito="+idambito+"&nombreambito="+nombreambito;
	 window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
function menucontextual(o,idmnctx){
	var menuctx=document.getElementById(idmnctx); // Toma objeto DIV
	muestra_contextual(ClickX,ClickY,menuctx) // muestra menu
}
//________________________________________________________________________________________________________
//	
//  Envía un comando para su ejecución o incorporación a procedimientos o tareas
//________________________________________________________________________________________________________
function confirmarcomando(ambito,idcomando,descricomando,pagina,gestor,funcion){
	reset_contextual(-1,-1); // Oculta menu contextual
	document.fcomandos.idcomando.value=idcomando; // Identificador del comandos
	document.fcomandos.descricomando.value=descricomando; // Descripción del comandos	
	document.fcomandos.ambito.value=ambito; // Ámbito de aplicación
	document.fcomandos.idambito.value=currentNodo.toma_identificador(); // Identificador del ámbito
	document.fcomandos.nombreambito.value=currentNodo.toma_infonodo() ; // Nombre del ámbito
	document.fcomandos.action=pagina; // Página interactiva del comando
	document.fcomandos.gestor.value=gestor; // Página gestora del comando
	document.fcomandos.funcion.value=funcion; // Página gestora del comando
	document.fcomandos.submit();
}
//________________________________________________________________________________________________________
//	
//  Envía un comando para su ejecución o incorporación a procedimientos o tareas
//________________________________________________________________________________________________________
function confirmarprocedimiento(ambito){
	reset_contextual(-1,-1); // Oculta menu contextual
	var idambito=currentNodo.toma_identificador(); // identificador del ambito
	var nombreambito=currentNodo.toma_infonodo(); // nombre del ámbito
	if(nombreambito=="")
		var  nombreambito=currentNodo.value; // nombre del ámbito desde  página aula.php
	var wurl="../varios/ejecutaracciones.php?ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito;
	window.open(wurl,"frame_contenidos")}
//________________________________________________________________________________________________________
//	
//	Muestra la cola de acciones
//________________________________________________________________________________________________________
function cola_acciones(tipoaccion){
	reset_contextual(-1,-1); // Oculta menu contextual
	var ambito;
	var litambito=currentNodo.toma_sufijo(); // ambito
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
	var idambito=currentNodo.toma_identificador(); // identificador del ámbito
	var nombreambito=currentNodo.toma_infonodo(); // nombre del ordenador
	if(nombreambito=="")
		var  nombreambito=currentNodo.value; // nombre del ámbito desde página aula.php
	var wurl="../principal/colasacciones.php?ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito;
	window.open(wurl,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Muestra la cola de reservas
//________________________________________________________________________________________________________
function cola_reservas(tiporeserva){
	reset_contextual(-1,-1); // Oculta menu contextual
	var ambito;
	var litambito=currentNodo.toma_sufijo(); // ambito

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
	var idambito=currentNodo.toma_identificador(); // identificador del ámbito
	var nombreambito=currentNodo.toma_infonodo(); // nombre del ordenador
	if(nombreambito=="")
		var  nombreambito=currentNodo.value; // nombre del ámbito desde página aula.php
	var wurl="../principal/programacionesaulas.php?ambito="+ambito+"&idambito="+idambito+"&nombreambito="+nombreambito+"&tipocola="+tiporeserva;
	window.open(wurl,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
// Muestra el formulario de captura de datos de un ordenador estandar
//________________________________________________________________________________________________________
function ordenador_estandar(){
	reset_contextual(-1,-1); // Oculta menu contextual
	var identificador=currentNodo.toma_identificador();
	var nombreaula=currentNodo.toma_infonodo();
	var whref="../propiedades/propiedades_ordenadorestandar.php?idaula="+identificador+"&nombreaula="+nombreaula;
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
function resultado_ordenadorestandar(resul,descrierror){
	if (!resul){ // Ha habido algn error
		alert(descrierror);
		return
	}
	alert(TbMsg[0]);
}
//________________________________________________________________________________________________________
//	
//	Muestra la configuración de los ordenadores
//	Parámetros:
//			- ambito: ámbito que se quiere investigar
//________________________________________________________________________________________________________
function configuraciones(ambito){
		reset_contextual(-1,-1); // Oculta menu contextual
		var identificador=currentNodo.toma_identificador();
		wurl="configuraciones.php?idambito="+identificador+"&ambito="+ambito;
	 window.open(wurl,"frame_contenidos")
}
//___________________________________________________________________________________________________________
//	
//	Muestra formulario para incorporar ordenadores a trav� de un fichero de configuraci� de un servidor dhcp
//___________________________________________________________________________________________________________
function incorporarordenador(){
	reset_contextual(-1,-1);
	var idaula=currentNodo.toma_identificador();
	var nombreaula=currentNodo.toma_infonodo();
	var whref="../varios/incorporaordenadores.php?idaula="+idaula+"&nombreaula="+nombreaula;
	window.open(whref,"frame_contenidos")
}

function ver_boot(){
	reset_contextual(-1,-1); // Oculta menu contextual
	var idambito=currentNodo.toma_identificador();
	var litambito=currentNodo.toma_sufijo();
	var nombreambito=currentNodo.toma_infonodo();
	//alert(idambito);
	//alert('nombreambito' + nombreambito);
	//alert('litambito' + litambito);
	var whref="boot.php?litambito="+litambito+"&idambito="+idambito+"&nombreambito="+nombreambito;
	 window.open(whref,"frame_contenidos")
}
function ver_ubicarordenadores(){
	reset_contextual(-1,-1); // Oculta menu contextual
	var idcentro;
	var idambito=currentNodo.toma_identificador();
	var litambito=currentNodo.toma_sufijo();
	var nombreambito=currentNodo.toma_infonodo();
	if (litambito==="centros"){idcentro=idambito;}
	//alert(idcentro);
	//alert('nombreambito' + nombreambito);
	//alert('litambito' + litambito);
	var whref="ubicarordenadores.php?idcentro="+idcentro+"&litambito="+litambito+"&idambito="+idambito+"&nombreambito="+nombreambito;
	 window.open(whref,"frame_contenidos")
}
function ver_movordenadoresAulas(){
	reset_contextual(-1,-1); // Oculta menu contextual
	var idambito=currentNodo.toma_identificador();
	var litambito=currentNodo.toma_sufijo();
	var nombreambito=currentNodo.toma_infonodo();
	//if (litambito==="centros"){idcentro=idambito;}
	//alert(idcentro);
	//alert('nombreambito' + nombreambito);
	//alert('litambito' + litambito);
	//alert('idambito = ' + idambito);
	var whref="../comandos/MoverordenadoresAulas.php?litambito="+litambito+"&idambito="+idambito+"&nombreambito="+nombreambito;
	//alert(whref);
	 window.open(whref,"frame_contenidos")
}
