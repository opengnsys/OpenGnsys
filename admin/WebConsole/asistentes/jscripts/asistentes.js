// ***********************************************************************************************************
// Libreria de scripts de Javascript
// Autor: 
// Fecha CreaciÃ³n: 2011
// Fecha Ãltima modificaciÃ³n: enero-2011
// Nombre del fichero: asistentes.js
// DescripciÃ³n : 
//		Este fichero implementa las funciones javascript del fichero AsistentesEjecutarScripts.php (Comandos)
// version 1.1: cliente con varios repositorios - Imagenes de todos los repositorios de la UO.
// autor: Irina Gomez, Universidad de Sevilla
// fecha 2015-06-17
// version 1.1: showPartitionForm: Se incluye aviso para particiones GTP.
// autor: Irina Gomez, ETSII Universidad de Sevilla
// fecha: 2016-06-21
// version 1.1: codeDeployImage: Compone atributo para el comando restaurar imagen (ticket #757)
// autor: Irina Gomez, ETSII Universidad de Sevilla
// fecha: 2016-10-27
// ***********************************************************************************************************

function codeCloneRemotePartition(form){
var protocol;
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
var command="cloneRemoteFromMaster " + form.ipMaster.value + " " + form.source.value + "  " + protocol  + " " + form.targetpart.value + " " + form.tool.value + " " + form.compresor.value;
form.codigo.value="\
ogEcho log session \"[0] $MSG_SCRIPTS_TASK_START " + command + "\"\n \
ogExecAndLog command " + command + " \n ";
}

// disableDirect(form): En Deploy de imagenes si se elige updateCache se impide elegir multicast-direct o unicast-direct
function disableDirect(form){
	// MULTICAST-DIRECT
	form.idmetodo.options[2].disabled=true;
	// UNICAST-DIRECT
	form.idmetodo.options[4].disabled=true;
}
// enableDirect(form): En Deploy de imagenes si se elige deployCache se permite elegir multicast-direct o unicast-direct
function enableDirect(form){
	// MULTICAST-DIRECT
	form.idmetodo.options[2].disabled=false;
	// UNICAST-DIRECT
	form.idmetodo.options[4].disabled=false;
}


function codeDeployImage(form){
var diskPart;
var imagen;
var command;

switch (form.idmetodo.value)
{
	case "MULTICAST":
 		protocol="MULTICAST " + form.mcastpuerto.value  + ":" + form.mcastmodo.value + ":" + form.mcastdireccion.value + ":" + form.mcastvelocidad.value + "M:" + form.mcastnclien.value + ":" + form.mcastseg.value + " ";
		break;
	case "MULTICAST-DIRECT":
 		protocol="MULTICAST-DIRECT " + form.mcastpuerto.value  + ":" + form.mcastmodo.value + ":" + form.mcastdireccion.value + ":" + form.mcastvelocidad.value + "M:" + form.mcastnclien.value + ":" + form.mcastseg.value + " ";
		break;
	case "TORRENT":
		protocol="TORRENT " +  form.modp2p.value + ":" + form.timep2p.value;
		break;
	case "UNICAST":
		protocol="UNICAST";
		break;
	case "UNICAST-DIRECT":
		protocol="UNICAST-DIRECT";
		break;
}

// Datos imagen
imagen = form.idimagen.value.split("_");

//form.codigo.value="deployImage REPO /";
if (form.modo[0].checked) 
{
	// UHU - Distinguimos entre disco y particion, el valor de idparticion sera disco;particion. eje. 1;1
	diskPart = form.idparticion.value.split(";");
	imagen = form.idimagen.value.split("_");
	command="deployImage " + imagen[0] + " /" + imagen[1] + " "+diskPart[0]+" " + diskPart[1] + " " + protocol  ;
	form.codigo.value="\
ogEcho log session \"[0] $MSG_SCRIPTS_TASK_START " + command + "\"\n \ " +
command + " \n";

	// Atributos para comando RestaurarImagen
	form.atrib_restore.value = "dsk=" + diskPart[0] + "@par="+ diskPart[1] +"@idi=" +imagen[2] +
				   "@nci="+imagen[1] + "@ipr="+ imagen[0] +"@ifs=" +imagen[3] +
				   "@ptc="+protocol +"@";
}
else
{
	command="updateCache " + imagen[0] + " /" + imagen[1] + ".img" + " " + protocol  ;
	form.codigo.value="\
ogEcho log session \"[0] $MSG_SCRIPTS_TASK_START " + command +"\"\n \ " +
command + " \n";
	//form.codigo.value="updateCache REPO /" + form.idimagen.value + ".img" + " " + protocol  ;
}

}

