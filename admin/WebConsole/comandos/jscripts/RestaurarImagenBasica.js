// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: RestaurarImagenBasica.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero RestaurarImagenBasica.php (Comandos)
// *************************************************************************************************************************************************
 function confirmar(){
	if(comprobar_datos()){
		var RC="@";
		var disco=1; // Siempre disco 1
		var atributos="dsk="+disco+RC;
		var tb_conf=document.getElementById("tabla_conf");
		var ochecks=tb_conf.getElementsByTagName('INPUT')
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked){
				var idradio=ochecks[i].id;	
				var numpar=ochecks[i].value;		
			
				atributos+="par="+numpar+RC; // Número de partición
				var despleimagenizda=document.getElementById("despleimagen_"+idradio+"_1");
				var despleimagen;
					
				if(despleimagenizda.selectedIndex>0) despleimagen=despleimagenizda;

				var imgcanrepo=despleimagen.value.split("_");
				atributos+="idi="+imgcanrepo[0]+RC; // Identificador de la imagen
				atributos+="nci="+imgcanrepo[1]+RC;	// Nombre canónico	
				atributos+="ipr="+imgcanrepo[2]+RC;	// Dirección ip del repositorio donde se aloja la imagen
				atributos+="ifs="+imgcanrepo[3]+RC;	// Identificador del perfil software de la imagen				
				atributos+="rti="+imgcanrepo[4]+RC;	// Ruta de origen de la imagen	
			
				var desplemet=document.getElementById("desplemet_"+idradio); // Desplegable metodo de restauración
				var  p=desplemet.selectedIndex // Toma índice seleccionado
				atributos+="met="+p+RC;	// Método de clonación 0=caché 1=repositorio	

				var cc=ochecks[i].getAttribute('idcfg'); // Toma identificador del bloque de configuración
				if(document.fdatosejecucion.ambito.value!=AMBITO_ORDENADORES){	
					var tbOrd=document.getElementById("tbOrd_"+cc);			
					var idordenadores=tbOrd.getAttribute('value'); // Toma identificadores de los ordenadores
					var cadenaid=document.fdatos.cadenaid.value; // Cadena de identificadores de todos los ordenadores del ámbito
					if(idordenadores!=cadenaid){ 
						document.fdatosejecucion.ambito.value=0; // Ambito de aplicación restringido
						document.fdatosejecucion.idambito.value=idordenadores;
					}
				}
				// Opciones adicionales
				var trObj=document.getElementById('trOpc'); // Recupera objeto fila de la tabla opciones adicionales
				var obChk=trObj.childNodes[3].childNodes[0]; // Recupera  objeto checkbox borrar de la Imagen	
				if(obChk.checked)	atributos+="bpi=1"+RC; else atributos+="bpi=0"+RC;
				var obChk=trObj.childNodes[7].childNodes[0]; // Recupera  objeto checkbox copiar en cache	
				if(obChk.checked)	atributos+="cpc=1"+RC; else atributos+="cpc=0"+RC;
				var obChk=trObj.childNodes[11].childNodes[0]; // Recupera  objeto checkbox borrar la cache	
				if(obChk.checked)	atributos+="bpc=1"+RC; else atributos+="bpc=0"+RC;
				var obChk=trObj.childNodes[15].childNodes[0]; // Recupera  objeto checkbox no borrar archivos en destino	
				if(obChk.checked)	atributos+="nba=1"+RC; else atributos+="nba=0"+RC;
	
				document.fdatosejecucion.atributos.value=atributos;
				filtrado();
				document.fdatosejecucion.submit();	
				break;		
			}
		}
	}
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
				var idradio=ochecks[i].id; // Toma idemtificador del desplegable de imagenes
				despleimagenizda=document.getElementById("despleimagen_"+idradio+"_1"); // Desplegable izda.
				var  p1=despleimagenizda.selectedIndex // Toma índice seleccionado
				if (p1==0){  
						alert(TbMsg[0])
						despleimagenizda.focus()
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

