// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: programaciones.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero programaciones.php
// *************************************************************************************************************************************************
var gris="#bbbcb9"
var rojo="#cc3366"
var negro="#000000"
var azul= "#0000cc"
var blanco="#eeeeee"
var fondooriginal="#EEEECC";
var colororiginal="#003300";
var gmes=0;
var ganno=0;
var op_alta=1;
var op_modificacion=2;
var op_eliminacion=3;
var op_suspension=4;
var currenthoras=null;
var currenthorasini=null;
var currenthorasfin=null;
var swpz=false
//___________________________________________________________________________________________________________
function ItemSeleccionado(o){
	return(o.style.backgroundColor==rojo) 
}
//___________________________________________________________________________________________________________
function Marca(o){
	o.style.color=blanco 
	o.style.backgroundColor=rojo 
}
//___________________________________________________________________________________________________________
function Resalta(o){
	o.style.color=blanco 
	o.style.backgroundColor=azul 
}
//___________________________________________________________________________________________________________
function Desmarca(o){
	o.style.color=colororiginal
	o.style.backgroundColor=fondooriginal
}
//___________________________________________________________________________________________________________
function TH_clic(o){
	var tbobj=TBSource(o); // Busca la tabla donde se pulsó
	var oTD=tbobj.getElementsByTagName('TD')
	for(var i=0;i<oTD.length;i++){
		if(oTD[i].getAttribute("id")!="")
			clic(oTD[i],true)
	}
	cuestionesclic(o)
}
//___________________________________________________________________________________________________________
function clic(o,sw){
	if (!ItemSeleccionado(o))
		Marca(o);
	else // Deselección
		Desmarca(o);
	if(document.fprogramaciones.tipoaccion.value==EJECUCION_RESERVA){
			var idtb=Sourcetb(o); // Busca la tabla donde se pulsó
			if (idtb=="tabla_horas"){
				if(currenthoras!=o &&currenthoras!=null )
						Desmarca(currenthoras);
				currenthoras=o;
			}
			if (idtb=="tabla_horasini" && currenthorasini !=null ){
				if(currenthorasini!=o)
						Desmarca(currenthorasini);
				currenthorasini=o;
			}
			if (idtb=="tabla_horasfin" && currenthorasfin !=null ){
				if(currenthorasfin!=o)
						Desmarca(currenthorasfin);
				currenthorasfin=o;
			}

	}
	if(!sw)
		cuestionesclic(o)
}
//___________________________________________________________________________________________________________
function cuestionesclic(o){
	var idtb=Sourcetb(o); // Busca la tabla donde se pulsó
	if (idtb=="tabla_meses" || idtb=="tabla_annos")
		cuestion_opciones();
	if (!swpz){
			activa("bt_cancelar");
			swpz=!swpz;
		}
}
//___________________________________________________________________________________________________________-
function Sourcetb(o){
	while (o.tagName!="TABLE"){
		o=o.parentNode;
	}
	return(o.getAttribute("id"));
}
//___________________________________________________________________________________________________________-
function TBSource(o){
	while (o.tagName!="TABLE"){
		o=o.parentNode;
	}
	return(o);
}
//___________________________________________________________________________________________________________
function activa(idbt){
	bt=document.getElementById(idbt);
	bt.style.cursor="hand"
	bt.style.color=negro;
}
//___________________________________________________________________________________________________________
function desactiva(idbt){
	bt=document.getElementById(idbt);
	bt.style.cursor="default"
	bt.style.color=gris;
}
//___________________________________________________________________________________________________________
function habilitado(idbt){
	bt=document.getElementById(idbt);
	if (bt.style.cursor=="hand") return true
	return false
}
//___________________________________________________________________________________________________________
function cuestion_opciones(){
	swotbm=opcion_simple("tabla_meses");
	swotba=opcion_simple("tabla_annos");
	if (swotbm && swotba){
		var vd=valor_HEX("tabla_mesanno");
		if (!detecta_cambio(vd))
			visible_simple();
	}
	else{ // Conmutación a opción multiple
		visible_multiple();
	}
}
//___________________________________________________________________________________________________________
function detecta_cambio(vitem){ // vitem es el valor hexdecimal a mostrar

	wmes=parseInt(valor_item("tabla_meses")); // Recupera mes
	wanno=parseInt(valor_item("tabla_annos")); // Recupera año
	if (wmes>0 && wanno>0){ // Si se ha elegido un año y un mes ...
		if (gmes!=wmes || ganno!=wanno){ // Cara de nuevo el mes en blanco
			gmes=wmes;
			ganno=wanno;
			ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
			wurl="toma_mes.php?idmes="+wmes+"&idanno="+wanno+"&vitem="+vitem
			ifr.src=wurl; // LLama a la página
			return(true);
		}
		return(false);
	}
}
//___________________________________________________________________________________________________________
function cambia_mesanno(cadecalendario,vitem){
	tbm=document.getElementById("tbmesanno");
	tbm.innerHTML=cadecalendario;
	marca_item("tabla_mesanno",vitem);
	visible_simple();
}
//___________________________________________________________________________________________________________
function visible_multiple(){
	fm=document.getElementById("fechasmultiples");
	fs=document.getElementById("fechassimples");
	fm.style.visibility = "visible" 
	fs.style.visibility = "hidden";
}
//___________________________________________________________________________________________________________
function visible_simple(){
	fm=document.getElementById("fechasmultiples");
	fs=document.getElementById("fechassimples");
	fm.style.visibility = "hidden" 
	fs.style.visibility = "visible";
}
//___________________________________________________________________________________________________________
function modifica_programacion(ida,tia){
	if (habilitado("bt_modificar")){
		idprogramacion=valor_programacion();
		if (idprogramacion>0)
			gestor_programacion(ida,tia,idprogramacion,op_modificacion);
	}
}
//___________________________________________________________________________________________________________
function elimina_programacion(){
	if (habilitado("bt_eliminar")){
		idprogramacion=valor_programacion();
		if (idprogramacion>0){
			wurl="../gestores/gestor_programaciones.php?wswop="+op_eliminacion+"&widprogramacion="+idprogramacion
			ifr=document.getElementById("iframes_comodin") // Toma IFRAME
			ifr.src=wurl;
		}
	}
}
//___________________________________________________________________________________________________________
function alta_programacion(ida,tia){
	if (habilitado("bt_insertar"))
		gestor_programacion(ida,tia,0,op_alta) 
}
//___________________________________________________________________________________________________________
function duplicar_programacion(){
	activa("bt_insertar")
	desactiva("bt_eliminar");
	desactiva("bt_modificar");
	desactiva("bt_duplicar");
	activa("bt_cancelar");
	nuevo_bloque();
}

