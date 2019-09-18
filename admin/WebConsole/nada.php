<?php
/**
 * @file    nada.php
 * @brief   Muestra el marco derecho "por defecto" cuando no se ejecuta un comando.
 * @version 1.1.1 - Se incluyen consejos del día la primera vez que se entra.
 * @date    2019-09-06
 */

include_once("./includes/ctrlacc.php");
include_once("./idiomas/php/".$idioma."/nada_".$idioma.".php");

// ################### Consejo del día ################# //
// Sólo lo muestro al entrar en la consola
$consejo = '';
if (basename($_SERVER['HTTP_REFERER']) == "frames.php") {
    // Elijo el consejo de hoy aleatoriamente.
    $numTip=rand(0,count($TipOfDay)-1);
    $tipMessage=$TipOfDay[$numTip];
    $tipImage=is_file("images/tipOfDay_$numTip.png") ? '<img src="images/tipOfDay_'.$numTip.'.png">' : '';

    $consejo = ' <div>'."\n".
               '    <p align=center class=cabeceras><img  border=0 nod="aulas-1" value="Sala Virtual" style="cursor:pointer" src="images/iconos/logocirculos.png" >&nbsp;&nbsp;'.$TbMsg["TIP"].'</p>'."\n".
               '    <div class="consejo">'."\n".
               '        <p class="subcabeceras help_menu">'.$tipMessage.'</p>'."\n".
               '        <div>'.$tipImage.'</div>'."\n".
               '    </div>'."\n".
               ' </div>'."\n";
}

// ##########################################################################################################
// ###############  PARA SABER QUE IP TIENE EL DISPOSITIVO QUE ESTA UTILIZANDO OPENGNSYS  ###################
// ##########################################################################################################
//Para saber la IP con Proxy o sin el

function getRemoteInfo () {
   $proxy="";
   if (isSet($_SERVER)) {
       if (isSet($_SERVER["HTTP_X_FORWARDED_FOR"])) {
           $IP = $_SERVER["HTTP_X_FORWARDED_FOR"];
           $proxy  = $_SERVER["REMOTE_ADDR"];
       } elseif (isSet($_SERVER["HTTP_CLIENT_IP"])) {
           $IP = $_SERVER["HTTP_CLIENT_IP"];
       } else {
           $IP = $_SERVER["REMOTE_ADDR"];
       }
   } else {
       if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
           $IP = getenv( 'HTTP_X_FORWARDED_FOR' );
           $proxy = getenv( 'REMOTE_ADDR' );
       } elseif ( getenv( 'HTTP_CLIENT_IP' ) ) {
           $IP = getenv( 'HTTP_CLIENT_IP' );
       } else {
           $IP = getenv( 'REMOTE_ADDR' );
       }
   }
   if (strstr($IP, ',')) {
       $ips = explode(',', $IP);
       $IP = $ips[0];
   }
   $RemoteInfo[0]=$IP;
   $RemoteInfo[1]=@GetHostByAddr($IP);
   $RemoteInfo[2]=$proxy;

 	return $RemoteInfo[0];
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////

// ##########################################################################################################
// ###############  PARA SABER QUE TIPO DISPOSITIVO ESTA UTILIZANDO OPENGNSYS  ##############################
// ##########################################################################################################
$device="";
$device = strtolower($_SERVER['HTTP_USER_AGENT']);
if(stripos($device,'iphone') == TRUE ){$device="iphone";$tipodevice="Iphone / Ipad";$ipreal=getRemoteInfo();$_SESSION["ipdevice"]=$ipreal;}
elseif  (stripos($device,'ipad') == TRUE) {$device="ipad";$tipodevice="Ipad / Iphone";$ipreal=getRemoteInfo();$_SESSION["ipdevice"]=$ipreal;}
elseif (stripos($device,'android') == TRUE) {$device="android";$tipodevice="Movil / Tablet";$ipreal=getRemoteInfo();$_SESSION["ipdevice"]=$ipreal;}
elseif (stripos($device,'linux') == TRUE) {$device="linux";$tipodevice="Linux";$ipreal=getRemoteInfo();$_SESSION["ipdevice"]=$ipreal;}
elseif (stripos($device,'macintosh') == TRUE) {$device="macintosh";$tipodevice="Macintosh";$ipreal=getRemoteInfo();$_SESSION["ipdevice"]=$ipreal;}
else{$device="0";$tipodevice="PC";}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////

