// ***********************************************************************************************************
// Libreria de scripts de Javascript
// Autor: 
// Fecha CreaciÃ³n: 2011
// Fecha Ãltima modificaciÃ³n: enero-2011
// Nombre del fichero: asistentes.js
// DescripciÃ³n : 
//		Este fichero implementa las funciones javascript del fichero AsistentesEjecutarScripts.php (Comandos)
// ***********************************************************************************************************

function codeCloneRemotePartition(form){
switch (form.idmetodo.value)
{
	case "MULTICAST":
 		protocol="MULTICAST " + form.mcastpuerto.value  + ":" + form.mcastmodo.value + ":" + form.mcastdireccion.value + ":" + form.mcastvelocidad.value + "M:" + form.mcastnclien.value + ":" + form.mcastseg.value + " ";
		break;
	case "UNICAST":
		protocol="UNICAST " +  form.ucastport.value + ":" + form.ucastclient.value + " ";
		break;
}
//form.codigo.value="cloneRemoteFromMaster " + form.ipMaster.value + " 1 " + form.PartOrigen.value + "  " + form.mcastpuerto.value  + ":" + form.mcastmodo.value + ":" + form.mcastdireccion.value + ":" + form.mcastvelocidad.value + "M:" + form.mcastnclien.value + ":" + form.mcastseg.value + " 1 " + form.PartOrigen.value + " " + form.tool.value + " " + form.compresor.value;
form.codigo.value="cloneRemoteFromMaster " + form.ipMaster.value + " " + form.source.value + "  " + protocol  + " " + form.targetpart.value + " " + form.tool.value + " " + form.compresor.value;

}

function codeDeployImage(form){
switch (form.idmetodo.value)
{
	case "MULTICAST":
 		protocol="MULTICAST " + form.mcastpuerto.value  + ":" + form.mcastmodo.value + ":" + form.mcastdireccion.value + ":" + form.mcastvelocidad.value + "M:" + form.mcastnclien.value + ":" + form.mcastseg.value + " ";
		break;
	case "TORRENT":
		protocol=" TORRENT " +  form.modp2p.value + ":" + form.timep2p.value;
		break;
	case "UNICAST":
		protocol=" UNICAST";
		break;
}
//form.codigo.value="deployImage REPO /";
if (form.modo[0].checked) 
{
	form.codigo.value="deployImage REPO /" + form.idimagen.value + " 1 " + form.idparticion.value + " " + protocol  ;
}
else
{
	form.codigo.value="updateCache REPO /" + form.idimagen.value + ".img" + " " + protocol  ;
}

}

function codeParticionado(form){
var value1;
var value2;
var value3;
var precache;
if (form.check1.checked) {
	if (form.part1.value == "CUSTOM" ) {value1 = form.part1custom.value}
	else {value1 = form.part1.value};
	if (form.size1.value == "CUSTOM")  {value1 += ":" + form.size1custom.value}
	else {value1 += ":" + form.size1.value}; 
}
else
{
   value1 = "EMPTY:0"
}
if (form.check2.checked) {
	if (form.part2.value == "CUSTOM" ) {value2 = form.part2custom.value}
	else {value2 = form.part2.value};
	if (form.size2.value == "CUSTOM")  {value2 += ":" + form.size2custom.value}
	else {value2 += ":" + form.size2.value}; 
}
else
{
   value2 = "EMPTY:0"
}
if (form.check3.checked) {
	if (form.part3.value == "CUSTOM" ) {value3 = form.part3custom.value}
	else {value3 = form.part3.value};
	if (form.size3.value == "CUSTOM")  {value3 += ":" + form.size3custom.value}
	else {value3 += ":" + form.size3.value}; 
}
else
{
   value3 = "EMPTY:0"
}

if (form.size4.value == "0") {
precache="ogUnmountCache \n ogUnmountAll 1 \n sizecache=`ogGetPartitionSize 1 4` \n ogDeletePartitionTable 1  \n ogUpdatePartitionTable 1 \n initCache $sizecache ";
//alert(precache);
}
else
{
	if (form.size4.value == "CUSTOM")  
	{ 
		cachesize = form.size4custom.value; 
	}
	else 
	{
		cachesize = form.size4.value;
	} 
	precache="ogUnmountCache \n ogUnmountAll 1 \n ogDeletePartitionTable 1  \n ogUpdatePartitionTable 1 \n initCache "  + cachesize + " ";
	//alert(precache);
}


form.codigo.value="\
" + precache + " \n \
ogListPartitions 1 \n \
ogCreatePartitions 1 " + value1 + " " + value2 + " " + value3 + " \n \
ogSetPartitionActive 1 1 \n \
ogUpdatePartitionTable 1 \n \
ogListPartitions 1 \n"; 
}


// Código de pulsación de selección de partición.
function clickPartitionCheckbox(form, npart) {
	var partCheck=eval("form.check"+npart);
	var partType=eval("form.part"+npart);
	var partSize=eval("form.size"+npart);
	var partTypeCustom=eval("form.part"+npart+"custom");
	var partSizeCustom=eval("form.size"+npart+"custom");
	var freeDisk=document.getElementById("freedisk");
	if (partCheck.checked) {
		partType.disabled=false;
		partSize.disabled=false;
		if (partType.options[partType.selectedIndex].value == "CUSTOM") {
			partTypeCustom.disabled=false;
		}
		//if (partType.options[partType.selectedIndex].value == "EXTENDED") {
		//	document.getElementById("logicas").style.visibility="visible";
		//}
		if (partSize.options[partSize.selectedIndex].value == "CUSTOM") {
			partSizeCustom.disabled=false;
		} else {
			partSizeCustom.disabled=true;
		}
	} else {
		partType.disabled=true;
		partSize.disabled=true;
		partTypeCustom.disabled=true;
		partSizeCustom.disabled=true;
		//if (partType.options[partType.selectedIndex].value == "EXTENDED") {
		//	document.getElementById("logicas").style.visibility="hidden";
		//}
	}
	calculateFreeDisk(form);
}


// Código para calcular el espacio libre del disco.
function calculateFreeDisk(form, npart) {
	var freeDisk=document.getElementById("freedisk");
	freeDisk.value=form.minsize.value;
	for (npart=1; npart<=4; npart++) {
		var partCheck=eval("form.check"+npart);
		var partSize=eval("form.size"+npart);
		var partSizeCustom=eval("form.size"+npart+"custom");
		if (partCheck.checked) {
			if (partSize.options[partSize.selectedIndex].value == "CUSTOM") {
				freeDisk.value -= parseInt(partSizeCustom.value);
			} else {
				freeDisk.value -= parseInt(partSize.options[partSize.selectedIndex].value);
			}
		}
	}
	if (parseInt(freeDisk.value) < 0) {
		freeDisk.style.fontWeight = "bold";
		freeDisk.style.fontStyle = "italic";
	} else {
		freeDisk.style.fontWeight = "normal";
		freeDisk.style.fontStyle = "normal";
	}
	if (form.size4.value == 0) {
		freeDisk.value += " (- cache)";		// Aviso de caché sin modificar.
	}
}

