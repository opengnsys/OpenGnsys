// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005 Jos�Manuel Alonso. Todos los derechos reservados.
// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: Configurar.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero Configurar.php (Comandos)
// *************************************************************************************************************************************************
var patrontablaparticion;
var ultpa;
var currentconfiguracion=null;
var currentimgconfiguracion=null;
//________________________________________________________________________________________________________
 function confirmar(){
	if (comprobar_datos()){
		var cadenaip=document.fdatosocultos.cadenaip.value;
		var identificador=document.fdatosocultos.identificador.value;
		var nombrefuncion=document.fdatosocultos.nombrefuncion.value;
		var ejecutor=document.fdatosocultos.ejecutor.value;
		var tipotrama=document.fdatosocultos.tipotrama.value;
		var ambito=document.fdatosocultos.ambito.value;
		var idambito=document.fdatosocultos.idambito.value;
		var parametros="";
		var tagnuevasipes=document.fdatos.nuevasipes;
		if(tagnuevasipes.length>0)
			var nuevasipes=tagnuevasipes
		else{
			nuevasipes=new Array();
			nuevasipes[0]=tagnuevasipes
		}
		swenv=false
		for(var x=0;x<nuevasipes.length;x++){
				cadenaip=nuevasipes[x].value;
				var auxsplit=nuevasipes[x].getAttribute("id").split("_");
				var idconfiguracion=auxsplit[1]
				// Toma los datos de la tabla correspondiente a esa configuracion
				var oTABLE=document.getElementById("tb_particiones_"+idconfiguracion) 
				var oTRs=oTABLE.getElementsByTagName('TR') // Numero de particiones
				swenvio=oTABLE.value
				if(parseInt(swenvio)==0) continue; // Tabla  de particiones no modificada
				swenv=true
				var tbparticiones=new Array(9);
				for(var i=0;i<9;i++) tbparticiones[i]=null // Inicializa matriz
				for(var i=1;i<oTRs.length;i++){ // recorre TR's de las particiones
					var oTDs=oTRs[i].getElementsByTagName('TD') // Numero de particiones 
					var desplepar=oTDs[1].childNodes[0] // recupera el desplegable de particiones
					var despletipopar=oTDs[2].childNodes[0] // recupera el desplegable de tipo de accion
					var inputtama=oTDs[4].childNodes[1] // recupera el tama�
					var despleacc=oTDs[5].childNodes[1] // recupera el desplegable de accion
					var particion=desplepar.value
					var tipopart=despletipopar.value
					var sizepart=inputtama.value
					var accion=despleacc.value
					var idp=parseInt(particion)
					tbparticiones[idp]=particion+";"+tipopart+";"+sizepart+";"+accion+'%0A'
				}
				var particiones=""
				for(var i=0;i<9;i++){
					if(tbparticiones[i]!=null){
						particiones+=tbparticiones[i]
					}
				}
				parametros+="cadenaip="+cadenaip+'%0D'+"identificador="+identificador+'%0D'+"nombrefuncion="+nombrefuncion+'%0D'+"ejecutor="+ejecutor+'%0D'+"tipotrama="+tipotrama+'%0D'+"ambito="+ambito+'%0D'+"idambito="+idambito+'%0D'+"particiones="+particiones
				parametros+='%09';
		}
		if(swenv){
			var wurl="./gestores/gestor_Configurar.php"
			wurl+="?parametros="+parametros
			wurl+="&" +compone_urlejecucion();
			ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
			ifr.src=wurl; // LLama a la p�ina gestora
		}
	else
		alert(TbMsg[0]); 
	}
}
//________________________________________________________________________________________________________
  function cancelar(){
	alert(CTbMsg[0]);
	location.href="../nada.php"
  }
