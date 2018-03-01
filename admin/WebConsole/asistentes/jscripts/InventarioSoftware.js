// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: InventarioHardware.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero InventarioHardware.php (Comandos)
// *************************************************************************************************************************************************
 function confirmar(){
	if (comprobar_datos()){
		tb_conf=document.getElementById("tabla_conf");
		var ochecks=tb_conf.getElementsByTagName('INPUT');
		var particion;
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked){
				particion=ochecks[i].value
			}
		}
		var RC="@";
		var disco=1; // Siempre disco 1
		document.fdatosejecucion.atributos.value="dsk="+disco+RC+"par="+particion+RC;
		document.fdatosejecucion.submit();
	}
 }
//________________________________________________________________________________________________________
  function cancelar(){
	alert(CTbMsg[0]);
	location.href="../nada.php"
  }
//________________________________________________________________________________________________________
  function comprobar_datos(){
		tb_conf=document.getElementById("tabla_conf");
		var ochecks=tb_conf.getElementsByTagName('INPUT');
		var op=0;
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked)		op++;
		}
		if(op==0){
			   alert(TbMsg[1]);
			 return(false);
		}
		return(comprobar_datosejecucion());
}
