// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: IniciarSesion.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero IniciarSesion.php (Comandos)
// Version: 0.1 - el valor del filtro = ips de los equipos comunes entre la seleccion de la configuración y el filtro.
// Nota: no se utiliza document.fdatosejecucion.idambito.value. Su valor no es correcto.
// Fecha: 2014-10-23
// Autora: Irina Gomez, ETSII Universidad de Sevilla
// *************************************************************************************************************************************************
function confirmar(){
	if (comprobar_datos()){
		var RC="@";
		// UHU - Ahora puede ser cualquier disco
		var atributos="";
		// devuelve las ip de los ordenadores del filtro o vacio si estan todos seleccionados.
		filtrado();
		var ipfiltro=document.fdatosejecucion.filtro.value;
	 	// Compone atributos del comando
		var tb_conf=document.getElementById("tabla_conf");
		var ochecks=tb_conf.getElementsByTagName('INPUT');
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked){
				var diskPart = ochecks[i].value.split(";");
				var disco = diskPart[0];
				var numpar= diskPart[1];
				atributos+="dsk="+disco+RC;
				atributos+="par="+numpar+RC;
				// Datos bloque de configuracion: ip equipos.
				var cc=ochecks[i].getAttribute('idcfg'); 
				var tbOrd=document.getElementById("tbOrd_"+cc);
				var iptabla=tbOrd.getAttribute('value'); 
					
				// Elimino los ordenadores del filtro que no estén en la tabla.
				if (ipfiltro!==''){
					var arraytabla = iptabla.split(",");
					var arrayfiltro =ipfiltro.split(";");
					arrayfiltro = array_interset (arrayfiltro.sort(), arraytabla.sort());
					ipfiltro = arrayfiltro.join(";");
					if (ipfiltro===''){
			   			alert(TbMsg["FILTER"]);
			 			return(false);
					}
				}
				else {
					ipfiltro=iptabla.replace(/,/g, ";");
				}
				document.fdatosejecucion.filtro.value=ipfiltro;
				document.fdatosejecucion.atributos.value=atributos;
				document.fdatosejecucion.submit();
				break;


			}
		}

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
		var tb_conf=document.getElementById("tabla_conf");
		var ochecks=tb_conf.getElementsByTagName('INPUT');
		var op=0;
		for(var i=0;i<ochecks.length;i++){
			if(ochecks[i].checked)		op++;
		}
		if(op===0){
			   alert(TbMsg[1]);
			 return(false);
		}
		return(comprobar_datosejecucion())
}

