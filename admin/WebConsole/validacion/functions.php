<?php
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/TomaDato.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/menucliente_".$idioma.".php");
//___________________________________________________________________________________________________
//
// Redupera la ip del cliente web
//___________________________________________________________________________________________________
function TomaIP(){
        // Se asegura que la pagina se solicita desde la IP que viene
        global $HTTP_SERVER_VARS;
        if ($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"] != "")
                $ipcliente = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
        else
                $ipcliente = $HTTP_SERVER_VARS["REMOTE_ADDR"];
        if (empty ($ipcliente))
                $ipcliente = $_SERVER["REMOTE_ADDR"];
        return($ipcliente);
}
/**/

function TomaPropiedades($cmd){
        global $idordenador;
        global $nombreordenador;
        global $ip;
        global $validacion;
        global $paginalogin;
        global $paginavalidacion;


        $rs=new Recordset;
        $cmd->texto="SELECT * FROM ordenadores WHERE ip='".$ip."'";
        $rs->Comando=&$cmd;
        if (!$rs->Abrir()) return(false); // Error al abrir recordset
        $rs->Primero();
        if (!$rs->EOF){
                $nombreordenador=$rs->campos["nombreordenador"];
                $ip=$rs->campos["ip"];
                $validacion=$rs->campos["validacion"];
                $paginalogin=$rs->campos["paginalogin"];
                $paginavalidacion=$rs->campos["paginavalidacion"];
                $rs->Cerrar();
                return(true);
        }
        else
                return(false);

        return true;
}

//
// Recupera Men
//___________________________________________________________________________________________________
function RecuperaMenu($cmd,$iph){
	$rs=new Recordset; 
	$cmd->texto="SELECT menus.idcentro,menus.resolucion,menus.titulo,menus.idurlimg,
			menus.modalidad,menus.smodalidad,menus.htmlmenupub,menus.htmlmenupri,
			acciones_menus.tipoaccion,acciones_menus.idaccionmenu,acciones_menus.idtipoaccion,
			acciones_menus.tipoitem,acciones_menus.descripitem,iconos.urlicono
			FROM ordenadores
			INNER JOIN menus ON menus.idmenu = ordenadores.idmenu 
			LEFT OUTER JOIN acciones_menus ON acciones_menus.idmenu = menus.idmenu
			LEFT OUTER JOIN iconos ON iconos.idicono=acciones_menus.idurlimg
			WHERE ordenadores.ip='".$iph."' ORDER by acciones_menus.orden";

	$rs->Comando=&$cmd; 
	$resul=$rs->Abrir();
	if (!$rs->Abrir()) return(false);
	if ($rs->EOF) return(false);
	return($rs);
}
//___________________________________________________________________________________________________
//
// Muestra el menu pblico
//___________________________________________________________________________________________________
function GeneraMenu($rs,$tipo,$iph){	
	global $ITEMS_PUBLICOS;
	global $ITEMS_PRIVADOS;
	global $UrlPaginaIconos;

	$titulo=$rs->campos["titulo"]; 
	$modalidad=$rs->campos["modalidad"]; 
	$smodalidad=$rs->campos["smodalidad"]; 
	$resolucion=$rs->campos["resolucion"]; 
	$htmlmenupub=$rs->campos["htmlmenupub"]; 
	$htmlmenupri=$rs->campos["htmlmenupri"]; 
			
	if($tipo==$ITEMS_PRIVADOS)
		$mod=$smodalidad;
	else
		$mod=$modalidad;
	$codeHTML="";

	//	Genera HTML de la página en función de las propiedades del menú del cliente.
	$codeHTML.='<P align=center>';
	$codeHTML.='<SPAN style="COLOR: #999999;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE: 20px;"><U>'.$titulo.'</U></SPAN>';
	$codeHTML.='</BR>';
	
	$codeHTML.='<TABLE cellspacing=4 cellpadding=0 align="center" border=0 >';
	$codeHTML.='<TR>';
	$codeHTML.='<TD colspan="'.(($mod*2)+1).'" >&nbsp;</TD>';
	$codeHTML.='</TR>';
	$codeHTML.='<TR>';

	$c=0; // Contador de columnas
	
	while (!$rs->EOF){ // Recorre acciones del menu
		$tipoitem=$rs->campos["tipoitem"]; 
		if($tipoitem==$tipo){
			$tipoaccion=$rs->campos["tipoaccion"]; 
			$idtipoaccion=$rs->campos["idtipoaccion"]; 
			$idaccionmenu=$rs->campos["idaccionmenu"]; 
			$descripitem=$rs->campos["descripitem"]; 
			$urlicono=$rs->campos["urlicono"]; 
			if(empty($urlicono))
				$urlicono="defaultitem.gif"; 

			$codeHTML.='<TD align=center>
				<A href="ejecutaritem.php?iph='.$iph.'&idt='.$idaccionmenu.'">
					<IMG border=0 src="http://'.$UrlPaginaIconos.'/'.$urlicono.'" width=64></A></TD>';
			$codeHTML.='<TD style="font-family:Arial;color: #a71026;FONT-SIZE:14">
				<A style="text-decoration:none" href="ejecutaritem.php?iph='.$iph.'&idt='.$idaccionmenu.'">
					<span style="FONT-FAMILY: Verdana,Arial, Helvetica, sans-serif;FONT-SIZE: 12px;COLOR:#999999">'.$descripitem.'</span></A></TD>';
			if($mod>1){
				//separación de columnas
				$codeHTML.='<TD width=10>&nbsp;</TD>';
			}
			$c++;
			if($c%$mod==0){
				$codeHTML.='</TR>';
				$codeHTML.='<TR>';
			}
		}
		$rs->Siguiente();
	}
	$codeHTML.='</TR>';
	$rs->Cerrar();
	$codeHTML.='</TABLE>';
	$codeHTML.='</P>';
	$codeHTML.='<BR><BR>';
	$codeHTML.='<P align=center>';

	if (empty($url)) $url="";
	switch($tipo){
		case $ITEMS_PUBLICOS:
			$url.='acceso_operador.php';
			$lit="Administrar";
			break;
		case $ITEMS_PRIVADOS:
			$url.='menucliente.php';
			$lit="Volver";
			break;
	}		
	$codeHTML.='<A style="text-decoration:none" href="'.$url.'?iph='.$iph.'">';
	$codeHTML.='<SPAN style="
				BORDER-BOTTOM: #999999 1px solid;
				BORDER-LEFT: #999999 1px solid;
				BORDER-RIGHT: #999999 1px solid;
				BORDER-TOP: #999999 1px solid;
				COLOR:#999999;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE:9px;">&nbsp;'.$lit.'&nbsp;</SPAN></A>';
	
	$codeHTML.='</P>';
	//$codeHTML.='</DIV>';
	return($codeHTML);
}

?>

