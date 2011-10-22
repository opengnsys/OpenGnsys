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
command="cloneRemoteFromMaster " + form.ipMaster.value + " " + form.source.value + "  " + protocol  + " " + form.targetpart.value + " " + form.tool.value + " " + form.compresor.value;
form.codigo.value="\
echo \"[0] iniciando " + command + " \" | tee -a $OGLOGFILE $OGLOGSESSION \n " +
command + " | tee -a $OGLOGCOMMAND \n ";
//cloneRemoteFromMaster " + form.ipMaster.value + " " + form.source.value + "  " + protocol  + " " + form.targetpart.value + " " + form.tool.value + " " + form.compresor.value + " | tee -a $OGLOGCOMMAND \n";
//form.codigo.value="cloneRemoteFromMaster " + form.ipMaster.value + " " + form.source.value + "  " + protocol  + " " + form.targetpart.value + " " + form.tool.value + " " + form.compresor.value;
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
	command="deployImage REPO /" + form.idimagen.value + " 1 " + form.idparticion.value + " " + protocol  ;
	form.codigo.value="\
echo [0] iniciando " + command +" | tee -a $OGLOGSESSION \n \ " +
command + " | tee -a $OGLOGCOMMAND";
	//form.codigo.value="deployImage REPO /" + form.idimagen.value + " 1 " + form.idparticion.value + " " + protocol  ;
}
else
{
	command="updateCache REPO /" + form.idimagen.value + ".img" + " " + protocol  ;
	form.codigo.value="\
echo [0] iniciando " + command +" | tee -a $OGLOGSESSION \n \ " +
command + " | tee -a $OGLOGCOMMAND";		
	//form.codigo.value="updateCache REPO /" + form.idimagen.value + ".img" + " " + protocol  ;
}

}

