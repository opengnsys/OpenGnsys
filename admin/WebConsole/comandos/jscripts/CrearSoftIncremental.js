// **************************************************************************************************************
//	Libreria de scripts de Javascript
// Copyright 2003-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación:2003-2004
// Fecha última modificación: Marzo-2005
// Nombre del fichero: CrearSoftIncremental.js
// Descripción:
//		Este fichero implementa las funciones javascript del fichero CrearSoftIncremental.php (Comandos)
// **************************************************************************************************************

 var RC="@";

 function confirmar()
 {
 	var prm=comprobar_datos();
	if(prm==="") return; // Ha habido algún error
 	
	var disco=1; // Siempre disco 1
	document.fdatosejecucion.atributos.value="dsk="+disco+RC+prm;
	document.fdatosejecucion.submit();
}
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
//	Comprobar_datos 
//________________________________________________________________________________________________________

function comprobar_datos()
{
	var prm=""; // Retorno parámetros
	var cadPar=document.getElementById("cadPar").getAttribute("value");
	var tbPar=cadPar.split(";");
	for(var i=0;i<tbPar.length;i++){
		var par=tbPar[i]; // Numero de partición
		if(par>0){ 
			var trObj=document.getElementById('trPar-'+par); // Recupera objeto fila
			var obRDO=trObj.childNodes[0].childNodes[0]; // Recupera Radio buton de la fila
			if(obRDO.checked){ // Si está seleccionado ...
				var obSel=trObj.childNodes[3].childNodes[0]; // Recupera  objeto select de la Imagen
				var idx=obSel.selectedIndex;
				if(idx===0){ // No ha seleccionado indice en el desplegable imagen
					alert(TbMsg[3]);
					return(false);	
				}			
				var tbIMG=obSel.options[idx].value.split(";");
				var idi=tbIMG[0]; // Identificador de la imagen básica
				var nci=tbIMG[1]; // Nombre canónico de la imagen básica
				var ipr=tbIMG[2]; // Ip del repositorio de la imagen
				var idf=tbIMG[3]; // NIdentificador de la imagen incremental
				var ncf=tbIMG[4]; // Nombre canónico de la imagen incremental
				var rti=tbIMG[5]; // Ruta de origen de la imagen
				
				// Compone parametros
				prm+="par="+par+RC;
				prm+="idi="+idi+RC;
				prm+="nci="+nci+RC;
				prm+="ipr="+ipr+RC;
				prm+="idf="+idf+RC;
				prm+="ncf="+ncf+RC;
				prm+="rti="+rti+RC;

				var desplemet=document.getElementById("desplesync_"+par); // Desplegable metodo de syncronización
				var  p=desplemet.selectedIndex; // Toma índice seleccionado

				// No ha elegido ninguna partición
				if(p===0){
					alert(TbMsg[4]);
					return("");
				}
				prm+="msy="+p+RC;	// Método de syncronización 1=Sincronización1 2=Sincronización2
	
				var chrChk=document.getElementById('whl-'+par); // Recupera objeto fila de la tabla opciones adicionales
				if(chrChk.checked)	prm+="whl=1"+RC; else prm+="whl=0"+RC;
				chrChk=document.getElementById('eli-'+par); // Recupera objeto fila de la tabla opciones adicionales
				if(chrChk.checked)	prm+="eli=1"+RC;	 else prm+="eli=0"+RC;
				chrChk=document.getElementById('cmp-'+par); // Recupera objeto fila de la tabla opciones adicionales
				if(chrChk.checked)	prm+="cmp=1"+RC; else prm+="cmp=0"+RC;
				
				
				trObj=document.getElementById('trOpc'); // Recupera objeto fila de la tabla opciones adicionales
				var obChk=trObj.childNodes[3].childNodes[0]; // Recupera  objeto checkbox borrar de la Imagen	
				if(obChk.checked)	prm+="bpi=1"+RC; else prm+="bpi=0"+RC;
				obChk=trObj.childNodes[7].childNodes[0]; // Recupera  objeto checkbox copiar en cache
				if(obChk.checked)	prm+="cpc=1"+RC; else prm+="cpc=0"+RC;
				obChk=trObj.childNodes[11].childNodes[0]; // Recupera  objeto checkbox borrar la cache
				if(obChk.checked)	prm+="bpc=1"+RC; else prm+="bpc=0"+RC;
				obChk=trObj.childNodes[15].childNodes[0]; // Recupera  objeto checkbox no borrar archivos en destino
				if(obChk.checked)	prm+="nba=1"+RC; else prm+="nba=0"+RC;
				
				if(comprobar_datosejecucion()) // Comprueba opciones de ejecución
					return(prm);
				else
					return("");
			}
		}
	}	
	// No ha elegido ninguna partición
	alert(TbMsg[2]);
	return("");
}

