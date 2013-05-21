// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: RestaurarImagen.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero RestaurarImagen.php (Comandos)
// *************************************************************************************************************************************************
 function confirmar(){
	if(comprobar_datos()){
		var RC="@";
		// UHU - Ahora puede ser cualquier disco
		var disco;
		var atributos="";
		var tb_conf=document.getElementById("tabla_conf");
		var ochecks=tb_conf.getElementsByTagName('INPUT')
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked){
				var idradio=ochecks[i].id;
				var diskPart = ochecks[i].value.split(";");
				disco = diskPart[0];
				var numpar=	diskPart[1];
				atributos+="dsk="+disco+RC;	// Numero de disco
				atributos+="par="+numpar+RC; // Número de partición
				var despleimagenizda=document.getElementById("despleimagen_"+idradio+"_1");
				var despleimagendrcha=document.getElementById("despleimagen_"+idradio+"_0");
				var despleimagen;
				
				var protoclonacion=document.getElementById("protoclonacion_"+idradio);
				
				if(despleimagenizda.selectedIndex>0) despleimagen=despleimagenizda;
				if(despleimagendrcha.selectedIndex>0) despleimagen=despleimagendrcha;
				var imgcanrepo=despleimagen.value.split("_");
				atributos+="idi="+imgcanrepo[0]+RC; // Identificador de la imagen
				atributos+="nci="+imgcanrepo[1]+RC;	// Nombre canónico	
				atributos+="ipr="+imgcanrepo[2]+RC;	// Ip del repositorio donde está alojada	
				atributos+="ifs="+imgcanrepo[3]+RC;	// Identificador del perfil soft contenido en la imagen				
				atributos+="ptc="+protoclonacion.value+RC; // Identificador del protocolo de clonación				
				
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
				despleimagendcha=document.getElementById("despleimagen_"+idradio+"_0"); // Desplegable derecha.
				
				var  p1=despleimagenizda.selectedIndex // Toma índice seleccionado
				var  p2=despleimagendcha.selectedIndex // Toma índice seleccionado

				if (p1==0 && p2==0){  
						alert(TbMsg[0])
						despleimagenizda.focus()
			      return(false)
				}
				if (p1==p2){  
						alert(TbMsg[6])
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

