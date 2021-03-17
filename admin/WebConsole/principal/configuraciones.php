<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: configuraciones.php
// Descripción : 
//		Muestra la configuración de las particiones de los ordenadores de un aula
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/comunes.php");
include_once("../includes/constantes.php");
include_once("../idiomas/php/".$idioma."/configuraciones_".$idioma.".php");
include_once("../includes/ConfiguracionesParticiones.php");

define("LOG_FILE", "/opt/opengnsys/log/ogagent.log");

//________________________________________________________________________________________________________
//
// Captura parámetros
//________________________________________________________________________________________________________

$ambito=0;
$idambito=0;
$swp=0; // Switch que indica si viene de las propiedades de ordenadores
// Agrupamiento por defecto
$fk_sysFi=0;
$fk_nombreSO=0;
$fk_tamano=0;
$fk_imagen=0;
$fk_perfil=0;
$fk_cache=0;

if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["ambito"])) $ambito=$_GET["ambito"]; 
if (isset($_GET["swp"])) $swp=$_GET["swp"]; 

if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
if (isset($_POST["ambito"])) $ambito=$_POST["ambito"]; 

if (isset($_POST["fk_sysFi"])) $fk_sysFi=$_POST["fk_sysFi"]; 
if (isset($_POST["fk_nombreSO"])) $fk_nombreSO=$_POST["fk_nombreSO"]; 
if (isset($_POST["fk_tamano"])) $fk_tamano=$_POST["fk_tamano"]; 
if (isset($_POST["fk_imagen"])) $fk_imagen=$_POST["fk_imagen"]; 
if (isset($_POST["fk_perfil"])) $fk_perfil=$_POST["fk_perfil"]; 
if (isset($_POST["fk_cache"])) $fk_cache=$_POST["fk_cache"];

//________________________________________________________________________________________________________

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<html lang="es">
<head>
    <title>Administración web de aulas</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<link rel="stylesheet" type="text/css" href="../estilos.css">
</head>
<body>
<?php
	switch($ambito){
			case $AMBITO_AULAS :
				$urlimg='../images/iconos/aula.gif';
				$textambito=$TbMsg[2];
				break;
			case $AMBITO_GRUPOSORDENADORES :
				$urlimg='../images/iconos/carpeta.gif';
				$textambito=$TbMsg[3];
				break;
			case $AMBITO_ORDENADORES :
				$urlimg='../images/iconos/ordenador.gif';
				$textambito=$TbMsg[4];
	}
	if(!$swp){
		echo '<p align=center><span class=cabeceras>'.$TbMsg[0].'&nbsp;</span><br>';
		echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[1].'
				: '.$textambito.'</U></span>&nbsp;&nbsp;</span></p>';
	}

	switch($ambito){
		case $AMBITO_AULAS :
			$resul=datosAulas($cmd,$idambito);
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$resul=datosGruposOrdenadores($cmd,$idambito);
			break;
		case $AMBITO_ORDENADORES :
			if(!$swp)
				$resul=datosOrdenadores($cmd,$idambito);
			break;
	}
	if($ambito!=$AMBITO_ORDENADORES){			
	?>
		<P align=center><SPAN align=center class=subcabeceras><?php echo $TbMsg[19]?></SPAN></P>
		<FORM action="configuraciones.php" name="fdatos" method="POST">
				<INPUT type="hidden" name="idambito" value="<?php echo $idambito?>">
				<INPUT type="hidden" name="ambito" value="<?php echo $ambito?>">			
				<TABLE class="tabla_busquedas" align=center border=0 cellPadding=0 cellSpacing=0>
				<TR>
					<TH height=15 align="center" colspan=17><?php echo $TbMsg[18]?></TH>
				</TR>
				<TR>

					<TD align=right><?php echo $TbMsg[30]?></TD>
					<TD align=center><INPUT type="checkbox" value="<?php echo $msk_sysFi?>" name="fk_sysFi" <?php if($fk_sysFi==$msk_sysFi) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>
					<TD align=right><?php echo $TbMsg[31]?></TD>
					<TD align=center><INPUT type="checkbox" value="<?php echo $msk_nombreSO?>" name="fk_nombreSO" <?php if($fk_nombreSO==$msk_nombreSO) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>
					<TD align=right><?php echo $TbMsg[32]?></TD>
					<TD align=center><INPUT type="checkbox" value="<?php echo $msk_tamano?>" name="fk_tamano" <?php if($fk_tamano==$msk_tamano) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>
					<TD align=right><?php echo $TbMsg[33]?></TD>
					<TD align=center><INPUT type="checkbox" value="<?php echo $msk_imagen?>" name="fk_imagen" <?php if($fk_imagen==$msk_imagen) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>		
					<TD align=right><?php echo $TbMsg[34]?></TD>
					<TD align=center><INPUT type="checkbox" value="<?php echo $msk_perfil?>" name="fk_perfil" <?php if($fk_perfil==$msk_perfil) echo " checked "?>></TD>
                    <TD width="20" align=center>&nbsp;</TD>
					<TD align=right><?php echo $TbMsg[495]?></TD>
                    <TD align=center><INPUT type="checkbox" value="<?php echo $msk_cache?>" name="fk_cache" <?php if($fk_cache==$msk_cache) echo " checked "?>></TD>


				</TR>
				<TR>
					<TD height=2 style="BORDER-TOP:#999999 1px solid;" align="center" colspan=17>&nbsp;</TD>			
				</TR>
				<TR>
					<TD height=20 align="center" colspan=14>
						<A href=#>
						<IMG border=0 src="../images/boton_confirmar.gif" onClick="document.fdatos.submit()"></A></TD>			
				</TR>
			</TABLE>
		</FORM>	
<?php
	}
	$sws=$fk_sysFi | $fk_nombreSO | $fk_tamano | $fk_imagen | $fk_perfil | $fk_cache;	
	pintaConfiguraciones($cmd,$idambito,$ambito,9,$sws,false);
	if ($ambito == $AMBITO_ORDENADORES) {
	    datos_sesiones($cmd, $idambito);
    }
