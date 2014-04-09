<?php
// *************************************************************************************************************************************************
// Nombre del fichero: EliminarImagenCache.php
// Descripci????n : 
//              Implementaci????n?????? del comando "Eliminar Imagen Cache"
// date: 13-junio-2013
// Cambio: se incluye mensaje equipos sin configuracion. En la funcion tabla_configuracion incluye cabecera de la tabla.
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/comandos/eliminarimagencache_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
//________________________________________________________________________________________________________
include_once("./includes/capturaacciones.php");
$funcion=EjecutarScript;
$idc=$_SESSION["widcentro"];
$ipservidor=$_SERVER['SERVER_ADDR'];
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
        Header('Location: '.$pagerror.'?herror=2'); // Error de conexi??n con servidor B.D.
//___________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administraci??n web de aulas</TITLE>
<HEAD>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="./jscripts/EliminarImagenCache.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/eliminarimagencache_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
</HEAD>
<BODY>
<?php

switch($ambito){
                case $AMBITO_CENTROS :
                        $urlimg='../images/iconos/centros.gif';
                        $textambito=$TbMsg[0];
                        break;
                case $AMBITO_GRUPOSAULAS :
                        $urlimg='../images/iconos/carpeta.gif';
                        $textambito=$TbMsg[1];
                        break;
                case $AMBITO_AULAS :
                        $urlimg='../images/iconos/aula.gif';
                        $textambito=$TbMsg[2];//#agp
                                $cmd->texto="SELECT DISTINCT ordenadores.idrepositorio
                                FROM aulas
                                LEFT JOIN ordenadores ON ordenadores.idaula=aulas.idaula
                                WHERE aulas.idaula=$idambito";
                        $rs=new Recordset;
                        $rs->Comando=&$cmd; 
                        if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
                        $rs->Primero();
                        $idx=0;
                        while (!$rs->EOF){
                        $rs->Siguiente();
                        $idx++;     }
                        $cuentarepos=$idx; // Guarda contador
                        $rs->Cerrar();
					if ($cuentarepos==1){
					$cmd->texto="SELECT repositorios.ip
					FROM repositorios
					INNER JOIN ordenadores ON ordenadores.idrepositorio=repositorios.idrepositorio
					AND ordenadores.idaula='$idambito'
					GROUP BY ip";
					$rs=new Recordset;
					$rs->Comando=&$cmd; 
					if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
					$rs->Primero();
					$iprepositorioord=$rs->campos["ip"];
					if ( $iprepositorioord == $ipservidor ){$cuentarepos=1;}else{$cuentarepos=2;}
					$rs->Cerrar();
										}//#agp 
                        break;

                case $AMBITO_GRUPOSORDENADORES :
                        $urlimg='../images/iconos/carpeta.gif';
                        $textambito=$TbMsg[3];//#agp
                                $cmd->texto="SELECT DISTINCT ordenadores.idrepositorio
                                 FROM aulas
                                 LEFT JOIN ordenadores ON ordenadores.idaula=aulas.idaula
                                 WHERE aulas.idaula=ordenadores.idaula
                                 AND aulas.idcentro='$idc'
                                 AND ordenadores.grupoid=".$idambito;
                        $rs=new Recordset;
                        $rs->Comando=&$cmd; 
                        if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
                        $rs->Primero();
                        $idx=0;
                        while (!$rs->EOF){
                        $rs->Siguiente();
                        $idx++;     }
                        $cuentarepos=$idx; // Guarda contador
                        $rs->Cerrar();
					if ($cuentarepos==1){
					$cmd->texto="SELECT repositorios.ip
					FROM repositorios
					INNER JOIN ordenadores ON ordenadores.idrepositorio=repositorios.idrepositorio
					AND ordenadores.grupoid='$idambito'
					GROUP BY ip";
					$rs=new Recordset;
					$rs->Comando=&$cmd; 
					if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
					$rs->Primero();
					$iprepositorioord=$rs->campos["ip"];
					if ( $iprepositorioord == $ipservidor ){$cuentarepos=1;}else{$cuentarepos=2;}
					$rs->Cerrar();
										}//#agp 
                        break;

                case $AMBITO_ORDENADORES :
                        $urlimg='../images/iconos/ordenador.gif';
                        $textambito=$TbMsg[4];//#agp 
                                $cmd->texto="SELECT repositorios.ip
                                 FROM repositorios
                                 INNER JOIN ordenadores ON ordenadores.idrepositorio=repositorios.idrepositorio
                                 AND ordenadores.idordenador=$idambito";//#agp
                        $rs=new Recordset;
                        $rs->Comando=&$cmd; 
                        if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
                        $rs->Primero();
			    $iprepositorioord=$rs->campos["ip"];
			    if ( $iprepositorioord == $ipservidor ){$cuentarepos=1;}else{$cuentarepos=2;}
                        $rs->Cerrar();//#agp 
                        break;
        }

        echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
        echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[6].': '.$textambito.','.$nombreambito.'</U></span>&nbsp;&nbsp;</span></p>';
