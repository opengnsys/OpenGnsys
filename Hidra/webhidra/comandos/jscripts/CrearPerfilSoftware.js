// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005 Jos�Manuel Alonso. Todos los derechos reservados.
// Fecha Creaci�:2003-2004
// Fecha �tima modificaci�: Marzo-2005
// Nombre del fichero: CrearPerfilSoftware.js
// Descripci� : 
//		Este fichero implementa las funciones javascript del fichero CrearPerfilSoftware.php (Comandos)
// *************************************************************************************************************************************************
function confirmar(){
	if (comprobar_datos()){
		var cadenaip=document.fdatos.cadenaip.value;
		var identificador=document.fdatos.identificador.value;
		var nombrefuncion=document.fdatos.nombrefuncion.value;
		var ejecutor=document.fdatos.ejecutor.value;
		var tipotrama=document.fdatos.tipotrama.value;
		var ambito=document.fdatos.ambito.value;
		var idambito=document.fdatos.idambito.value;
		tb_conf=document.getElementById("tabla_conf");
		var ochecks=tb_conf.getElementsByTagName('INPUT')
		var perfiles=""
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked){
				var particion=ochecks[i].value
				desple=document.getElementById("desple_"+particion);
				perfiles+=particion+"_"+desple.value+";"			
			}
		}
		var wurl="./gestores/gestor_CrearPerfilSoftware.php"
		wurl+="?cadenaip="+cadenaip+"&identificador="+identificador+"&nombrefuncion="+nombrefuncion+"&ejecutor="+ejecutor+"&tipotrama="+tipotrama+"&ambito="+ambito+"&idambito="+idambito+"&perfiles="+perfiles
		wurl+="&" +compone_urlejecucion();
		ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		ifr.src=wurl; // LLama a la p�ina gestora
	}
}
//________________________________________________________________________________________________________
//	
//	Cancela la edici� 
//________________________________________________________________________________________________________
  function cancelar(){
	alert(CTbMsg[0]);
	location.href="../nada.php"
  }
//________________________________________________________________________________________________________
//	
//	Comprobar_datos 
//________________________________________________________________________________________________________
function comprobar_datos(){
	tb_conf=document.getElementById("tabla_conf");
	var ochecks=tb_conf.getElementsByTagName('INPUT')
	var op=0
	for(var i=0;i<ochecks.length;i++){
		if(ochecks[i].checked){
			op++;
			var particion=ochecks[i].value
			desple=document.getElementById("desple_"+particion);
			var  p=desple.selectedIndex
			if (p==0){  
		     alert(TbMsg[0])
			 desple.focus()
	         return(false)
			}
		}
	}
	if(op==0){
	     alert(TbMsg[1])
		 return(false);
	}
	return(comprobar_datosejecucion())
}
//________________________________________________________________________________________________________
//	
//	Comprobar retorno
//________________________________________________________________________________________________________
function resultado_crearperfilsoftware(resul){
	if (!resul){
		alert(CTbMsg[1]);	
		return
	}
	alert(CTbMsg[2]);
}