?>
</body>
</html>

<?php
//________________________________________________________________________________________________________
function datosAulas($cmd,$idaula)
{
	global $TbMsg;
	
	$cmd->texto="SELECT DISTINCT aulas.*, COUNT(ordenadores.idordenador) AS numordenadores
			 FROM aulas
			 LEFT JOIN ordenadores ON ordenadores.idaula=aulas.idaula
			 WHERE aulas.idaula=$idaula";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$rs->Primero(); 
		if (!$rs->EOF){
			$nombreaula=$rs->campos["nombreaula"];
			$urlfoto=$rs->campos["urlfoto"];
			$cagnon=$rs->campos["cagnon"];
			$pizarra=$rs->campos["pizarra"];
			$ubicacion=$rs->campos["ubicacion"];
			$comentarios=$rs->campos["comentarios"];
			$puestos=$rs->campos["puestos"];
			$ordenadores=$rs->campos["numordenadores"];
			$numord=$rs->campos["numordenadores"];
		}
		$rs->Cerrar();
	}
?> 
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
			<TR>	
			<TH align=center>&nbsp;<?php echo $TbMsg[5]?>&nbsp;</TD>
			<?php
					echo '<TD>'. $nombreaula.'</TD>';
					echo '<TH align=center>&nbsp;'.$TbMsg[7].'&nbsp;</TH>';
					echo '<TD><INPUT  class="formulariodatos" name=cagnon type=checkbox ';
					if ($cagnon) echo ' checked ';
					echo '></TD>';
			?>
							<TD valign=top align=center rowspan=3>
					<IMG border=3 style="border-color:#63676b"
					src="<?php echo "../images/fotos/".$urlfoto?>">
					 <BR><center>&nbsp;<?php echo $TbMsg[13].':&nbsp;'. $ordenadores?></center></TD>
		</TR>
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[6]?>&nbsp;</TH>
			<?php
					echo '<TD>'.$ubicacion.'</TD>';
			?>
			<TH align=center>&nbsp;<?php echo $TbMsg[8]?>&nbsp;</TD>
			<?php
					echo '<TD><INPUT  class="formulariodatos" name=pizarra type=checkbox ';
					if ($pizarra) echo ' checked ';
					echo '></TD>';					
			?>
		</TR>	
		<TR>
			<TH align=center&nbsp;><?php echo $TbMsg[9]?>&nbsp;</TD>
			<?php
					echo '<TD>'.$puestos.'</TD>';
			?>
			<TH align=center>&nbsp;<?php echo $TbMsg[12]?>&nbsp;</TD>
			<?php
					echo '<TD>'.$comentarios.'</TD>';
			?>
		</TR>
		<TABLE style="border: 1px solid #d4d0c8;" align="center"><TR><td align=center width=200 height=10 class=subcabeceras><?php echo $TbMsg[4]." - ".$numord; ?></TD></TR>
	</TABLE>
