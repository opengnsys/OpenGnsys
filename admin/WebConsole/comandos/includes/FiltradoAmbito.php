<?php
// ******************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2012
// Fecha Última modificación: Febrero-2012
// Nombre del fichero: FiltradoAmbito.php
// Descripción : 
//		Colección de funciones en php y javascript que implementan la posibilidad de aplicar filtro de selección
//		a un determinado grupo de ordenadores dentro de un aula para aplicarles comandos.
//
// ********************************************************************************************************************
//
include_once("../idiomas/php/".$idioma."/estados_".$idioma.".php");

$cadenaip="";
$cid=0; // Identificadores para tablas de ordenadores
switch($ambito)
{
	case $AMBITO_CENTROS :
		$cmd->texto="SELECT idcentro,nombrecentro FROM centros WHERE idcentro=".$idambito;
		RecorriendoCentro($cmd);
		break;
	case $AMBITO_GRUPOSAULAS :
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE tipo=".$AMBITO_GRUPOSAULAS." AND idgrupo=".$idambito;
		RecorriendoGruposAulas($cmd);
		break;
	case $AMBITO_AULAS :
		$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE idaula=".$idambito;
		RecorriendoAulas($cmd);
		break;
	case $AMBITO_GRUPOSORDENADORES :
		$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposordenadores WHERE idgrupo=".$idambito;
		RecorriendoGruposOrdenadores($cmd);
		break;
}
echo '<P><INPUT type="hidden" id="cadenaip" value="'.$cadenaip.'"></P>';
//________________________________________________________________________________________________________
//
function RecorriendoCentro($cmd)
{
	global $AMBITO_GRUPOSAULAS;
	global $TbMsg;
		
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	if(!$rs->EOF){
		$idcentro=$rs->campos["idcentro"];
		$nombrecentro=$rs->campos["nombrecentro"];
		
		$urlimg='../images/iconos/centros.gif';
		$ambito=$TbMsg[0];
		echo '<p align=center><IMG src="'.$urlimg.'">&nbsp;&nbsp;
		<span align=center class=subcabeceras><U>'.$ambito.':'.$nombrecentro.'</U></span></p>';
	
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE idcentro=".$idcentro." AND grupoid=0 
						AND tipo=".$AMBITO_GRUPOSAULAS." ORDER BY nombregrupo ";
		RecorriendoGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE idcentro=".$idcentro." AND grupoid=0 ORDER BY nombreaula";
		RecorriendoAulas($cmd);
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
//
function RecorriendoGruposAulas($cmd)
{
	global $AMBITO_GRUPOSAULAS;
	global $TbMsg;
	
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idgrupo=$rs->campos["idgrupo"];
		$nombregrupo=$rs->campos["nombregrupo"];
		
		$urlimg='../images/iconos/carpeta.gif';
		$ambito=$TbMsg[1];
		echo '<p align=center><IMG src="'.$urlimg.'">&nbsp;&nbsp;
		<span align=center class=subcabeceras><U>'.$ambito.':'.$nombregrupo.'</U></span></p>';		
		
		$cmd->texto="SELECT idgrupo,nombregrupo FROM grupos WHERE grupoid=".$idgrupo." 
						AND tipo=".$AMBITO_GRUPOSAULAS." ORDER BY nombregrupo";
		RecorriendoGruposAulas($cmd);
		$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE  grupoid=".$idgrupo." ORDER BY nombreaula";
		RecorriendoAulas($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
//
function RecorriendoAulas($cmd)
{
	global $TbMsg;

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idaula=$rs->campos["idaula"];
		$nombreaula=$rs->campos["nombreaula"];
		
		$urlimg='../images/iconos/aula.gif';
		$ambito=$TbMsg[2];
		echo '<p align=center><IMG src="'.$urlimg.'">&nbsp;&nbsp;
		<span align=center class=subcabeceras><U>'.$ambito.':'.$nombreaula.'</U></span></p>';		
				
		$cmd->texto="SELECT idordenador,nombreordenador,ip,mac FROM ordenadores WHERE  idaula=".$idaula;
		RecorriendoOrdenadores($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
//
function RecorriendoGruposOrdenadores($cmd)
{
	global $TbMsg;
	
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$idgrupo=$rs->campos["idgrupo"];
		$nombregrupo=$rs->campos["nombregrupo"];
		
		$urlimg='../images/iconos/carpeta.gif';
		$ambito=$TbMsg[3];
		echo '<p align=center><IMG src="'.$urlimg.'">&nbsp;&nbsp;
		<span align=center class=subcabeceras><U>'.$ambito.':'.$nombregrupo.'</U></span></p>';	
		
		$cmd->texto="SELECT idgrupo,nombregrupoordenador FROM gruposOrdenadores WHERE grupoid=".$idgrupo." ORDER BY nombregrupoordenador";
		RecorriendoGruposOrdenadores($cmd);
		$cmd->texto="SELECT idordenador,nombreordenador,ip,mac FROM ordenadores WHERE  grupoid=".$idgrupo;
		RecorriendoOrdenadores($cmd);
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//____________________________________________________________________________________________________
//
function RecorriendoOrdenadores($cmd)
{
	global $TbMsg;
	global $cadenaip;
	global $cid;		
			
	$cid++;		
	$cmd->texto.= " ORDER BY nombreordenador";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	$htmlCode="";
	$con=0;

	$htmlCode.='
<TABLE border=0 class="tabla_busquedas" align=center>
<TR>

<TD align="center">'.$TbMsg["STATUS_OFF"].'</TD>
<TD><INPUT  type="checkbox" checked onclick="selector(this,1,'.$cid.')"></TD>
		
<TD align="center">'.$TbMsg["STATUS_BSY"].'</TD>
<TD><INPUT   type="checkbox" checked onclick="selector(this,2,'.$cid.')"></TD>

<TD align="center">'.$TbMsg["STATUS_OPG"].'</TD>
<TD><INPUT  type="checkbox" checked onclick="selector(this,3,'.$cid.')"></TD>

<TD align="center">'.$TbMsg["STATUS_WXP"].'</TD>
<TD><INPUT  type="checkbox" checked onclick="selector(this,4,'.$cid.')"></TD>

<TD align="center">'.$TbMsg["STATUS_LNX"].'</TD>
<TD><INPUT  type="checkbox" checked onclick="selector(this,5,'.$cid.')"></TD>

</TR>
</TABLE>';
	
	$htmlCode.='<BR>';					
	$htmlCode.='<TABLE id="tbo-'.$cid.'" border=0 align=center>';
	$htmlCode.='<TR>';
		
	while (!$rs->EOF){
		$htmlCode.='<TD style="BORDER-BOTTOM: #999999 1px solid;BORDER-LEFT: #999999 1px solid;BORDER-RIGHT: #999999 1px solid;BORDER-TOP: #999999 1px solid;">';
		$nombreordenador=$rs->campos["nombreordenador"];
		$ip=$rs->campos["ip"];
		$cadenaip.=$ip.";";
		$htmlCode.='<TABLE  cellspacing=1 cellpadding=0>';
		$htmlCode.='	<TR><TD align="center"><img sondeo="" id="img-'.$ip.'" width=24 src="../images/ordenador_OFF.gif"></TD></TR>';
		$htmlCode.='	<TR><TD align="center"><SPAN style="FONT-SIZE:9px;	COLOR: #4f4f4f;">'.$nombreordenador.'</SPAN></TD></TR>';			
		$htmlCode.='	<TR><TD align="center"><SPAN style="FONT-SIZE:8px;	COLOR: #4f4f4f;">'.$ip.'</SPAN></TD></TR>';			
		$htmlCode.='	<TR><TD align="center"><INPUT id="chk-'.$ip.'" type="checkbox" ></TD></TR>';	
		$htmlCode.='</TABLE>';
		$htmlCode.='</TD>';
		$con++;
		if($con>15){
			$htmlCode.='</TR>';
			$htmlCode.='<TR>';
			$con=0;
		}
		$rs->Siguiente();
	}
	$htmlCode.='</TR>';
	$htmlCode.='</TABLE>';
	echo $htmlCode;
}
//______________________________________________________________________________________________________
//
echo '<SCRIPT language="javascript">';
echo 'var cid='.$cid.';';
echo '</SCRIPT>';
?>
<SCRIPT language="javascript">
function Sondeo(){
	var ambito=<? echo $ambito?>;
	var idambito=<? echo $idambito?>;
	var wurl="../principal/sondeo.php";
	var prm="ambito="+ambito+"&idambito="+idambito+"&sw=1";
	CallPage(wurl,prm,"retornoSondeo","POST");
	setTimeout("respuestaSondeo();",100); 	
}
//______________________________________________________________________________________________________
//
function retornoSondeo(resul)
{
	// No se hace nada
}
//________________________________________________________________________________________________________
//
function respuestaSondeo(){
	var ambito=<? echo $ambito?>;
	var idambito=<? echo $idambito?>;
	var wurl="../principal/sondeo.php";
	var prm="ambito="+ambito+"&idambito="+idambito+"&sw=2"; 
	CallPage(wurl,prm,"retornorespuestaSondeo","POST");
	setTimeout("respuestaSondeo();",5000); 	
}
//______________________________________________________________________________________________________
//
function retornorespuestaSondeo(resul)
{
	if(resul.length>0){
		var ip=""; // Dirección IP del ordenador
		var so=""; // Sistema operativo activo
		var objOrd=null; // Objeto ordenador
		var imgOrd="";
		var cadena=resul.split(";"); 
		for (var i=0;i<cadena.length;i++){
			var dual=cadena[i].split("/");
			ip=dual[0];
			so=dual[1];
			objOrd=document.getElementById("img-"+ip);
			if(objOrd){ 
					imgOrd=soIMG(so);
					if(objOrd.getAttribute("sondeo")!=imgOrd){ 
						objOrd.setAttribute("src", "../images/"+imgOrd);
						objOrd.setAttribute("sondeo",imgOrd);
						var objChk=document.getElementById("chk-"+ip);
						if(objChk)
							objChk.checked=true;
					}
			}		
		}
	}
}
//______________________________________________________________________________________________________
//
function soIMG(so)
{
	var MimgOrdenador="";
	switch(so){
				case 'INI':
								MimgOrdenador="ordenador_INI.gif";  // Cliente ocupado
								break;
				case 'BSY':
								MimgOrdenador="ordenador_BSY.gif";  // Cliente ocupado
								break;
				case 'OPG':
								MimgOrdenador="ordenador_OPG.gif";  // Cliente Rembo
								break;
				case 'WS2': 
								MimgOrdenador="ordenador_WS2.gif"; // Windows Server 2003
								break;
				case 'W2K':
								MimgOrdenador="ordenador_W2K.gif"; // Windows 2000
								break;
				case 'WXP':
								MimgOrdenador="ordenador_WXP.gif"; // Windows XP
								break;
				case 'WNT':
								MimgOrdenador="ordenador_WNT.gif"; // Windows NT
								break;
				case 'W95':
								MimgOrdenador="ordenador_W95.gif"; // Windows 95
								break;
				case 'W98':
								MimgOrdenador="ordenador_W98.gif"; // Windows 98
								break;
				case 'WML':
								MimgOrdenador="ordenador_WML.gif"; // Windows Millenium
								break;
				case 'WIN':
								MimgOrdenador="ordenador_WXP.gif"; // Windows
								break;								
				case 'LNX':
								MimgOrdenador="ordenador_LNX.gif"; // Linux
								break;
				default:
								MimgOrdenador="ordenador_OFF.gif"; // Apagado
								break;
	}
	return(MimgOrdenador);
}
//______________________________________________________________________________________________________
//
function selector(oSLCT,op,id)
{
	var sw=oSLCT.checked;
	var objTB=document.getElementById("tbo-"+id);
	
		if(objTB){ 
			var imagenes = objTB.getElementsByTagName('img');
			for(var i=0;i<imagenes.length;i++){
				var ip=imagenes[i].id.split("-")[1];
				var estado=imagenes[i].getAttribute("sondeo");
				
				var oCHK = document.getElementById('chk-'+ip); // Recupera checkbox

				switch(parseInt(op)){
					case 1: // Apagados						
						if(estado=="ordenador_OFF.gif")
							oCHK.checked=sw;
						break;	
						
					case 2: // Ocupados
						if(estado=="ordenador_BSY.gif")
							oCHK.checked=sw;
						break;
						
					case 3: // Opengnsys
						if(estado=="ordenador_OPG.gif")
							oCHK.checked=sw;
						break;
						
					case 4: // Windows
						if(estado=="ordenador_WXP.gif")
							oCHK.checked=sw;
						break;
						
					case 5: // Linux
						if(estado=="ordenador_LNX.gif")
							oCHK.checked=sw;
						break;

	
				}
			}
		}
}

//______________________________________________________________________________________________________
//
function filtrado()
{
	var ipes="";
	for(j=1;j<=cid;j++){
		var objTB=document.getElementById("tbo-"+j);
		if(objTB){ 
			var imagenes = objTB.getElementsByTagName('img');
			for(var i=0;i<imagenes.length;i++){
				var ip=imagenes[i].id.split("-")[1];
				var oCHK = document.getElementById('chk-'+ip); // Recupera checkbox			
				if(oCHK.checked){
					ipes+=ip+";";
				}
			}
		}
	}
	var preipes=document.getElementById("cadenaip").value;
	if(preipes!=ipes)
		document.fdatosejecucion.filtro.value=ipes;
	else
		document.fdatosejecucion.filtro.value="";
}
</SCRIPT>

