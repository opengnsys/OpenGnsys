// ***************************************************************************
//  Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla

// Fichero: menucontextual.js
// Este fichero implementa las funciones javascript de la clase MenuContextual
// ***************************************************************************
var ctx_grissistema="#d4d0c8"
var ctx_azulmarino="#0a266a";
var ctx_blanco="#ffffff";
var ctx_negro="#000000";
var ctx_grissombra="#808080";

gmenuctx=new Array(); // Guarda el último menu flotante
var idxmnu=0 // Indice de los menus flotantes
var currentItem=null;
var currentPadresubmenu;
var currentPadreY;

var ClickX=null // Coordenada x del evento click del boton derecho
var ClickY=null // Coordenada y del evento click del boton derecho
var botonraton=null;
//____________________________________________________________________________
//	
//	Esta función muestra un menu contextual 
// Parámetros:
//		- x: Coordenada x de referencia
//		- y: Coordenada y de referencia
//		- menuctx: Objeto DIV contenedor del menu contextual
//____________________________________________________________________________
function muestra_contextual(x,y,menuctx){
	var margen=0
	dpzx=16
	dpzy=16
	wtop=calculatop_ctx(y,dpzy,margen,menuctx) // Calcula posición del menu contextual
	wleft=calculaleft_ctx(x,dpzx,margen,menuctx)
	ftop=wtop+parseInt(document.body.scrollTop) // Tiene en cuenta el scrolling
	fleft=wleft+parseInt(document.body.scrollLeft)
	menuctx.style.top=ftop
	menuctx.style.left=fleft
	menuctx.style.visibility="visible"
	menuctxSetSelectedIndex(menuctx,-1) // Coloca el nuevo indice
	gmenuctx[idxmnu++]=menuctx;
}
//____________________________________________________________________________
//
// Calcula coordenada top para el menu contextual que se mostrará.
// Parametros:
//	- oriy : Coordenada Y del objeto que provoca el evento
//	- dpzy : Desplazamiento sobre el eje y
//	- margen : Margen para que el menu aparezca un poco separado del ese objeto
//	- menuctx: El menu (objeto DIV) que se mostrará
//____________________________________________________________________________
function calculatop_ctx(oriy,dpzy,margen,menuctx){ // Calcula Y del menu contextual
	largodiv=parseInt(menuctx.offsetHeight);
	var wtop=oriy+dpzy+margen
	if (wtop+largodiv>parseInt(document.body.clientHeight)){
		var nwtop=oriy-dpzy-margen-largodiv
		if (nwtop>0) wtop=nwtop
	}
	return(wtop)
}
//____________________________________________________________________________
//
// Calcula coordenada left para el menu contextual que se mostrará.
// Parametros:
//	- orix : Coordenada X del objeto que provoca el evento
//	- dpzx : Desplazamiento sobre el eje x
//	- margen : Margen para que el menu aparezca un poco separado del ese objeto
//	- menuctx: El menu (objeto DIV) que se mostrará
//____________________________________________________________________________
function calculaleft_ctx(orix,dpzx,margen,menuctx){ // Calcula Y del menu contextual
	anchodiv=parseInt(menuctx.offsetWidth)
	var wleft=orix+dpzx+margen
	var maximodpl=parseInt(document.body.clientWidth)
	if (wleft+anchodiv>maximodpl){ // Si no cabe a la derecha
		var nwleft=orix-dpzx-margen-anchodiv // lo intenta a la izda.
		if (nwleft>0) wleft=nwleft
		else{
			wleft=maximodpl-dpzx-margen-anchodiv;
			if(wleft<document.body.scrollLeft) wleft=document.body.scrollLeft+16
		}
	}
	return(wleft)
}
//____________________________________________________________________________
//
// Esta función devuelve el objeto DIV al que pertenece el item <TR>  
// Parametros:
//	- o: El objeto <TR>
//____________________________________________________________________________
function contextual(o){
	while(o.tagName!="DIV")
		o=o.parentNode
	return(o)
}
//____________________________________________________________________________
//
// Esta función devuelve el objeto <TR> apuntado por el indice
// Parametros:
//	- o: El objeto TR
//  - idx: el indice del item, si es nulo se devuelve el item(objeto TR), seleccionado
//____________________________________________________________________________
function menuctxSelectedItem(o,idx){
	var oDIV=contextual(o); // Se asegura que el objeto de inicio es DIV
	var oTABLE=oDIV.childNodes[0]; // objeto TABLE
	var oINPUT=oDIV.childNodes[1]; // objeto INPUT
	var oTBODY=oTABLE.getElementsByTagName('TBODY')[0];
	if(idx==null) // No se especificó indice, devuelve el item seleccionado
		idx=oINPUT.getAttribute("value");
	var oTRS=oTBODY.getElementsByTagName('TR');
	for (var i=0;i<oTRS.length;i++){
		var oTR=oTRS[i];
		if(oTR.getAttribute("id")==idx) return(oTR);
	}
	return(null);
}
//____________________________________________________________________________
//
// Esta función actualiza el nuevo el indice del item seleccionado
// Parametros:
//	- o: El objeto DIV que contiene el menu contextual o un item(objeto TR) de él 
//  - i: El valor del indice
//____________________________________________________________________________
function menuctxSetSelectedIndex(o,idx){
	var oDIV=contextual(o); // Se asegura que el objeto de inicio es DIV
	var oINPUT=oDIV.childNodes[1];
	oINPUT.value=idx;
}
//____________________________________________________________________________
//
// Esta función devuelve el indice del item seleccionado
// Parametros:
//	-o : El objeto DIV que contiene el menu contextual o un item(objeto TR) de él 
//____________________________________________________________________________
function menuctxSelectedIndex(o){
	var oDIV=contextual(o); // Se asegura que el objeto de inicio es DIV
	var oINPUT=oDIV.childNodes[1];
	return(oINPUT.value);
}
//____________________________________________________________________________
// Se ejecuta cuando se posiciona el cursor dentro de un item de algún menú contextual.
// Parámetros:
//	- o: El item (objeto TR) donde se ha colocado el ratón 
//____________________________________________________________________________
function sobre_contextual(o){
	var oDIV=contextual(o) // Se asegura que el objeto de inicio es DIV
	var idx=menuctxSelectedIndex(oDIV) // Indice del Item anterior seleccionado
	var nwid=o.getAttribute("id");
	if (parseInt(idx)!=parseInt(nwid)){ // Si cambio de item
		if(idx>0){ // Si existía item anterior seleccionado
			desmarcar_item(oDIV,idx) // Desmarca item anterior
		}
		marcar_item(o); // Marca el actual item
		currentItem=o;
	}
}
//____________________________________________________________________________
//
// Hace resaltar el item del menu contextual donde se coloca el cursor.
// Si este item tuviese un submenu contextual,éste también aparecería.
// Además, inicializa el campo oculto de cada DIV donde se guarda el índice
// del item selecionado.
//
// Parametros:
//	- item: El objeto <TR>
//____________________________________________________________________________
function marcar_item(item){
	marca_desmarca(item,true) // Marca el item
	if (item.getAttribute("name")!=""){ // Existe submenu contextual
		currentPadresubmenu=item
		currentPadreY=ClickY
		setTimeout ("muestra_submenu();", 300); 
	}
	menuctxSetSelectedIndex(contextual(item),item.getAttribute("id")); // Coloca el nuevo indice
}
//____________________________________________________________________________
//
// Quita el resalte de un item y oculta los posibles submenus que tuviera 
// Parametros:
//	-o : El objeto DIV que contiene el menu contextual
//  - idx: el indice del item, si es nulo desmarca el item(objeto TR), seleccionado
//____________________________________________________________________________
function desmarcar_item(o,idx){	
	var oDIV=contextual(o) // Se asegura que el objeto de inicio es DIV
	if(idx==null) // No se especificó indice
		idx=menuctxSelectedIndex(oDIV) // Indice del Item seleccionado
	var item=menuctxSelectedItem(oDIV,idx)
	if(item==null) return // No hay item seleccionado
	marca_desmarca(item,false);
	var nomsub=item.getAttribute("name");
	if (nomsub!=null &&nomsub!=""){ // Tiene submenu
		var submenuctx=document.getElementById(nomsub);
		desmarcar_item(submenuctx); // Desmarca submenu
		submenuctx.style.visibility="hidden";
	}
}
//____________________________________________________________________________
//
// Marca o desmarca items dependiendo del parametro sw.
// Parámetros:
//	- o: El item (objeto TR) 
// 	Si sw=true marca, si sw=false demarca
//____________________________________________________________________________
function marca_desmarca(o,sw){
	if(sw){ // Marca
		var wfondo=ctx_azulmarino;
		var wcolor=ctx_blanco;
	}
	else{ // Desmarca
		var wfondo=ctx_grissistema;
		var wcolor=ctx_negro;
	}
	(MenuconImagen(contextual(o)) ? i0=2:i0=1);
	var nh=o.childNodes.length;
	for (var i=i0;i<nh-1;i++){
		var oTD=o.childNodes[i];
		var oIMGS=oTD.getElementsByTagName('IMG');
		if (oIMGS.length>0){
			var oIMG=oIMGS[0];
			if (oIMG.getAttribute("name")=="swsbfn"){ // imagen switch submenu
				oTD.style.backgroundColor=wfondo
				oTD.style.color=wcolor
				if (sw) 
					oIMG.setAttribute("src","../images/flotantes/swsbfb.gif",null);
				else
					oIMG.setAttribute("src","../images/flotantes/swsbfn.gif",null);
			}
			else{ // imagen del item
				if (sw){ // Marcar
					oIMG.style.border="1px";
					oIMG.style.borderStyle="outset";

				}
				else{ // Desmarcar
					oIMG.style.borderStyle="none";
				}
			}
		}
		else{
				oTD.style.backgroundColor=wfondo
				var oSPAN=oTD.getElementsByTagName('SPAN');
				if (oSPAN.length>0)
							oSPAN[0].style.color=wcolor
		}
	}
}
//____________________________________________________________________________
//
// Detecta si el menu contextual tiene items con imágenes asociadas
// Devuelve true en caso afirmativo y false en caso contrario.
//____________________________________________________________________________
function MenuconImagen(o){
	var oDIV=contextual(o);
	var oIMGS=oDIV.getElementsByTagName('IMG');
	return(oIMGS.length>0);
}
//____________________________________________________________________________
function reset_contextual(x,y){
	var swm=false;
	for (var i=0;i<idxmnu;i++ ){
		if (gmenuctx[i].style.visibility=="visible")
			swm=swm || EnContextual(x,y,gmenuctx[i])
	}
	if (!swm){ // No se ha hecho click en ningún menu contextual
		for (var i=0;i<idxmnu;i++ ){
			desmarcar_item(gmenuctx[i]);
			gmenuctx[i].style.visibility="hidden";
			gmenuctx[i]=null
		}
		idxmnu=0;
	}
}
//____________________________________________________________________________
//
// Detecta si ha hecho fuera del menu contextual pasado como parametro
// Parametros:
//	- x : Coordenada X de la pantalla donde se hizo click
//	- y : Coordenada Y de la pantalla donde se hizo click
//	- menuctx: El submenu (objeto DIV)
//____________________________________________________________________________
function EnContextual(x,y,menuctx){
	origen_x=parseInt(menuctx.offsetLeft)-parseInt(document.body.scrollLeft)
	origen_y=parseInt(menuctx.offsetTop)-parseInt(document.body.scrollTop)
	anchodiv=parseInt(menuctx.offsetWidth)
	largodiv=parseInt(menuctx.offsetHeight)

	if ( x>=origen_x && x<=origen_x+anchodiv && y>=origen_y  && y<=origen_y+largodiv  ) return true
	return(false)
}
//____________________________________________________________________________
//
// Muestra un submenu
// Parametros:
//	- item: El objeto <TR> padre del submenu
//____________________________________________________________________________
function muestra_submenu(){
	if(currentPadresubmenu==currentItem){
		var objdiv=contextual(currentPadresubmenu) 
		var menuctx=document.getElementById(currentPadresubmenu.getAttribute("name")); // Obtiene el submenu
		//desmarcar_item(menuctx)  // Desmarca el   submenu por si  se ha usado anteriormente
		wleft=subcalculaleft_ctx(objdiv,menuctx) // La x en función del padre
		wtop=subcalculatop_ctx(currentPadreY,menuctx) // La y depende de la longitud del submenu
		menuctx.style.top=wtop
		menuctx.style.left=wleft
		menuctx.style.visibility="visible";
		menuctxSetSelectedIndex(menuctx,-1) // Coloca el nuevo indice
		gmenuctx[idxmnu++]=menuctx;
	}
}
//____________________________________________________________________________
//
// Calcula coordenada top para el submenu contextual que se mostrará.
// Parametros:
//	- y : Coordenada Y de la pantalla donde se hizo click
//	- menuctx: El submenu (objeto DIV) que se mostrará
//____________________________________________________________________________
function subcalculatop_ctx(y,menuctx){ // Calcula el posicionamiento (y) del DIV ( SUBmenu contextual)
	var dpl=0
	largodiv=parseInt(menuctx.offsetHeight)
	var wtop=y+dpl+parseInt(document.body.scrollTop)
	if (parseInt(wtop+largodiv)>parseInt(document.body.clientHeight+parseInt(document.body.scrollTop))){
		var nwtop=y+parseInt(document.body.scrollTop)-16-largodiv
		if (nwtop>0) wtop=nwtop
	}
	return(wtop)
}
//____________________________________________________________________________
//
// Calcula coordenada left para el submenu contextual que se mostrará.
// Parametros:
//	- padrediv : Objeto DIV padre del submenu a mostrar
//	- menuctx: El submenu (objeto DIV) que se mostrará
//____________________________________________________________________________
function subcalculaleft_ctx(padrediv,menuctx){ // Calcula el posicionamiento (x) del DIV ( SUBmenu contextual)
	anchopadrediv=parseInt(padrediv.offsetWidth) // Ancho del div padre
	anchomenuctx=parseInt(menuctx.offsetWidth) // Ancho del div 
	if(IE)
		leftpadrediv=padrediv.style.pixelLeft // Coordenada x del div padre
	else 
		if(NS)
			leftpadrediv=parseInt(padrediv.style.left) // Coordenada x del div padre
	desplazamiento=leftpadrediv+anchopadrediv-4 // Desplazamiento
	var wleft=parseInt(desplazamiento)
	var maximodpl=parseInt(document.body.clientWidth)+parseInt(document.body.scrollLeft)
	if (wleft+anchomenuctx>maximodpl){
		var nwleft=leftpadrediv-anchomenuctx
		if (nwleft>0) wleft=nwleft
		else{
			wleft=maximodpl-anchomenuctx;
			if(wleft<document.body.scrollLeft) wleft=document.body.scrollLeft+18
		}
	}
	return(wleft)
}
//____________________________________________________________________________
//
//	Se ejecuta cada vez que se hace click con el puntero del ratón. Se usa para desmarca
//	cualquier item de menu contextual que estuviese activo
//____________________________________________________________________________
function click_de_raton(e){	
	if(IE){
		botonraton=event.button
		event.returnValue=true;
	}
	if(NS){
		botonraton=e.which;
		e.returnValue=true;
	}
	if (gmenuctx.length>0){
		reset_contextual(ClickX,ClickY);
	}
}
//____________________________________________________________________________
//
//	Se ejecuta cada vez que se mueve el puntero del ratón. Se usa para capturar coordenadas
//____________________________________________________________________________
function move_de_raton(e){	
	if(IE){
		ClickX=event.clientX
		ClickY=event.clientY
		event.returnValue=true;
	}
	if(NS){
		ClickX=e.clientX
		ClickY=e.clientY
		e.returnValue=true;
	}
}
//____________________________________________________________________________
//
//	Redirecciona el evento onmousedown a la función de usuario especificada. 
//____________________________________________________________________________
document.onmousedown = click_de_raton; // Redefine el evento onmousedown
document.onmousemove = move_de_raton; // Redefine el evento onmousedown