//________________________________________________________________________________________________________
  function comprobar_datos(){
	var tbconfigur=document.getElementById("tbconfigur") ;
	var tbidc=tbconfigur.value.split(";");
	for(var j=0;j<tbidc.length-1;j++){
		var oTABLE=document.getElementById("tb_particiones_"+tbidc[j]) 
		var oTRs=oTABLE.getElementsByTagName('TR')
		var tbp=new Array(9);
		var otbp=new Array(9);
		for(var i=0;i<9;i++){
			tbp[i]=0; // Inicializar matriz
			otbp[i]=null
		}
		for(var i=1;i<oTRs.length;i++){ // recorre TR's de las particiones
			var oTDs=oTRs[i].getElementsByTagName('TD')
			var desplepar=oTDs[1].childNodes[0]
			var p=desplepar.selectedIndex
			var wpar=desplepar.options[p].value
			if(tbp[wpar]==1){
				alert(TbMsg[1])
				desplepar.focus();
				return(false)
			}
			else{
				tbp[wpar]=1;
				otbp[wpar]=desplepar;
			}
			var inputtama=oTDs[4].childNodes[0]
			var tama=inputtama.value
			if (tama<=0){
				alert(TbMsg[2]);
				inputtama.focus();
				return(false)
			}
		}
		var swsw=false;
		for(var i=1;i<9;i++){ 
			if(i!=4){
				if (tbp[i]==0) swsw=true
				if (tbp[i]==1 && swsw){
					alert(TbMsg[3]);
					otbp[i].focus();
					return(false)
				}
			}
		}
	}
	return(comprobar_datosejecucion())
}
//________________________________________________________________________________________________________
function chgpar(o){
	var auxSplit=o.getAttribute("id").split("_");
	var despletipopar=document.getElementById("tipospar_"+auxSplit[1]+"_"+auxSplit[2]) 
	var despleacc=document.getElementById("acciones_"+auxSplit[1]+"_"+auxSplit[2]) 
	var littiposo=document.getElementById("tiposo_"+auxSplit[1]+"_"+auxSplit[2]) 
	var swenvio=document.getElementById("tb_particiones_"+auxSplit[2]) 
	var p=despletipopar.selectedIndex
	var tipopar=despletipopar.options[p].value
	switch(parseInt(tipopar)){
			case 0: // Sin particionar
				littiposo.innerHTML='&nbsp;<span style="COLOR:red"> Espacio sin particionar !!</span>&nbsp;';
				littiposo.value=0
				despleacc.selectedIndex=0
				break;
			case 1: // Bigdos
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Msdos,Windows 95</span>&nbsp;';
				littiposo.value=1
				despleacc.selectedIndex=1
				break;
			case  2: // FAt32
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Windows 98,Millenium</span>&nbsp;';
				littiposo.value=1
				despleacc.selectedIndex=1
				break;
			case 3: // NTFS
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Windows XP, Windows 2000, Windows 2003</span>&nbsp;';
				littiposo.value=1
				despleacc.selectedIndex=1
				break;
			case 4: //Linux Ext2
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Linux (Ext2)</span>&nbsp;';
				littiposo.value=1
				despleacc.selectedIndex=1
				break;
			case 5: //Linux Ext3
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Linux(Ext3)</span>&nbsp;';
				littiposo.value=1
				despleacc.selectedIndex=1
				break;
			case 6: //Linux Ext4
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Linux (Ext4)</span>&nbsp;';
				littiposo.value=1
				despleacc.selectedIndex=1
				break;
			case  7:
				littiposo.innerHTML='&nbsp;<span style="COLOR:blue">Linux swap</span>&nbsp;';
				littiposo.value=0
				despleacc.selectedIndex=0
				break;
			case  8:
				littiposo.innerHTML='&nbsp;<span style="COLOR:blue">Caché</span>&nbsp;';
				littiposo.value=1
				despleacc.selectedIndex=1
				break;
		}
		swenvio.value=1; // marca la partici� para ser tratada en el env� de trama  
}
//________________________________________________________________________________________________________
function chgtipopar(o){

	var auxSplit=o.getAttribute("id").split("_"); 
	var despleacc=document.getElementById("acciones_"+auxSplit[1]+"_"+auxSplit[2]) 
	var littiposo=document.getElementById("tiposo_"+auxSplit[1]+"_"+auxSplit[2]) 
	var swenvio=document.getElementById("tb_particiones_"+auxSplit[2]) 
	var p=o.selectedIndex
	var tipopar=o.options[p].value
	if(tipopar!=0 && tipopar!=7)
		despleacc.selectedIndex=1;
	else
		despleacc.selectedIndex=0;

switch(parseInt(tipopar)){
			case 0: // Bigdos
				littiposo.innerHTML='&nbsp;<span style="COLOR:red"> Espacio sin particionar !!</span>&nbsp;';
				break;
			case 1: // Bigdos
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Msdos,Windows 95</span>&nbsp;';
				break;
				littiposo.value=1
			case  2: // FAt32
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Windows 98,Millenium</span>&nbsp;';
				littiposo.value=1
				break;
			case 3: // NTFS
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Windows XP, Windows 2000, Windows 2003</span>&nbsp;';
				littiposo.value=1
				break;
			case 4: //Linux Ext2
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Linux (Ext2)</span>&nbsp;';
				littiposo.value=1
				break;
			case 5: //Linux Ext3
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Linux(Ext3)</span>&nbsp;';
				littiposo.value=1
				break;
			case 6: //Linux Ext4
				littiposo.innerHTML='&nbsp;<span style="COLOR:red">Linux (Ext4)</span>&nbsp;';
				littiposo.value=1
				break;
			case  7:
				littiposo.innerHTML='&nbsp;<span style="COLOR:blue">Linux swap</span>&nbsp;';
				littiposo.value=0
				break;
			case  8:
				littiposo.innerHTML='&nbsp;<span style="COLOR:blue">Caché</span>&nbsp;';
				littiposo.value=1
				break;
	
		}
	swenvio.value=1; // marca la partici� para ser tratada en el env� de trama  
}
//________________________________________________________________________________________________________
function chgtama(idc){
		var oTABLE=document.getElementById("tb_particiones_"+idc) 
		var oTRs=oTABLE.getElementsByTagName('TR') // Numero de particiones
		for(var i=1;i<oTRs.length;i++){ // recorre TR's de las particiones
			var oTDs=oTRs[i].getElementsByTagName('TD') // Numero de particiones 
			var despleacc=oTDs[5].childNodes[0] // recupera el desplegable de accion
			var desplepar=oTDs[2].childNodes[0] // recupera el desplegable de tipos departiciones
			if(desplepar.selectedIndex!=0 && desplepar.selectedIndex!=7){ // Si la particion no esta vacia
				despleacc.selectedIndex=1;
				var littiposo=oTDs[3].childNodes[0]
				littiposo.value=1 // Marca como forzamente formaeable esta paticion
				oTABLE.value=1; // marca la partici� para ser tratada en el env� de trama  
			}
		}
}
//________________________________________________________________________________________________________
function chgaccion(o){
	var auxSplit=o.getAttribute("id").split("_"); // Toma numero de particion
	var littiposo=document.getElementById("tiposo_"+auxSplit[1]+"_"+auxSplit[2]) 
	var despleacc=document.getElementById("acciones_"+auxSplit[1]+"_"+auxSplit[2]) 
	var despletipopar=document.getElementById("tipospar_"+auxSplit[1]+"_"+auxSplit[2]) 
	var swenvio=document.getElementById("tb_particiones_"+auxSplit[2]) 
	if(despletipopar.selectedIndex==0){
		alert(TbMsg[4]);
		o.selectedIndex=0
		return
	}
	if (littiposo.value==1){
		alert(TbMsg[5]);
		o.selectedIndex=1
	}
	if(despleacc.selectedIndex==2){
		if(despletipopar.selectedIndex>3)
			alert(TbMsg[6]);
	}
	if(despleacc.selectedIndex==3){
		if(despletipopar.selectedIndex>3)
			alert(TbMsg[7]);
	}
	swenvio.value=1; // marca la partici� para ser tratada en el env� de trama  
}
//________________________________________________________________________________________________________
function annadir_particion(idc){
	var oTABLE=document.getElementById("tb_particiones_"+idc) 
	var oTRs=oTABLE.getElementsByTagName('TR') // Numero de particiones
	if(parseInt(oTRs.length)>7){
		alert(TbMsg[8]);
		return;
	}
	oTABLE=document.getElementById("tabla_contenidoparticion_"+idc) 
	var oTDs=oTABLE.getElementsByTagName('TD') // LLega hasta TD ( punto de pivote )
	textHtml=oTDs[0].innerHTML     //  Toma la rama a sustituir

	oTABLE=document.getElementById("patron_contenidoparticion") 
	var wpatrontablaparticion=oTABLE.innerHTML     //  Toma la rama a sustituir
	oINPUT=document.getElementById("ultpa_"+idc) 
	var wultpa=parseInt(oINPUT.value);
	wultpa++;
	oINPUT.value=wultpa;
	ultpa=oINPUT.value;

	var re = new RegExp ('_upa_', 'gi') ; // Reemplaza partici� y configuraci�
	var rs =ultpa
	var patrontablaparticion = wpatrontablaparticion.replace(re,rs) ;
	wpatrontablaparticion=patrontablaparticion
	var re = new RegExp ('_cfg_', 'gi') ;  // Reemplaza configuraci�
	var rs =idc
	var patrontablaparticion = wpatrontablaparticion.replace(re,rs) ;
	posb=textHtml.length
	for (var posa=posb;posa>=0;posa--) {
		if ("</TR>" == textHtml.substr(posa,5))	break; // Retrocede buscando etiqueta </TR>
	 }
	var nwrama=textHtml.substr(0,posa+5) // Primer trozo
	nwrama+=patrontablaparticion
	nwrama+=textHtml.substr(posa,textHtml.length-posa) // Segundo trozo
	oTDs[0].innerHTML=nwrama;
	var oDESPLE=document.getElementById("numpar_"+ultpa+"_"+idc)  // Selecciona item creado
	var ise=wultpa-1
	if (ise>3 && ise<7) ise-=1
	if(ise>6) ise=6
	oDESPLE.selectedIndex=ise
}
//________________________________________________________________________________________________________
function elimina_particion(o,idc){
	oTABLE=document.getElementById("tabla_contenidoparticion_"+idc) 
	oTDs=oTABLE.getElementsByTagName('TD') // LLega hasta TD ( punto de pivote )
	textHtml=oTDs[0].innerHTML     //  Toma la rama a sustituir
	var patron=o.getAttribute("id") 
	var re = new RegExp (patron, 'gi') ;
	var pos=textHtml.search(patron)
	for (var posa=pos;posa>=0;posa--) {
		if ("<TR" == textHtml.substr(posa,3))	break; // Retrocede buscando etiqueta <TR>
	 }
	for (var posb=pos;posb<textHtml.length;posb++) { // Avanza buscando etiqueta </TR>
		if ("</TR>" == textHtml.substr(posb,5))	break;
	 }
	 posb+=5
	var nwrama=textHtml.substr(0,posa) // Primer trozo
	nwrama+=textHtml.substr(posb,textHtml.length-posb) // Segundo trozo
	oTDs[0].innerHTML=nwrama;

	var swenvio=document.getElementById("tb_particiones_"+idc) 
	swenvio.value=1; // marca la partici� para ser tratada en el env� de trama  
}
//________________________________________________________________________________________________________
//	
//	Comprobar retorno
//________________________________________________________________________________________________________
function resultado_Configurar(resul){
	if (!resul){
		alert(CTbMsg[1]);	
		return
	}
	alert(CTbMsg[2]);
}
//________________________________________________________________________________________________________
	function resalta(o,idc){
		currentconfiguracion=idc
		if(currentimgconfiguracion!=null)
			currentimgconfiguracion.src="../images/iconos/configuraciones.gif"
		currentimgconfiguracion=o;
		o.src="../images/iconos/configuraciones_ON.gif"
		menu_contextual(o,'flo_configuraciones'); 
	}