function codeParticionado (form) {
	var partCode="";
	var logicalCode="";
	var cacheCode;
	var cacheSize;
	var extended=false;

	for (var nPart=1; nPart<4; nPart++) {
		var partCheck=eval("form.check"+nPart);
		if (partCheck.checked) {
			var partType=eval("form.part"+nPart);
			if (partType.value == "CUSTOM" ) {
				var partTypeCustom=eval("form.part"+nPart+"custom");
				partCode += " " + partTypeCustom.value;
				if (partTypeCustom.value == "EXTENDED") {
					extended=true;
				}
			} else {
				partCode += " " + partType.value;
				if (partType.value == "EXTENDED") {
					extended=true;
				}
			}
			var partSize=eval("form.size"+nPart);
			if (partSize.value == "CUSTOM" ) {
				var partSizeCustom=eval("form.size"+nPart+"custom");
				partCode += ":" + partSizeCustom.value;
			} else {
				partCode += ":" + partSize.value;
			}
		} else {
			partCode += " EMPTY:0";
		}
	}
	if (form.check4.checked) {
		if (form.size4.value == "0") {
			//cacheCode = " ogUnmountCache \n ogUnmountAll 1 \n sizecache=`ogGetPartitionSize 1 4` \n ogDeletePartitionTable 1 \n ogUpdatePartitionTable 1 \n initCache $sizecache ";
			cacheCode="\
			echo \"[20] detectando tamaño de la cache\" | tee -a $OGLOGSESSION \n \
			sizecache=`ogGetPartitionSize 1 4` \n \
			echo \"[30] borrando y actualizando tabla particiones disco 1\" | tee -a $OGLOGSESSION \n \
			ogDeletePartitionTable 1  \n \
			ogUpdatePartitionTable 1  | tee -a $OGLOGCOMMAND \n \
			echo \"[50] Activando cache\" | tee -a $OGLOGSESSION \n \
			initCache $sizecache  &>/dev/null  | tee -a $OGLOGCOMMAND \n ";		
		} else {
			if (form.size4.value == "CUSTOM") { 
				cacheSize = form.size4custom.value; 
			} else {
				cacheSize = form.size4.value;
			} 
			//cacheCode = " ogUnmountCache \n ogUnmountAll 1 \n ogDeletePartitionTable 1 \n ogUpdatePartitionTable 1 \n initCache " + cacheSize;
			cacheCode="\
			echo \"[30] borrando y actualizando tabla particiones disco 1\" | tee -a $OGLOGSESSION \n \
			ogDeletePartitionTable 1  \n \
			ogUpdatePartitionTable 1 \n \
			echo \"[50] Activando cache\" | tee -a $OGLOGSESSION \n \
			initCache "  + cacheSize + " &>/dev/null  | tee -a $OGLOGCOMMAND";	
		} 
	} else {
		//cacheCode = " ogUnmountCache \n ogUnmountAll 1 \n ogDeletePartitionTable 1 \n ogUpdatePartitionTable 1 ";
		cacheCode="\
		echo \"[30] borrando y actualizando tabla particiones disco 1\" | tee -a $OGLOGSESSION \n \
		ogDeletePartitionTable 1  \n \
		ogUpdatePartitionTable 1 \n";
		partCode += " EMPTY:0";
	}
	if (extended) {
		var lastLogical=5;
		for (var nPart=9; nPart>5; nPart--) {
			if (eval ("form.check"+nPart+".checked")) {
				lastLogical = nPart;
				break;
			}
		}
		for (var nPart=5; nPart<=lastLogical; nPart++) {
			var partCheck=eval("form.check"+nPart);
			if (partCheck.checked) {
				var partType=eval("form.part"+nPart);
				if (partType.value == "CUSTOM" ) {
					var partTypeCustom=eval("form.part"+nPart+"custom");
					logicalCode += " " + partTypeCustom.value;
				} else {
					logicalCode += " " + partType.value;
				}
				var partSize=eval("form.size"+nPart);
				if (partSize.value == "CUSTOM" ) {
					var partSizeCustom=eval("form.size"+nPart+"custom");
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

//	form.codigo.value="\
//" + cacheCode + " \n \
//ogListPartitions 1 \n \
//ogCreatePartitions 1 " + partCode + " \n \
//ogSetPartitionActive 1 1 \n \
//ogUpdatePartitionTable 1 \n \
//ogListPartitions 1 \n"; 
form.codigo.value="\
echo \"[0] iniciando particionado\" | tee -a $OGLOGSESSION $OGLOGFILE \n \
echo \"[10] desmontando cache y particiones del disco 1\" | tee -a $OGLOGSESSION \n \
ogUnmountCache \n \
ogUnmountAll 1 \n  \
" + cacheCode + " \n \
echo \"[60] Listando tabla particiones 1\" | tee -a $OGLOGSESSION \n \
ogListPartitions 1 | tee -a $OGLOGCOMMAND $OGLOGSESSION \n \
echo \"[70] Creando tabla particiones " + partCode + " \" | tee -a $OGLOGSESSION \n \
ogCreatePartitions 1 " + partCode + " | tee -a $OGLOGCOMMAND \n \
echo \"[80] Activando primera particion del disco 1\" | tee -a $OGLOGSESSION \n \
ogSetPartitionActive 1 1 \n \
echo \"[100] Listando la nueva tabla particiones  1\" | tee -a $OGLOGSESSION $OGLOGFILE \n \
ogUpdatePartitionTable 1 \n \
ms-sys /dev/sda | grep unknow && ms-sys /dev/sda \n \
ogListPartitions 1 | tee -a $OGLOGCOMMAND $OGLOGSESSION $OGLOGFILE \n"; 	

}


// Código de pulsación de selección de partición.
function clickPartitionCheckbox(form, npart) {
	var partCheck=eval("form.check"+npart);
	var partType=eval("form.part"+npart);
	var partSize=eval("form.size"+npart);
	var partTypeCustom=eval("form.part"+npart+"custom");
	var partSizeCustom=eval("form.size"+npart+"custom");
	var freeDisk=document.getElementById("freedisk");
	var logical=document.getElementById("logicas");
	if (partCheck.checked) {
		partType.disabled=false;
		partSize.disabled=false;
		if (partType.options[partType.selectedIndex].value == "CUSTOM") {
			partTypeCustom.disabled=false;
		}
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
	}
	if (npart <= 4) {
		checkExtendedPartition(form);
		calculateFreeDisk(form);
	}
}


// Código para calcular el espacio libre del disco.
function calculateFreeDisk(form) {
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

// Código para comprobar si hay partición extendida activa para mostrar las lógicas.
function checkExtendedPartition(form) {
	var logical=document.getElementById("logicas");
	var visible=false;
	for (npart=1; npart<4; npart++) {
		var partCheck=eval("form.check"+npart);
		var partType=eval("form.part"+npart);
		var partTypeCustom=eval("form.part"+npart+"custom");
		if (partCheck.checked) {
			partType.style.fontWeight = "normal";
			partTypeCustom.style.fontWeight = "normal";
			if (partType.value == "EXTENDED") {
				visible=true;
				partType.style.fontWeight = "bold";
			}
			if (partType.value == "CUSTOM" && partTypeCustom.value == "EXTENDED") {
				visible=true;
				partTypeCustom.style.fontWeight = "bold";
			}
		}
	}
	if (visible) {
		logical.style.visibility="visible";
	} else {
		logical.style.visibility="hidden";
	}
}