<?php
}
//________________________________________________________________________________________________________
function datosOrdenadores($cmd,$idordenador)
{
	global $TbMsg;

	$cmd->texto="SELECT nombreordenador, ip, mac, fotoord, perfileshard.descripcion AS perfilhard 
			 FROM ordenadores
			 LEFT JOIN perfileshard ON perfileshard.idperfilhard=ordenadores.idperfilhard
			 WHERE ordenadores.idordenador=$idordenador";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$rs->Primero(); 
		if (!$rs->EOF){
			$nombreordenador=$rs->campos["nombreordenador"];
			$ip=$rs->campos["ip"];
			$mac=$rs->campos["mac"];
			$fotoordenador=$rs->campos["fotoord"];
			$perfilhard=$rs->campos["perfilhard"];
		}
		$rs->Cerrar();
	}
?> 
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[14]?>&nbsp;</TD>
			<TD><?php echo $nombreordenador;?></TD>
			<TD colspan=2 valign=top align=left rowspan=4><IMG border=2 style="border-color:#63676b"
src="<?php if ($fotoordenador==""){echo "../images/fotos/fotoordenador.gif";}
                else{echo "../images/fotos/".$fotoordenador;}?>">
			</TD>
			
			</TR>	
		<TR>
				<TH align=center>&nbsp;<?php echo $TbMsg[15]?>&nbsp;</TD>
				<?php echo '<TD>'.$ip.'</TD>';?>
			</TR>
		<TR>
				<TH align=center>&nbsp;<?php echo $TbMsg[16]?>&nbsp;</TD>
				<?php echo '<TD>'.$mac.'</TD>';?>
			</TR>	
		<TR>
				<TH align=center>&nbsp;<?php echo $TbMsg[17]?>&nbsp;</TD>
				<?php echo '<TD>'.$perfilhard.'</TD>';?>
			</TR>	
		<TR>
	</TABLE>
<?php
}
?>	
<?php
//________________________________________________________________________________________________________
function datosGruposOrdenadores($cmd,$idgrupo)
{
	global $TbMsg;

	$cmd->texto="SELECT DISTINCT gruposordenadores.*, COUNT(*) AS numordenadores
			 FROM gruposordenadores
			 INNER JOIN ordenadores ON ordenadores.grupoid=gruposordenadores.idgrupo
			 WHERE gruposordenadores.idgrupo=$idgrupo";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$rs->Primero(); 
		if (!$rs->EOF){
			$nombregrupoordenador=$rs->campos["nombregrupoordenador"];
			$ordenadores=$rs->campos["numordenadores"];
			$idaula=$rs->campos["idaula"];
			$numord=$rs->campos["numordenadores"];
		}
		$rs->Cerrar();
	}
		if ($ordenadores==0)
		{
		$cmd->texto="SELECT *, COUNT(*) AS numordenadores
			 FROM gruposordenadores
			 WHERE idgrupo=".$idgrupo;
		$rs=new Recordset; 
		$rs->Comando=&$cmd; 
		if ($rs->Abrir()){
			$rs->Primero(); 
			if (!$rs->EOF){
				$nombregrupoordenador=$rs->campos["nombregrupoordenador"];
				$ordenadores=$rs->campos["numordenadores"];
				$idaula=$rs->campos["idaula"];
			}
			$rs->Cerrar();
					}
		}
	//////////////////////////////////////
    $cmd->texto="SELECT DISTINCT aulas.*,count(*) as numordenadores
            	FROM aulas
                INNER JOIN ordenadores ON ordenadores.idaula=aulas.idaula
                WHERE aulas.idaula=".$idaula;  
				 
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$rs->Primero(); 
		if (!$rs->EOF){
			$urlfoto=$rs->campos["urlfoto"];
			$nombreaula=$rs->campos["nombreaula"];
		}
		$rs->Cerrar();
	}