//___________________________________________________________________________________________________________
function gestor_programacion(ida,tia,idr,swop){
	widentificador=ida;
	wtipoaccion=tia;
	widprogramacion=idr;
	wswop=swop;
	wannos=valor_HEX("tabla_annos");
	if (wannos==0){
		alert(TbMsg[0]);
		return
	}
	wmeses=valor_HEX("tabla_meses");
	if (wmeses==0){
		alert(TbMsg[1]);
		return
	}
	fm=document.getElementById("fechasmultiples");
	if (fm.style.visibility == "visible"){ // Activada opciones múltiples
		wdiario=valor_HEX("tabla_diasmes");
		wdias=valor_HEX("tabla_dias");
		wsemanas=valor_HEX("tabla_semanas");
		if (wdiario==0 && wdias==0 && wsemanas==0 ){
			alert(TbMsg[2]);
			return
		}
	}
   else{
		wdiario=valor_HEX("tabla_mesanno");
		if (wdiario==0){
			alert(TbMsg[3]);
			return
		}
		wdias=0
		wsemanas=0
   }
	whoras=valor_HEX("tabla_horas");
	if (whoras==0){
		if(wtipoaccion!=EJECUCION_RESERVA){
			alert(TbMsg[4]);
			return
		}
	}
	if(wtipoaccion==EJECUCION_RESERVA){
		whorasini=valor_HEX("tabla_horasini");
		if (whorasini==0){
			alert(TbMsg[4]);
			return
		}
		whorasfin=valor_HEX("tabla_horasfin");
		if (whorasfin==0){
			alert(TbMsg[4]);
			return
		}
	}

	inputprogramacion=document.getElementById("nombrebloque")
	wnombrebloque=inputprogramacion.value
	if (wnombrebloque==""){
		alert(TbMsg[5]);
		return
	}
	wampm=document.getElementById("ampm").value;
	wminutos=document.getElementById("minutos").value;
	if (wminutos<0 || wminutos>59){
		alert(TbMsg[6]);
		document.getElementById("minutos").focus()
		return
	}
	if(wtipoaccion==EJECUCION_RESERVA){
		wampmini=document.getElementById("ampmini").value;
		wminutosini=document.getElementById("minutosini").value;
		if (wminutosini<0 || wminutosini>59){
			alert(TbMsg[6]);
			document.getElementById("minutosini").focus()
			return;
		}
		wampmfin=document.getElementById("ampmfin").value;
		wminutosfin=document.getElementById("minutosfin").value;
		if (wminutosfin<0 || wminutosfin>59){
			alert(TbMsg[6]);
			document.getElementById("minutosfin").focus()
			return;
		}
	}
	else{
		whorasini=0;
		wampmini=0;
		wminutosini=0;
		whorasfin=0;
		wampmfin=0;
		wminutosfin=0;
	}

	wsegundos=0;

	wurl="../gestores/gestor_programaciones.php?wswop="+wswop+"&widprogramacion="+widprogramacion+"&widentificador="+widentificador+"&wtipoaccion="+wtipoaccion+"&wnombrebloque="+wnombrebloque+"&wannos="+wannos+"&wmeses="+wmeses+"&wdiario="+wdiario+"&wdias="+wdias+"&wsemanas="+wsemanas+"&whoras="+whoras+"&whorasini="+whorasini+"&whorasfin="+whorasfin
	wurl+="&wampm="+wampm+"&wminutos="+wminutos+"&wsegundos="+wsegundos
	wurl+="&wampmini="+wampmini+"&wminutosini="+wminutosini+"&wampmfin="+wampmfin+"&wminutosfin="+wminutosfin
	wsw_sus=document.getElementById("sw_sus").checked;
	wurl+="&wsw_sus="+wsw_sus
	ifr=document.getElementById("iframes_comodin") // Toma IFRAME
	ifr.src=wurl;
	return
}
//___________________________________________________________________________________________________________
function suspender_programacion(ida,tia){
	var listalen=lista.options.length
	if(listalen==0){
		alert(TbMsg[8]);
		wsw_sus=document.getElementById("sw_sus").checked=false;
		return
	}
	widentificador=ida;
	wtipoaccion=tia;
	wurl="../gestores/gestor_programaciones.php?wswop="+op_suspension+"&widentificador="+widentificador+"&wtipoaccion="+wtipoaccion
	wsw_sus=document.getElementById("sw_sus").checked;
	wurl+="&wsw_sus="+wsw_sus
	ifr=document.getElementById("iframes_comodin") // Toma IFRAME
	ifr.src=wurl;
}
//___________________________________________________________________________________________________________
function resultado_suspender_programacion(){
	wsw_sus=document.getElementById("sw_sus").checked;
	if(wsw_sus)
		alert(TbMsg[9]);
	else
		alert(TbMsg[10]);
}
//___________________________________________________________________________________________________________
// Devualve el valor Hexadecimal que corresponde a los items 
//___________________________________________________________________________________________________________
function valor_HEX(idtb){
	otb=document.getElementById(idtb);
	var aux=0x00000000;
	filas=otb.rows.length
	for (i=0;i<filas;i++){
		columnas=otb.rows[i].cells.length
		for (j=0;j<columnas;j++){
			if (ItemSeleccionado(otb.rows[i].cells[j]))
				aux=aux | otb.rows[i].cells[j].value
		}
	}
	return(aux)
}
//___________________________________________________________________________________________________________
// Devuelve el valor decimal de un item de la tabla (ID)
// 0=no elección -1=más de un item n:valor del item
//___________________________________________________________________________________________________________
function valor_item(idtb){
	var valor=0,sw=0;
	otb=document.getElementById(idtb); 
	filas=otb.rows.length
	for (i=0;i<filas;i++){
		columnas=otb.rows[i].cells.length
		for (j=0;j<columnas;j++){
			if (ItemSeleccionado(otb.rows[i].cells[j])){
				if (sw==0){
					valor=otb.rows[i].cells[j].getAttribute("id");
					sw++;
				}
				else
					return(-1);
			}
		}
	}
	return(valor) 
}
//___________________________________________________________________________________________________________
// Devuelve true si existe uno ningún item seleccionado en la tabla
// y false en caso contrario
//  El parametro de entrada es el identificador dela tabla
//___________________________________________________________________________________________________________
function opcion_simple(idtb){
	var conta=0;
	otb=document.getElementById(idtb); 
	filas=otb.rows.length
	for (i=0;i<filas;i++){
		columnas=otb.rows[i].cells.length
		for (j=0;j<columnas;j++){
			if (ItemSeleccionado(otb.rows[i].cells[j])){
				conta++;
				if (conta>1) return(false);
			}
		}
	}
	return(true);
}
//___________________________________________________________________________________________________________
function sobre(o){
	if (!ItemSeleccionado(o))
		Resalta(o);
}
//___________________________________________________________________________________________________________
function fuera(o){
	if (!ItemSeleccionado(o))
		Desmarca(o);
}

