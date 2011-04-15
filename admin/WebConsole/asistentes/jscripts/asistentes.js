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
form.codigo.value="cloneRemoteFromMaster " + form.ipMaster.value + " 1 " + form.PartOrigen.value + "  " + protocol  + " 1 " + form.PartOrigen.value + " " + form.tool.value + " " + form.compresor.value;

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
}
//form.codigo.value="deployImage REPO /";
form.codigo.value="deployImage REPO /" + form.idimagen.value + " 1 " + form.idparticion.value + " " + protocol  ;
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