//________________________________________________________________________________________________________
function chgtotal(op){
	idc=currentconfiguracion
	var oTABLE=document.getElementById("tb_particiones_"+idc) 
	var oTRs=oTABLE.getElementsByTagName('TR') // Numero de particiones
	for(var i=1;i<oTRs.length;i++){ // recorre TR's de las particiones
		var oTDs=oTRs[i].getElementsByTagName('TD') // Numero de particiones 
		var despleacc=oTDs[5].childNodes[0] // recupera el desplegable de accion
		var despletipopar=oTDs[2].childNodes[0] // recupera el desplegable de tipos de particiones
		var littiposo=oTDs[3].childNodes[0]
		if(despletipopar.selectedIndex==0 || despletipopar.selectedIndex==5) // partici� est�vac�o es swap no puede llevarse a cabo ningn tipo de acci� sobre ella
			continue
		if (littiposo.value==1) // Est�partici� debe ser necesariamente formateada porque se ha cambiado el S.O. 
			continue
		if(op==2){ // No tiene sentido ocultar esta partici� al no tratarse de un sistema Windows;
			if(despletipopar.selectedIndex>3)
				continue
		}
		if(op==3){ //  No tiene sentido mostrar esta partici� al no tratarse de un sistema Windows;
			if(despletipopar.selectedIndex>3)
			continue
		}
		despleacc.selectedIndex=op; // Coloca la acci� en el desplegable
		oTABLE.value=1; // marca la partici� para ser tratada en el env� de trama  
	}
}