?>
<!-- //#agp-->
<?php 
    // Mensaje aviso limitacion version si hay dos repositorios
    if ($cuentarepos >1){ ?>
         <TABLE  id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
       		<TH align=center >&nbsp;
		<? if ($ambito==16){
			echo $TbMsg[17]."</br>".$nombreambito.$TbMsg[16]."</br>".$TbMsg[18];
		}else{ 
			echo $TbMsg[17]."</br>".$nombreambito.$TbMsg[15]."</br>".$TbMsg[18]; }
		?>&nbsp;</TH> </TR>

        </TABLE>  
<?php }?>
<!-- //#agp-->

        <P align=center>
        <SPAN align=center class=subcabeceras><? echo $TbMsg[7] ?></SPAN>
	</P>

<form  align=center name="fdatos"> 
     <?php echo tabla_configuraciones($cmd,$idambito); ?>
</form>
<P></P>
<!-- //#agp-->

<?php
        //________________________________________________________________________________________________________
        include_once("./includes/formularioacciones.php");
        //________________________________________________________________________________________________________
        include_once("./includes/opcionesacciones.php");
        //________________________________________________________________________________________________________
?>
</BODY>
</HTML>

<?php
/**************************************************************************************************************************************************
        Recupera los datos de un ordenador
                Parametros: 
                - cmd: Una comando ya operativo (con conexi??nabierta)  
                - ido: El identificador del ordenador
________________________________________________________________________________________________________*/
function TomaPropiedades($cmd,$idambito)
{
        
        $rs=new Recordset; 
        $cmd->texto="SELECT     COUNT(ordenadores.idordenador) AS numordenadores, aulas.* , 
                                GROUP_CONCAT(DISTINCT CAST( ordenadores.idmenu AS char( 11 ) )  
                                ORDER BY ordenadores.idmenu SEPARATOR ',' ) AS idmenus,
                                GROUP_CONCAT(DISTINCT CAST( ordenadores.idrepositorio AS char( 11 ) )  
                                ORDER BY ordenadores.idrepositorio SEPARATOR ',' ) AS idrepositorios,
                                GROUP_CONCAT(DISTINCT CAST( ordenadores.idperfilhard AS char( 11 ) )  
                                ORDER BY ordenadores.idperfilhard SEPARATOR ',' ) AS idperfileshard,
                                GROUP_CONCAT(DISTINCT CAST( ordenadores.cache AS char( 11 ) )  
                                ORDER BY ordenadores.cache SEPARATOR ',' ) AS caches,
                                GROUP_CONCAT(DISTINCT CAST( ordenadores.idproautoexec AS char( 11 ) )  
                                ORDER BY ordenadores.idproautoexec SEPARATOR ',' ) AS idprocedimientos
                        FROM aulas
                        LEFT OUTER JOIN ordenadores ON ordenadores.idaula = aulas.idaula
                        WHERE aulas.idaula =".$idambito." 
                        GROUP BY aulas.idaula";

        $rs->Comando=&$cmd; 
        if (!$rs->Abrir()) return(false); // Error al abrir recordset
        if (!$rs->EOF){
                $idaula=$rs->campos["idaula"];
                $nombreaula=$rs->campos["nombreaula"];
                $urlfoto=$rs->campos["urlfoto"];
                if ($urlfoto=="" ) $urlfoto="aula.jpg";
                $cagnon=$rs->campos["cagnon"];
                $pizarra=$rs->campos["pizarra"];
                $ubicacion=$rs->campos["ubicacion"];
                $comentarios=$rs->campos["comentarios"];
                $puestos=$rs->campos["puestos"];
                $horaresevini=$rs->campos["horaresevini"];
                $horaresevfin=$rs->campos["horaresevfin"];
                $grupoid=$rs->campos["grupoid"];
                $modomul=$rs->campos["modomul"];
                $ipmul=$rs->campos["ipmul"];
                $pormul=$rs->campos["pormul"];
                $velmul=$rs->campos["velmul"];
#################### ADV                
                $router=$rs->campos["router"];
                $netmask=$rs->campos["netmask"];
                $modp2p=$rs->campos["modp2p"];
                $timep2p=$rs->campos["timep2p"];
###################### ADV
###################### UHU
                $validacion=$rs->campos["validacion"];
                $paginalogin=$rs->campos["paginalogin"];
                $paginavalidacion=$rs->campos["paginavalidacion"];
###################### UHU

                $ordenadores=$rs->campos["numordenadores"];
                $idmenu=$rs->campos["idmenus"];
                if(count(split(",",$idmenu))>1) $idmenu=0;              
                $idrepositorio=$rs->campos["idrepositorios"];
                if(count(split(",",$idrepositorio))>1) $idrepositorio=0;                
                $idperfilhard=$rs->campos["idperfileshard"];            
                if(count(split(",",$idperfilhard))>1) $idperfilhard=0;          
                $cache=$rs->campos["caches"];           
                if(count(split(",",$cache))>1) $cache=0;        
                $idmenu=$rs->campos["idmenus"];
                if(count(split(",",$idmenu))>1) $idmenu=0;              
                $idprocedimiento=$rs->campos["idprocedimientos"];
                if(count(split(",",$idprocedimiento))>1) $idprocedimiento=0;    
        
                $gidmenu=$idmenu;
                $gidprocedimiento=$idprocedimiento;
                $gidrepositorio=$idrepositorio;
                $gidperfilhard=$idperfilhard;
                $gcache=$cache; 
        
                $rs->Cerrar();
                
                return(true);
        }
        return(false);
}

