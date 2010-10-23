// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: colasacciones.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero colasacciones.php
// *************************************************************************************************************************************************
	var currentCol;
	var currentAcc;
	var iresul;
	var ifechafin;			
	var ihorafin;	
	var ifechareg;
	var ihorareg;
	var iiconamb;
	var iamb;
	var iinfor;
	var isitu;
	var iporcen;
	var curentwprg;
//________________________________________________________________________________________________________

	function resaltar(o){
		o.style.color="#999999"
		o.style.fontWeight="bold"

	}
//________________________________________________________________________________________________________

	function desresaltar(o){
		o.style.color="#999999"
		o.style.fontWeight="normal"
	}
//________________________________________________________________________________________________________

	function marcalinea(o){
	}	
//________________________________________________________________________________________________________

	function chgdespleestados(o)
	{

		var dplResultados=document.fdatos.resultado;
		var op=o.selectedIndex;
		switch(op){
			case ACCION_DETENIDA:
				dplResultados.selectedIndex=ACCION_SINRESULTADO;
				break;
			case ACCION_INICIADA:
				dplResultados.selectedIndex=ACCION_SINRESULTADO;
				break;
			case ACCION_FINALIZADA:
				dplResultados.selectedIndex=ACCION_EXITOSA;
				break;
		}
	}
//________________________________________________________________________________________________________

	function chgdespleresultados(o)
	{
		var dplEstados=document.fdatos.estado;
		var op=o.selectedIndex;
		switch(op){
			case ACCION_SINRESULTADO:
				//dplEstados.selectedIndex=0; // Todos
				break;
			case ACCION_EXITOSA:
				dplEstados.selectedIndex=ACCION_FINALIZADA;
				break;
			case ACCION_FALLIDA:
				dplEstados.selectedIndex=ACCION_FINALIZADA;
				break;
		}
	}
//________________________________________________________________________________________________________

	function vertabla_calendario(ofecha)
	{
		currentFecha=ofecha;
		url="../varios/calendario_ventana.php?fecha="+ofecha.value
		window.open(url,"vf","top=160,left=250,height=220,width=160,scrollbars=no")
	}
//________________________________________________________________________________________________________

	function vertabla_horario(ohora)
	{
		currentHora=ohora;
		url="../varios/horario_ventana.php?hora="+ohora.value
		window.open(url,"vh","top=120,left=115,height=180,width=590,scrollbars=no")
	}
//________________________________________________________________________________________________________

	function anade_fecha(fecha)
	{
		currentFecha.value=fecha
	}
//________________________________________________________________________________________________________

	function anade_hora(hora)
	{
		currentHora.value=hora
	}
//________________________________________________________________________________________________________
//
// Procesa operaciones sobre acciones
//
//	Parámetros:
//		op: Código de operacion
//				1- Eliminar
//				2.-Reiniciar
//				3.- Parar
//				4.- Reanudar
//		o: Es nulo si se eligen las opraciones globales, en caso de ser operaciones locales no lo es
//			y la cadena con el formato "tipoaccion,idtipoaccio,sesion,idaccion;" se encuentra en el atributo value
//
//	Especificaciones;
//
//		La forma de procesar las operaciones sobre acciones es a través de identificadores con el formato:
//			"tipoaccion,idtipoaccio,sesion,idaccion" separados cada tripla por ";" el gestor recibe esta cadena
//			y el tipo de operación.
//			
//		En caso de que idaccion sea cero se actua atendiendo a la sesion en caso contrario con la propia idaccion
//________________________________________________________________________________________________________

	function eleccion(op,o)
	{
		reset_contextual(-1,-1);
		if(o==null)
			var acciones=document.facciones.acciones.value;
		else
			var acciones=o.value;

		switch(op){
			case 1: // Eliminar
					if(confirm(TbMsg[1]))
						gestionaAccion(op,acciones);

					break;
			case 2: // Reiniciar
					if(confirm(TbMsg[2]))
						gestionaAccion(op,acciones);
					break;
			case 3: // Detener
					if(confirm(TbMsg[3]))
						gestionaAccion(op,acciones);
					break;
			case 4: // Reanudar
					if(confirm(TbMsg[4]))
						gestionaAccion(op,acciones);
					break;
			case 5: // Finalizar sin errores
					if(confirm(TbMsg[5]))
						gestionaAccion(op,acciones);
					break;
			case 6: // Finalizar con errores
					if(confirm(TbMsg[6]))
						gestionaAccion(op,acciones);
					break;				
		}
	}
