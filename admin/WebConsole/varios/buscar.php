<?php
// *********************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: Ramón M. Gómez, ETSII - Universidad de Sevilla
// Fecha Creación: Noviembre 2011
// Nombre del fichero: buscar.php
// Descripción : Buscador de equipos.
// *********************************************************************

include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/buscar_".$idioma.".php");


# Inicializar variables.
$criterio="";
$valor="";

# Tomar varlores de sesión.
if (isset($_POST["criterio"])) $criterio=htmlspecialchars($_POST["criterio"]);
if (isset($_POST["valor"])) $valor=htmlspecialchars($_POST["valor"]); 
if (!empty ($valor) or $criterio == "duplic" or $criterio == "profe") {
    $cmd=CreaComando($cadenaconexion);
    if ($cmd) {
	$rs=new Recordset; 
	switch ($criterio) {
		case "nombre":	// Buscar por nombre de equipo.
			$cmd->texto="SELECT grupos.nombregrupo AS grupo,
					    aulas.nombreaula AS aula,
					    ordenadores.idordenador AS id,
					    ordenadores.nombreordenador AS nombre,
					    ordenadores.ip AS ip,
					    ordenadores.mac AS mac
					FROM ordenadores
					JOIN aulas ON aulas.idaula=ordenadores.idaula
				   LEFT JOIN grupos ON grupos.idgrupo=aulas.grupoid
					WHERE ordenadores.nombreordenador='$valor'
					  AND aulas.idcentro='$idcentro'
					ORDER BY ordenadores.nombreordenador";
			break;
		case "ip":	// Buscar por IP.
			$cmd->texto="SELECT grupos.nombregrupo AS grupo,
					    aulas.nombreaula AS aula,
					    ordenadores.idordenador AS id,
					    ordenadores.nombreordenador AS nombre,
					    ordenadores.ip AS ip,
					    ordenadores.mac AS mac
					FROM ordenadores
					JOIN aulas ON aulas.idaula=ordenadores.idaula
				   LEFT JOIN grupos ON grupos.idgrupo=aulas.grupoid
					WHERE ordenadores.ip='$valor'
					  AND aulas.idcentro='$idcentro'
					ORDER BY ordenadores.nombreordenador";
			break;
		case "mac":	// Buscar por dirección MAC (Ethernet).
			$cmd->texto="SELECT grupos.nombregrupo AS grupo,
					    aulas.nombreaula AS aula,
					    ordenadores.idordenador AS id,
					    ordenadores.nombreordenador AS nombre,
					    ordenadores.ip AS ip,
					    ordenadores.mac AS mac
					FROM ordenadores
					JOIN aulas ON aulas.idaula=ordenadores.idaula
				   LEFT JOIN grupos ON grupos.idgrupo=aulas.grupoid
					WHERE ordenadores.mac='".strtoupper($valor)."'
					  AND aulas.idcentro='$idcentro'
					ORDER BY ordenadores.nombreordenador";
			break;
		case "duplic":	// Mostrar duplicados.
			$cmd->texto="SELECT grupos.nombregrupo AS grupo,
					    aulas.nombreaula AS aula,
					    ordenadores.idordenador AS id,
					    ordenadores.nombreordenador AS nombre,
					    ordenadores.ip AS ip,
					    ordenadores.mac AS mac
					FROM ordenadores
					JOIN aulas ON aulas.idaula=ordenadores.idaula
				   LEFT JOIN grupos ON grupos.idgrupo=aulas.grupoid
					WHERE nombreordenador IN
						(SELECT nombreordenador
						   FROM ordenadores
						  GROUP BY nombreordenador
						 HAVING count(*) > 1)
					   OR ip in
						(SELECT ip FROM ordenadores
						  GROUP BY ip HAVING count(*) > 1)
					   OR mac in
						(SELECT mac FROM ordenadores
						  GROUP BY mac HAVING count(*) > 1)
					  AND aulas.idcentro='$idcentro'";
			break;
		case "profe":	// Mostrar ordenadores de profesor.
			$cmd->texto="SELECT grupos.nombregrupo AS grupo,
					    aulas.nombreaula AS aula,
					    ordenadores.idordenador AS id,
					    ordenadores.nombreordenador AS nombre,
					    ordenadores.ip AS ip,
					    ordenadores.mac AS mac
					FROM ordenadores
					JOIN aulas ON aulas.idaula=ordenadores.idaula
				   LEFT JOIN grupos ON grupos.idgrupo=aulas.grupoid
					WHERE aulas.idordprofesor=ordenadores.idordenador
					  AND aulas.idcentro='$idcentro'
					ORDER BY aulas.nombreaula";
			break;
	}
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()) {
		while (!$rs->EOF) {
			if (empty ($rs->campos["grupo"])) {
				$aula[]=$rs->campos["aula"];
			} else {
				$aula[]=$rs->campos["grupo"]." / ".$rs->campos["aula"];
			}
			$id[]=$rs->campos["id"];
			$nombre[]=$rs->campos["nombre"];
			$ip[]=$rs->campos["ip"];
			$mac[]=$rs->campos["mac"];
			$rs->Siguiente();
		}
	}
    }
}
//_________________________________
?>
<html>
<title>Administración web de aulas</title>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<link rel="stylesheet" type="text/css" href="../estilos.css">
	<script languaje="javascript">