// Activa el área de texto del código, permitiendo modificarlo.
function modificarCodigo() {
	document.getElementById("codigo").disabled = false;
}

function codeParticionado(form){
	var n_disk = form.n_disk.value;
	var tipo_part_table = form.tipo_part_table.value;
	var freedisk;
	var freediskGPT;
	// Comprobamos si la opcion elejida es GPT o MSDOS para llamar a una funcion u otra
	if(tipo_part_table === "GPT"){
		freediskGPT = parseInt(document.getElementById("freediskGPT").value);
		// Comprobamos que el espacio libre en el disco no sea negativo, si lo es, dar aviso
		if(freediskGPT < 0){
			alert(TbMsg['NODISKSIZE']);
		}
		else if (!validaCache(freediskGPT)) {
			alert(TbMsg['NOCACHESIZE']);
		}
		else{
			codeParticionadoGPT(form);
		}
	}
	else{
		freedisk = parseInt(document.getElementById("freedisk").value);
		// Comprobamos que el espacio libre en el disco no sea negativo, si lo es, dar aviso
		if(freedisk < 0){
			alert(TbMsg['NODISKSIZE']);
		}
		else if (!validaCache(freedisk)) {
			alert(TbMsg['NOCACHESIZE']);
		}
		else{
			codeParticionadoMSDOS(form);
		}
	}
}