// ##########################################################################################################
// ################################  PARA SABER QUE SISTEMA DEL DISPOSITIVO  ################################
// ##########################################################################################################
$sistem="";
$buscasistem=strtolower($_SERVER['HTTP_USER_AGENT']);
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	SISTEMAS WINDOWS //
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(stripos($buscasistem,'windows nt 10.0') == TRUE ){$sistem="Windows 10";} 
if(stripos($buscasistem,'windows nt 6.2') == TRUE ){$sistem="Windows 8";} 
if(stripos($buscasistem,'windows nt 6.1') == TRUE ){$sistem="Windows 7";}
if(stripos($buscasistem,'windows nt 6.0') == TRUE ){$sistem="Windows Vista/Server 2008";} 
if(stripos($buscasistem,'windows nt 5.2') == TRUE ){$sistem="Windows Server 2003";} 
if(stripos($buscasistem,'windows nt 5.1') == TRUE ){$sistem="Windows XP";} 
if(stripos($buscasistem,'windows nt 5.0') == TRUE ){$sistem="Windows 2000";} 

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	SISTEMAS APPLE //
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(stripos($buscasistem,'ipad') == TRUE ){$sistem="iOS";}
if(stripos($buscasistem,'iphone') == TRUE ){$sistem="iOS";}
if ($device == "macintosh" ){$sistem="Mac OSX";}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	SISTEMAS LINUX //
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(stripos($buscasistem,'ubuntu') == TRUE ){$sistem="Ubuntu";}
if(stripos($buscasistem,'red hat') == TRUE ){$sistem="Red Hat";}
if(stripos($buscasistem,'centos') == TRUE ){$sistem="CentOS";}
if(stripos($buscasistem,'suse') == TRUE ){$sistem="openSUSE";}
if(stripos($buscasistem,'mandriva') == TRUE ){$sistem="Mandriva";}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	SISTEMAS ANDROID //
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(stripos($buscasistem,'android') == TRUE ){$sistem="Android";}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ##########################################################################################################
// ##########################  PARA SABER QUE VERSION DEL SISTEMA DEL DISPOSITIVO  ##########################
// ##########################################################################################################
$buscaversistem=strtolower($_SERVER['HTTP_USER_AGENT']);

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	VERSION WINDOWS //
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(stripos($buscaversistem,'windows nt') == TRUE ){$versistem="-";}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	VERSION APPLE //
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(stripos($buscaversistem,'os x') == TRUE ){
$buscaversistemapple=$_SERVER['HTTP_USER_AGENT'];
$buscaversistemapple=str_replace("OS","OS:",$buscaversistemapple);
$buscaversistemapple=str_replace("like",":like",$buscaversistemapple);
$buscaversistemapple=explode(":",$buscaversistemapple);
$versistem=$buscaversistemapple[1];}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	VERSION LINUX //
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(stripos($buscaversistem,'linux') == TRUE ){$versistem="-";}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	VERSION ANDROID //
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(stripos($buscaversistem,'android') == TRUE ){
$buscaversistemandroid=str_replace(")",";",$buscaversistem);
$buscaversistemandroid=explode(";",$buscaversistemandroid);
$versistem=$buscaversistemandroid[2];
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ##########################################################################################################
// ##########################  PARA SABER QUE NAVEGADOR DEL SISTEMA DEL DISPOSITIVO  ########################
// ##########################################################################################################
$buscanav=strtolower($_SERVER['HTTP_USER_AGENT']);
if(stripos($buscanav,'firefox') == TRUE ){$nav="Firefox";}
if(stripos($buscanav,'safari') == TRUE ){$nav="Safari";}
if(stripos($buscanav,'msie') == TRUE ){$nav="Internet Explorer";}
if(stripos($buscanav,'chrome') == TRUE ){$nav="Google Chrome";}
if(stripos($buscanav,'opera') == TRUE ){$nav="Opera";}
if(stripos($buscanav,'qtembedded') == TRUE ){$nav="OpenGnsys Browser";}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ##########################################################################################################
// ##########################  PARA SABER VERSION DEL NAVEGADOR DEL DISPOSITIVO  ############################
// ##########################################################################################################
$buscavernav=explode("/", strtolower($_SERVER['HTTP_USER_AGENT']));
$vernav=end($buscavernav);

//////////////////////////////////////////////////////////////////////////////////////////////////////////////


if ($device == "ipad" || $device == "iphone" || $device == "android" )
{
$data = json_decode(@file_get_contents(__DIR__ . '/../doc/VERSION.json'));
if (empty($data->project)) {
    $version = "OpenGnsys";
} else {
    $version = @$data->project.' ' . @$data->version.' '
             . (isset($data->codename) ? '('.$data->codename.') ' : '') . @$data->release;
}
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
  <link rel="stylesheet" type="text/css" href="./estilos.css">
</head>
<body>

<table width="100%" border="0">
  <tr>
    <td colspan="3" align="center">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3" align="center"><SPAN align=center class=cabeceras><font size="4"><?php echo $TbMsg[0] ;?></font></SPAN></td>
  </tr>
  <tr>
    <td colspan="3" align="center"><SPAN align=center class=cabeceras><font size="4"><?php echo $version; ?></font></SPAN></td>
  </tr>
  <tr>
    <td colspan="3" align="center">&nbsp;</td>
  </tr>
  <tr>
    <td width="23%">&nbsp;</td>
    <td width="28%"><SPAN align=center class=subcabeceras><font size="3"><?php echo $TbMsg[1] ;?></font></SPAN></td>
    <td width="49%"><SPAN align=center class=sobrecabeceras><font size="3"><?php echo $_SESSION['ipdevice']; ?></font></SPAN></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><SPAN align=center class=subcabeceras><font size="3"><?php echo $TbMsg[2] ;?></font></SPAN></td>
    <td><SPAN align=center class=sobrecabeceras><font size="3"><?php echo $tipodevice; ?></font></SPAN></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><SPAN align=center class=subcabeceras><font size="3"><?php echo $TbMsg[3] ;?></font></SPAN></td>
    <td><SPAN align=center class=sobrecabeceras><font size="3"><?php echo $sistem; ?></font></SPAN></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><SPAN align=center class=subcabeceras><font size="3"><?php echo $TbMsg[4] ;?></font></SPAN></td>
    <td><SPAN align=center class=sobrecabeceras><font size="3"><?php echo $versistem; ?></font></SPAN></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><SPAN align=center class=subcabeceras><font size="3"><?php echo $TbMsg[5] ;?></font></SPAN></td>
    <td><SPAN align=center class=sobrecabeceras><font size="3"><?php echo $nav; ?></font></SPAN></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><SPAN align=center class=subcabeceras><font size="3"><?php echo $TbMsg[6] ;?></font></SPAN></td>
    <td><SPAN align=center class=sobrecabeceras><font size="3"><?php echo $vernav; ?></font></SPAN></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>


</body>
</html>

<?php } else { ?>

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
  <link rel="stylesheet" type="text/css" href="./estilos.css">
</head>
<body>
   <?php  echo $consejo; ?>
</body>
</html>

<?php } ?>

