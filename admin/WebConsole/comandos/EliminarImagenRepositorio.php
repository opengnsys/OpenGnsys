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
//###############################################################
// INCLUIDO PARA ELIMINAR OBJETO IMAGEN
//###############################################################
include_once("../gestores/relaciones/imagenes_eliminacion.php");
//###############################################################
if (isset($_POST["opcion"])) {$opcion=$_POST["opcion"];}else{$opcion;} // Recoge parametros
//$opcion=$_POST["opcion"]; // Recoge parametros
if (isset($_POST["idrepositorio"])) {$idrepositorio=$_POST["idrepositorio"];}else{$idrepositorio;}
//$idrepositorio=$_POST["idrepositorio"]; 
if (isset($_POST["grupoid"])) {$grupoid=$_POST["grupoid"];}else{$grupoid;}
//$grupoid=$_POST["grupoid"]; 
$idcentro=$_SESSION["widcentro"];
if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idrepositorio"])) $idrepositorio=$_GET["idrepositorio"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idrepositorio=$_GET["identificador"]; 
//___________________________________________________________________________
//________________________________________________________________________________________________________
$idcomando=10;
$descricomando="Ejecutar Script";
//echo $ambito."<br>";
//echo $idambito."<br>";
$funcion="EjecutarScript";
//echo $atributos."<br>";
//echo $gestor;
$gestor="../comandos/gestores/gestor_Comandos.php";
//$gestor="./ElimininarImagenRepositorio.php";
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexiÃƒÂ³n con servidor B.D.
//___________________________________________________________________________________________________
//#########################################################################
// BUSCANDO INFORMACION SOBRE EL ESPACIO EN EL REPOSITORIO
//#########################################################################
	$espaciorepo=exec("df -h /opt/opengnsys/images");
	$espaciorepo=split(" ",$espaciorepo);
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
//#########################################################################
//##### BUSCAMOS LA IP DEL SERVER
//#########################################################################
$ipservidor=$_SERVER['SERVER_ADDR'];
//#########################################################################
//#### BUSCAMOS LA IP DEL REPOSITORIO SEGUN NOS LLEGA EL ID DEL REPOSITORIO
//#########################################################################
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
//#########################################################################
// SI LA IP DEL SERVER Y REPOSITORIO SON IGUALES ( ES LOCAL )
//#########################################################################
if ($iprepositorio == $ipservidor)
{
$repolocal="si";
	//#########################################################################
	// LEYENDO EL DIRECTORIO local en el server
	// /opt/opengnsys/images/
	//#########################################################################
	$dirtemplates="/opt/opengnsys/images/";
	$directorio=dir($dirtemplates);
	$imarepo= array();//pila de nombres
	//bucle para llenar las pilas :P
	while ($archivo = $directorio->read())
	{
		//no mostrar ni "." ni ".." ni "pxe"
		if(($archivo!=".")&&($archivo!=".."))
		{
		array_push($imarepo, $archivo);
		}
	}
	$directorio->close();
	//#########################################################################
	// RECOGEMOS LAS IMAGENES Y OBJETO IMAGENES QUE VAMOS A ELIMINAR
	//#########################################################################
	if (isset($_POST["contar"])) {$cuantos=$_POST["contar"];}else{$cuantos=0;$contar;}
	//$cuantos=$_POST["contar"];
	for ($i=1;$i<=$cuantos;$i++)
	{
		//#########################################################################
		// PARA SELECCIONAR EL FICHERO IMAGEN
		//$checkbox=$_POST["checkbox".$i];
		if (isset($_POST["checkbox".$i])){$checkbox=$_POST["checkbox".$i];}else{$checkbox="checkbox".$i;}
		$nombre=$_POST["nombre".$i];
		$nombre=trim($nombre);
		$chekmarcadif=$_POST["marcadif".$i];

		if ($checkbox == "si" && $chekmarcadif == 1)
		{
			$delete=$nombre.".img.diff.delete";
			//echo $delete;
			exec("touch ../tmp/$delete");
			exec("(echo '$nombre') > ../tmp/$delete");
		}
		if ($checkbox == "si" && $chekmarcadif == 0)
		{
			$delete=$nombre.".img.delete";
			//echo $delete;
			exec("touch ../tmp/$delete");
			exec("(echo '.$nombre.') > ../tmp/$delete");
		}
	//#########################################################################
	// PARA SELECCIONAR EL OBJETO IMAGEN
		if (isset($_POST["checkboxobjeto".$i])){$checkboxobjeto=$_POST["checkboxobjeto".$i];}else{$checkboxobjeto="checkboxobjeto".$i;}
		if ($checkboxobjeto == "si")
		{
	//#########################################################################
		$cmd->texto="SELECT * FROM imagenes WHERE nombreca='$nombre' AND idcentro='$idcentro'";
		$rs=new Recordset; 
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(0); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF){
		$idimagen=$rs->campos["idimagen"];
		$nombrecanonico=$rs->campos["nombreca"];
		$centroimagen=$rs->campos["idcentro"];
		$idimagen=$rs->campos["idimagen"];
		//$cmd->texto="DELETE FROM imagenes WHERE idimagen='$idimagen'";
		//$resul=$cmd->Ejecutar();
				}
		$rs->Cerrar();

		EliminaImagenes($cmd,$idimagen,"idimagen");// EliminaciÃƒÂ³n en cascada

		//echo $nombrecanonico." - ".$centroimagen."<br />";
		//#########################################################################
	   }
}
//#########################################################################
//#########################################################################
// EL REPOSITORIO NO ES LOCAL
//#########################################################################
//#########################################################################
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