//________________________________________________________________________________________________________

	function gestionaAccion(op,acciones){	
		currentAcc=acciones;
		/* LLamada a la gestión */
		var wurl="../gestores/gestor_colasacciones.php";
		var prm="opcion="+op+"&acciones="+acciones
		CallPage(wurl,prm,"retornoGestion","POST");
}
//______________________________________________________________________________________________________

	function retornoGestion(op)
	{
		//alert("Retorno:"+op)
		var opcion=parseInt(op);
		if(opcion==0){ // Error en el proceso anterior
			alert(TbMsg[0]);
			return;
		}
		var tipoaccion;
		var idtipoaccion;
		var sesion;
		var idaccion;
		var tbAcciones=currentAcc.split(";");

		/* Busca nodos afectados y los coloca en array para proceso posterior */	
		for(i=0;i<tbAcciones.length-1;i++){
			var tbAccion=tbAcciones[i].split(",");
			tipoaccion=tbAccion[0];
			idtipoaccion=tbAccion[1];
			sesion=tbAccion[2];
			idaccion=tbAccion[3];
			var oTB=document.getElementById("tbAcciones"); 
			var oTRs=new Array(); 
			var oTIs=new Array(); 
			var k=r=0;
			for(var j=0;j<oTB.childNodes.length;j++){ // Recopila nodos
				if(idaccion>0){ // Operación sobre un ordenador concreto
					if(k==0)
						oTRs[k++]=document.getElementById(idaccion).parentNode;
					if(oTB.childNodes[j].id==sesion)
						oTIs[r++]=oTB.childNodes[j];					
				}
				else{	
					if(oTB.childNodes[j].id==sesion)
						oTRs[k++]=oTB.childNodes[j];
				}
			}
			for( var j=0;j<k;j++){ // Recorre nodos afectados
				switch(opcion){
					case 1: 
						oTB.removeChild(oTRs[j]); // Elimina nodo <TR> completo
						break;
					case 2: 
						cambioReinicio(oTRs[j]); // Coloca icono de accion iniciada
						break;	
					case 3:
						cambioParada(oTRs[j]); // Coloca icono de accion parada a las que estén activas
						break;	
					case 4: 
						cambioReanudacion(oTRs[j]); // Coloca icono de accion iniciada a las que estén paradas
						break;	
					case 5: 
						cambioFinalizar(oTRs[j],true); // Coloca icono de accion finalizada sin errores
						break;	
					case 6: 
						cambioFinalizar(oTRs[j],false); // Coloca icono de accion finalizada con errores
						break;							
				}				
			}
			// Recalculo de porcentaje
			if(opcion==2 || opcion==5 || opcion==6){
				if(idaccion>0) // Si es reinicio de un sólo ordenador
					recalculaAccion(oTIs,r,op);
				else
					recalculaAccion(oTRs,k,op);				
			}
		}
		alert(TbMsg[10]);
	}
//______________________________________________________________________________________________________
//
// Gestiona todas las operaciones posteriores al reinicio de la acción
//______________________________________________________________________________________________________

	function cambioReinicio(nodo)	
	{	
		if(nodo.getAttribute('value')!="D") return; // Sólo nodos de notificaciones
	
		setIndices(nodo);

		nodo.childNodes[ifechafin].innerHTML="&nbsp;";
		nodo.childNodes[ihorafin].innerHTML="&nbsp;";	
		nodo.childNodes[iinfor].innerHTML="&nbsp;";	

		//if(nodo.childNodes[isitu].childNodes[0].getAttribute('value')==ACCION_FINALIZADA){
			nodo.childNodes[isitu].childNodes[0].setAttribute("src","../images/iconos/acIniciada.gif");	
			nodo.childNodes[isitu].childNodes[0].setAttribute("value",ACCION_INICIADA)	
			nodo.childNodes[iresul].childNodes[0].setAttribute("value",ACCION_SINRESULTADO)
			nodo.childNodes[iresul].childNodes[0].setAttribute("src","../images/iconos/nada.gif");			
			nodo.childNodes[iporcen].innerHTML="&nbsp;"
		//}
	}	