//___________________________________________________________________________________________________________
//	Recibe una notificación(del IFRAME) de error al grabar programacion
//___________________________________________________________________________________________________________
function error_programacion(swe,resultado){
	desmarca_tablas();
	inicializa_variables();
	nuevo_bloque();
}
//___________________________________________________________________________________________________________
//	Recibe una notificación(del IFRAME) de la acción ejecutada
//___________________________________________________________________________________________________________		
function registro_programacion(idr,nombrere,swop){
	wswop=parseInt(swop); // Toma la opción ALTA,MODIFICACION O ELIMINACION
	switch (wswop){
		case op_alta:
			alert(TbMsg[11]);
			programacion_metelista(idr,nombrere);
			break;
		case op_modificacion:	
			alert(TbMsg[12]);
			modifica_texto(nombrere);
			break;
		case op_eliminacion:
			alert(TbMsg[13]);
			elimina_item();
			break;
	}
	desmarca_tablas();
	inicializa_variables();
	nuevo_bloque();

	visible_simple();
	activa("bt_insertar")
	desactiva("bt_eliminar");
	desactiva("bt_modificar");
	desactiva("bt_duplicar");
	desactiva("bt_cancelar");
	swpz=false;
}
//___________________________________________________________________________________________________________
//	Devuelve el valor del item seleccionado
//___________________________________________________________________________________________________________
function valor_programacion(){
	var lista=document.getElementById("lista_programaciones");
	p=lista.selectedIndex;
	if (p==-1){
		alert(TbMsg[14]);
		return(-1);
	}
	else
		return(lista.options[p].value)
}
//___________________________________________________________________________________________________________
//	Modifica el texto del item seleccionado
//___________________________________________________________________________________________________________
function modifica_texto(nombrere){
	var lista=document.getElementById("lista_programaciones");
	p=lista.selectedIndex;
	lista.options[p].text=nombrere
}
//___________________________________________________________________________________________________________
//	Elimina el item seleccionado
//___________________________________________________________________________________________________________
function elimina_item(){
	var lista=document.getElementById("lista_programaciones");
	p=lista.selectedIndex;
	lista.remove(p);
}
//___________________________________________________________________________________________________________
//	Recibe una notificación(del IFRAME) de grabación correcta programacion
//___________________________________________________________________________________________________________		
function inicializa_variables(){
	gmes=0;
	ganno=0;
}
//___________________________________________________________________________________________________________
//	Desmarca todos los items de todas las tablas
//___________________________________________________________________________________________________________		
function desmarca_tablas(){
	desmarca_tabla("tabla_annos");
	desmarca_tabla("tabla_meses");
	desmarca_tabla("tabla_mesanno");
	desmarca_tabla("tabla_dias");
	desmarca_tabla("tabla_semanas");
	desmarca_tabla("tabla_diasmes");
	desmarca_tabla("tabla_horas");
	
	document.getElementById("ampm").selectedIndex=1;
	document.getElementById("minutos").value="";
	//document.getElementById("segundos").value="";

	whorasini=	document.getElementById("tabla_horasini")
	if(whorasini!=null){
		desmarca_tabla("tabla_horasini");
		wampmini=	document.getElementById("ampmini")
		wminutosini=	document.getElementById("minutosini")
		wampmini.selectedIndex=1;
		wminutosini.value="";
	}
	whorasfin=	document.getElementById("tabla_horasfin")
	if(whorasfin!=null){
		desmarca_tabla("tabla_horasfin");
		wampmfin=	document.getElementById("ampmfin")
		wminutosfin=	document.getElementById("minutosfin")
		wampmfin.selectedIndex=1;
		wminutosfin.value="";
	}
}
//___________________________________________________________________________________________________________
// Averigua el nombre del bloque según lo que ya existe
//___________________________________________________________________________________________________________
function nuevo_bloque(){
	var lista=document.getElementById("lista_programaciones");
	var listalen=lista.options.length
	var nb=1
	var cbloque="bloque";
	var nbloque=cbloque+nb
	var swb=false;
	while(true){
		swb=false;
		for(var i=0;i<listalen;i++){
			if(lista.options[i].text==nbloque){
				swb=true
				break;
			}
		}
		if(swb){
			nb++;
			nbloque=cbloque+nb
		}
		else
			break;
	}
	document.getElementById("nombrebloque").value=nbloque;
	var lista=document.getElementById("lista_programaciones");
	lista.selectedIndex=-1;
}
//___________________________________________________________________________________________________________
// Desmarca todos los items de una tabla
//___________________________________________________________________________________________________________
function desmarca_tabla(idtb){
	otb=document.getElementById(idtb);
	if (!otb) return
	desmarcando_tabla(otb);
}
//___________________________________________________________________________________________________________
// Desmarca todos los items de una tabla ( parametro objeto tabla)
//___________________________________________________________________________________________________________
function desmarcando_tabla(otb){
	filas=otb.rows.length
	for (var i=0;i<filas;i++){
		columnas=otb.rows[i].cells.length
		for (var j=0;j<columnas;j++){
			if (ItemSeleccionado(otb.rows[i].cells[j]))
				Desmarca(otb.rows[i].cells[j])
		}
	}
}
//___________________________________________________________________________________________________________
//	Añade la programacion a la caja de lista
//___________________________________________________________________________________________________________
function programacion_metelista(valor,texto){
	var lista=document.getElementById("lista_programaciones");
	var e=document.createElement("OPTION");
	e.value=valor;
	e.text=texto;
	lista.add(e);
}
//___________________________________________________________________________________________________________
//	Llama a una página dentro del IFRAME que recupera una programacion
//___________________________________________________________________________________________________________
function consulta_programacion(){ 
	idprogramacion=valor_programacion();
	if (idprogramacion>0){
		desmarca_tablas();
		wurl="consulta_programaciones.php?idprogramacion="+idprogramacion;
		ifr=document.getElementById("iframes_comodin") // Toma IFRAME
		ifr.src=wurl;
		desactiva("bt_insertar")
		activa("bt_eliminar");
		activa("bt_modificar");
		activa("bt_duplicar");
		activa("bt_cancelar");
	}
}
//___________________________________________________________________________________________________________
//	Recibe una consulta de programacion (del IFRAME) y la visualiza
//___________________________________________________________________________________________________________
function muestra_programacion(cadena_campos){ 
	campos=cadena_campos.split(";");
	var pnombrebloque=campos[3];
	var pannos=campos[4];
	var pmeses=campos[5];
	var pdiario=campos[6];
	var pdias=campos[7];
	var psemanas=campos[8];
	var phoras=campos[9];
	var pampm=campos[10];
	var pminutos=campos[11];
	var psegundos=campos[12];
	var phorasini=campos[13];
	var pampmini=campos[14];
	var pminutosini=campos[15];
	var phorasfin=campos[16];
	var pampmfin=campos[17];
	var pminutosfin=campos[18];

	document.getElementById("nombrebloque").value=pnombrebloque;
	marca_item("tabla_annos",pannos);
	marca_item("tabla_meses",pmeses);
	if (opcion_multiple(pannos) || opcion_multiple(pmeses)){
			marca_item("tabla_diasmes",pdiario);
			marca_item("tabla_dias",pdias);
			marca_item("tabla_semanas",psemanas);
			visible_multiple();
	}
	else{
			gmes=pmeses;
			ganno=pannos;
			ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
			wmes=parseInt(valor_item("tabla_meses")); // Recupera mes
			wanno=parseInt(valor_item("tabla_annos")); // Recupera año
			wurl="toma_mes.php?idmes="+wmes+"&idanno="+wanno+"&vitem="+pdiario
			ifr.src=wurl; // LLama a la página

	}
	marca_item("tabla_horas",phoras);
	document.getElementById("ampm").value=pampm;
	document.getElementById("minutos").value=pminutos;
	//document.getElementById("segundos").value=psegundos;

	wtabla_horasini=	document.getElementById("tabla_horasini")
	if(wtabla_horasini!=null)
		marca_item("tabla_horasini",phorasini);
	wampmini=	document.getElementById("ampmini")
	wminutosini=	document.getElementById("minutosini")
	if(wampmini!=null){
		wampmini.value=pampmini;
		wminutosini.value=pminutosini;
	}
	wtabla_horasfin=	document.getElementById("tabla_horasfin")
	if(wtabla_horasfin!=null)
		marca_item("tabla_horasfin",phorasfin);
	wampmfin=	document.getElementById("ampmfin")
	wminutosfin=	document.getElementById("minutosfin")
	if(wampmfin!=null){
		wampmfin.value=pampmfin;
		wminutosfin.value=pminutosfin;
	}
}
// ------------------------------------------------------------------
//  Devuelve true si hay más de un item seleccionado, false al contrario
//  El parametro de entrada es el valor HEXadecimal
//___________________________________________________________________________________________________________
function opcion_multiple(valor){ 
	var conta=0;
	var auxhex=0x00000001;
	for (auxhex=0x00000001;auxhex!=0x00000000;auxhex=auxhex<<1){
		if (valor & auxhex){
			conta++;
			if (conta>1) return(true);
		}
	}
	return(false);
}
// ------------------------------------------------------------------
//  Marca todos los items de una tabla según el valor HEX enviado
//___________________________________________________________________________________________________________
function marca_item(idtabla,vhex){ 
	otb=document.getElementById(idtabla);
	filas=otb.rows.length
	for (i=0;i<filas;i++){
		columnas=otb.rows[i].cells.length
		for (j=0;j<columnas;j++){
			if (otb.rows[i].cells[j].value & vhex){
					Marca(otb.rows[i].cells[j]);
					if (idtabla=="tabla_horas")
						currenthoras=otb.rows[i].cells[j]
					if (idtabla=="tabla_horasini")
						currenthorasini=otb.rows[i].cells[j]
					if (idtabla=="tabla_horasfin")
						currenthorasfin=otb.rows[i].cells[j]
			}
		}
	}
}
// ------------------------------------------------------------------
//  Cancela todos los items de las tabalas e inicia variables
//___________________________________________________________________________________________________________
function cancela_programacion(){ 
	desmarca_tablas();
	inicializa_variables();
	nuevo_bloque();
	visible_simple();
	activa("bt_insertar")
	desactiva("bt_eliminar");
	desactiva("bt_modificar");
	desactiva("bt_duplicar");
	desactiva("bt_cancelar");
	swpz=false;

}