//_________________________________
function confirmar(){
	if (comprobar_datos())
		document.fdatos.submit();
}
//_________________________________
function comprobar_datos(){
	if (document.fdatos.valor.value=="" && document.fdatos.criterio.value!="duplic" && document.fdatos.criterio.value!="profe") {
		alert("<?php echo $TbMsg["SEARCH_NOVALUE"] ?>");
		document.fdatos.valor.focus();
		return(false)
	}
	return(true)
}
//_________________________________
function PulsaEnter(oEvento){ 
    var iAscii; 
    if (oEvento.keyCode) 
        iAscii = oEvento.keyCode; 
    else{
	if (oEvento.which) 
		iAscii = oEvento.which; 
	else 
		return false; 
	}
    if (iAscii == 13)  confirmar();
	return true; 
} 
//_________________________________
	</script>
</head>
<body>
<p align="center"><u><span class="cabeceras"><?php echo $TbMsg["SEARCH_TITLE"] ?></span></u></p>

<?php
if (!empty ($valor) or $criterio == "duplic" or $criterio == "profe") {
	if (empty ($aula)) {
		echo '<p class="subcabeceras" align="center">'.$TbMsg["SEARCH_NOMATCHES"].'</p>';
	} else {
?>
<div align="center" style="margin:20;">
<table class="tabla_listados">
  <caption><?php echo $TbMsg["SEARCH_RESULTS"];?></caption>
  <tr>
    <th colspan="2"><?php echo $TbMsg["SEARCH_LAB"];?></th>
    <th><?php echo $TbMsg["SEARCH_NAME"];?></th>
    <th><?php echo $TbMsg["SEARCH_IP"];?></th>
    <th><?php echo $TbMsg["SEARCH_MAC"];?></th>
  </tr>
<?php
		for ($i=0; !empty($aula[$i]); $i++) {
			echo "  <tr>\n    <td><img src=\"../images/iconos/ordenador.gif\" alt=\"PC\"></td>\n";
			echo "    <td>".$aula[$i]."</td>\n";
			echo "    <td><a href=\"../propiedades/propiedades_ordenadores.php?opcion=2&identificador=".$id[$i]."\">".$nombre[$i]."</a></td>\n";
			echo "    <td>".$ip[$i]."</td>\n";
			echo "    <td>".$mac[$i]."</td>\n  </tr>\n";
		}
	}
?>
</table>
</div>
<hr width="50%">
<?php } ?>

<div align="center" style="margin:20;">
	<form action="#" class="formulariodatos" name="fdatos" method="post">
		<?php echo $TbMsg["SEARCH_CRITERIA"] ?>:
		<select name="criterio" id="criterio" onchange="if (document.fdatos.criterio.value=='duplic' || document.fdatos.criterio.value=='profe') document.fdatos.valor.disabled=true; else document.fdatos.valor.disabled=false">
			<option value="nombre"> <?php echo $TbMsg["SEARCH_NAME"] ?> </option>
			<option value="ip"> <?php echo $TbMsg["SEARCH_IP"] ?> </option>
			<option value="mac"> <?php echo $TbMsg["SEARCH_MAC"] ?> </option>
			<option value="duplic"> <?php echo $TbMsg["SEARCH_DUPLICATES"] ?> </option>
			<option value="profe"> <?php echo $TbMsg["SEARCH_PROFESSOR"] ?> </option>
		</select>
		<input type="text" name="valor" id="valor" size="20" />
		<div align="center">
			<img onclick="confirmar()" src="../images/botonok.png" style="margin:20;cursor: hand" />
		</div>
	</form>
</div>
</body>
</html>

