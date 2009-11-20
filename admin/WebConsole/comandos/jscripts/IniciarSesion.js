// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: IniciarSesion.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero IniciarSesion.php (Comandos)
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
		var particion;
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked){
				particion=ochecks[i].value
			}
		}
		var wurl="./gestores/gestor_IniciarSesion.php"
		wurl+="?cadenaip="+cadenaip+"&identificador="+identificador+"&nombrefuncion="+nombrefuncion+"&ejecutor="+ejecutor+"&tipotrama="+tipotrama+"&ambito="+ambito+"&idambito="+idambito+"&particion="+particion
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
		}
	}
	if(op==0){
	     alert(TbMsg[1])
		 return(false);
	}
	return(true)
}
//________________________________________________________________________________________________________
//	
//	Comprobar retorno
//________________________________________________________________________________________________________
function resultado_iniciarsesion(resul){
	if (!resul){
		alert(CTbMsg[1]);	
		return
	}
	alert(CTbMsg[2]);
}
