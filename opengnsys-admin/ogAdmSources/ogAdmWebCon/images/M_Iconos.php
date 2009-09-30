<?
// ********************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Copyright 2003-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Diciembre-2003
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: M_Iconos.php
// Descripción :Este fichero implementa  el mantenimiento de la tabla Iconos
// ********************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../clases/SockHidra.php");
include_once("../includes/FicherosPost.php");
//-------------------------------------------------------------------------------------------------------------------------------------------------
// Captura de parámetros 
//-------------------------------------------------------------------------------------------------------------------------------------------------
$opcion="";
$accion="";
$idicono=0;

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; 
if (isset($_POST["accion"])) $accion=$_POST["accion"]; 
if (isset($_POST["idicono"])) $idicono=$_POST["idicono"]; 

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; 
if (isset($_GET["accion"])) $accion=$_GET["accion"]; 
if (isset($_GET["idicono"])) $idicono=$_GET["idicono"]; 
//-------------------------------------------------------------------------------------------------------------------------------------------------
// Constantes
//-------------------------------------------------------------------------------------------------------------------------------------------------
// $opciones
$INSERTAR=1;
$ELIMINAR=2;
$MODIFICAR=3;
$CONSULTAR=4;

// Acciones
$SIN_ACCION=0;
$INSERTAR_REGISTRO=1;
$BORRAR_REGISTRO=2;
$MODIFICAR_REGISTRO=3;
$LEER_REGISTRO=4;