function codeParticionadoMSDOS (form) {
	var partCode="";
	var logicalCode="";
	var sizecacheCode="";
	var cacheCode="";
	var cacheSize;
	var extended=false;
	var n_disk = form.n_disk.value;
	var tipo_part_table = form.tipo_part_table.value;
	var maxParts = 4;
	var swapPart = [];
	var swapCode = "";
	var partCheck;
	var partType;
	var partTypeCustom;
	var partSize;
	var partSizeCustom;
	// Comprobamos si esta seleccionada la cuarta particion y no es CACHE
	if(form.check4.checked && form.part4.value !== "CACHE")
		maxParts = 5;

	for (var nPart=1; nPart<maxParts; nPart++) {
		partCheck=eval("form.check"+nPart);
		if (partCheck.checked) {
			partType=eval("form.part"+nPart);
			if (partType.value === "CUSTOM" ) {
				partTypeCustom=eval("form.part"+nPart+"custom");
				partCode += " " + partTypeCustom.value;
				switch(partTypeCustom.value) {
				    case "EXTENDED":
					extended=true;
					break;
				    case "LINUX-SWAP":
					swapPart.push(nPart);
					break;
				}
	
			} else {
				partCode += " " + partType.value;
				switch(partType.value) {
				    case "EXTENDED":
					extended=true;
					break;
				    case "LINUX-SWAP":
					swapPart.push(nPart);
					break;
				}
			}
			partSize=eval("form.size"+nPart);
			if (partSize.value === "CUSTOM" ) {
				partSizeCustom=eval("form.size"+nPart+"custom");
				partCode += ":" + partSizeCustom.value;
			} else {
				partCode += ":" + partSize.value;
			}
		} else {
			partCode += " EMPTY:0";
		}
	}

	// Si se selecciono la particion 4 y es CACHE
	if(form.part4.value === "CACHE"){
		if (form.check4.checked) {
			if (form.size4.value === "0") {
				sizecacheCode="\
ogEcho session \"[20] $MSG_HELP_ogGetCacheSize\"\n \
sizecache=`ogGetCacheSize` \n ";
				cacheCode="\
initCache "+n_disk+" $sizecache NOMOUNT  &>/dev/null \n ";		
			} else {
				if (form.size4.value === "CUSTOM") {
					cacheSize = form.size4custom.value; 
				} else {
					cacheSize = form.size4.value;
				} 
				cacheCode="\
initCache " + n_disk + " " + cacheSize + " NOMOUNT &>/dev/null \n ";
			} 
			cacheCode += "ogEcho session \"[60] $MSG_HELP_ogListPartitions "+n_disk+"\" \n ";
			cacheCode += "ogExecAndLog command session ogListPartitions "+n_disk+" \n ";
		} else {
			partCode += " EMPTY:0";
		}
	}

	if (extended) {
		var lastLogical=5;
		for (nPart=9; nPart>5; nPart--) {
			if (eval ("form.check"+nPart+".checked")) {
				lastLogical = nPart;
				break;
			}
		}
		for (nPart=5; nPart<=lastLogical; nPart++) {
			partCheck=eval("form.check"+nPart);
			if (partCheck.checked) {
				partType=eval("form.part"+nPart);
				if (partType.value === "CUSTOM" ) {
					partTypeCustom=eval("form.part"+nPart+"custom");
					logicalCode += " " + partTypeCustom.value;
					// Partición swap
					if (partTypeCustom.value === "LINUX-SWAP")
						swapPart.push(nPart);
				} else {
					logicalCode += " " + partType.value;
					// Partición swap
					if (partType.value === "LINUX-SWAP")
						swapPart.push(nPart);
				}
				partSize=eval("form.size"+nPart);
				if (partSize.value === "CUSTOM" ) {
					partSizeCustom=eval("form.size"+nPart+"custom");
					logicalCode += ":" + partSizeCustom.value;
				} else {
					logicalCode += ":" + partSize.value;
				}
			} else {
				logicalCode += " EMPTY:0";
			}
		}
		partCode += logicalCode;
	}

	// Formateo de la partición swap
	if (swapPart.length > 0) {
	    for (var i=0; i < swapPart.length; i++) {
		swapCode += " ogEcho session log \"[95] $MSG_HELP_ogFormat "+n_disk+" "+swapPart[i]+" LINUX-SWAP \"\n  " ;
		swapCode += " ogExecAndLog command ogFormat "+n_disk+" "+swapPart[i]+" LINUX-SWAP \n ";
	    }

        }

	form.codigo.value="\
ogEcho log session \"[0]  $MSG_HELP_ogCreatePartitions "+n_disk+"\"\n \
ogEcho session \"[10] $MSG_HELP_ogUnmountAll "+n_disk+"\"\n \
ogUnmountAll "+n_disk+" 2>/dev/null \n \
ogUnmountCache \n \
" + sizecacheCode + "\
ogEcho session \"[30] $MSG_HELP_ogUpdatePartitionTable "+n_disk+"\"\n \
ogCreatePartitionTable "+n_disk+" "+tipo_part_table +" \n \
ogDeletePartitionTable "+n_disk+" \n \
ogUpdatePartitionTable "+n_disk+" \n \
" + cacheCode + "\
ogEcho session \"[70] $MSG_HELP_ogCreatePartitions  " + partCode + "\"\n \
ogExecAndLog command ogCreatePartitions "+n_disk+" " + partCode + " \n \
EVAL=$? \n \
if [ $EVAL -eq 0 ]; then \n \
  ogEcho session \"[80] $MSG_HELP_ogSetPartitionActive "+n_disk+" 1\"\n \
  ogSetPartitionActive "+n_disk+" 1 \n \
  ogEcho log session \"[90] $MSG_HELP_ogListPartitions  "+n_disk+"\"\n \
  ogUpdatePartitionTable "+n_disk+" \n \
  ms-sys /dev/sda | grep unknow && ms-sys /dev/sda \n \
  ogExecAndLog command session log ogListPartitions "+n_disk+" \n\
  "+ swapCode +"\
else \n \
  ogEcho session log \"[100] ERROR: $MSG_HELP_ogCreatePartitions\" \n \
  return $EVAL \n \
fi";
}