//#########################################################################
//	EN PRUEBAS
/*
$espaciorepo=exec("ssh root@$ip 'df -h /opt/opengnsys/images'");
if ($espaciorepo != "")
	{
	$espaciorepo=split(" ",$espaciorepo);
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
	}
		
*/
//	EN PRUEBAS
//#########################################################################
 }

//#########################################################################
?>

<?php if ( $repolocal == "si" ){ 
//#########################################################################
// SI REPOSITORIO ES LOCAL

//#########################################################################
?>

<HTML>
<TITLE>AdministraciÃƒÂ³n web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<script type="text/javascript">
function confirmeliminar() {var mensaje="<?php echo $TbMsg[17];?>";if(confirm(mensaje)) {document.eliimarepo.submit();}}
</script> 
</script> 
</HEAD>
<BODY>
<?

			$urlimg='../images/iconos/repositorio.gif';
			$textambito=$TbMsg[0];

	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[6].': '.$iprepositorio.','.$nombrerepositorio.'</U></span>&nbsp;&nbsp;</span></p>';
?>


	<TABLE  align=center border=0 cellPadding=2 cellSpacing=2 class=tabla_datos >
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<?php if ($espaciorepo != ""){?>
			<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[18]?>&nbsp;</TD>
			<TH align=center>&nbsp;<?echo $TbMsg[19]?>&nbsp;</TD>
			<TH align=center>&nbsp;<?echo $TbMsg[20]?>&nbsp;</TD>
			<TH align=center>&nbsp;<?echo $TbMsg[21]?>&nbsp;</TD>
		</TR>
                <TR>
			<TD align=center width=110>&nbsp;<?echo $totalrepo?>&nbsp;</TD>
            <TD align=center width=120>&nbsp;<?echo $ocupadorepo?>&nbsp;</TD>
            <TD align=center width=120>&nbsp;<?echo $librerepo?>&nbsp;</TD>
            <TD align=center width=101>&nbsp;<?echo $porcentajerepo?>&nbsp;</TD>
                </TR>
		<?php }else {?>
        			<TR>
            <TH align=center width=485>&nbsp;<?echo $TbMsg[22]?>&nbsp;</TD>

					</TR>
        <?php } ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
      	</TABLE>

	<P align=center>
	<SPAN align=center class=subcabeceras><? echo $TbMsg[7] ?></SPAN>
	</BR>






<form  align=center name="eliimarepo" action="./EliminarImagenRepositorio.php" method="post"> 

	<TABLE  id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<? echo $TbMsg[11] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[12] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[27] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[10] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[13] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[26] ?>&nbsp;</TH>


		</TR>
			<?
				//echo tabla_configuraciones($cmd,$idambito);

				
	$idc=$_SESSION["widcentro"];
	for ($x=0;$x<count($imarepo); $x++)
	{ //Llave For
			if(ereg(".img",$imarepo[$x])  ) //si contiene .img
			{	
					if (ereg(".sum",$imarepo[$x]) || ereg(".torrent",$imarepo[$x])|| ereg(".lock",$imarepo[$x])  )//Si el nombre contiene .img.sum o img.torrent o .img.lock
					{}else{	// COMPROBANDO EL NOMBRE DIF
						if(ereg(".img.diff",$imarepo[$x]))
						{
							$imarepo[$x] = str_replace(".diff", "", $imarepo[$x]); //quitar todos los .img
							$imarepo[$x]=trim($imarepo[$x]);
							$imarepo[$x] = str_replace(".img", "", $imarepo[$x]); //quitar todos los .img
							$imarepo[$x]=trim($imarepo[$x]);
							$nombreimagenes[]=$imarepo[$x].'.diff';
							$tipo[]="F";
						}
						else{
							$imarepo[$x] = str_replace(".img", "", $imarepo[$x]); //quitar todos los .img
							$imarepo[$x]=trim($imarepo[$x]);
							$nombreimagenes[]=$imarepo[$x];
							$tipo[]="F";
							}
				   		}
			}else{
				$imarepo[$x]=trim($imarepo[$x]);
				$nombreimagenes[]=$imarepo[$x];
				$tipo[]="D";
				}
		} //Fin Llave For



	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					// Tenemos los nombres en un Array[]
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$sin_duplicados=array_unique($nombreimagenes);
	$contandotipo=0;
	$contar=1;
	foreach($sin_duplicados as $value) //imprimimos $sin_duplicados
	{ //Llave Forach


		//Buscamos tamano de fichero
		//Buscamos .torrent y .sum con lock
		$gensum=$value.".img.sum.lock";
		$gentor=$value.".img.lock";
		if(ereg(".diff",$value))$gendif=$value.".img.diff";
		// ########### Buscando si existe fichero imagen #####################
		$buscando="find /opt/opengnsys/images/ -maxdepth 1 -name ".$gentor." -print";
		$generando="Generando .torrent";
		$bustor=exec($buscando);
		if(ereg(".diff",$value)) 
			{
			$marcadif=1;
			$value = str_replace(".diff", "", $value); //quitar todos los .diff y continuamos
			$nombrefichero=trim($value);
			$nombrefichero=$value.'.img.diff';
			}
		else
			{
			$nombrefichero=$value.'.img';$marcadif=0;
			}


		// ####################################################################################
		// ########## Si el nombre imagen existe en la Unidad Organizativa ####################
		// ####################################################################################
		//echo $value." - ".$idcentro."</br>";

		$cmd->texto="SELECT * FROM imagenes WHERE nombreca='$value' ";//AND idcentro='$idcentro'";
		$rs=new Recordset; 
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(0); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF){
		$nombrecacentro=$rs->campos["nombreca"];
		$nombrecaidcentro=$rs->campos["idcentro"];
						}
		if ($nombrecacentro != $value){$nombrecaidcentro=0;}
		$rs->Cerrar();

		if($nombrecaidcentro == $idcentro || $nombrecaidcentro==0)
			{	//Comienzo de Condicion si es nombrecaidcentro
			//echo "Value   -  ".$value."/   -  Id Centro - ".$idc." /Nombrecacentro -  ".$nombrecacentro." /Base ID Centro ".$nombrecaidcentro."</br>";
		
		// ####################################################################################
		// ####################################################################################
		// ########## Buscando si existe objeto imagen ########################################
		// ########## Si el Nombre contiene .diff lo quitamos para buscar objeto imagen
		if(ereg(".diff",$value)){ $valuediff=$value; $value = str_replace(".diff", "", $value);} //quitar todos los .diff y continuamos
		// ####################################################################################

		$encontradoobjetoimagen="";
		$cmd->texto="SELECT * FROM imagenes WHERE nombreca='$value' AND idcentro='$idcentro'";
		$rs=new Recordset; 
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(0); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF){
		$encontradoobjetoimagen=$rs->campos["nombreca"];
					}
		if($encontradoobjetoimagen == $value){$encontradoobjetoimagen;}else{$encontradoobjetoimagen="";}
		$rs->Cerrar();
		// ####################################################################################		

		// ########################## VARIABLES FICHERO DELETE ################################
		$nombredirectorio="/opt/opengnsys/images/".$value;
		$ficherodelete="../tmp/".$nombrefichero.".delete";
		// ########################## VARIABLES FICHERO DELETE ################################
		// ####################################################################################	
		// ######## TAMAÃ‘O DEL FICHERO Y DIRECTORIO ##########################

		if (is_dir ($nombredirectorio))
			{
			$tamanofich=exec("ls -lah ".$nombredirectorio." | awk 'NR==1 {print $2}'");
			}
		else
			{
			$tamanofich=exec("du -h --max-depth=1 /opt/opengnsys/images/$nombrefichero");
			$tamanofich=split("/",$tamanofich);//////////////////////////////////////////echo $nombrefichero."</br>";
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
		else
		{
			echo '<TD align=center >'.$tipo[$contandotipo].'</TD>'.chr(13);
		}

		echo '<input type="hidden" name="nombre'.$contar.'" value='.$value.'></TD>'.chr(13);;
		echo '<input type="hidden" name="contar" value='.$contar.'></TD>'.chr(13);;
		echo '<input type="hidden" name="marcadif'.$contar.'" value='.$marcadif.'></TD>'.chr(13);;

		// ########## Nombre de Imagen ########################################################
		if ($tipo[$contandotipo]=="D")
		{
			echo '<TD align=center><font color=blue>&nbsp;'.$value.'&nbsp;</TD>'.chr(13);
		}
		else
		{
			echo '<TD align=center>&nbsp;'.$value.'&nbsp;</TD>'.chr(13);
		}

		// ########## Tamaño de Imagen ########################################################
		if (is_dir ($nombredirectorio))
		{echo '<TD align=center>&nbsp;'.$tamanofich.'</TD>'.chr(13);}
		else{echo '<TD align=center>&nbsp;'.$tamanofich[0].'</TD>'.chr(13);}

		// ########## Objeto Imagen ###########################################################
		if($encontradoobjetoimagen<>"")
		{
			echo '<TD align=center ><input type="checkbox" name="checkboxobjeto'.$contar.'"  value="si"></TD>'.chr(13);
		}
		else
		{
			echo '<TD align=center><font color=red><strong>&nbsp;'.$TbMsg[25].'</strong></TD>'.chr(13);
		}
		// #####################################################################################
		echo '</TR>'.chr(13);
		$contar++;
		$contandotipo++;

						}else{$contandotipo++;}//Fin de Condicion si es nombrecaidcentro
	} //Fin Llave Forach

	?>

			
	<INPUT type="hidden" name="opcion" value="<? echo $opcion?>">
	<INPUT type="hidden" name="idrepositorio" value="<? echo $idrepositorio?>">
	<INPUT type="hidden" name="grupoid" value="<? echo $grupoid ?>">

	</TABLE><BR/>
	<TABLE align=center>
		<TR>
			<TD></TD>
			<TD align=center></TD>
		</TR>
		<TR>
			<TD></TD>
		<TD align=center><A href=#><IMG border=0 src="../images/boton_confirmar_<? echo $idioma ?>.gif" onclick="javascript:confirmeliminar()" ></A></TD>
		</TR>
	</TABLE>
</FORM>
 

</BODY>
</HTML>

<?php }
//#########################################################################
// SI REPOSITORIO ES LOCAL
//#########################################################################
 ?>
