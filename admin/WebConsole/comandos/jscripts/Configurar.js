// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: Configurar.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero Configurar.php (Comandos)
// *************************************************************************************************************************************************

var atributos; // Variable global

//________________________________________________________________________________________________________ 
// 
//	Elimina una fila de una tabla de configuraciones perteneciente a las propiedades de una partición
//	Parametros:
//		icp: Identificador de la configuración-partición
//		o: Objeto checkbox que invoca la función
//________________________________________________________________________________________________________ 

function eliminaParticion(icp,o)
{
	var res=confirm(TbMsg[4]); // Pide confirmación
	if(!res){
		o.checked=false;
		return;
	}
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
	var fclbk="eliminaParticion('"+icp+"');";
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
		var RC="@";
		var disco=1; // Siempre disco 1
		atributos+=RC+"dsk="+disco+RC; // Le añade a la variable global el parámetro disco
		//alert(atributos)
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
		document.fdatosejecucion.submit();		
	}
}
//________________________________________________________________________________________________________ 
// 
//	Confirma un bloque de configuración de particiones
//	Parametros:
//		cc: Identificador de la configuración (bloque de particiones)
//________________________________________________________________________________________________________
 
function comprobarDatos(cc)	
{
	// Indices de campos a recuperar
	var ipar=1;
	var icodpar=2;
	var isysfi=3;
	var itama=4;
	var iope=6;
	
	var SL="#";
	var TB="$";
	
	var tbpar=new Array(); // Para control de particiones duplicadas
	atributos="cfg="; // Inicializa variable global de parámetros del comando
	
	var trCfg = document.getElementById("TR_"+cc); // Recupera primer <TR> de la configuración
	trCfg=trCfg.nextSibling; // Primera fila de particiones
	while(trCfg.id!="TRIMG_"+cc){
		var par=tomavalorDesple(trCfg.childNodes[ipar].childNodes[0]); // Partición
		if(par==0){
			alert(TbMsg[1]);
			trCfg.childNodes[ipar].childNodes[0].focus();
			return(false);
		}	
		if(tbpar[par]==1){ // Existe ya una partición con ese número
			alert(TbMsg[0]);
			trCfg.childNodes[ipar].childNodes[0].focus();
			return(false);
		}
		tbpar[par]=1;
		var codpar=tomavalorDesple(trCfg.childNodes[icodpar].childNodes[0]); // Tipo de partición
		if(codpar==0){
			alert(TbMsg[2]);
			trCfg.childNodes[icodpar].childNodes[0].focus();
			return(false);
		}	

		var sysfi=tomatextDesple(trCfg.childNodes[isysfi].childNodes[0]); // Sistema de ficheros
	
		var tama=trCfg.childNodes[itama].childNodes[0].value; // Tamaño de partición
		if(tama==0){
			alert(TbMsg[3]);
			trCfg.childNodes[itama].childNodes[0].focus();
			return(false);
		}
		var ope=tomavalorDesple(trCfg.childNodes[iope].childNodes[0]); // Operación a realizar	
	
		trCfg=trCfg.nextSibling; // Primera fila de particiones
		/* Compone formato del comando */
		atributos+="par="+par+TB+"cpt="+codpar+TB+"sfi="+sysfi+TB+"tam="+tama+TB+"ope="+ope+SL;
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