function codeParticionadoGPT (form) {
        var partCode="";
        var logicalCode="";
	var sizecacheCode="";
        var cacheCode="";
        var cacheSize;
        var extended=false;
        var n_disk = form.n_disk.value;
        var tipo_part_table = form.tipo_part_table.value;
	var swapPart = [];
	var swapCode = "";
		var numParts=document.getElementById("numGPTpartitions").value;
		
        for (var nPart=1; nPart <= numParts; nPart++) {
                var partCheck=eval("form.checkGPT"+nPart);
                if (partCheck.checked) {
			// Distinguimos entre cache y el resto de particiones
			// Solo tratamos la particion 4 como cache, si se selecciono este tipo
			if(nPart === 4 && form.partGPT4.value === "CACHE") {
				if (form.sizeGPT4.value === "0") {
                                        sizecacheCode="\
ogEcho session \"[20] $MSG_HELP_ogGetCacheSize\" \n \
sizecache=`ogGetCacheSize` \n ";
					cacheCode="\
ogEcho session \"[50] $MSG_HELP_ogCreateCache\"\n \
initCache "+ n_disk +" $sizecache NOMOUNT &>/dev/null \n ";
				} else {
					if (form.sizeGPT4.value === "CUSTOM") {
						cacheSize = form.sizeGPT4custom.value;
					} else {
						cacheSize = form.sizeGPT4.value;
					}
					cacheCode="\
ogEcho session \"[50] $MSG_HELP_ogCreateCache\"\n \
initCache "  + n_disk +" "+ cacheSize + " NOMOUNT &>/dev/null \n ";
				}
				cacheCode += "ogEcho session \"[60] $MSG_HELP_ogListPartitions "+n_disk+"\"\n ";
				cacheCode += "ogExecAndLog command session ogListPartitions "+n_disk+" \n ";
			} else{
				var partType=eval("form.partGPT"+nPart);
				if (partType.value === "CUSTOM" ) {
					var partTypeCustom=eval("form.partGPT"+nPart+"custom");
					partCode += " " + partTypeCustom.value;
					// Partición swap
					if (partTypeCustom.value === "LINUX-SWAP")
						swapPart.push(nPart);
				} else {
					partCode += " " + partType.value;
					// Partición swap
					if (partType.value === "LINUX-SWAP")
						swapPart.push(nPart);
				}
				var partSize=eval("form.sizeGPT"+nPart);
				if (partSize.value === "CUSTOM" ) {
					var partSizeCustom=eval("form.sizeGPT"+nPart+"custom");
					partCode += ":" + partSizeCustom.value;
				} else {
					partCode += ":" + partSize.value;
				}
			}
                } else {
			partCode += " EMPTY:0";
                }
        }
	// Formateo de la partición swap
	if (swapPart.length > 0) {
            for (var i=0; i < swapPart.length; i++) {
                swapCode += " ogEcho session log \"[95] $MSG_HELP_ogFormat "+n_disk+" "+swapPart[i]+" LINUX-SWAP \" \n" ;
                swapCode += " ogExecAndLog command ogFormat "+n_disk+" "+swapPart[i]+" LINUX-SWAP \n";
	    }
	}
            
	form.codigo.value="\
ogEcho log session \"[0]  $MSG_HELP_ogCreatePartitions "+n_disk+"\"\n \
ogEcho session \"[10] $MSG_HELP_ogUnmountAll "+n_disk+"\"\n \
ogUnmountAll "+n_disk+" \n \
ogUnmountCache \n \
" + sizecacheCode + "\
ogEcho session \"[30] $MSG_HELP_ogUpdatePartitionTable "+n_disk+"\"\n \
ogCreatePartitionTable "+n_disk+" "+tipo_part_table +" \n \
ogDeletePartitionTable "+n_disk+" \n \
ogUpdatePartitionTable "+n_disk+" \n \
" + cacheCode + "\
ogEcho session \"[70] $MSG_HELP_ogCreatePartitions " + partCode + "\"\n \
ogExecAndLog command ogCreatePartitions "+n_disk+" " + partCode + "\n \
EVAL=$? \n \
if [ $EVAL -eq 0 ]; then \n \
    ogEcho session \"[80] $MSG_HELP_ogSetPartitionActive "+n_disk+" 1\"\n \
    ogSetPartitionActive "+n_disk+" 1 \n \
    ogEcho log session \"[90] $MSG_HELP_ogListPartitions "+n_disk+"\"\n \
    ogUpdatePartitionTable "+n_disk+" \n \
    ms-sys /dev/sda | grep unknow && ms-sys /dev/sda \n \
    ogExecAndLog command session log ogListPartitions "+n_disk+" \n \
else \n \
    ogEcho session log \"[100] ERROR: $MSG_HELP_ogCreatePartitions\" \n \
    return $EVAL \n \
fi \n ";

// Formateo de la swap
form.codigo.value += swapCode;
}


