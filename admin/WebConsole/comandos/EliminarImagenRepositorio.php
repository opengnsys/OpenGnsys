<?php
// *************************************************************************************************************************************************
// Nombre del fichero: EliminarImagenRepositorio.php
// DescripciÃƒÆ’Ã‚Â³n : 
//		ImplementaciÃƒÆ’Ã‚Â³nÃƒÂ¯Ã‚Â¿Ã…" del comando "Eliminar Imagen Repositorio"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/comandos/eliminarimagenrepo_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");

include_once("../gestores/relaciones/imagenes_eliminacion.php");

if (isset($_POST["opcion"])) {$opcion=$_POST["opcion"];}else{$opcion='';} // Recoge parametros
if (isset($_POST["idrepositorio"])) {$idrepositorio=$_POST["idrepositorio"];}else{$idrepositorio=0;}
if (isset($_POST["grupoid"])) {$grupoid=$_POST["grupoid"];}else{$grupoid='';}
$idcentro=$_SESSION["widcentro"];
if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idrepositorio"])) $idrepositorio=$_GET["idrepositorio"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idrepositorio=$_GET["identificador"]; 
if (isset($_POST["modov"])) {$modov=$_POST["modov"];}else{$modov=0;}
//___________________________________________________________________________
//________________________________________________________________________________________________________
$idcomando=10;
$descricomando="Ejecutar Script";
$funcion="EjecutarScript";
$gestor="../comandos/gestores/gestor_Comandos.php";
//$gestor="./ElimininarImagenRepositorio.php";
$espaciorepos=array();
$separarogunit=0;
$iprepositorio='';
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexiÃƒÂ³n con servidor B.D.
//___________________________________________________________________________________________________

$logusu=$_SESSION["wusuario"];
$cmd->texto="SELECT * FROM usuarios WHERE usuario='$logusu'";
$rs=new Recordset;
$rs->Comando=&$cmd; 
if (!$rs->Abrir()) return(true); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF)
	{
		$tipologusu=$rs->campos["idtipousuario"];
	}
	$rs->Cerrar();

	$espaciorepo=exec("df -h /opt/opengnsys/images");
	$espaciorepo=explode(" ",$espaciorepo);
	for ($j=0;$j<count($espaciorepo);$j++)
	{
		if ($espaciorepo[$j]!="")
	       {$espaciorepos[]=$espaciorepo[$j];}
	}
	for ($k=0;$k<count($espaciorepos);$k++)
	{
		$totalrepo=$espaciorepos[1];
		$ocupadorepo=$espaciorepos[2];
		$librerepo=$espaciorepos[3];
		$porcentajerepo=$espaciorepos[4];
	}

$ipservidor=$_SERVER['SERVER_ADDR'];

$cmd->texto="SELECT * FROM repositorios WHERE idrepositorio=$idrepositorio";
$rs=new Recordset;
$rs->Comando=&$cmd; 
if (!$rs->Abrir()) return(true); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF)
	{
		$nombrerepositorio=$rs->campos["nombrerepositorio"];
		$iprepositorio=$rs->campos["ip"];
	}
	$rs->Cerrar();

// Compruebo si se separan directorio de unidades organizativas
$cmd->texto="SELECT ogunit FROM entidades INNER JOIN centros USING(identidad) where idcentro=$idcentro";
$rs=new Recordset;
$rs->Comando=&$cmd;
if (!$rs->Abrir()) return(true); // Error al abrir recordset
$rs->Primero();
if (!$rs->EOF)
{
        $separarogunit=$rs->campos["ogunit"]; // 1 -> si, 0 -> no
}
$rs->Cerrar();

// Directorios de las imágenes, si separo por unidades organizativas leo la BD
$dircentros= array();
$dircentros[0]='/'; 	// Incluimos /opt/opengnsys/images
if ($separarogunit == 1 ) {
	$cmd->texto="SELECT idcentro, directorio FROM centros ORDER BY idcentro";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(true); // Error al abrir recordset
	$rs->Primero();
	while (!$rs->EOF)
	{
		$dircentros[$rs->campos["idcentro"]]="/".$rs->campos["directorio"];
		$rs->Siguiente();
	}
}

