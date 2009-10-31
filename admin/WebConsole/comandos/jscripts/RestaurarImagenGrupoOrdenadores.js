// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla

// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: RestaurarImagenGrupoOrdenadores.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero RestaurarImagenGrupoOrdenadores.php (Comandos)
// *************************************************************************************************************************************************
//___________________________________________________________________________________________________________
//	
//	Cancela la edición 
//___________________________________________________________________________________________________________
  function cancelar(){
	alert(CTbMsg[0]);
	location.href="../nada.php"
  }
//___________________________________________________________________________________________________________
//	
//	Esta función desabilita la marca de un checkbox en opcion "bajas"
//___________________________________________________________________________________________________________
 function desabilita(o) {
	var b
    b=o.checked
    o.checked=!b
 }
//___________________________________________________________________________________________________________
//	
//	Confirma la edición 
//___________________________________________________________________________________________________________
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
		for(var x=0;x<nuevasipes.length;x++){
				cadenaip=nuevasipes[x].value;
				var auxsplit=nuevasipes[x].getAttribute("id").split("_");
				var idperfilhard=auxsplit[1]
				var idparticion=auxsplit[2]
				var ochecks=document.fdatos.getElementsByTagName("INPUT")
				var perfiles=""
				var pathrmb="";
				for(var i=0;i<ochecks.length;i++){
							if(ochecks[i].checked){
								var particion=ochecks[i].value
								var valparticion=particion.split("_");
								var widperfilhard=valparticion[1]
								var widparticion=valparticion[2]
								if(idperfilhard==widperfilhard && idparticion==widparticion){
									var desple_M=document.getElementById("desple_M_"+particion);
									var desple_O=document.getElementById("desple_O_"+particion);
									var  p_M=desple_M.selectedIndex
									var  p_O=desple_O.selectedIndex
									if(p_M>0)
										perfiles+=valparticion[0]+"_M_"+desple_M.value+";"
									if(p_O>0)
										perfiles+=valparticion[0]+"_O_"+desple_O.value+";"
									var opathrmb=document.getElementById("pathrmb_"+particion);
									pathrmb+=opathrmb.value+";";
								}
						}
				}
				if(perfiles!=""){
					parametros+="cadenaip="+cadenaip+'%0D'+"identificador="+identificador+'%0D'+"nombrefuncion="+nombrefuncion+'%0D'+"ejecutor="+ejecutor+'%0D'+"tipotrama="+tipotrama+'%0D'+"ambito="+ambito+'%0D'+"idambito="+idambito+'%0D'+"pathrmb="+pathrmb+'%0D'+"perfiles="+perfiles
					parametros+='%09';
				}
		}
		var wurl="./gestores/gestor_RestaurarImagenGrupoOrdenadores.php"
		wurl+="?parametros="+parametros
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
			var valparticion=particion.split("_");
			var  p_M=desple_M.selectedIndex
			var  p_O=desple_O.selectedIndex
			if (p_M==0 && p_O==0){  
		     alert(TbMsg[0]+valparticion[0])
			 desple_M.focus()
	         return(false)
			}
			var desple_path=document.getElementById("pathrmb_"+particion);
			var  p=desple_path.selectedIndex
			if(p<1) {
				alert(TbMsg[5]+valparticion[0])
				 return(false);
			}
			if (p_M>0 && p_O>0){  
		     alert(TbMsg[1]+valparticion[0])
			 desple_O.focus()
	         return(false)
			}
			if (p_O>0){  
			     var resul=confirm(TbMsg[2]+valparticion[0]+"."+ TbMsg[3])
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
function resultado_RestaurarImagenGrupoOrdenadores(resul){
	if (!resul){
		alert(CTbMsg[1]);	
		return
	}
	alert(CTbMsg[2]);
}
