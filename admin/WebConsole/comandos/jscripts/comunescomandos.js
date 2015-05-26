// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: comunescomandos.js
// Descripción : 
//		Este fichero implementa las funciones javascript comunes a todos los comandos
// *************************************************************************************************************************************************
  function comprobar_datosejecucion(){
	/* Comprobación de las opciones de ejecución  */
	var sw_ejya=document.fdatosejecucion.sw_ejya.checked;
	var sw_ejprg=document.fdatosejecucion.sw_ejprg.checked;

	var sw_seguimientocon=document.fdatosejecucion.sw_seguimiento[0].checked;
	var sw_seguimientosin=document.fdatosejecucion.sw_seguimiento[1].checked;

	var sw_mkprocedimiento=document.fdatosejecucion.sw_mkprocedimiento.checked;
	var sw_nuevaprocedimiento=document.fdatosejecucion.sw_procedimiento[0].checked;
	var descripcion_nuevaprocedimiento=document.fdatosejecucion.nombreprocedimiento.value;
	var sw_procedimientoexistente=document.fdatosejecucion.sw_procedimiento[1].checked;

	var sw_mktarea=document.fdatosejecucion.sw_mktarea.checked;
	var sw_nuevatarea=document.fdatosejecucion.sw_tarea[0].checked;
	var descripcion_nuevatarea=document.fdatosejecucion.nombretarea.value;
	var sw_tareaexistente=document.fdatosejecucion.sw_tarea[1].checked;

	var  pprocedimiento=document.fdatosejecucion.idprocedimiento.selectedIndex
	var  ptarea=document.fdatosejecucion.idtarea.selectedIndex

	if(!sw_ejya && !sw_ejprg && !sw_mkprocedimiento && !sw_mktarea  ){
		//alert("ATENCIÓN.- Debe elegir al menos un modo de ejecución");
		alert(CTbMsg[8]);
		return(false);
	}

	// Cuestión procedimiento ---------------------------------------------------------------------
	
	if(sw_ejya){
		if(!sw_seguimientocon && !sw_seguimientosin){
			//alert("ATENCIÓN.- Debe elegir un modo de ejecución inmediata");
			alert(CTbMsg[9]);
			return(false);
		}
	}
	// Cuestión procedimiento -----------------------------------------------------------------------
	if(sw_mkprocedimiento){
		if(!sw_nuevaprocedimiento && !sw_procedimientoexistente){
			//alert("ATENCIÓN.- Debe elegir un modo de inclusión en procedimiento de este comando");
			alert(CTbMsg[10]);
			return(false);
		}
		if(sw_nuevaprocedimiento && descripcion_nuevaprocedimiento==""){
			//alert("ATENCIÓN.- Debe especificar el nombre del nuevo procedimiento que se creará y al que se añadirá este comando");
			alert(CTbMsg[11]);
			document.fdatosejecucion.nombreprocedimiento.focus();
			return(false);
		}
		if(sw_procedimientoexistente && pprocedimiento==0){
			//alert("ATENCIÓN.- Debe elegir el procedimiento al que se añadirá este comando");
			alert(CTbMsg[12]);
			document.fdatosejecucion.idprocedimiento.focus();
			return(false);
		}
	}
	
	// Cuestión tarea ------------------------------------------------------------------------------------------------------------------------------
	if(sw_mktarea){
		if(!sw_nuevatarea && !sw_tareaexistente){
			//alert("ATENCIÓN.- Debe elegir un modo de inclusión en tarea ejecutable, de este comando");
			alert(CTbMsg[13]);
			return(false);
		}
		if(sw_nuevatarea && descripcion_nuevatarea==""){
			//alert("ATENCIÓN.- Debe especificar el nombre de la nueva tarea ejecutable que se creará y a la que se añadirá este comando");
			alert(CTbMsg[14]);
			document.fdatosejecucion.nombretarea.focus();
			return(false);
		}
		if(sw_tareaexistente && ptarea==0){
			//alert("ATENCIÓN.- Debe elegir la tarea a la que se añadirá este comando");
			alert(CTbMsg[15]);
			document.fdatosejecucion.idtarea.focus();
			return(false);
		}
	}
	//-----------------------------------------------------------------------------------------------------------------------------------------------------
	return(true)
}
//____________________________________________________________________________
function	clic_mktarea(o){
	if(!o.checked){
		document.fdatosejecucion.sw_tarea[0].checked=false;
		document.fdatosejecucion.nombretarea.value="";
		document.fdatosejecucion.sw_tarea[1].checked=false;
		document.fdatosejecucion.idtarea.selectedIndex=0;
	}else{
		// Avisar si el código incluye reinicio o apagado.
		if (typeof document.fdatos.codigo !== undefined) {
			if (document.fdatos.codigo.value.match(/(poweroff|reboot)/)) {
				// AVISO: si el código incluye reinicio o apagado, puede provocar que el cliente no inicie correctamente.
				alert(CTbMsg[16]);
			}
		}
	}
}
function	clic_nwtarea(o){
	if(o.checked){
		document.fdatosejecucion.sw_mktarea.checked=true;
		document.fdatosejecucion.sw_tarea[1].checked=false;
		document.fdatosejecucion.idtarea.selectedIndex=0;
	}
}
function	clic_extarea(o){
	if(o.checked){
		document.fdatosejecucion.sw_mktarea.checked=true;
		document.fdatosejecucion.sw_tarea[0].checked=false;
		document.fdatosejecucion.nombretarea.value="";
	}
}
function	clic_nomtarea(o){
	document.fdatosejecucion.sw_mktarea.checked=true;
	document.fdatosejecucion.sw_tarea[0].checked=true;
	document.fdatosejecucion.idtarea.selectedIndex=0;
}
function	clic_mkprocedimiento(o){
	if(!o.checked){
		document.fdatosejecucion.sw_procedimiento[0].checked=false;
		document.fdatosejecucion.nombreprocedimiento.value="";
		document.fdatosejecucion.sw_procedimiento[1].checked=false;
		document.fdatosejecucion.idprocedimiento.selectedIndex=0;
	}else{
		// Avisar si el código incluye reinicio o apagado.
		if (typeof document.fdatos.codigo !== undefined) {
			if (document.fdatos.codigo.value.match(/(poweroff|reboot)/)) {
				// AVISO: si el código incluye reinicio o apagado, puede provocar que el cliente no inicie correctamente.
				alert(CTbMsg[16]);
			}
		}
	}
}
function	clic_nwprocedimiento(o){
	if(o.checked){
		document.fdatosejecucion.sw_mkprocedimiento.checked=true;
		document.fdatosejecucion.sw_procedimiento[1].checked=false;
		document.fdatosejecucion.idprocedimiento.selectedIndex=0;
	}
}
function	clic_exprocedimiento(o){
	if(o.checked){
		document.fdatosejecucion.sw_mkprocedimiento.checked=true;
		document.fdatosejecucion.sw_procedimiento[0].checked=false;
		document.fdatosejecucion.nombreprocedimiento.value="";
	}
}
function	clic_nomprocedimiento(o){
	document.fdatosejecucion.sw_mkprocedimiento.checked=true;
	document.fdatosejecucion.sw_procedimiento[0].checked=true;
	document.fdatosejecucion.idprocedimiento.selectedIndex=0;
}