//______________________________________________________________________________________________________
//
// Gestiona todas las operaciones posteriores a la detención de la acción
//______________________________________________________________________________________________________

	function cambioParada(nodo)
	{	
		if(nodo.getAttribute('value')!="D") return; // Sólo nodos de notificaciones 
		setIndices(nodo);
		if(nodo.childNodes[isitu].childNodes[0].getAttribute('value')==ACCION_INICIADA){
			nodo.childNodes[isitu].childNodes[0].setAttribute("src","../images/iconos/acDetenida.gif");	
			nodo.childNodes[isitu].childNodes[0].setAttribute("value",ACCION_DETENIDA)		
		}
	}
//______________________________________________________________________________________________________
//
// Gestiona todas las operaciones posteriores a la reanudación de la acción
//______________________________________________________________________________________________________

	function cambioReanudacion(nodo)
	{	
		if(nodo.getAttribute('value')!="D") return; // Sólo nodos de notificaciones 
		setIndices(nodo);
		if(nodo.childNodes[isitu].childNodes[0].getAttribute('value')==ACCION_DETENIDA){
			nodo.childNodes[isitu].childNodes[0].setAttribute("src","../images/iconos/acIniciada.gif");	
			nodo.childNodes[isitu].childNodes[0].setAttribute("value",ACCION_INICIADA)				
		}
	}
//______________________________________________________________________________________________________
//
// Gestiona todas las operaciones posteriores a la finalización de la acción
//______________________________________________________________________________________________________

	function cambioFinalizar(nodo,sw)
	{	
		if(nodo.getAttribute('value')!="D") return; // Sólo nodos de notificaciones 
		setIndices(nodo);
		if(nodo.childNodes[isitu].childNodes[0].getAttribute('value')==ACCION_INICIADA){
			nodo.childNodes[isitu].childNodes[0].setAttribute("value",ACCION_FINALIZADA)
			nodo.childNodes[isitu].childNodes[0].setAttribute("src","../images/iconos/nada.gif");
			if(sw){
				nodo.childNodes[iresul].childNodes[0].setAttribute("value",ACCION_EXITOSA)
				nodo.childNodes[iresul].childNodes[0].setAttribute("src","../images/iconos/acExitosa.gif");
				nodo.childNodes[iinfor].innerHTML=LITACCION_EXITOSA;			}
			else{
				nodo.childNodes[iresul].childNodes[0].setAttribute("value",ACCION_FALLIDA)
				nodo.childNodes[iresul].childNodes[0].setAttribute("src","../images/iconos/acFallida.gif");
				nodo.childNodes[iinfor].innerHTML=LITACCION_FALLIDA;
			}
			var ahora = new Date()
			var fechafin=ahora.getDate()+"-"+(ahora.getMonth()+1)+"-"+ahora.getFullYear();
			nodo.childNodes[ifechafin].innerHTML=fechafin;
			var horafin=ahora.getHours()+":"+(ahora.getMinutes()+1)+":"+ahora.getSeconds();
			nodo.childNodes[ihorafin].innerHTML=horafin;
		}			
	}	
	
//______________________________________________________________________________________________________
//
// Configura indices para acceo a nodos
//______________________________________________________________________________________________________

	function setIndices(nodo)
	{	
		if(nodo.getAttribute('value')=="C") 
			iresul=2; // Nodo cabecera de sesion
		else{
			if(nodo.getAttribute('value')=="D") 
				iresul=1; // Nodo detalle de sesion
			else
				return; // Nodo de cambio de ámbito
		}	
		ifechafin=iresul+1;			
		ihorafin=ifechafin+1;	
		ifechareg=ihorafin+1;
		ihorareg=ifechareg+1;
		iiconamb=ihorareg+1;
		iamb=iiconamb+1;
		iinfor=iamb+1;
		isitu=iinfor+1;
		iporcen=isitu+1;
	}	