/*________________________________________________________________________________________________________
        Crea la tabla de configuraciones y perfiles a crear
________________________________________________________________________________________________________*/

function tabla_configuraciones($cmd,$idambito){

        global $TbMsg;
		global $cuentarepos;
        global $idc;
        global $ambito;
        global $idambito;
        global $nombreambito;

        global $AMBITO_CENTROS;
        global $AMBITO_GRUPOSAULAS;
        global $AMBITO_AULAS;
        global $AMBITO_GRUPOSORDENADORES;
        global $AMBITO_ORDENADORES;

switch($ambito){
                case $AMBITO_CENTROS :
                        $urlimg='../images/iconos/centros.gif';
                        //echo "ambito - ".$ambito."<br>";
                        //echo "idcentro - ".$idc;
                        break;

                case $AMBITO_GRUPOSAULAS :

        $cmd->texto="SELECT * FROM grupos WHERE nombregrupo='$nombreambito' AND idcentro='$idc'";
        $rs=new Recordset;
        $rs->Comando=&$cmd; 
        if (!$rs->Abrir()) return(true); // Error al abrir recordset
        $rs->Primero(); 
        if (!$rs->EOF){
                $identificadorgrupo=$rs->campos["idgrupo"];
        }
        $rs->Cerrar();

                        $cmd->texto="SELECT * FROM aulas,grupos
                                        WHERE grupos.nombregrupo='$nombreambito'
                                        AND aulas.idcentro='$idc'
                                        AND aulas.grupoid='$identificadorgrupo'
                                        AND aulas.grupoid=grupos.idgrupo";


                        break;

                case $AMBITO_AULAS :
                        $cmd->texto="SELECT * FROM ordenadores,aulas,ordenadores_particiones 
                                        WHERE ordenadores_particiones.idordenador=ordenadores.idordenador 
                                        AND ordenadores.idaula=aulas.idaula
                                        AND aulas.nombreaula='$nombreambito'
                                   AND aulas.idcentro='$idc'
                                        AND ordenadores_particiones.numpar=4  
                                        GROUP BY ordenadores_particiones.cache";

                        break;

                case $AMBITO_GRUPOSORDENADORES :
                        $cmd->texto="SELECT * FROM ordenadores,aulas,ordenadores_particiones,gruposordenadores 
                                        WHERE ordenadores_particiones.idordenador=ordenadores.idordenador 
                                        AND ordenadores.idaula=aulas.idaula
                                   AND gruposordenadores.idaula=aulas.idaula
                                   AND aulas.idcentro='$idc'
                                        AND ordenadores_particiones.numpar=4  
                                        AND ordenadores.grupoid='$idambito'
                                        GROUP BY ordenadores_particiones.cache";

                        break;
                case $AMBITO_ORDENADORES :
                        $cmd->texto="SELECT * FROM ordenadores,ordenadores_particiones 
                                        WHERE ordenadores_particiones.idordenador=ordenadores.idordenador 
                                        AND ordenadores.nombreordenador='$nombreambito'
                                        AND ordenadores_particiones.numpar=4  
                                        GROUP BY ordenadores_particiones.cache";
                        break;
        }

        $tablaHtml="";


        $rs->Comando=&$cmd;  
        $rs=new Recordset; 
        $rs->Comando=&$cmd; 
        if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
        $rs->Primero(); 

        while (!$rs->EOF){

                                $cache=$rs->campos["cache"];
                                $idordenador=$rs->campos["idordenador"];
                                $ima=split(",",$cache);
                                
                                for ($x=0;$x<count($ima); $x++)
                                {
                                    if(ereg(".img",$ima[$x])  ) //si contiene .img son ficheros de imagen
                                        {
                                                if (ereg(".img.sum",$ima[$x]) || ereg(".img.torrent",$ima[$x])  )//Si el nombre contiene .img.sum o img.torrent
                                                  {}else{$esdir[]="f";
								if (ereg(".img.diff",$ima[$x]))
									{
									$ima[$x] = str_replace(".img.diff", "", $ima[$x]); //quitar todos los .img
									$ima[$x]=trim($ima[$x]);
									$nombreimagenes[]=$ima[$x];
									}else{
										$ima[$x] = str_replace(".img", "", $ima[$x]); //quitar todos los .img
										$ima[$x]=trim($ima[$x]);
										$nombreimagenes[]=$ima[$x];
										
										}
                                                        }
                                         }elseif (ereg("MB",$ima[$x]))
							{}else{	// Es un directorio
								$ima[$x]=trim($ima[$x]);
								$nombreimagenes[]=$ima[$x];
								$esdir[]="d";
								}
                                 }
        
                                 $rs->Siguiente();
                        }
                        $rs->Cerrar();

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
                                        $sin_duplicados=array_unique($nombreimagenes);
                                        $contar=1;
					if (empty($sin_duplicados)) {
                                                // Equipo sin configuracion en base de datos.
                                                $inicioTabla='<table id="tabla_conf" width="95%" class="tabla_listados_sin" align="center" border="0" cellpadding="0" cellspacing="1">'.chr(13);
                                                $inicioTabla.='<tr><th align="center" >'.$TbMsg["CONFIG_NOCONFIG"].'</th><tr>'.chr(13);
					}else{
                                                // Equipo con configuracion en BD
                                                // Incluimos primera linea de la tabla.
                                                $inicioTabla='<TABLE  id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>'.chr(13);
                                                $inicioTabla.='         <TR>'.chr(13);
                                                $inicioTabla.='         <TH align=center>&nbsp;'.$TbMsg[11].'&nbsp;</TH>'.chr(13);
                                                $inicioTabla.='         <TH align=center>&nbsp;'.$TbMsg[19].'&nbsp;</TH>'.chr(13);
                                                $inicioTabla.='         <TH align=center>&nbsp;'.$TbMsg[12].'&nbsp;</TH>'.chr(13);
                                                $inicioTabla.='         <TH align=center>&nbsp;'.$TbMsg[10].'&nbsp;</TH>'.chr(13);
                                                if ($cuentarepos==1)
                                                        $inicioTabla.='         <TH align=center>&nbsp;'.$TbMsg[13].'&nbsp;</TH>'.chr(13);



                                        }

					echo $inicioTabla;
					$numdir=0;
					     
                                        foreach($sin_duplicados as $value) //imprimimos $sin_duplicados
                                        {
						if (empty($value)){
                                                // Equipo sin imagenes en la cache.
                                                $inicioTabla='<table id="tabla_conf" width="25%" class="tabla_listados_sin" align="center" border="0" cellpadding="0" cellspacing="1">'.chr(13);
                                                $inicioTabla.='<tr><th align="center" >NO '.$TbMsg["7"].'</th><tr>'.chr(13);
							echo $inicioTabla;
						}else{
							$nombrefichero=$value.'.img';
							$tamanofich=exec("du -h /opt/opengnsys/images/$nombrefichero");
							if ($tamanofich==""){$tamanofich=$TbMsg[14];}
							$tamanofich=split("/",$tamanofich);     
							$todo=".*";
							if ($esdir[$numdir] == "d"){
								$ruta[]='rm%20-r%20/opt/opengnsys/cache/opt/opengnsys/images/'.$value;
							}else{
								$ruta[]='rm%20-r%20/opt/opengnsys/cache/opt/opengnsys/images/'.$value.$todo;
							}
							echo '<TR>'.chr(13);
							echo '<TD align=center>&nbsp;'.$contar.'&nbsp;</TD>'.chr(13);
							if ($esdir[$numdir]=="d"){echo '<TD align=center><font color=blue>&nbsp;D&nbsp;</font></TD>'.chr(13);}else{echo '<TD align=center>&nbsp;F&nbsp;</TD>'.chr(13);}
							echo '<TD align=center ><input type="radio" name="codigo"  value='.$ruta[$numdir].'></TD>'.chr(13);
							if ($esdir[$numdir]=="d"){echo '<TD align=center><font color=blue>&nbsp;'.$value.'&nbsp;</font></TD>'.chr(13);}else{echo '<TD align=center>&nbsp;'.$value.'&nbsp;</TD>'.chr(13);}
							if ($cuentarepos==1){echo '<TD align=center>&nbsp;'.$tamanofich[0].'</TD>'.chr(13);}
							echo '</TR>'.chr(13);
							$contar++;$numdir++;
							}
						}
						echo "</table>".chr(13);
							

                        return($tablaHtml);
}

?>
