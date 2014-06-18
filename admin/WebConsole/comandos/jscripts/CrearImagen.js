// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: CrearImagen.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero CrearImagen.php (Comandos)
// *************************************************************************************************************************************************
 function confirmar()
 {
	if (!comprobar_datos()) return;
	
	var RC="@";
	// UHU - Ahora puede ser cualquier disco
	var disco;
	var atributos="";
	
	var tb_conf=document.getElementById("tabla_conf");
	var ochecks=tb_conf.getElementsByTagName('INPUT')
	for(var i=0;i<ochecks.length;i++){
		if(ochecks[i].checked){
			var parcod=ochecks[i].value.split("_");
			atributos+="dsk="+parcod[0]+RC; // Número de disco
			atributos+="par="+parcod[1]+RC; // Número de partición
			atributos+="cpt="+parcod[2]+RC;	// Código de partición				
			
			var despleimagen=document.getElementById("despleimagen_"+parcod[0]+"_"+parcod[1]);
			var imgcan=despleimagen.value.split("_");
			atributos+="idi="+imgcan[0]+RC; // Identificador de la imagen
			atributos+="nci="+imgcan[1]+RC;	// Nonbre canónico		
		
			var desplerepositorios=document.getElementById("desplerepositorios_"+parcod[0]+"_"+parcod[1]);
			var idx=desplerepositorios.selectedIndex;
			atributos+="ipr="+desplerepositorios.options[idx].value+RC;
			break;		
		}
	}
	document.fdatosejecucion.atributos.value=atributos;
	document.fdatosejecucion.submit();
}
//________________________________________________________________________________________________________
  function cancelar(){
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
			if(ochecks[i].checked){
				op++;
				// Los checks tienen en su nombre disco_nparticion_idparticion (Ej. 1_1_7 -> disco 1, part 1, NTFS
				var disco=ochecks[i].value.split("_")[0];
				var particion=ochecks[i].value.split("_")[1];
				// El desplegable de imagen ahora indica disco y particion en su nombre
				despleimagen=document.getElementById("despleimagen_"+disco+"_"+particion);
				var  p=despleimagen.selectedIndex
				if (p==0){  
					alert(TbMsg[0])
				 	despleimagen.focus()
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