//______________________________________________________________________________________________________
//
// Recalcula porcentaje después de reinicios
//
//	Especificaciones:
//		El parámetro sw indica si finaliza sin errores (true) o con errore (false)
//______________________________________________________________________________________________________

	function recalculaAccion(nodos,r,op)
	{
		var c,nt,nn;
		nt=nf=0;
		var resultado=ACCION_EXITOSA;

		for(var j=0;j<r;j++){ // Recorre nodo para recalcular porcentaje
			if(nodos[j].getAttribute('value')=="C")
				c=j; // Guarda indice nodo cabecera de sesión
			else{
				if(nodos[j].getAttribute('value')=="D"){
					nf++;
					if(nf==1) setIndices(nodos[j]); // Sólo la primera vez
					if(nodos[j].childNodes[isitu].childNodes[0].getAttribute("value")==ACCION_FINALIZADA)
						nt++;
					if(nodos[j].childNodes[iresul].childNodes[0].getAttribute("value")==ACCION_FALLIDA)
						resultado=ACCION_FALLIDA;
					if(nodos[j].childNodes[iresul].childNodes[0].getAttribute("value")==ACCION_SINRESULTADO){
						if(resultado==ACCION_EXITOSA) 
							resultado=ACCION_SINRESULTADO;
					}
				}
			}	
		}
		var porcen=0;
		if(nf>0)
			porcen=nt*100/nf; // Calcula porcentaje de finalización
		setIndices(nodos[c])	
		nodos[c].childNodes[iporcen].innerHTML=Math.floor(porcen)+"%";	
		switch(resultado){
			case ACCION_EXITOSA:
				nodos[c].childNodes[iresul].childNodes[0].setAttribute("value",ACCION_EXITOSA)
				nodos[c].childNodes[iresul].childNodes[0].setAttribute("src","../images/iconos/acExitosa.gif");			
				break;

			case ACCION_FALLIDA:
				nodos[c].childNodes[iresul].childNodes[0].setAttribute("value",ACCION_FALLIDA)
				nodos[c].childNodes[iresul].childNodes[0].setAttribute("src","../images/iconos/acFallida.gif");			
				break;
			case ACCION_SINRESULTADO:
				nodos[c].childNodes[iresul].childNodes[0].setAttribute("value",ACCION_SINRESULTADO)
				nodos[c].childNodes[iresul].childNodes[0].setAttribute("src","../images/iconos/nada.gif");
			break;
		}
	}
//______________________________________________________________________________________________________
//
// Filtra según una determinada accion o bien elimina ese filtro
//
// Parámetros:
//	sw: Indica si hay que filtrar o hay que eliminar el filtro por acción
//______________________________________________________________________________________________________

	function filtroAccion(sw)
	{
		if(sw==0){ // Quitar filtro
			document.fdatos.sesion.value=0;
			document.fdatos.visuprm.checked=false;
			document.fdatos.visucmd.checked=true;
			document.fdatos.visupro.checked=false;
		}
		else{ // Filtrar
			document.fdatos.sesion.value=document.facciones.sesion.value;
			document.fdatos.visuprm.checked=true;
			document.fdatos.visucmd.checked=true;
			document.fdatos.visupro.checked=true;
		}
		document.fdatos.submit();
	}
//________________________________________________________________________________________________________
//	
//		Muestra formulario de programaciones para acciones
//________________________________________________________________________________________________________

function programacion(idcmd,sesion,descripcion)
{
	reset_contextual(-1,-1)
	var whref="../varios/programaciones.php?idcomando="+idcmd+"&sesion="+sesion;
	whref+="&descripcioncomando="+descripcion+"&tipoaccion="+EJECUCION_COMANDO
	if(curentwprg)
		curentwprg.close();
	curentwprg=window.open(whref,"wprg","left=50,top=20,height=520,width=480,scrollbars=no")
}
	