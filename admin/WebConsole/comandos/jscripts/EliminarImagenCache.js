// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Nombre del fichero: EliminarImagenCache.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero IniciarSesion.php (Comandos)
// *************************************************************************************************************************************************
function confirmar(){
	if (comprobar_datos()){
	 	// Compone atributos del comando
		tb_conf=document.getElementById("tabla_conf");
		var ochecks=tb_conf.getElementsByTagName('INPUT')
		var atributos;
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked){
				atributos=ochecks[i].value
			}
		}
		var RC='@';
		document.fdatosejecucion.atributos.value="scp="+atributos+RC;
		document.fdatosejecucion.submit();
	}
 }
//________________________________________________________________________________________________________
  function cancelar()
{
	alert(CTbMsg[0]);
	location.href="../nada.php"
  }
//________________________________________________________________________________________________________
  function comprobar_datos()
{
		tb_conf=document.getElementById("tabla_conf");
		var ochecks=tb_conf.getElementsByTagName('INPUT')
		var op=0
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked)		op++;
		}
		if(op==0){
			   alert(TbMsg[1])
			 return(false);
		}
		return(comprobar_datosejecucion())
}