function showPartitionForm (tipo_table_part) {
	document.getElementById("form"+tipo_table_part).style.display="inline";
	if(tipo_table_part === "MSDOS"){
		// De los dos tipos, se oculta el otro
		document.getElementById("formGPT").style.display="none";
		document.getElementById("warngpt").style.display="none";
	} else{
		document.getElementById("formMSDOS").style.display="none";
		// Para GPT obliga que primera partición sea EFI
		document.getElementById("checkGPT1").checked=true;
		document.getElementById("checkGPT1").disabled=true;
		document.getElementById("partGPT1").value="CUSTOM";
		document.getElementById("partGPT1custom").value="EFI";
		document.getElementById("sizeGPT1").value="CUSTOM";
		document.getElementById("sizeGPT1").disabled=false;
		document.getElementById("sizeGPT1custom").value="512000";
		document.getElementById("sizeGPT1custom").disabled=false;
		document.getElementById("warngpt").style.display="table-row";
	}
}


// Código de pulsación de selección de partición.
function clickPartitionCheckbox (form, npart, isGPT) {
	// Si el parametro no esta definido, se toma como false
	isGPT = (isGPT)?isGPT:"false";
	var prefix="";
	if(isGPT === true){
		prefix="GPT";
	}
	var partCheck=eval("form.check"+prefix+npart);
	var partType=eval("form.part"+prefix+npart);
	var partSize=eval("form.size"+prefix+npart);
	var partTypeCustom=eval("form.part"+prefix+npart+"custom");
	var partSizeCustom=eval("form.size"+prefix+npart+"custom");
	var freeDisk=document.getElementById("freedisk"+prefix);
	//var logical=document.getElementById("logicas"+prefix);
	if (partCheck.checked) {
		partType.disabled=false;
		partSize.disabled=false;
		if(npart !== 4){
			if (partType.options[partType.selectedIndex].value === "CUSTOM") {
				partTypeCustom.disabled=false;
			}
		}
		partSizeCustom.disabled = partSize.options[partSize.selectedIndex].value !== "CUSTOM";
	} else {
		partType.disabled=true;
		partSize.disabled=true;
		// El campo TypeCustom no existe para la particion 4
		if(npart !== 4)
			partTypeCustom.disabled=true;
		partSizeCustom.disabled=true;
	}
	if (npart <= 4) {
		// Si el formulario es GPT no hay extendidas
		if(isGPT !== true){
			checkExtendedPartition(form);
		}
		calculateFreeDisk(form);
	}
}

/**
 * Dado un numero de disco, recorre todos los input hidden con nombre disksize_"disco"
 * y devuelve el de menor valor
 */
function getMinDiskSize(disk){
	var diskSizeArray = document.getElementsByName("disksize_"+disk);
	var minSize = diskSizeArray[0].value;
	for(var i= 1; i < diskSizeArray.length; i++){
		if(diskSizeArray[i].value < minSize)
			minSize = diskSizeArray[i].value;
	}
	// Restar sectores iniciales del disco al tamaño total (1 MB).
	return (minSize > 1024 ? minSize - 1024 : minSize)
}

// Calcula el tamaño de la mayor cache y lo guarda en un campo oculto
function getMaxCacheSize() {
	var cacheSizeArray = document.getElementsByName("cachesize");
        // Si no existe cache el valor es cero.
        if (cacheSizeArray[0]) {
	    var maxSize = cacheSizeArray[0].value;
	    for(var i= 1; i < cacheSizeArray.length; i++){
		if(maxSize < cacheSizeArray[i].value)
			maxSize = cacheSizeArray[i].value;
	    }
	    document.getElementById("maxcachesize").value = maxSize;
        } else {
	    document.getElementById("maxcachesize").value = 0;
        }
}


// Comprueba que la cache quepa en el espacio libre del disco
function validaCache (freedisk) {
	var form = document.fdatos;
	var maxcachesize = parseInt(document.getElementById("maxcachesize").value);
	if(form.part4.value === "CACHE" && form.check4.checked && form.size4.value === 0 ){
	    return ((freedisk - maxcachesize) > 0);
	}
	return true;
}

