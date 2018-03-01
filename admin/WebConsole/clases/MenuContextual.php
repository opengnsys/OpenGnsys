<?php
/*==================================================================================================
Clase para trabajar con menús contextuales.

	Especificaciones de las etiquetas XML:
		- MENUCONTEXTUAL: Elemento raiz en el se especifican los atributos del <DIV>
		- ITEM: Especificaciones de cada item.
		- SEPARADOR: Indica una linea divisoria entre items

	Especificaciones de atributos:
		- idctx: Identificador del menu contextual (DIV)
		- imgitem: La url de la imagen que acompañará al literal 
		- alignitem: La alineación del texto del item (por defecto left)
		- textoitem: El literal del item
		- subflotante: Si el item despliega otro menu contextual. El valor es el id de ése
		- maxanchu: Máxima anchura del menu contextual
		- swimg: Vale 1 si el menu tiene algún item con imagen y 0 en caso contrario
		- alpulsar: Nombre de la función javascript que se ejecutará en respuesta al evento onclick
		- clase: Estilo CSS que tendrán los item  menu contextual
		- origen_x: Posición de origen, coordenada x
		- origen_y: Posición de origen, coordenada y


	Especificaciones de eventos:
		 - Los eventos onmouseover y onmouseout están implicitos en la clase por defecto
	Especificaciones de submenus:
		- Si una opción lleva un submenu asociado el id de éste va en el atributo name del <TR>


==================================================================================================*/
class MenuContextual{
	Function __construct($urlImages="../images/flotantes"){
		$this->urlImg=$urlImages;
	}
	/*---------------------------------------------------------------------------------------------
		Devuelve una cadena en formato HTML de un layer para usarlo como menu contextual
		Parametros: 
			- cadenaXML: Una cadena en formato XML con los atributos de cada item
	---------------------------------------------------------------------------------------------*/
	function CreaMenuContextual($cadenaXML){
		$idx=0;
		$layer="";
		$idctx="";
		$maxanchu=100;
		$swimg=0;
		$imgitem="";
		$alignitem="";
		$textoitem="";
		$clase="";
		$subflotante="";
		$origen_x="0";
		$origen_y="0";

		$gXML=new XmlPhp($cadenaXML,0); 
		$gXML->NodoRaiz();
		$atributosHTML=$gXML->Atributos();
		$atributosHTML=$gXML->TomaAtributoEspecial("maxanchu",$maxanchu,$atributosHTML);
		$atributosHTML=$gXML->TomaAtributoEspecial("swimg",$swimg,$atributosHTML);
		$atributosHTML=$gXML->TomaAtributoEspecial("clase",$clase,$atributosHTML);
		$atributosHTML=$gXML->TomaAtributoEspecial("idctx",$idctx,$atributosHTML);
		$atributosHTML=$gXML->TomaAtributoEspecial("origen_x",$origen_x,$atributosHTML);
		$atributosHTML=$gXML->TomaAtributoEspecial("origen_y",$origen_y,$atributosHTML);
		if(empty($origen_x)) $origen_x=0;
		if(empty($origen_y)) $origen_y=0;
		if (!$clase) $clase="menu_contextual";
		$layer.='<DIV class="'.$clase.'" id="'.$idctx.'" width='.$maxanchu.' style="visibility:hidden;position:absolute;top:'.$origen_y.';left:'.$origen_x.'" >';
		$nuitems=2;
		if ($gXML->PrimerNodoHijo()){
			$layer.='<TABLE  border=0 width='.$maxanchu.' border=0 cellspacing=0 cellpadding=0>';
			$layer.='<TR width='.$maxanchu.' height=3>'; // Primera linea
			$layer.='<TD width=3  background="'.$this->urlImg.'/esi.gif"></TD>'; 
			$layer.='<TD colspan=6 background="'.$this->urlImg.'/lsu.gif"></TD>'; 
			$layer.='<TD width=3  background="'.$this->urlImg.'/esd.gif"></TD>';
			$layer.='</TR>';
			
			$layer.='<TR  width='.$maxanchu.' height=3>'; // Linea de relleno
			$layer.='<TD width=3 background="'.$this->urlImg.'/liz.gif"></TD>'; 
			$layer.='<TD width=3></TD>'; 
			$layer.='<TD colspan=4></TD>'; 
			$layer.='<TD width=3></TD>'; 
			$layer.='<TD width=3 background="'.$this->urlImg.'/ldr.gif"></TD>';
			$layer.='</TR>';
			do{
				$nuitems++;
				$atributosHTML=$gXML->Atributos();
				$tiponodo=$gXML->NombreNodo();
				if ($tiponodo=="ITEM"){
					$atributosHTML=$gXML->TomaAtributoEspecial("imgitem",$imgitem,$atributosHTML);
					$atributosHTML=$gXML->TomaAtributoEspecial("textoitem",$textoitem,$atributosHTML);
					$atributosHTML=$gXML->TomaAtributoEspecial("subflotante",$subflotante,$atributosHTML);
					$atributosHTML=$gXML->TomaAtributoEspecial("alpulsar",$alpulsar,$atributosHTML);
					$atributosHTML=$gXML->TomaAtributoEspecial("alignitem",$alignitem,$atributosHTML);

					if ($alignitem==null) $alignitem="left";

					$clickcontextual=' onclick="'.$alpulsar.'" ';
					$oncontextual=' onmouseover="sobre_contextual(this)" ';
					$offcontextual="";

					$idx++;
					$layer.='<TR id='.$idx.' name="'.$subflotante.'" width='.$maxanchu.' '.$clickcontextual.' '.$oncontextual.' '.$offcontextual.' height=20>'; // Linea de item
					$layer.='<TD  width=3 background="'.$this->urlImg.'/liz.gif"></TD>'; 
					$layer.='<TD  width=3></TD>'; 
					
					if ($imgitem!=null){ // Item con imagen
						$imgonclick="";
						$layer.='<TD width=20 align=center id="TDimg-'.$idx .'"><IMG   width=16 src="'.$imgitem.'"></TD>';
						$layer.='<TD width=3></TD>'; 
						$layer.='<TD  align='.$alignitem.'  id="TDLit-'.$idx .'" width='.($maxanchu-38).' '.$atributosHTML.'><A href="javascript:void(0)" style="text-decoration: none"><SPAN>'.$textoitem.'</SPAN></A></TD>';
					}
					else{
						if ($swimg==1){ // Hay algún item con imagen
							$layer.='<TD width=20></TD>'; 
							$layer.='<TD width=3></TD>'; 
							$layer.='<TD align='.$alignitem.' width='.($maxanchu-38).' '.$atributosHTML.'><A href="#" style="text-decoration: none"><SPAN>'.$textoitem.'</SPAN></A></TD>';
						}
						else{
							$layer.='<TD width=10></TD>'; 
							$layer.='<TD colspan=2 align='.$alignitem.' width='.($maxanchu-25).' ' .$atributosHTML.' ><A href="#" style="text-decoration: none"><SPAN>'.$textoitem.'</SPAN></A></TD>';
						}
					}
					if ($subflotante!=null)
						$layer.='<TD  valign=middle><IMG  width=3 name="swsbfn" align=left src="'.$this->urlImg.'/swsbfn.gif">';
					else
						$layer.='<TD width=3 >';
					$layer.='</TD>'; 
					$layer.='<TD width=3></TD>'; 
					$layer.='<TD width=3 background="'.$this->urlImg.'/ldr.gif"></TD>';
					$layer.='</TR>';
				}
				if ($tiponodo=="SEPARADOR"){ // Separadores
					$layer.='<TR  width='.$maxanchu.' height=16>'; // Linea de separación
					$layer.='<TD width=3 background="'.$this->urlImg.'/liz.gif"></TD>'; 
					$layer.='<TD width=3></TD>'; 
					$layer.='<TD colspan=4 background="'.$this->urlImg.'/sep.gif"></TD>';
					$layer.='<TD width=3></TD>'; 
					$layer.='<TD width=3 background="'.$this->urlImg.'/ldr.gif"></TD>';
					$layer.='</TR>';
				}
	
			}while($gXML->SiguienteNodoHermano());

			$layer.='<TR  width='.$maxanchu.' height=3>'; // Linea de relleno
			$layer.='<TD width=3 background="'.$this->urlImg.'/liz.gif"></TD>'; 
			$layer.='<TD width=3></TD>'; 
			$layer.='<TD colspan=4></TD>'; 
			$layer.='<TD width=3></TD>'; 
			$layer.='<TD width=3 background="'.$this->urlImg.'/ldr.gif"></TD>';
			$layer.='</TR>';


			$layer.='<TR width='.$maxanchu.' height=3>'; // Última linea
			$layer.='<TD width=3 background="'.$this->urlImg.'/eii.gif"></TD>'; 
			$layer.='<TD colspan=6 background="'.$this->urlImg.'/lin.gif"></TD>'; 
			$layer.='<TD width=3 background="'.$this->urlImg.'/eid.gif"></TD>';
			$layer.='</TR>';
			$layer.='</TABLE>'; 
			$layer.='<INPUT type=hidden value="-1">'; // Representará el índice seleccionado
			$layer.='</DIV>'; 
		}
		return($layer);
	}
}	
?>