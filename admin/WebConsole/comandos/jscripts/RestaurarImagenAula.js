// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla

// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: RestaurarImagenAula.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero RestaurarImagenAula.php (Comandos)
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
//	Esta función desabilita la marca de un checkbox en opcion "bajas"
//________________________________________________________________________________________________________
 function desabilita(o) {
	var b
    b=o.checked
    o.checked=!b
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
				var protclona="";
				for(var i=0;i<ochecks.length;i++){
							if(ochecks[i].checked){
								var particion=ochecks[i].value
								var valparticion=particion.split("_");
								var widperfilhard=valparticion[1]
								var widparticion=valparticion[2]
								if(idperfilhard==widperfilhard && idparticion==widparticion){
									var desple_M=document.getElementById("desple_M_"+particion);
									var  p_M=desple_M.selectedIndex
									if(p_M>0)
										perfiles+=valparticion[0]+"_M_"+desple_M.value+";"
									var opathrmb=document.getElementById("pathrmb_"+particion);
									pathrmb+=opathrmb.value+";";
									var protclon=document.getElementById("protoclonacion_"+particion);
									protclona+=protclon.value+";";
								}
						}
				}
				if(perfiles!=""){
					parametros+="cadenaip="+cadenaip+'%0D'+"identificador="+identificador+'%0D'+"nombrefuncion="+nombrefuncion+'%0D'+"ejecutor="+ejecutor+'%0D'+"tipotrama="+tipotrama+'%0D'+"ambito="+ambito+'%0D'+"idambito="+idambito+'%0D'+"pathrmb="+pathrmb+'%0D'+"protclona="+protclona+'%0D'+"perfiles="+perfiles
					parametros+='%09';
				}
		}
		var wurl="./gestores/gestor_RestaurarImagenAula.php"
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
//________________________________________________________________________________________________________
//	
//	Marcar automaticamente los check box 
//________________________________________________________________________________________________________
function marcar(desple,id){
		var  p=desple.selectedIndex
		if(p>0){
			var casilla=document.getElementById("particion_"+id);
			casilla.checked=true;
		}
		var desplepath=document.getElementById("pathrmb_"+id);
		var  p=desplepath.selectedIndex
		if(p<1){
			desplepath.selectedIndex=1
		}
}
//________________________________________________________________________________________________________
//	
//	Comprobar_datos 
//________________________________________________________________________________________________________
function comprobar_datos(){
	var ochecks=document.fdatos.getElementsByTagName("INPUT")
	var op=0
	for(var i=0;i<ochecks.length;i++){
		if(ochecks[i].checked){
			op++;
			var particion=ochecks[i].value
			var desple_M=document.getElementById("desple_M_"+particion);
			var valparticion=particion.split("_");
			var  p_M=desple_M.selectedIndex
			if (p_M==0){  
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
			var desple_P=document.getElementById("protoclonacion_"+particion);
			if(desple_P.value=="TORRENT" || desple_P.value=="MULTICAST"){
				if(desple_path.value!=1)
					alert(TbMsg[6]+particion) // Debe existir caché
			}
		}
	}
	if(op==0){
	     alert(TbMsg[4])
		 return(false);
	}
	return(comprobar_datosejecucion())
}
//________________________________________________________________________________________________________
//	
//	Comprobar retorno
//________________________________________________________________________________________________________
function resultado_RestaurarImagenAula(resul){
	if (!resul){
		alert(CTbMsg[1]);	
		return
	}
	alert(CTbMsg[2]);
}