// Código para calcular el espacio libre del disco.
function calculateFreeDisk(form) {
	// Si esta seleccionada la opcion GPT, se llama a la funcion correspondiente
	if(document.getElementById("tipo_part_table").value === "GPT"){
		calculateFreeGPTDisk(form);
	}
	// Capturamos el disco seleccionado
	var disk = document.getElementById("n_disk").value;
	// Buscamos por nombre todos los campos disksize_"disk" y nos quedamos con el de menor valor
	var diskSize = getMinDiskSize(disk);
	
		
	var freeDisk=document.getElementById("freedisk");
	freeDisk.value=diskSize;
	for (var npart=1; npart<=4; npart++) {
		var partCheck=eval("form.check"+npart);
		var partSize=eval("form.size"+npart);
		var partSizeCustom=eval("form.size"+npart+"custom");
		if (partCheck.checked) {
			if (partSize.options[partSize.selectedIndex].value === "CUSTOM") {
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
	if (form.size4.value === 0) {
		freeDisk.value += " (- cache)";		// Aviso de caché sin modificar.
	}
}

// Código para calcular el espacio libre del disco. en el formulario GPT
function calculateFreeGPTDisk(form) {
	// Si esta seleccionada la opcion MSDOS, se llama a la funcion correspondiente
	if(document.getElementById("tipo_part_table").value === "MSDOS"){
		calculateFreeDisk(form);
	}
	// Capturamos el disco seleccionado
	var disk = document.getElementById("n_disk").value;
	// Buscamos el input hidden para el disco seleccionado
	document.getElementById('freediskGPT').value=getMinDiskSize(disk);
	
	var freeDisk=document.getElementById("freediskGPT");
	// Capturamos el numero de particiones que hay hechas
	var numParts=document.getElementById("numGPTpartitions").value;
	for (var npart=1; npart<=numParts; npart++) {
            var partCheck=eval("form.checkGPT"+npart);
            var partSize=eval("form.sizeGPT"+npart);
            var partSizeCustom=eval("form.sizeGPT"+npart+"custom");
            if (partCheck.checked) {
                    if (partSize.options[partSize.selectedIndex].value === "CUSTOM") {
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
    if (form.size4.value === 0) {
            freeDisk.value += " (- cache)";         // Aviso de caché sin modificar.
    }
}

// Agrega una nueva fila a la tabla de particiones con una nueva particion
function addGPTPartition(){
	var partitionTypes = "";
	partitionTypes+='<OPTION value="WINDOWS"> Windows </OPTION>';
	partitionTypes+='<OPTION value="WIN-RESERV"> Windows Reserved </OPTION>';
	partitionTypes+='<OPTION value="LINUX"> Linux </OTION>';
	partitionTypes+='<OPTION value="LINUX-RESERV"> Linux Reserved </OPTION>';
	partitionTypes+='<OPTION value="LINUX-SWAP"> Linux Swap </OPTION>';
	partitionTypes+='<OPTION value="LINUX-RAID"> Linux RAID </OPTION>';
	partitionTypes+='<OPTION value="LINUX-LVM"> Linux LVM </OPTION>';
	partitionTypes+='<OPTION value="CHROMEOS"> ChromeOS </OTION>';
	partitionTypes+='<OPTION value="CHROMEOS-KRN"> ChromeOS Kernel </OPTION>';
	partitionTypes+='<OPTION value="CHROMEOS-RESERV"> ChromeOS Reserved </OPTION>';
	partitionTypes+='<OPTION value="HFS"> MacOS HFS </OPTION>';
	partitionTypes+='<OPTION value="HFS-BOOT"> MacOS HFS Boot </OPTION>';
	partitionTypes+='<OPTION value="HFS-RAID"> MacOS HFS RAID </OPTION>';
	partitionTypes+='<OPTION value="FREEBSD"> FreeBSD </OPTION>';
	partitionTypes+='<OPTION value="FREEBSD-DISK"> FreeBSD Disk </OPTION>';
	partitionTypes+='<OPTION value="FREEBSD-BOOT"> FreeBSD Boot </OPTION>';
	partitionTypes+='<OPTION value="FREEBSD-SWAP"> FreeBSD Swap </OPTION>';
	partitionTypes+='<OPTION value="SOLARIS"> Solaris </OPTION>';
	partitionTypes+='<OPTION value="SOLARIS-DISK"> Solaris Disk </OPTION>';
	partitionTypes+='<OPTION value="SOLARIS-BOOT"> Solaris Boot </OPTION>';
	partitionTypes+='<OPTION value="SOLARIS-SWAP"> Solaris Swap </OPTION>';
	partitionTypes+='<OPTION value="EFI"> EFI </OPTION>';
	partitionTypes+='<OPTION value="MBR"> MBR </OPTION>';
	partitionTypes+='<OPTION value="BIOS-BOOT"> BIOS Boot </OPTION>';


	var table = document.getElementById("particionesGPT");
	// Capturamos el numero de particiones, antes incrementamos
	document.getElementById("numGPTpartitions").value = parseInt(document.getElementById("numGPTpartitions").value)+1;
	var numPart=document.getElementById("numGPTpartitions").value;
	var partitionRow = table.insertRow(-1);
	partitionRow.id = "trPartition"+numPart;
	partitionRow.innerHTML="<td> \
<input type='checkbox' name='checkGPT"+numPart+"' value='checkGPT"+numPart+"' onclick='clickPartitionCheckbox(this.form, "+numPart+",true);' /> Partici&oacute;n "+numPart+"</td> \
<td>\
<select name='partGPT"+numPart+"' id='partGPT"+numPart+"' style='width:220' disabled='true' onclick=' \
        if (this.options[this.selectedIndex].value === \"CUSTOM\") { \
                this.form.partGPT"+numPart+"custom.disabled=false; \
        } else { \
                this.form.partGPT"+numPart+"custom.disabled=true; \
        }'><option value='CUSTOM'> Personalizar </option> \
</select> \
<br> \
<select name='partGPT"+numPart+"custom' id='partGPT"+numPart+"custom' style='width:220px' disabled='true' >"+partitionTypes+"</select> \
</td> \
<td> \
<select name='sizeGPT"+numPart+"' id='sizeGPT"+numPart+"' style='width:220px' disabled='true' onclick=' \
        if (this.form.size"+numPart+".options[this.form.size"+numPart+".selectedIndex].value === \"CUSTOM\") { \
                this.form.sizeGPT"+numPart+"custom.disabled=false; \
        } else { \
                this.form.sizeGPT"+numPart+"custom.disabled=true; \
        } \
' onchange='calculateFreeGPTDisk(this.form);'>0<option value='CUSTOM'> Personalizar </option> \
</select> \
<br /> \
<input type='text' style='width:100px' name='sizeGPT"+numPart+"custom' value='0' disabled='true' onchange='calculateFreeDisk(this.form);' /> \
</td>"

}

// Agrega una nueva fila a la tabla de particiones con una nueva particion
function deleteGPTPartition(){
	var table = document.getElementById("particionesGPT");
        // Capturamos el numero de particiones
	var numPart=document.getElementById("numGPTpartitions").value;
	// Si ya solo quedan 4 particiones, no se elimina ni se decrementa el contador
	if(numPart > 4){
		var partitionRow = document.getElementById("trPartition"+numPart);
		table.deleteRow(partitionRow.rowIndex);
		// Decrementamos el numero de particiones
		document.getElementById("numGPTpartitions").value = parseInt(document.getElementById("numGPTpartitions").value)-1;
	}
}

// Código para comprobar si hay partición extendida activa para mostrar las lógicas.
function checkExtendedPartition(form) {
	var logical=document.getElementById("logicas");
	var visible=false;
	for (var npart=1; npart<=4; npart++) {
		var partCheck=eval("form.check"+npart);
		var partType=eval("form.part"+npart);
		var partTypeCustom=eval("form.part"+npart+"custom");
		if (partCheck.checked) {
			partType.style.fontWeight = "normal";

			if (partType.value === "EXTENDED") {
				visible=true;
				partType.style.fontWeight = "bold";
			}
			// La particion 4 no tiene partTypeCustom
			if(npart !== 4){
				partTypeCustom.style.fontWeight = "normal";
				if (partType.value === "CUSTOM" && partTypeCustom.value === "EXTENDED") {
					visible=true;
					partTypeCustom.style.fontWeight = "bold";
				}
			}
		}
	}
	if (visible) {
		logical.style.visibility="visible";
	} else {
		logical.style.visibility="hidden";
	}
}
