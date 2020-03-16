<?php
// *******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Noviembre-2012
// Nombre del fichero: imagenes.php
// Descripción : 
//		Administra imágenes de un determinado Centro
// ********************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/imagenes_".$idioma.".php");
//________________________________________________________________________________________________________

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2');  // Error de conexión con servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idcentro); // Crea el código XML del arbol 
	
// Genera vista del árbol usando como origen de datos el XML anterior
$baseurlimg="../images/signos"; // Url de las imágenes de signo
$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault,1,0,5);

//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/imagenes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>	
	<SCRIPT language="javascript" src="../api/jquery.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/imagenes_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY onclick="ocultar_menu('menu-contextual');" >

<?php
//________________________________________________________________________________________________________

echo $arbol->CreaArbolVistaXML(); // Muestra árbol en pantalla

// Crea contextual de las imágenes
$flotante=new MenuContextual(); 
 
$XMLcontextual=CreaContextualXMLTiposImagenes($AMBITO_GRUPOSIMAGENESMONOLITICAS,
						$LITAMBITO_GRUPOSIMAGENESMONOLITICAS,
						$AMBITO_IMAGENESMONOLITICAS,
						$LITAMBITO_IMAGENESMONOLITICAS,
						$IMAGENES_MONOLITICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreaContextualXMLTiposImagenes($AMBITO_GRUPOSIMAGENESBASICAS,
						$LITAMBITO_GRUPOSIMAGENESBASICAS,
						$AMBITO_IMAGENESBASICAS,
						$LITAMBITO_IMAGENESBASICAS,
						$IMAGENES_BASICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreaContextualXMLTiposImagenes($AMBITO_GRUPOSIMAGENESINCREMENTALES,
						$LITAMBITO_GRUPOSIMAGENESINCREMENTALES,
						$AMBITO_IMAGENESINCREMENTALES,
						$LITAMBITO_IMAGENESINCREMENTALES,
						$IMAGENES_INCREMENTALES);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreaContextualXMLGruposImagenes($AMBITO_GRUPOSIMAGENESMONOLITICAS,
						$LITAMBITO_GRUPOSIMAGENESMONOLITICAS,
						$AMBITO_IMAGENESMONOLITICAS,
						$LITAMBITO_IMAGENESMONOLITICAS,
						$IMAGENES_MONOLITICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreaContextualXMLGruposImagenes($AMBITO_GRUPOSIMAGENESBASICAS,
						$LITAMBITO_GRUPOSIMAGENESBASICAS,
						$AMBITO_IMAGENESBASICAS,
						$LITAMBITO_IMAGENESBASICAS,
						$IMAGENES_BASICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreaContextualXMLGruposImagenes($AMBITO_GRUPOSIMAGENESINCREMENTALES,
						$LITAMBITO_GRUPOSIMAGENESINCREMENTALES,
						$AMBITO_IMAGENESINCREMENTALES,
						$LITAMBITO_IMAGENESINCREMENTALES,
						$IMAGENES_INCREMENTALES);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreacontextualXMLImagen($AMBITO_IMAGENESMONOLITICAS,
					$LITAMBITO_IMAGENESMONOLITICAS,
					$IMAGENES_MONOLITICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreacontextualXMLImagen($AMBITO_IMAGENESBASICAS,
					$LITAMBITO_IMAGENESBASICAS,
					$IMAGENES_BASICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);										

$XMLcontextual=CreacontextualXMLImagen($AMBITO_IMAGENESINCREMENTALES,
					$LITAMBITO_IMAGENESINCREMENTALES,
					$IMAGENES_INCREMENTALES);
echo $flotante->CreaMenuContextual($XMLcontextual);											
        echo "<br><br><br>";
        echo "<br><br><br>";
$Repos=repos();
$imagenes=img($Repos);
$grupos_hijos= grupos();
listaImg($imagenes,$grupos_hijos);

// Tipos de menús contextuales:
// id="TipoImagen_1"
// id="TipoImagen_2"
// id="TipoImagen_3"
// id="flo_gruposimagenesmonoliticas"
// id="flo_gruposimagenesbasicas"
// id="flo_gruposimagenesincrementales"
// id="flo_imagenesmonoliticas"
// id="flo_imagenesbasicas"
// id="flo_imagenesincrementales"
// En realidas son tres : tipos imagenes, grupos e imagenes.

?>


 <ul id="menu-tipes" name="menu-tipes" oncontextmenu="return false;">
  <li onclick="insertar_grupos(0,'<?php echo $LITAMBITO_GRUPOSIMAGENES ?>');"><img class="menu-icono" src="../images/iconos/carpeta.gif"> Nuevo grupo de imágenes</li>
  <li class="separador" onclick="insertar_imagen('<?php echo $LITAMBITO_IMAGENES ?>',0);"><img class="menu-icono" src="../images/iconos/imagen.gif"> Definir nueva imagen</li>
  <li onclick="mover()"><img class="menu-icono" src="../images/iconos/colocar.gif"> Colocar imagen</li>
 </ul>

 <ul id="menu-groups" name="menu-groups" oncontextmenu="return false;">
 <li onclick="insertar_grupos(0,'<?php echo $LITAMBITO_GRUPOSIMAGENES ?>');"><img class="menu-icono" src="../images/iconos/carpeta.gif"><span class="menu-texto"> Nuevo grupo de imágenes</span></li>
  <li class="separador" onclick="insertar_imagen('<?php echo $LITAMBITO_IMAGENES ?>',0);"><img class="menu-icono" src="../images/iconos/imagen.gif">Definir nueva imagen</li>
  <li class="separador" onclick="colocar('../gestores/gestor_imagenes.php',0)"><img class="menu-icono" src="../images/iconos/colocar.gif"> Colocar imagen</li>
  <li onclick="modificar_grupos('<?php echo $LITAMBITO_GRUPOSIMAGENES ?>');"><img class="menu-icono" src="../images/iconos/modificar.gif"> Propiedades</li>
  <li onclick="eliminar_grupos('<?php echo $LITAMBITO_GRUPOSIMAGENES ?>');"><img class="menu-icono" src="../images/iconos/eliminar.gif"> Eliminar grupo de imágenes</li>
 </ul>

 <ul id="menu-images" name="menu-images" oncontextmenu="return false;">
  <li class="separador" onclick="muestra_informacion();"><img class="menu-icono" src="../images/iconos/informacion.gif"> Imagen información</li>
  <li class="separador" onclick="mover()"><img class="menu-icono" src="../images/iconos/mover.gif"> Mover Imagen</li>
  <li onclick="modificar_imagen();"><img class="menu-icono" src="../images/iconos/propiedades.gif"> Propiedades</li>
  <li onclick="eliminar_imagen();"><img class="menu-icono" src="../images/iconos/eliminar.gif"> Eliminar imagen</li>
  <!-- li>Comandos prueba <span>»</span>
   <ul>
    <li onclick="location.href='http://frikiblogeeo.blogspot.com'">Friki Bloggeo</li>
    <li onclick="location.href='http://blogger.com'">Blogger</li>
    <li onclick="location.href='http://gmail.com'">Gmail</li>
   </ul>
  </li -->
  </ul>

 <!-- div id="outer-wrapper" onclick="ocultar_menu();" oncontextmenu="mostrarMenu(event, this.id, 'menu-contextual');return false;"> </div -->
</BODY>
</HTML>
<?php
// ********************************************************************************************************
//	Devuelve una cadena con formato XML con toda la información de las imáges registradas en un Centro 
//	concreto
//	Parametros: 
//		- cmd:Una comando ya operativo ( con conexión abierta)  
//		- idcentro: El identificador del centro
//________________________________________________________________________________________________________

function CreaArbol($cmd,$idcentro)
{
	// Variables globales.
	global $TbMsg;

	global $LITAMBITO_IMAGENES;
	global $AMBITO_GRUPOSIMAGENESMONOLITICAS,
			$LITAMBITO_GRUPOSIMAGENESMONOLITICAS,
			$AMBITO_IMAGENESMONOLITICAS,
			$LITAMBITO_IMAGENESMONOLITICAS,
			$IMAGENES_MONOLITICAS;
			
	global $AMBITO_GRUPOSIMAGENESBASICAS,
			$LITAMBITO_GRUPOSIMAGENESBASICAS,
			$AMBITO_IMAGENESBASICAS,
			$LITAMBITO_IMAGENESBASICAS,
			$IMAGENES_BASICAS;
			
	global $AMBITO_GRUPOSIMAGENESINCREMENTALES,
			$LITAMBITO_GRUPOSIMAGENESINCREMENTALES,
			$AMBITO_IMAGENESINCREMENTALES,
			$LITAMBITO_IMAGENESINCREMENTALES,
			$IMAGENES_INCREMENTALES;
			
	$cadenaXML='<RAIZ';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/imagenes.gif"';
	$cadenaXML.=' nodoid=Raiz'.$LITAMBITO_IMAGENES;
	$cadenaXML.=' infonodo="'.$TbMsg[9].'"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_tiposimagenes($AMBITO_GRUPOSIMAGENESMONOLITICAS,
						$LITAMBITO_GRUPOSIMAGENESMONOLITICAS,
						$AMBITO_IMAGENESMONOLITICAS,
						$LITAMBITO_IMAGENESMONOLITICAS,
						$IMAGENES_MONOLITICAS,
						$TbMsg[11]);

	$cadenaXML.=SubarbolXML_tiposimagenes($AMBITO_GRUPOSIMAGENESBASICAS,
						$LITAMBITO_GRUPOSIMAGENESBASICAS,
						$AMBITO_IMAGENESBASICAS,
						$LITAMBITO_IMAGENESBASICAS,
						$IMAGENES_BASICAS,
						$TbMsg[12]);

	$cadenaXML.=SubarbolXML_tiposimagenes($AMBITO_GRUPOSIMAGENESINCREMENTALES,
						$LITAMBITO_GRUPOSIMAGENESINCREMENTALES,
						$AMBITO_IMAGENESINCREMENTALES,
						$LITAMBITO_IMAGENESINCREMENTALES,
						$IMAGENES_INCREMENTALES,
						$TbMsg[13]);											
	$cadenaXML.='</RAIZ>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________

function SubarbolXML_tiposimagenes($ambg,$litambg,$amb,$litamb,$tipo,$msg)
{
	$cadenaXML="";
	$cadenaXML.='<TIPOSIMAGENES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' nodoid=SubRaiz-0';
	$cadenaXML.=' infonodo='.$msg;
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'TipoImagen_".$tipo."'".')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_gruposimagenes(0,$ambg,$litambg,$amb,$litamb,$tipo);
	$cadenaXML.='</TIPOSIMAGENES>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________

function SubarbolXML_gruposimagenes($grupoid,$ambg,$litambg,$amb,$litamb,$tipo)
{
	global $cmd;
	global $idcentro;
	
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid 
					FROM grupos WHERE grupoid=".$grupoid." 
					AND idcentro=".$idcentro." 
					AND tipo=".$ambg." 
					ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	//echo $cmd->texto;
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSIMAGENES';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$litambg."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid="'.$litambg."-".$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_gruposimagenes($rs->campos["idgrupo"],$ambg,$litambg,$amb,$litamb,$tipo);
		$cadenaXML.='</GRUPOSIMAGENES>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_Imagenes($grupoid,$amb,$litamb,$tipo);
	return($cadenaXML);
}
//________________________________________________________________________________________________________

function SubarbolXML_Imagenes($grupoid,$amb,$litamb,$tipo)
{
	global $TbMsg;
	global $cmd;
	global $idcentro;	
	
	$cadenaXML="";
	$rs=new Recordset; 
	#### agp ### Añado la consulta el campo idrepositorio	####
	$cmd->texto="SELECT DISTINCT imagenes.idimagen,imagenes.descripcion, IFNULL(repositorios.nombrerepositorio,'".$TbMsg["DELETEDREPO"]."') AS nombrerepositorio
				FROM  imagenes ";
	// Para hallar el repositorio de las incrementales hay que buscar los datos de la imagen basica (en la propia tablas imágenes)
	if ($tipo == 3) {
	    $cmd->texto.="      INNER JOIN imagenes AS basica
				LEFT JOIN repositorios ON basica.idrepositorio=repositorios.idrepositorio
			        WHERE imagenes.imagenid=basica.idimagen AND ";
	} else {
	    $cmd->texto.="      LEFT JOIN repositorios USING  (idrepositorio) WHERE ";
	}
	$cmd->texto.="          imagenes.idcentro=".$idcentro."
				AND imagenes.grupoid=".$grupoid."  
				AND imagenes.tipo=".$tipo." 
				ORDER BY imagenes.descripcion";
	//echo "<br>".$cmd->texto;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<IMAGEN';
		// Atributos
		$cadenaXML.=' imagenodo="../images/iconos/imagen.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].' ('.$rs->campos["nombrerepositorio"].')"';
		$cadenaXML.=' nodoid='.$litamb.'-'.$rs->campos["idimagen"];
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$litamb."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</IMAGEN>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
//
//	Menús Contextuales

//________________________________________________________________________________________________________

function CreaContextualXMLTiposImagenes($ambg,$litambg,$amb,$litamb,$tipo)
{
	global $TbMsg;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="TipoImagen_'.$tipo.'"';
	$layerXML.=' maxanchu=175';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$ambg.',' ."'".$litambg."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_imagen(\''.$litamb.'\','.$tipo.')"';
	$layerXML.=' imgitem="../images/iconos/imagen.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_imagenes.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$tipo.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________

function CreaContextualXMLGruposImagenes($ambg,$litambg,$amb,$litamb,$tipo)
{
	global $TbMsg;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$litambg.'"';
	$layerXML.=' maxanchu=175';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$ambg.',' ."'".$litambg."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_imagen(\''.$litamb.'\','.$tipo.')"';
	$layerXML.=' imgitem="../images/iconos/imagen.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_imagenes.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$tipo.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>'; 

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/modificar.gif"';
	$layerXML.=' textoitem='.$TbMsg[7];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//__________________________________________________________________________________________

function CreacontextualXMLImagen($amb,$litamb,$tipo)
{
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$litamb.'"';
	$layerXML.=' maxanchu=150';
	$layerXML.=' swimg=1';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="muestra_informacion()"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$tipo.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_imagen('.$tipo.')"';	
	$layerXML.=' textoitem='.$TbMsg[7];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar_imagen('.$tipo.')"';	
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}

// Descripción. Devuelve un array con los nombres de los repositorios
// Parámetros: ninguno
// Devuelve un array con los nombres de los repositorios
function repos(){
        global $TbMsg;
        global $cmd;
        global $idcentro;

        $repositorios=Array();
        $rs=new Recordset;
        $cmd->texto="SELECT idrepositorio, nombrerepositorio FROM repositorios;";
        $rs->Comando=&$cmd;
        if (!$rs->Abrir()) return($repositorios);

        $rs->Primero();
        while (!$rs->EOF){
                $repositorios[$rs->campos["idrepositorio"]] = $rs->campos["nombrerepositorio"];
                $rs->Siguiente();
        }
        return($repositorios);
}

// Descripción: Devuelve un array de grupos de imágenes. Ordenados por tipos de imágenes y grupo padre
// Parámetros: ninguno
// devuelve: array de grupos.
function grupos(){
	global $cmd;
	global $idcentro;

	$grupos_hijos=Array();
	$rs=new Recordset;
	$cmd->texto="SELECT idgrupo, nombregrupo, grupos.grupoid AS grupopadre, tipo
		       FROM grupos
		      WHERE idcentro=$idcentro AND tipo IN (70, 71, 72)
                   ORDER BY tipo, grupopadre, grupoid;";
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return($grupos_hijos);

        $rs->Primero();
	$oldgrupopadre=0;
	$num=-1;
        while (!$rs->EOF){
		$grupopadre=$rs->campos["grupopadre"];
		$nombregrupo=$rs->campos["nombregrupo"];

		$idgrupo=$rs->campos["idgrupo"];
                // El tipo de grupo de imagenes son 70, 71 y 72 correspondiendo al tipo de imagen 1, 2 y 3
		$tipo=$rs->campos["tipo"] - 69;
		if ($oldgrupopadre != $grupopadre) {
			$oldgrupopadre=$grupopadre;
		        // Cuando cambio de grupo pongo el orden del array a cero
			$num=0;
		} else {
			$num++;
	        }
		$grupos_hijos[$tipo][$grupopadre][$num]["id"] = $idgrupo;
		$grupos_hijos[$tipo][$grupopadre][$num]["nombre"] = $nombregrupo;

		$rs->Siguiente();
	}
	return ($grupos_hijos);

}

// Descripción: Devuelve un array de las imágenes ordenadas por tipo y grupo al que pertenecen.
// Parámetros: repositorios
// array repositorios: array con los nombres del repositorio, para usarlo en la descripción de la imagen.
// Devuelve: array de imágenes
function img($repositorios){
        global $TbMsg;
        global $cmd;
        global $idcentro;

        $imagenes=Array();
        $grupos_hijos=Array();
        $rs=new Recordset;
	$cmd->texto="SELECT DISTINCT imagenes.idimagen,imagenes.descripcion, imagenes.tipo, imagenes.grupoid,
			     IF(imagenes.idrepositorio=0,basica.idrepositorio,imagenes.idrepositorio)  AS repo
                       FROM  imagenes
                  LEFT JOIN imagenes AS basica  ON imagenes.imagenid=basica.idimagen
                      WHERE imagenes.idcentro=$idcentro ORDER BY imagenes.tipo, grupoid;";

        $rs->Comando=&$cmd;
        if (!$rs->Abrir()) return(Array($imagenes));

        $rs->Primero();
        $ordenImg=-1;
        $oldgrupoid=(isset($rs->campos["grupoid"]))? $rs->campos["grupoid"] : 0;
        while (!$rs->EOF){
                $tipo=$rs->campos["tipo"];
                $idimagen=$rs->campos["idimagen"];
                $descripcion=$rs->campos["descripcion"];
                $idrepo=$rs->campos["repo"];
                // Las imágenes de un grupo son un array. Cuando cambio de grupo pongo el orden a cero:
                $grupoid=(isset($rs->campos["grupoid"]))? $rs->campos["grupoid"] : 0;
                if ($oldgrupoid != $grupoid) {
                        $oldgrupoid=$grupoid;
                        $ordenImg=0;
                } else {
                        $ordenImg=$ordenImg+1;
                }

                $imagenes[$tipo][$grupoid][$ordenImg]["descripcion"]=$descripcion." (".$repositorios[$idrepo].")";
                $imagenes[$tipo][$grupoid][$ordenImg]["id"]=$idimagen;
                $rs->Siguiente();
        }

        return($imagenes);
}

// Descripción: Comienza el árbol de imágenes en froma de lista.
// Parámetros: imágenes grupos_hijos
// array imágenes: Array con las imágenes según tipo y grupo al que pertenecen.
// array grupos_hijos: array de los grupos hijos de cada grupo.
// Devuelve: nada
function listaImg ($imagenes, $grupos_hijos){
        global $TbMsg;
        global $NUM_TIPOS_IMAGENES;
        $orden=0;
        echo '<ul id="menu_arbol">'."\n";
        echo '  <li><input type="checkbox" name="list" id="nivel1-1"><label for="nivel1-1"><img class="menu-icono" src="../images/iconos/imagenes.gif">'.str_replace ('"','',$TbMsg[9]).'</label>'."\n";
        for ($tipo = 1; $tipo <= $NUM_TIPOS_IMAGENES; $tipo++) {
                // Recorremos los grupos hijos desde el cero
		echo '    <ul>'."\n";
		echo '       <li id="grupo_'.$tipo.'_0" oncontextmenu="mostrar_menu(event, '. $tipo.', 0, \'menu-tipes\');return false;">'."\n";
		echo '          <input type="checkbox" name="list" id="nivel2-'.$tipo.'"><label for="nivel2-'.$tipo.'"><img class="menu-icono" src="../images/iconos/carpeta.gif"> '.str_replace ('"','',$TbMsg[10+$tipo]).'</label>'."\n";
		$orden=listaGrupo($tipo,0,2,$orden,$imagenes,$grupos_hijos);
		$orden=$orden+1;
		echo '       </li>'."\n";
		echo '    </ul>'."\n";
        }
        echo "  </li>"."\n";
        echo "</ul>"."\n";
}

// Descripción: Construye la parte del árbol correspondiente a un grupo de imágenes: lista sus imágenes y sus grupos hijos.
// Parametros: tipo idgrupo nivel orden imagenes grupos_hijos
// int tipo: tipo de imágenes (moniliticas, básicas, )
// int idgrupo: identificador del grupo
// int nivel: nivel de la lista
// int orden: orden de la lista
// array imagenes: array con info de la imagen
// array grupos_hijos: array de grupos hijos
// Devuelve: el orden de la lista del último elemento.
function listaGrupo($tipo,$idgrupo,$nivel,$orden,$imagenes,$grupos_hijos){
        $nivel=$nivel+1;
        echo '    <ul class="interior">'."\n";
        // si existen grupos hijos del actual creo la lista con la función listaGrupo.
        if (isset ($grupos_hijos[$tipo][$idgrupo])){
            foreach ($grupos_hijos[$tipo][$idgrupo] as $hijo) {
                $orden=$orden+1;
                echo '      <li id="grupo_'.$hijo["id"].'" oncontextmenu="mostrar_menu(event,'. $tipo.', '.$hijo["id"].', \'menu-groups\');return false;"><input type="checkbox" name="list" id="nivel'.$nivel.'-'.$orden.'"><label for="nivel'.$nivel.'-'.$orden.'"><img class="menu_icono" src="../images/iconos/carpeta.gif">'.$hijo["nombre"].'</label>'."\n";
                //echo '      <li oncontextmenu="mostrar_menu(event,'. $tipo.', '.$hijo["id"].', \'menu-groups\');return false;"><input type="checkbox" name="list" id="nivel'.$nivel.'-'.$hijo["id"].'"><label for="nivel'.$nivel.'-'.$hijo["id"].'"><img class="menu_icono" src="../images/iconos/carpeta.gif">'.$hijo["nombre"].'</label>'."\n";

                $orden=listaGrupo($tipo,$hijo["id"],$nivel,$orden,$imagenes,$grupos_hijos);
            }
            echo "      </li>"."\n";
	}
	// creo la lista de las imágenes dentro del grupo (si existen).
	if (isset ($imagenes[$tipo][$idgrupo])){
	    foreach ($imagenes[$tipo][$idgrupo] as $img){
		echo '      <li id="img_'.$img["id"].'" oncontextmenu="ocultar_menu(); mostrar_menu(event,'. $tipo.', '.$img["id"].', \'menu-images\');return false;"><a href="#r"><img class="menu_icono" src="../images/iconos/imagen.gif"> '.$img["descripcion"].'</a></li>'."\n";
	    }
	}
        echo "    </ul>"."\n";
	return($orden);
}

?>

