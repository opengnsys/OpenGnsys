// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: RestaurarImagenOrdenador.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero RestaurarImagenOrdenador.php (Comandos)
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
//	
//	Cancela la edición 
//________________________________________________________________________________________________________
  function cancelar(){
	alert(CTbMsg[0]);
	location.href="../nada.php"
  }
//________________________________________________________________________________________________________
//	
//	Confirma la edición 
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
		var ochecks=document.fdatos.getElementsByTagName("INPUT")
		var perfiles=""
		var pathrmb="";
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked){
				var particion=ochecks[i].value
				var desple_M=document.getElementById("desple_M_"+particion);
				var desple_O=document.getElementById("desple_O_"+particion);
				var  p_M=desple_M.selectedIndex
				var  p_O=desple_O.selectedIndex
				if(p_M>0)
					perfiles+=particion+"_M_"+desple_M.value+";"
				if(p_O>0)
					perfiles+=particion+"_O_"+desple_O.value+";"
				var opathrmb=document.getElementById("pathrmb_"+particion);
				pathrmb+=opathrmb.value+";";
			}
		}
		var wurl="./gestores/gestor_RestaurarImagenOrdenador.php"
		wurl+="?cadenaip="+cadenaip+"&identificador="+identificador+"&nombrefuncion="+nombrefuncion+"&ejecutor="+ejecutor+"&tipotrama="+tipotrama+"&ambito="+ambito+"&idambito="+idambito+"&pathrmb="+pathrmb+'%0D'+"&perfiles="+perfiles
		wurl+="&" +compone_urlejecucion();
		ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		ifr.src=wurl; // LLama a la página gestora
	}
}
//________________________________________________________________________________________________________
//	
//	seleccionar automaticamente las particiones
//________________________________________________________________________________________________________
function seleccionar(particion){

		var desplepath=document.getElementById("pathrmb_"+particion);
		var  p=desplepath.selectedIndex
		if(p<1){
			desplepath.selectedIndex=1
		}
}
//___________________________________________________________________________________________________________
//	
//	Marcar automaticamente los check box 
//___________________________________________________________________________________________________________
function marcar(desple,particion){
		var casilla=document.getElementById("particion_"+particion);
		var  p=desple.selectedIndex
		if(p>0)
			casilla.checked=true;

		var desplepath=document.getElementById("pathrmb_"+particion);
		var  p=desplepath.selectedIndex
		if(p<1){
			desplepath.selectedIndex=1
		}
}
//___________________________________________________________________________________________________________
//	
//	Comprobar_datos 
//___________________________________________________________________________________________________________
function comprobar_datos(){
	var ochecks=document.fdatos.getElementsByTagName("INPUT")
	var op=0
	for(var i=0;i<ochecks.length;i++){
		if(ochecks[i].checked){
			op++;
			var particion=ochecks[i].value
			var desple_M=document.getElementById("desple_M_"+particion);
			var desple_O=document.getElementById("desple_O_"+particion);
			var  p_M=desple_M.selectedIndex
			var  p_O=desple_O.selectedIndex
			if (p_M==0 && p_O==0){  
		     alert(TbMsg[0]+particion)
			 desple_M.focus()
	         return(false)
			}
			var desple_path=document.getElementById("pathrmb_"+particion);
			var  p=desple_path.selectedIndex
			if(p<1) {
				alert(TbMsg[5]+particion)
				 return(false);
			}
			if (p_M>0 && p_O>0){  
		     alert(TbMsg[1]+particion)
			 desple_O.focus()
	         return(false)
			}
			if (p_O>0){  
 			     var resul=confirm(TbMsg[2]+particion+"."+ TbMsg[3])
				 desple_M.focus()
			      if(!resul)
					 return(false)
			}
		}
	}
	if(op==0){
	     alert(TbMsg[4])
		 return(false);
	}
	return(comprobar_datosejecucion())
}
//___________________________________________________________________________________________________________
//	
//	Comprobar retorno
//___________________________________________________________________________________________________________
function resultado_RestaurarImagenOrdenador(resul){
	if (!resul){
		alert(CTbMsg[1]);	
		return
	}
	alert(CTbMsg[2]);
}
