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

function allSelect()
{
var saveString = "";
// seleccionamos cada uno de los select
var input = document.getElementsByTagName('select');
//alert(input.length);
for(var i=0; i<input.length; i++){
//if(inputs[i].getAttribute('type')=='button'){
// your statements
patron = "L";
parm = input[i].name;
//alert(parm);
parm = parm.replace(patron,'');
//alert(parm);
for (j=0;j<input[i].length;j++)
		{
			//List.options[i].selected = true;
			saveString = saveString + parm + '|' + input[i].options[j].value + ';';
			//alert(saveString);			
		}
}
document.forms['myForm'].listOfItems.value = saveString;
}