if ($iprepositorio == $ipservidor)
{


//#########################################################################

$repolocal="si";
	//#########################################################################
	// LEYENDO EL DIRECTORIO local en el server
	// /opt/opengnsys/images/
	//#########################################################################
	$imarepo= array();//pila de nombres
	foreach ( array_unique($dircentros) as $subdir) {
	   $dirtemplates="/opt/opengnsys/images/$subdir";
	   $directorio=dir($dirtemplates);
	   // quitamos la barra inicial
	   $subdir = ($subdir == "/") ? '' : substr($subdir,1).':';
	   //bucle para llenar las pilas :P
	   while ($archivo = $directorio->read())
	   {
		//no mostrar ni "." ni ".." ni "pxe"
		if(($archivo!=".")&&($archivo!="..")&&($archivo!="mount")&&($archivo!="lost+found"))
		{
		array_push($imarepo, $subdir.$archivo);
		}
	   }
	   $directorio->close();
	}

	sort($imarepo); // Ordenamos el Array

	if (isset($_POST["contar"])) {$cuantos=$_POST["contar"];}else{$cuantos=0;}
	for ($i=1;$i<=$cuantos;$i++)
	{
		//#########################################################################
		// PARA SELECCIONAR EL FICHERO IMAGEN
		//$checkbox=$_POST["checkbox".$i];
		if (isset($_POST["checkbox".$i])){$checkbox=$_POST["checkbox".$i];}else{$checkbox="checkbox".$i;}
		$nombre=$_POST["nombre".$i];
		$nombre=trim($nombre);
		$chekmarcadif=$_POST["marcadif".$i];
		$tipoimg=$_POST["tipoimg".$i];
		$idcentroimg=$_POST["idcentroimg".$i];

		if ($checkbox == "si" && $chekmarcadif == 1)
		{
			$delete=$nombre.".img.diff.delete";
			//echo $delete;
			exec("touch ../tmp/$delete");
			exec("(echo '$nombre.img.diff') > ../tmp/$delete");
		}
		if ($checkbox == "si" && $chekmarcadif == 0)
		{
			if(preg_match("/.ant/",$nombre))
			{
				$nombre = str_replace(".ant", "", $nombre); //quitar todos los .backup y continuamos
				$delete=$nombre.".img.ant.delete";
				//echo $nombre;
				//echo $delete;
				exec("touch ../tmp/$delete");
				exec("(echo '$nombre.img.ant') > ../tmp/$delete");
			}elseif ($tipoimg == "D"){
					$delete=$nombre.".delete";
					//echo $delete;
					exec("touch ../tmp/$delete");
					exec("(echo '$nombre') > ../tmp/$delete");
			}else{
					$delete=$nombre.".img.delete";
					//echo $delete;
					exec("touch ../tmp/$delete");
					exec("(echo '$nombre.img') > ../tmp/$delete");
				}
		}
	//#########################################################################
	// PARA SELECCIONAR EL OBJETO IMAGEN
		if (isset($_POST["checkboxobjeto".$i])){$checkboxobjeto=$_POST["checkboxobjeto".$i];}else{$checkboxobjeto="checkboxobjeto".$i;}
		if ($checkboxobjeto == "si")
		{
	//#########################################################################
		// Si la imagen tiene directorio lo elimino
		$cmd->texto="SELECT * FROM imagenes WHERE nombreca='".preg_replace('/^\w*:/','',$nombre)."' AND idcentro='$idcentroimg'";
		$rs=new Recordset; 
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(0); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF){
			$idimagen=$rs->campos["idimagen"];
		}
		$rs->Cerrar();

		EliminaImagenes($cmd,$idimagen,"idimagen");// EliminaciÃƒÂ³n en cascada

		//echo $nombrecanonico." - ".$centroimagen."<br />";
		//#########################################################################
	   }
}

}else{
$repolocal="no";
//#########################################################################
			$urlimg='../images/iconos/repositorio.gif';
			$textambito=$TbMsg[0];
			$nombreambito="";

	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[6].': '.$iprepositorio.','.$nombrerepositorio.'</U></span>&nbsp;&nbsp;</span></p>';
	echo '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
	echo' <LINK rel="stylesheet" type="text/css" href="../estilos.css">';
       echo '<TABLE  id=tabla_conf align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>';
		  echo '<TR>';
               echo '</TR>';
		  echo '<TR>';
       	echo	'<TH align=center >&nbsp;'.$TbMsg[22].'</br>'.$nombreambito.$TbMsg[23].'</br>'.$TbMsg[24].'&nbsp;</TH>';
               echo '</TR>';
       echo '</TABLE>';


 }