$mopciones[1]="INSERTAR";
$mopciones[2]="ELIMINAR";
$mopciones[3]="MODIFICAR";
$mopciones[4]="CONSULTAR";
//-------------------------------------------------------------------------------------------------------------------------------------------------
// Datos por defecto 
//-------------------------------------------------------------------------------------------------------------------------------------------------
if(empty($opcion)) $opcion=$INSERTAR;
if(empty($accion)) $accion=$SIN_ACCION;
if(empty($idicono)) $idicono=0;
$msg="";
//-------------------------------------------------------------------------------------------------------------------------------------------------
// Conexion a la base de datos 
//-------------------------------------------------------------------------------------------------------------------------------------------------
$cmd=CreaComando($cadenaconexion);
if (!$cmd) // Fallo conexión con servidor de datos
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//-------------------------------------------------------------------------------------------------------------------------------------------------
// Acción a ejecutar
//-------------------------------------------------------------------------------------------------------------------------------------------------
if($accion==$INSERTAR_REGISTRO || $accion==$MODIFICAR_REGISTRO){
	$cmd->CreaParametro("@idicono",$idicono,1);
	IncializaCampos();
	if (isset($_POST["swbf_urlicono"])) $swbf_urlicono=$_POST["swbf_urlicono"];
	if (isset($_POST["urlicono"])) $urlicono=$_POST["urlicono"];
	if (isset($_POST["idtipoicono"])) $idtipoicono=$_POST["idtipoicono"];
	if (isset($_POST["descripcion"])) $descripcion=$_POST["descripcion"];
	$cmd->CreaParametro("@urlicono",$urlicono,0);
	$cmd->CreaParametro("@idtipoicono",$idtipoicono,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);

	$UrlPagina=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; // Url página
	// Se recibe fichero adjunto
	$NombreFichero_urlicono = $HTTP_POST_FILES['urlicono']['name']; 
	if(!empty($NombreFichero_urlicono)){
		$NombreFicheroPost_urlicono = $HTTP_POST_FILES['urlicono']['tmp_name']; 
		$tamano_archivo = $HTTP_POST_FILES['urlicono']['size']; 
		if($tamano_archivo>100000){
			$msg="El tamaño del archivo no corresponde con los límites permitidos, debe ser mayor  que 0 y menor de 100 KB";
			IncializaCampos();
			$opcion=$INSERTAR;
			$accion=$SIN_ACCION;
		}
		else{
			if(!SalvaFichero_POST($UrlPagina,$NombreFicheroPost_urlicono,$NombreFichero_urlicono,&$UrlFichero_urlicono))
				Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
			else{
				$msg="Se ha enviado al servidor web el fichero de Icono, correctamente";
				$cmd->ParamSetValor("@urlicono",basename($UrlFichero_urlicono));
			}
		}
	}
	switch($accion){
		case $INSERTAR_REGISTRO :
			$cmd->texto="INSERT INTO iconos (urlicono,idtipoicono,descripcion ) VALUES (@urlicono,@idtipoicono,@descripcion);";
			$resul=$cmd->Ejecutar();
			if (!$resul)
				Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
			break;
		case $MODIFICAR_REGISTRO:
			if(!empty($NombreFichero_urlicono) || !empty($swbf_urlicono)){
				$filebaja_urlicono="";
				if (isset($_POST["filebaja_urlicono"])) $filebaja_urlicono=$_POST["filebaja_urlicono"];
				$UrlPagina=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; // Url página
				if(!empty($filebaja_urlicono)){
					if(!EliminaFichero($UrlPagina,$filebaja_urlicono))
						Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
					else
						$msg="Se ha sustituido del servidor web el fichero de Icono, correctamente";
				}
			}
			else{
				if (isset($_POST["fileexist_urlicono"])) $urlicono=$_POST["fileexist_urlicono"];
				$cmd->ParamSetValor("@urlicono",basename($urlicono));
			}
			$cmd->texto="UPDATE iconos SET urlicono=@urlicono,idtipoicono=@idtipoicono,descripcion=@descripcion  WHERE idicono=@idicono";
			$resul=$cmd->Ejecutar();
			if (!$resul) 
				Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
			break;
	}
	IncializaCampos();
	$opcion=$INSERTAR;
	$accion=$SIN_ACCION;
}else{
	if($accion==$BORRAR_REGISTRO){
		$cmd->texto="DELETE FROM iconos WHERE idicono=".$idicono;
		$resul=$cmd->Ejecutar();
		if (!$resul)
			Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
		$filebaja_urlicono="";
		if (isset($_POST["filebaja_urlicono"])) $filebaja_urlicono=$_POST["filebaja_urlicono"];
		$UrlPagina=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; // Url página
		if(!empty($filebaja_urlicono)){
			if(!EliminaFichero($UrlPagina,$filebaja_urlicono))
				Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
			else
				$msg="Se ha eliminado del servidor web el fichero de Icono, correctamente";
		}
		IncializaCampos();
		$opcion=$INSERTAR;
		$accion=$SIN_ACCION;
	}
	else{
		if($accion==$LEER_REGISTRO){
			$rs=new Recordset; 
			$cmd->texto="SELECT * FROM iconos WHERE idicono=".$idicono;
			$rs->Comando=&$cmd; 
			if (!$rs->Abrir()) 
				Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
			if ($rs->EOF) 
				Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
			$urlicono=$rs->campos["urlicono"];
			$idtipoicono=$rs->campos["idtipoicono"];
			$descripcion=$rs->campos["descripcion"];
		}
		else{ // Sin accion
			IncializaCampos();
			$opcion=$INSERTAR;
			$accion=$SIN_ACCION;
		}
	}
}
?>
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
<!-- Página HTML del Mantenimiento de la tabla
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
  <HTML>
  <HEAD>
  <LINK rel="stylesheet" type="text/css" href="../estilos.css">
  <SCRIPT language="javascript" src="M_Iconos.js"></SCRIPT>
  </HEAD>
  <BODY>
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
  <FORM name="fdatos" action="M_Iconos.php"  enctype="multipart/form-data" method="post">
	<input name="opcion" type="hidden" value="<? echo $opcion?>">
	<input name="accion" type="hidden" value="<? echo $accion?>">
	<input name="idicono" type="hidden" value="<? echo $idicono?>">
	<input name="filebaja_urlicono" type="hidden" value="<? echo basename($urlicono)?>">
	<input name="fileexist_urlicono" type="hidden" value="<? echo $urlicono?>">
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
     <DIV align=center id="Layer_Datos">
		<P class=cabeceras>Iconos<BR>
		<SPAN class="subcabeceras"><?=$mopciones[$opcion]?></SPAN></P>
		<P align="center"><SPAN class=textos>____ Datos de Iconos ____</SPAN></P>
        <TABLE class="tabla_datos" align="center">
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
			<TR>
				<TH>&nbsp;Tipo&nbsp;</TH>
				<?if ($opcion==$CONSULTAR || $opcion==$ELIMINAR){?>
					<TD><?
							$TBtipo[1]="Iconos web";
							$TBtipo[2]="Iconos items";
							echo $TBtipo[$idtipoicono];
					}else{
								$parametros="0=".chr(13);
								$parametros.="1=iconos web".chr(13);
								$parametros.="2=iconos items";
								echo '<TD>'.HTMLCTESELECT($parametros, "idtipoicono","estilodesple","",$idtipoicono,100).'</TD>';
					}?>
			</TR>

