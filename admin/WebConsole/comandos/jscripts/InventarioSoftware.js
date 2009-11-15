// *************************************************************************************************************************************************
//	Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creació�:2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: InventarioHardware.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero InventarioHardware.php (Comandos)
// *************************************************************************************************************************************************
 function confirmar(){
	if (comprobar_datos()){
		var tb_conf=document.getElementById("tabla_conf");
		var ochecks=tb_conf.getElementsByTagName('INPUT')
		var particiones=""
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked){
				var particion=ochecks[i].value
				particiones+=particion+";"			
			}
		}
		var wurl="./gestores/gestor_InventarioSoftware.php"
		wurl+="?particiones="+particiones+"&"+compone_urlejecucion();
		ifr=document.getElementById("iframes_comodin"); // Toma objeto Iframe
		ifr.src=wurl; // LLama a la p�ina gestora
	}
 }
//________________________________________________________________________________________________________
  function cancelar(){
	alert(CTbMsg[0]);
	location.href="../nada.php"
  }
//________________________________________________________________________________________________________
  function comprobar_datos(){
		return(comprobar_datosejecucion())
}
//________________________________________________________________________________________________________
//	
//	Comprobar retorno
//________________________________________________________________________________________________________
function resultado_inventariosoftware(resul){
	if (!resul){
		alert(CTbMsg[1]);	
		return
	}
	alert(CTbMsg[2]);	
}
