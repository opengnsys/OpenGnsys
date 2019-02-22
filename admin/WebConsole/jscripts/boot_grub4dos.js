// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: Alberto García Padilla (Universidad de Málaga)
// Fecha Creación: 2012
// Fecha Última modificación: Mayo-2012
// Nombre del fichero: boot_grub4dos.js
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero boot_grub4dos.php
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
// Se utiliza en los botones in y out de las columnas
// Permite mover los equipos seleccionados desde una columna a otra
function move(fbox, tbox) {
	var arrFbox = new Array();
	var arrTbox = new Array();
	var arrLookup = new Array();
	var i;
	for (i = 0; i < tbox.options.length; i++) {
		arrLookup[tbox.options[i].text] = tbox.options[i].value;
		arrTbox[i] = tbox.options[i].text;
	}
	var fLength = 0;
	var tLength = arrTbox.length;
	for(i = 0; i < fbox.options.length; i++) {
		arrLookup[fbox.options[i].text] = fbox.options[i].value;
		if (fbox.options[i].selected && fbox.options[i].value != "") {
			arrTbox[tLength] = fbox.options[i].text;
			tLength++;
		}
		else {
			arrFbox[fLength] = fbox.options[i].text;
			fLength++;
  		  }
		}
	arrFbox.sort();
	arrTbox.sort();
		fbox.length = 0;
		tbox.length = 0;
	var c;

for(c = 0; c < arrFbox.length; c++) {
var no = new Option();
no.value = arrLookup[arrFbox[c]];
no.text = arrFbox[c];
fbox[c] = no;
}

for(c = 0; c < arrTbox.length; c++) {
var no = new Option();
no.value = arrLookup[arrTbox[c]];
no.text = arrTbox[c];
tbox[c] = no;
    }
}
// Se utiliza al enviar el formulario
// Asigna como valor del campo listOfItems un listado
// con las correspodendencias nombre plantilla - nombre equipo.
// Version 1.1.1 - Se identifica plantilla y equipo como necesita el script setclienmode (#802)
function allSelect()
{
    var saveString = "";
    // seleccionamos cada uno de los select
    var input = document.getElementsByTagName('select');

    for(var i=0; i<input.length; i++){
        label = input[i].parentNode.id;
	// La plantilla 00unknown no existe, no se incluye en el listado
	if (label === "00unknown") continue;

        for (j=0;j<input[i].length;j++)
		{
			saveString = saveString + label + '|' + input[i].options[j].text + ';';
		}
    }
    document.forms[0].listOfItems.value = saveString;
}