//#########################################################################
?>

<?php if ( $repolocal == "si" ){ 

?>

<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<script type="text/javascript">
function confirmeliminar() {var mensaje="<?php echo $TbMsg[17];?>";if(confirm(mensaje)) {document.eliimarepo.submit();}}
</script> 
</HEAD>
<BODY>
<?php

			$urlimg='../images/iconos/repositorio.gif';
			$textambito=$TbMsg[0];

	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[6].': '.$iprepositorio.','.$nombrerepositorio.'</U></span>&nbsp;&nbsp;</span></p>';
?>


	<TABLE  align=center border=0 cellPadding=2 cellSpacing=2 class=tabla_datos >
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<?php if ($espaciorepo != ""){?>
			<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[18]?>&nbsp;</TD>
			<TH align=center>&nbsp;<?php echo $TbMsg[19]?>&nbsp;</TD>
			<TH align=center>&nbsp;<?php echo $TbMsg[20]?>&nbsp;</TD>
			<TH align=center>&nbsp;<?php echo $TbMsg[21]?>&nbsp;</TD>
		</TR>
                <TR>
			<TD align=center width=110>&nbsp;<?php echo $totalrepo?>&nbsp;</TD>
            <TD align=center width=120>&nbsp;<?php echo $ocupadorepo?>&nbsp;</TD>
            <TD align=center width=120>&nbsp;<?php echo $librerepo?>&nbsp;</TD>
            <TD align=center width=101>&nbsp;<?php echo $porcentajerepo?>&nbsp;</TD>
                </TR>
		<?php }else {?>
        			<TR>
            <TH align=center width=485>&nbsp;<?php echo $TbMsg[22]?>&nbsp;</TD>

					</TR>
        <?php } ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
      	</TABLE>

	<P align=center>
	<div align=center class=subcabeceras><?php echo $TbMsg[7] ?>

		
			<form  align="center" name="modoadmin" action="./EliminarImagenRepositorio.php" method="post">
			<INPUT type="hidden" name="opcion" value="<?php echo $opcion?>">
			<INPUT type="hidden" name="idrepositorio" value="<?php echo $idrepositorio?>">
			<INPUT type="hidden" name="grupoid" value="<?php echo $grupoid ?>">
			<?php if ($modov == "0"){?>
				<INPUT type="hidden" name="modov" value="1">
				<input type=button onclick="submit();" value="<?php echo $TbMsg[28]; ?>"/>
			<?php }else{ ?>
				<INPUT type="hidden" name="modov" value="0">
				<input type=button onclick="submit();" value="<?php echo $TbMsg[29]; ?>"/>
				<?php } ?>
			</form>
		</div>

	
	


<form  align=center name="eliimarepo" action="./EliminarImagenRepositorio.php" method="post"> 
	<TABLE  id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TD align=center>&nbsp;</TD>
			<TH align=center>&nbsp;<?php echo $TbMsg[27] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<?php echo "F => ".$TbMsg[31];  ?>&nbsp;</TH>
			<TD align=center>&nbsp;</TD>
			<TD align=center>&nbsp;</TD>
			<TH align=center>&nbsp;<?php echo "D => ".$TbMsg[32]; ?>&nbsp;</TH>
			<TD align=center>&nbsp;</TD>
			<TD align=center>&nbsp;</TD>
			<TH align=center>&nbsp;<?php echo "B => Backup" ?>&nbsp;</TH>
			<TD align=center>&nbsp;</TD>
		</TR>
	</TABLE>

	<TABLE  id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[11] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<?php echo $TbMsg[12] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<?php echo $TbMsg[27] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<?php echo $TbMsg[10] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<?php echo $TbMsg[13] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<?php echo $TbMsg[26] ?>&nbsp;</TH>
			<?php if ($modov == 1){ // Vista Repositorio Completo ?>
			<TH align=center>&nbsp;<?php echo $TbMsg[30] ?>&nbsp;</TH>
			<?php } ?>


		</TR>
			<?php
				//echo tabla_configuraciones($cmd,$idambito);

				
	$idc=$_SESSION["widcentro"];
	for ($x=0;$x<count($imarepo); $x++)
	{ //Llave For
			if(preg_match("/.img/",$imarepo[$x])  ) //si contiene .img
			{	
					if (preg_match("/.sum/",$imarepo[$x]) or preg_match("/.torrent/",$imarepo[$x]) or preg_match("/.lock/",$imarepo[$x])  )//Si el nombre contiene .img.sum o img.torrent o .img.lock
					{}else{	// COMPROBANDO EL NOMBRE DIF
						if(preg_match("/.img.diff/",$imarepo[$x]))
						{
							$imarepo[$x] = str_replace(".diff", "", $imarepo[$x]); //quitar todos los .img
							$imarepo[$x]=trim($imarepo[$x]);
							$imarepo[$x] = str_replace(".img", "", $imarepo[$x]); //quitar todos los .img
							$imarepo[$x]=trim($imarepo[$x]);
							$nombreimagenes[]=$imarepo[$x].'.diff';
							$tipo[]="F";
						}elseif(preg_match("/.ant/",$imarepo[$x]))
							{
								$imarepo[$x] = str_replace(".img", "", $imarepo[$x]); //quitar todos los .img
								$imarepo[$x]=trim($imarepo[$x]);
								$nombreimagenes[]=$imarepo[$x];
								$tipo[]="B";
							}else{
								$imarepo[$x] = str_replace(".img", "", $imarepo[$x]); //quitar todos los .img
								$imarepo[$x]=trim($imarepo[$x]);
								$nombreimagenes[]=$imarepo[$x];
								$tipo[]="F";
							}
				   		}
			}else{
				// Compruebo si es un directorio
				$buscodir="/opt/opengnsys/images/".str_replace(":","/",$imarepo[$x]);
				$buscopengnsys=$buscodir."/.marcimg";
				if(is_dir($buscodir)  && file_exists($buscopengnsys)){
						$imarepo[$x]=trim($imarepo[$x]);
						$nombreimagenes[]=$imarepo[$x];
						$tipo[]="D";
				}
			}
		} //Fin Llave For



	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					// Tenemos los nombres en un Array[]
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	$sin_duplicados=array_unique($nombreimagenes);
	$sin_duplicados=$nombreimagenes;
	$contandotipo=0;
	$contar=1;
	foreach($sin_duplicados as $value) //imprimimos $sin_duplicados
	{ //Llave Forach
		// Guardo los valores del directorio y el nombre imagen
		// Si no se separan los directorios queda igual
		$arrayname=explode(':', $value);
		if (sizeof( $arrayname) == 1){
			$imgdir="";
			$imgname=$arrayname[0];
		} else {
			$imgdir=$arrayname[0];
			$imgname=$arrayname[1];
		}	


		//Buscamos tamano de fichero
		//Buscamos si existe fichero de bloqueo
		$gentor=str_replace(":","/",$value).".img.lock";
		if(preg_match("/.diff/",$value))$gendif=$value.".img.diff";
		// ########### Buscando si existe fichero imagen #####################
		$buscando="ls /opt/opengnsys/images/$gentor";
		$bustor=exec($buscando);
		if(preg_match("/.diff/",$value))
			{
			$marcadif=1;
			$value = str_replace(".diff", "", $value); //quitar todos los .diff y continuamos
			$nombrefichero=trim($value);
			$nombrefichero=$value.'.img.diff';
			}
		elseif(preg_match("/.ant/",$value))
			{
				$nombrefichero=str_replace(".ant", "", $value);
				$nombrefichero=$nombrefichero.".img.ant";$marcadif=0;
			}else
			{
				$nombrefichero=$value.'.img';$marcadif=0;
			}

		// ####################################################################################
		// ########## Buscando si existe objeto imagen ########################################
		// ####################################################################################
		$encontradoobjetoimagen='';
		// Version anterior tomaba nombrecentro donde $nombrecaidcentro=$idrepodefault
		$nombrecaidcentro=0; // No afecta a vista unidad organizativa  
		$nombrecentro='';	//  No afecta a vista unidad organizativa
		// ########## Si el Nombre contiene .diff lo quitamos para buscar objeto imagen
		if(preg_match("/.diff/",$imgname)){ $imgname = str_replace(".diff", "", $imgname);}

		$cmd->texto="SELECT idcentro, nombrecentro, nombreca FROM imagenes LEFT JOIN centros USING(idcentro) WHERE nombreca='$imgname' ";
		$rs=new Recordset; 
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(0); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF){
		$encontradoobjetoimagen=(is_null($rs->campos["nombreca"]))? '': $rs->campos["nombreca"];
		$nombrecaidcentro=(is_null($rs->campos["idcentro"]))? 0 :$rs->campos["idcentro"];
		$nombrecentro=(is_null($rs->campos["nombrecentro"]))? '' : $rs->campos["nombrecentro"];
		}
		$rs->Cerrar();

		// Sobre: si devuelve una imagen tiene que coincidir
		if($encontradoobjetoimagen == $imgname) {
			$encontradoobjetoimagen=$value;
		}

		if ($modov != 1){  //VISTA UNIDAD ORGANIZATIVA
		   // Si la imagen no es del centro no la muestro
		   if ($nombrecaidcentro != $idcentro &&  $nombrecaidcentro != 0) {
			$contandotipo++;
			continue; 
		   }
		   // si ogunit con dir separados -> si la imagen no es del dir del centro no la muestro
		   if ($separarogunit == 1 and  ! preg_match("~".$dircentros[$idcentro]."~", "/".$value)) {
			$contandotipo++;
			continue; 
		   };

		}

		// ####################################################################################		
		// ########################## VARIABLES FICHERO DELETE ################################
		$nombredirectorio="/opt/opengnsys/images/".$value;
		// ####################################################################################	
		if ($tipo[$contandotipo] == "D")
		{
			$nombrefichero=str_replace(".img", "", $nombrefichero);
			$ficherodelete="../tmp/".$nombrefichero.".delete";
		}else{
			$ficherodelete="../tmp/".$nombrefichero.".delete";
			}
		// ########################## VARIABLES FICHERO DELETE ################################
		$nombredirectorio=str_replace(":","/",$nombredirectorio);
		$nombrefichero=str_replace(":","/",$nombrefichero);

		// ####################################################################################	
		// ######## TAMAÃ‘O DEL FICHERO Y DIRECTORIO ##########################
		if (is_dir ($nombredirectorio) && $tipo[$contandotipo] == "D")
			{
			$tamanofich=exec("ls -lah ".$nombredirectorio." | awk 'NR==1 {print $2}'");
			}
		elseif (preg_match("/.ant/",$nombrefichero))
			{
				$nombreficheroant=str_replace(".ant", "", $nombrefichero); //quitar todos los .ant y continuamos

				$nombreficheroant=$nombreficheroant.".ant";
				$tamanofich=exec("du -h --max-depth=1 /opt/opengnsys/images/$nombreficheroant");
				$tamanofich=explode("/",$tamanofich);//////////////////////////////////////////echo $nombrefichero."</br>";
			}else{
				$tamanofich=exec("du -h --max-depth=1 /opt/opengnsys/images/$nombrefichero");
				$tamanofich=explode("/",$tamanofich);//////////////////////////////////////////echo $nombrefichero."</br>";
			}
		// ######## TAMAÃ‘O DEL FICHERO Y DIRECTORIO ##########################
												
		$todo=".delete";
		$ruta='touch%20/opt/opengnsys/images/'.$value.$todo;//////////////////////////////////////echo $value;//

		echo '<TR>'.chr(13);

		// ########## Nº ######################################################################
		echo '<TD align=center>&nbsp;'.$contar.'&nbsp;</TD>'.chr(13);

		// ########## Marcar ##################################################################

		if ($bustor<>"") 
			{
			echo '<TD align=center><font color=red><strong>&nbsp;'.$TbMsg[14].'</strong></TD>'.chr(13);
			}
			elseif (file_exists($ficherodelete))
				{
					echo '<TD align=center><font color=red><strong>&nbsp;'.$TbMsg[15].'</strong></TD>'.chr(13);}
				else
				{
					echo '<TD align=center ><input type="checkbox" name="checkbox'.$contar.'"  value="si"></TD>'.chr(13);
				}

		// ########## Tipo ####################################################################
		if ($tipo[$contandotipo]=="D")
		{
			echo '<TD align=center ><font color=blue>'.$tipo[$contandotipo].'</TD>'.chr(13);
		}
		elseif ($tipo[$contandotipo]=="B")
		{
			echo '<TD align=center><font color=red>&nbsp;'.$tipo[$contandotipo].'&nbsp;</TD>'.chr(13);
			}else{
			echo '<TD align=center >'.$tipo[$contandotipo].'</TD>'.chr(13);
		}

		echo '<input type="hidden" name="nombre'.$contar.'" value='.$value.'></TD>'.chr(13);;
		echo '<input type="hidden" name="contar" value='.$contar.'></TD>'.chr(13);;
		echo '<input type="hidden" name="marcadif'.$contar.'" value='.$marcadif.'></TD>'.chr(13);;
		echo '<input type="hidden" name="tipoimg'.$contar.'"  value='.$tipo[$contandotipo].'></TD>'.chr(13);;
		echo '<input type="hidden" name="idcentroimg'.$contar.'"  value='.$nombrecaidcentro.'></TD>'.chr(13);;

		// ########## Aviso si directorio distinto al del centro - en vista repositorio ##########
		$aviso='';
		if ($separarogunit == 1) {
 		    if ( $nombrecaidcentro != 0 and  "/".$imgdir != $dircentros[$nombrecaidcentro]){
 			$aviso="<font color=red> * </font>";
			$textoaviso="<tr>\n	<th colspan='7' align='center'>".
				"&nbsp;<sup>*</sup> $TbMsg[33] &nbsp;</th>\n".
				"</tr>\n";
 		    }
		}

		// ########## Nombre de Imagen ########################################################
		if ($tipo[$contandotipo]=="D")
		{
			echo '<TD align=center><font color=blue>&nbsp;'.str_replace(":"," / ",$value).' '.$aviso.'&nbsp;</TD>'.chr(13);
		}
		else
		{

			echo '<TD align=center>&nbsp;'.str_replace(":"," / ",$value).' '.$aviso.'&nbsp;</TD>'.chr(13);
		}

		// ########## Tamaño de Imagen ########################################################
		if (is_dir ($nombredirectorio) && $tipo[$contandotipo] == "D")
		{echo '<TD align=center>&nbsp;'.$tamanofich.'</TD>'.chr(13);}
		else{echo '<TD align=center>&nbsp;'.$tamanofich[0].'</TD>'.chr(13);}

		// ########## Objeto Imagen ###########################################################
		if($encontradoobjetoimagen<>"")
		{
			echo '<TD align=center ><input type="checkbox" name="checkboxobjeto'.$contar.'"  value="si"></TD>'.chr(13);
		}
		elseif (preg_match("/.ant/",$nombrefichero))
			{
				echo '<TD align=center><font color=red>&nbsp;------</strong></TD>'.chr(13);
			}else{
				echo '<TD align=center><font color=red>&nbsp;'.$TbMsg[25].'</strong></TD>'.chr(13);
		}
		// VISTA REPOSITORIO COMPLETO
		if ($modov == 1){
                // #####################################################################################
                // ########## Unidad Organizativa ######################################################

                        echo '<TD align=center >'.$nombrecentro.'</TD>'.chr(13);

                // #####################################################################################
		}

		// #####################################################################################
		echo '</TR>'.chr(13);
		$contar++;
		$contandotipo++;

	} //Fin Llave Forach
	if (isset ($textoaviso)) echo $textoaviso;

	?>

			
	<INPUT type="hidden" name="opcion" value="<?php echo $opcion?>">
	<INPUT type="hidden" name="idrepositorio" value="<?php echo $idrepositorio?>">
	<INPUT type="hidden" name="grupoid" value="<?php echo $grupoid ?>">
	<INPUT type="hidden" name="modov" value="<?php echo $modov; ?>">

	</TABLE><BR/>
	<TABLE align=center>
		<TR>
			<TD></TD>
			<TD align=center></TD>
		</TR>
		<TR>
			<TD></TD>
		<TD align=center><A href=#><IMG border=0 src="../images/boton_confirmar_<?php echo $idioma ?>.gif" onclick="confirmeliminar()" ></A></TD>
		</TR>
	</TABLE>
</FORM>
 

</BODY>
</HTML>

<?php } ?>