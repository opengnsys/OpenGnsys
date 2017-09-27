// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: Configurar.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero Configurar.php (Comandos)
// *************************************************************************************************************************************************

var atributos=""; // Variable global
var swc=false; // Switch para detectar cache
var swe=false; // Switch para detectar partición extendida

//________________________________________________________________________________________________________ 
// 
//	Elimina una fila de una tabla de configuraciones perteneciente a las propiedades de una partición
//	Parametros:
//		icp: Identificador de la configuración-partición
//		o: Objeto checkbox que invoca la función
//________________________________________________________________________________________________________ 

function eliminaParticion(o,icp)
{
	var res=confirm(TbMsg[4]); // Pide confirmación
	if(!res){
		o.checked=false;
		return;
	}

// Toma desplegable de tipo de partición
	var desplepar=o.parentNode.nextSibling.nextSibling.childNodes[0]; 
	var tipar=tomavalorDesple(desplepar); // Partición
	if(tipar=="CACHE") swc=false; // Si es la caché se pone a false su switch
	if(tipar=="EXTENDED") swe=false; // Si es la EXTENDED se pone a false su switch

	var tbCfg = document.getElementById("tabla_conf"); // Recupera objeto <TABLE>
	var trCfg = document.getElementById("TR_"+icp); // Recupera <TR> de la fila a eliminar
	var tbodyObj=tbCfg.firstChild; // Recupera hijo <TBODY> del objeto <TABLE>
	tbodyObj.removeChild(trCfg); // Elimina nodo <TR> completo
}
//________________________________________________________________________________________________________ 

function addParticion(objImg,cc)
{
	var tbCfg = document.getElementById("tabla_conf"); // Recupera objeto <TABLE>
	var tbodyObj=tbCfg.firstChild; // Recupera hijo <TBODY> del objeto <TABLE>
		
	var trImg=document.getElementById("TRIMG_"+cc); // Recupera <TR> de los botones para insertar antes

	var k=objImg.getAttribute('value'); // Toma siguiente identificador de nodo
	var icp=cc+"_"+k; // Identificador de la configuración-partición

	/* Crea objeto TR y lo añade al TBODY de la tabla*/	
	var nwTR = document.createElement('TR');
	nwTR.setAttribute("id","TR_"+icp);
	tbodyObj.insertBefore(nwTR,trImg); // Inserta nodo <TR> completo 
	var patron = document.getElementById("TR_patron"); // Recupera <TR> patron
	nwTR.innerHTML=patron.innerHTML;
	/* Actualiza objeto checkbox nuevo para que actue como los demás */
	var nwCHK = document.getElementById("CHK_patron"); // Recupera reciente chekcbox
	var fclbk="eliminaParticion(this,'"+icp+"');";
	nwCHK.setAttribute("onclick",fclbk);	
	nwCHK.removeAttribute("id");				
	/* Incrementa para siguiente identificador de nodo */				
	k++;			
	objImg.setAttribute('value',k);		
}
//________________________________________________________________________________________________________ 
// 
//	Confirma un bloque de configuración de particiones
//	Parametros:
//		idordenadores: Identificadores de los ordenadores a los que se aplicará el comando
//		cc: Identificador de la configuración (bloque de particiones)
// 	Especificaciones:
//		Cuando un comando se va a aplicar a un conjunto aleatorio de ordenaores, el código del
//		ámbito será cero y la variable idambito contendrá la cadena con los identificadores de 
//		los oordenadores separados por coma (este dato aparece en esta función como promer parámetro)
//________________________________________________________________________________________________________
 
function Confirmar(cc)
{
	if(comprobarDatos(cc)){
		if(document.fdatosejecucion.ambito.value!=AMBITO_ORDENADORES){
			var tbOrd= document.getElementById("tbOrd_"+cc); // Recupera tabla de ordenadores de la configuración
			var idordenadores=tbOrd.getAttribute('value'); // Toma identificadores de los ordenadores
			var cadenaid=document.fdatos.cadenaid.value; // Cadena de identificadores de todos los ordenadores del ámbito
			if(idordenadores!=cadenaid){ // Si no son iguales es que el ámbito de aplicación es restringido
				document.fdatosejecucion.ambito.value=0; // Ambito de aplicación restringido
				document.fdatosejecucion.idambito.value=idordenadores;
			}
		}	
		document.fdatosejecucion.atributos.value=atributos;	
		//alert(atributos)
		//filtrado();		
		document.fdatosejecucion.submit();		
	}
}
//________________________________________________________________________________________________________ 
// 
//	Confirma un bloque de configuración de particiones
//	Parametros:
//		cc: Identificador de la configuración (bloque de particiones)
//	Version 1.1: 2015-02-25. Irina Gomez ETSII US. Se envian datos de cuarta particion.
//________________________________________________________________________________________________________
 