function	procedimientoexistente(o){
	document.fdatosejecucion.sw_mkprocedimiento.checked=true;
	document.fdatosejecucion.sw_procedimiento[1].checked=true;
	document.fdatosejecucion.nombreprocedimiento.value="";
}

function	tareaexistente(o){
		document.fdatosejecucion.sw_mktarea.checked=true;
		document.fdatosejecucion.sw_tarea[1].checked=true;
		document.fdatosejecucion.nombretarea.value="";
}
//____________________________________________________________________________
function compone_urlejecucion(){

		var wurl="" 

		var sw_ejya=document.fdatosejecucion.sw_ejya.checked;
		var sw_seguimiento=document.fdatosejecucion.sw_seguimiento[0].checked;

		var sw_ejprg=document.fdatosejecucion.sw_ejprg.checked;
		
		var sw_mkprocedimiento=document.fdatosejecucion.sw_mkprocedimiento.checked;
		if (document.fdatosejecucion.sw_procedimiento[0].checked){
			var nwidprocedimiento=0
			var nwdescriprocedimiento=document.fdatosejecucion.nombreprocedimiento.value;
		}
		else{
			var  p=document.fdatosejecucion.idprocedimiento.selectedIndex
			var nwidprocedimiento=document.fdatosejecucion.idprocedimiento.options[p].value
			var nwdescriprocedimiento=document.fdatosejecucion.idprocedimiento.options[p].text
		}

		var sw_mktarea=document.fdatosejecucion.sw_mktarea.checked;
		if (document.fdatosejecucion.sw_tarea[0].checked){
			var nwidtarea=0
			var nwdescritarea=document.fdatosejecucion.nombretarea.value;
		}
		else{
			var  p=document.fdatosejecucion.idtarea.selectedIndex
			var nwidtarea=document.fdatosejecucion.idtarea.options[p].value
			var nwdescritarea=document.fdatosejecucion.idtarea.options[p].text
		}
		wurl+="sw_ejya="+sw_ejya +"&sw_seguimiento="+sw_seguimiento+"sw_ejprg="+sw_ejprg+"&sw_mktarea="+sw_mktarea+"&nwidtarea="+nwidtarea+"&nwdescritarea="+nwdescritarea
		wurl+="&sw_mkprocedimiento="+sw_mkprocedimiento+"&nwidprocedimiento="+nwidprocedimiento+"&nwdescriprocedimiento="+nwdescriprocedimiento
		return(wurl)
}
//________________________________________________________________________________________________________
//	
//	Resultado ejecución de un comando
//________________________________________________________________________________________________________
function resultado_comando(resul){
		alert(CTbMsg[resul]);	
}