<!-------------------------------------------------------------------------------------------------------------------------------------------------->
			<TR>
				<TH>&nbsp;Descripcion&nbsp;</TH>
				<?if ($opcion==$CONSULTAR || $opcion==$ELIMINAR){?>
					<TD><?echo $descripcion?></TD>
				<?}else{?>
					<TD><INPUT class=cajatexto name="descripcion" maxlength=50  style="width:150" value="<? echo $descripcion?>"></TD>
				<?}?>
			</TR>
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
		<?if ($opcion==$CONSULTAR || $opcion==$ELIMINAR){?>
			<TR>
				<TH>&nbsp;Icono&nbsp;</TH>
				<TD><?echo basename($urlicono)?></TD>
			</TR>
		<?}else{
						if ($opcion==$INSERTAR || ($opcion==$MODIFICAR && empty($urlicono))){?>
							<TR>
								<TH>&nbsp;Icono&nbsp;</TH>
								<TD><INPUT type=file class=cajatexto name="urlicono"  style="width:500" value="<? echo $urlicono?>">
						</TR>
					<?}else{?>
							<TR>
								<TH>&nbsp;Icono&nbsp;</TH>
								<TD><?echo basename($urlicono)?></TD>
							</TR>
							<TR>
								<TH>&nbsp;Sustituir Fichero&nbsp;</TH>
								<TD><INPUT type=file class=cajatexto name="urlicono"  style="width:500" value="<? echo $urlicono?>">
						</TR>
					<?}?>
			<?}?>
			</TR>
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
	</DIV>
	<BR>
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
    <DIV id="Layer_opciones">
        <TABLE align="center" border="0" >
             <TR>
             <?switch($opcion){
                   case $CONSULTAR:

						  break;
					case $ELIMINAR:
							echo '<TD><img SRC="../images/boton_confirmar.gif" style="cursor:hand" onclick="Confirmar()"></TD>';
							echo '<TD><img SRC="../images/boton_cancelar.gif" style="cursor:hand" onclick="Cancelar()"></TD>';
							break;
                      default:
						  echo '<TD><img style="cursor:hand" SRC="../images/boton_confirmar.gif" onclick="Confirmar()"></TD>';
						  echo '<TD><img style="cursor:hand" SRC="../images/boton_cancelar.gif" onclick="Cancelar()"></TD>';
						  break;
				}?>
           </TR>
        </TABLE>
	</DIV>
<?
//________________________________________________________________________________________________________
// Posiciona cursor en campo usuario y muestra mensaje de error si lo hubiera
echo '<SCRIPT LANGUAGE="javascript">';
if (!empty($msg))
	echo 'alert("'.$msg.'")';
echo '</SCRIPT>';
?>
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
  </FORM>
  </BODY>
  </HTML>
  <?
//______________________________________________________________________
//	Inicialiciza los campos de trabajo de la tabla
//______________________________________________________________________
function IncializaCampos(){

  	global $idicono;
  	$idicono=0;
	
	global $urlicono;
	global $idtipoicono;
	global $descripcion;

	$urlicono="";
	$idtipoicono=0;
	$descripcion="";
}