function comprobarDatos(cc)	
{
	// Indices de campos a recuperar
	var ipar=1;
	var icodpar=2;
	var isysfi=3;
	var itama=4;
	var iope=6;
	
	var SL="%";
	var TB="*";
	var maxpar=0;
	var tbpar=new Array(); // Para control de particiones duplicadas
	var tbparam=new Array(); // Para control de configuración 
	var npar; // Partición en formato integer
	var tch=0; // Tamaño de la caché

	var allpartsize=0; // Tamaño total de todas las particiones.
	var extsize=0; // Tamaño partición "EXTENDED"
	var allextsize=0; // Tamaño total de las particiones extendidas.

	var hdsize = document.getElementById("hdsize").value;

	var trCfg = document.getElementById("TR_"+cc); // Recupera primer <TR> de la configuración
	trCfg=trCfg.nextSibling; // Primera fila de particiones
	trCfg=trCfg.nextSibling; // Fila datos disco duro
	while(trCfg.id!="TRIMG_"+cc){

		var tama=trCfg.childNodes[itama].childNodes[0].value; // Tamaño de partición


		var par=tomavalorDesple(trCfg.childNodes[ipar].childNodes[0]); // Partición
		npar=parseInt(par);
		if(maxpar<npar) maxpar=npar; // Guarda partición de mayor orden

		if (npar==4){
			swc=true; // Se especifica partición caché
			tch=tama;
		}

		if(npar==0){
			alert(TbMsg[1]);
			trCfg.childNodes[ipar].childNodes[0].focus();
			return(false);
		}	

		if(tbpar[npar]==1){ // Existe ya una partición con ese número
			alert(TbMsg[0]);
			trCfg.childNodes[ipar].childNodes[0].focus();
			return(false);
		}

		tbpar[npar]=1;
		var codpar=tomavalorDesple(trCfg.childNodes[icodpar].childNodes[0]); // Tipo de partición
		if(codpar==""){
			alert(TbMsg[2]);
			trCfg.childNodes[icodpar].childNodes[0].focus();
			return(false);
		}	

		if(codpar=="EXTENDED") {
			swe=true;
			extsize=tama;
		} else {
			if (npar<=4){
				allpartsize+=parseInt(tama);
			} else {
				allextsize+=parseInt(tama);
			}
		}

		if(codpar=="CACHE" && npar!=4){
			alert(TbMsg[6]);
			trCfg.childNodes[icodpar].childNodes[0].focus();
			return(false);
		}

		var ope=tomavalorCheck(trCfg.childNodes[iope].childNodes[0]); // Formatar a realizar	

		var sysfi=tomatextDesple(trCfg.childNodes[isysfi].childNodes[0]); // Sistema de ficheros
		if(sysfi=="" || sysfi=="EMPTY" ){ // Si el sistema de fichero es vacio o empty...
				if(ope==1){ // Si se quiere formatear...
					alert(TbMsg[5]);
					trCfg.childNodes[isysfi].childNodes[0].focus();
					return(false);
				}	
				else
					sysfi="EMPTY";
		}

		if(tama==0 && codpar!="EXTENDED") {
			alert(TbMsg[3]);
			trCfg.childNodes[itama].childNodes[0].focus();
			return(false);
		}

		trCfg=trCfg.nextSibling; // Siguiente fila de particiones
		/* Compone formato del comando */
		tbparam[npar]="par="+par+TB+"cpt="+codpar+TB+"sfi="+sysfi+TB+"tam="+tama+TB+"ope="+ope+SL;

	}	
	
	//Controles finales de los paramtros a enviar

	if(!swe){ // Si no se han especificado particiones extendidas ...
		if(maxpar>4){ // La partición de mayor orden supera el número 4
			alert(TbMsg[7]);
			return(false);
		}
	}


	// Alerta si las particiones lógicas son mayores que la extendida
	if(swe){
		if (allextsize>extsize) {
			alert(TbMsg["EXTSIZE"]);
			return(false);
		}
		allpartsize+=parseInt(extsize);
	}
	// Alerta si tamaño del disco menor que las particiones 
	if (hdsize<allpartsize) {
		alert(TbMsg["HDSIZE"]);
		return(false);

	}

	/* Compone cadena de particiones (Deja fuera la cache,
		 si se especificó) ya que va en parametro aparte 
	*/

	var RC="!";
	var disco=1; // Siempre disco 1

	atributos="dsk="+disco+"@"+"cfg="; // Inicializa variable global de parámetros del comando


	if(swc){
 		atributos+="dis="+disco+TB+"che=1"+TB+"tch="+tch+RC; // Caché con su tamaño 
	}
	else{
 		atributos+="dis="+disco+TB+"che=0"+TB+"tch=0"+RC; // No se especifica caché
	}

	for(var i=1;i<=maxpar;i++){
		// Version 1.1: Se envian datos cuarta particion.
		//if(i!=4){
			if(tbparam[i]!=undefined)
				atributos+=tbparam[i];
			else
				atributos+="par="+i+TB+"cpt=EMPTY"+TB+"sfi=EMPTY"+TB+"tam=0"+TB+"ope=0"+SL;
		//}
	}

	// Completa con EMPTY si las particiones son menores a 4 y no hay cache
	if(maxpar<4){
		var up=4;
		if(swc) up=3;  
		for(var i=maxpar+1;i<=up;i++)
			atributos+="par="+i+TB+"cpt=EMPTY"+TB+"sfi=EMPTY"+TB+"tam=0"+TB+"ope=0"+SL;
	}
											
	return(true);
}
//________________________________________________________________________________________________________ 
//
// Devuelve el valor seleccionado de un desplegable cualquiera
//________________________________________________________________________________________________________ 

function tomavalorDesple(desplegable)
{
	var idx=desplegable.selectedIndex; // Indice seleccionado en el desplegable
	var val=desplegable.options[idx].value; // Valor seleccionado en el desplegable
	return(val);
}

function tomavalorCheck(chk)
{
	if(chk.checked) // Valor seleccionado en el desplegable
		return(1);
	return(0);
}
//________________________________________________________________________________________________________ 
//
// Devuelve el valor seleccionado de un desplegable cualquiera
//________________________________________________________________________________________________________ 

function tomatextDesple(desplegable)
{
	var idx=desplegable.selectedIndex; // Indice seleccionado en el desplegable
	var txt=desplegable.options[idx].text; // Valor seleccionado en el desplegable
	return(txt);
}

