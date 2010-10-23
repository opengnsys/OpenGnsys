// *******************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: inclusionacciones.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero inclusionacciones.php
// ********************************************************************************************************
	var ichk,iorden,iimg;
	var altas,bajas,modificaciones;	
//________________________________________________________________________________________________________

function confirmar()
{
	altas=bajas=modificaciones="";
	var conTR=document.fdatos.conTR.value; // Toma número de filas
	setIndices();
	for(var i=0;i<conTR;i++){
		var oTR=document.getElementById("TR-"+i); 
		if(oTR){
			var identificador=oTR.getAttribute('value'); 
			var objChk=oTR.childNodes[ichk].childNodes[0];
			var std=objChk.value; // Estado original del checkbox 1=seleccionado 0=No seleccionado
			var objOrden=oTR.childNodes[iorden].childNodes[0];
			var aorden=objOrden.getAttribute('id'); // Valor originario del orden
			var orden=objOrden.value;
			if(objChk.checked){
				/* Control de errores */
				if(orden=="" || orden<0 || !IsNumeric(orden)){
					alert(TbMsg[1]);
					objOrden.focus();
					return(false);
				}
				/* Compone parametros */
				if(std>0){ // Originalmente estaba seleccionado, se trata de una modificación
					if(aorden!=orden) // Si se ha cambiado el orden
						modificaciones+=identificador+","+orden+";";
				}
				else{
					var objImg=oTR.childNodes[iimg].childNodes[0];
					var tipoaccion=objImg.getAttribute('value');
					altas+=identificador+","+orden+","+tipoaccion+";";
				}
			}
			else{ // Baja
				if(std>0){ // Si originariamente estaba marcado se trata de una baja
					bajas+=identificador+";";
				}
			}			
		}	
	}
	/* LLamada a la gestión */
	var wurl="../gestores/gestor_inclusionacciones.php";
	var prm="tipoaccion="+document.fdatos.tipoaccion.value+"&idtipoaccion="+document.fdatos.idtipoaccion.value
	prm+="&altas="+altas+"&bajas="+bajas+"&modificaciones="+modificaciones;
	CallPage(wurl,prm,"retornoGestion","POST");	
}
//________________________________________________________________________________________________________

	function retornoGestion(ret)
	{	
		//alert(ret);
		eval(ret);
	}
//________________________________________________________________________________________________________

function resultado_gestion_inclusionacciones(resul,descrierror)
{
	if (!resul){ // Ha habido algún error en la inserción
		alert(descrierror)
		return
	}
	alert(TbMsg[2])
	location.href="../nada.php";
}
//______________________________________________________________________________________________________
//
// Configura indices para acceo a nodos
//______________________________________________________________________________________________________

	function setIndices()
	{	
		ichk=0;			
		iorden=ichk+1;	
		iimg=iorden+1;
	}