?> 
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[5].'</br>'.$nombreaula?>&nbsp;</TD>
			<?php
					echo '<TD>'.$nombregrupoordenador.'</TD>
								<TD colspan=2 valign=top align=center rowspan=2>
					<IMG border=3 style="border-color:#63676b" src="../images/fotos/'.$urlfoto.'"><br>
									<center>&nbsp;'.$TbMsg[13].':&nbsp;'. $ordenadores.'</center>
								</TD>';

			?>
		</TR>
		<TABLE style="border: 1px solid #d4d0c8;" align="center"><TR><td align=center width=200 height=10 class=subcabeceras><?php echo $TbMsg[4]." - ".$numord; ?></TD></TR>
	</TABLE>
<?php
}

/**
 * @param object $cmd
 * @param int $idordenador
 */
function datos_sesiones($cmd, $idordenador)
{
    global $TbMsg;
    $os_color = ['Windows' => "blue", 'Linux' => "magenta", 'MacOS' => "orange"];
    $html = "";
    $ip = "";

    $cmd->texto = "SELECT ip FROM ordenadores WHERE idordenador = $idordenador";
    $rs = new Recordset;
    $rs->Comando=&$cmd;
    if ($rs->Abrir()){
        $rs->Primero();
        $ip = $rs->campos["ip"];
        $rs->Cerrar();
    }
    if ($ip) {
        foreach (file(LOG_FILE) as $line) {
            if (strstr($line, "ip=$ip,")) {
                $fields = preg_split("/[:,=]/", rtrim($line, ". \t\n\r\0\x0B"));
                $date_time = str_replace("T", " ", $fields[0]) . ":" . $fields[1] . ":" .
                    preg_replace("/\+.*$/", "", $fields[2]);
                $operation = trim($fields[3]);
                $username = $os_type = $os_version = "";
                switch ($operation) {
                    case "OGAgent started":
                        $operation = "Iniciar";
                        $os_type = $fields[14] ?? "";
                        $os_version = trim($fields[15] ?? "");
                        break;
                    case "OGAgent stopped":
                        $operation = "Apagar";
                        $os_type = $fields[14] ?? "";
                        $os_version = trim($fields[15] ?? "");
                        break;
                    case "User logged in":
                        $operation = "Entrar";
                        $username = $fields[7] ?? "";
                        $os_type = $fields[11] ?? "";
                        $os_version = trim($fields[12] ?? "");
                        break;
                    case "User logged out":
                        $operation = "Salir";
                        $username = $fields[7] ?? "-";
                        break;
                    default:
                        $operation = "ERROR";
                }
                $color = $os_color[$os_type] ?? "";
                $html .= <<<EOT
  <tr>
    <td>$date_time</td>
    <td>$operation</td>
    <td style="background-color: $color; color: white;">$os_version</td>
    <td>$username</td>
  </tr>
EOT;
            }
        }
        if (!empty($html)) {
            echo <<<EOT
<table class="tabla_datos" style="margin-left: auto; margin-right: auto;">
  <tr>
    <th colspan="5">${TbMsg["SECT_SESSIONS"]}</th>
  </tr>
  <tr>
    <th>${TbMsg["SESS_DATETIME"]}</th>
    <th>${TbMsg["SESS_OPERATION"]}</th>
    <th>${TbMsg["SESS_OPSYS"]}</th>
    <th>${TbMsg["SESS_USER"]}</th>
  </tr>
$html
</table>
EOT;
        } else {
            echo <<<EOT
<table class="tabla_datos" style="margin-left: auto; margin-right: auto;">
  <tr>
    <th>${TbMsg["SESS_NOSESSIONS"]}</th>
  </tr>
</table>
EOT;
        }
    }
}

