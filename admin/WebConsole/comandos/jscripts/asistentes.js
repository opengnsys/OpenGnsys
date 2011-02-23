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
//alert("codeCloneRemotePartition(form)");
form.codigo.value="cloneRemoteFromMaster " + form.ipMaster.value + " 1 " + form.PartOrigen.value + "  " + form.mcastpuerto.value  + ":" + form.mcastmodo.value + ":" + form.mcastdireccion.value + ":" + form.mcastvelocidad.value + "M:" + form.mcastnclien.value + ":" + form.mcastseg.value + " 1 " + form.PartOrigen.value + " " + form.tool.value + " " + form.compresor.value;
}

function codeDeployImage(form){
alert("codeDeployImage(form)");
switch (form.idmetodo.value)
{
case "MULTICAST":
 protocol="MULTICAST " + form.mcastpuerto.value  + ":" + form.mcastmodo.value + ":" + form.mcastdireccion.value + ":" + form.mcastvelocidad.value + "M:" + form.mcastnclien.value + ":" + form.mcastseg.value + " ";
break;
case "TORRENT":
protocol=" TORRENT " +  form.modp2p.value + ":" + form.timep2p.value;
break;
}
//form.codigo.value="deployImage REPO /";
form.codigo.value="deployImage REPO /" + form.idimagen.value + " 1 " + form.idparticion.value + " " + protocol  ;
}

function codeParticionado(form){
alert("codeParticionado(form)");
form.codigo.value="ogUnmountCache \n ogUnmountAll 1 \n initCache "  + form.size4.value +" \n ogListPartitions 1 \n ogCreatePartitions 1 " + form.part1.value  + ":" + form.size1.value + " \n ogListPartitions 1"; 
//
// ;
// + " " + form.part2.value + ":" + form.size2.value + " " + form.part3.value + ":" + form.size3.value + " ogListPartitions 1";
}
