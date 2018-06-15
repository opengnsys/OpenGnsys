<?php
/*========================================================================================================
	This class generates HTML tables for selecting dates (English version)

	Class attributes:

		class: Class [CSS] of HTML table that will be generated.
		onmouseover: The Javascript function that will be run when generating the event.
		onmouseout: Javascript function that will be run when generating the event.
		onclick: Javascript function that will be run when clicking over the object.

=========================================================================================================*/
class Calendario{
	var $aula;
	var $horaresevini;
	var $horaresevfin;
	var $clase ;
	var $onmouseover;
	var $onmouseout;
	var $onclick;

	var $desplazamiento_dias=6; // Fine tunning depending on the algorithm start year
	var $nombre_mes=array();
	var $nombre_dia=array();
	var $numero_annos=array();
	var $dias_meses=array();

	function __construct($pclase="", $ponmouseover="sobre(this)", $ponmouseout="fuera(this)", $ponclick="clic(this)"){ //Constructor
		$this->clase=$pclase;
		$this->onmouseover=$ponmouseover;
		$this->onmouseout=$ponmouseout;
		$this->onclick=$ponclick;

		$this->nombre_mes[1]=array ("January",0x0001);
		$this->nombre_mes[2]=array ("February",0x0002);
		$this->nombre_mes[3]=array ("March",0x0004);
		$this->nombre_mes[4]=array ("April",0x0008);
		$this->nombre_mes[5]=array ("May",0x0010);
		$this->nombre_mes[6]=array ("June",0x0020);
		$this->nombre_mes[7]=array ("July",0x0040);
		$this->nombre_mes[8]=array ("August",0x0080);
		$this->nombre_mes[9]=array ("September",0x0100);
		$this->nombre_mes[10]=array ("October",0x0200);
		$this->nombre_mes[11]=array ("November",0x0400);
		$this->nombre_mes[12]=array ("December",0x0800);


		$this->numero_annos[1]=array ("2004",0x01); // size 1 bytes
		$this->numero_annos[2]=array ("2005",0x02);
		$this->numero_annos[3]=array ("2006",0x04);
		$this->numero_annos[4]=array ("2007",0x08);
		$this->numero_annos[5]=array ("2008",0x10);
		$this->numero_annos[6]=array ("2009",0x20);
		$this->numero_annos[7]=array ("2010",0x40);
		$this->numero_annos[8]=array ("2011",0x80);

		$this->dias_meses[1]=31;
		$this->dias_meses[2]=28;
		$this->dias_meses[3]=31;
		$this->dias_meses[4]=30;
		$this->dias_meses[5]=31;
		$this->dias_meses[6]=30;
		$this->dias_meses[7]=31;
		$this->dias_meses[8]=31;
		$this->dias_meses[9]=30;
		$this->dias_meses[10]=31;
		$this->dias_meses[11]=30;
		$this->dias_meses[12]=31;

		$this->nombre_dia[1]=array ("Mo",0x01); // size 1 bytes
		$this->nombre_dia[2]=array ("Tu",0x02);
		$this->nombre_dia[3]=array ("We",0x04);
		$this->nombre_dia[4]=array ("Th",0x08);
		$this->nombre_dia[5]=array ("Fr",0x10);
		$this->nombre_dia[6]=array ("Sa",0x20);
		$this->nombre_dia[7]=array ("Su",0x40);
	}
/*________________________________________________________________________________________________________
		This function returns a string with the HTML code of year and month schedule chosen
		and they are class properties.
________________________________________________________________________________________________________*/
	function MesAnno($mes,$anno,$CntMes){
		$fecha="1/".$mes."/".$anno;
		$ds=$this->_DiaSemana($fecha);
		if ($ds==0) $ds=7;

		$swbi=0; // add for leap year
		if ($this->bisiesto($anno) && $mes==2)	$swbi=1;

 		$HTML_calendario='<TABLE  border=1 cellspacing=0 cellpadding=1 id="tabla_mesanno" class="'.$this->clase.'">'.chr(13);
		$HTML_calendario.='<TR>'.chr(13);
		$HTML_calendario.='<TH colspan=7 id="'.$mes.'/'.$anno.'" value="'.$this->aula.'" style="cursor:hand" onclick="TH_'.$this->onclick.'">'.$this->nombre_mes[$mes][0].'</TH></TR>'.chr(13); // Month name
		$HTML_calendario.='<TR>'.chr(13);
		for ($i=1;$i<8;$i++)
			$HTML_calendario.='<TH>'.$this->nombre_dia[$i][0].'</TH>'.chr(13); // Days of the week
		$HTML_calendario.='</TR><TR>'.chr(13);
		for ($i=1;$i<$ds;$i++)
			$HTML_calendario.='<TD>&nbsp;</TD>'.chr(13); // Filling firsts days of the week
		$sm=$ds; // Control leap of week
		for ($i=1;$i<=$this->dias_meses[$mes]+$swbi;$i++){
			$HTML_calendario.='<TD align=center ';
			if(isset($CntMes[$i])){
				if($CntMes[$i]==1){
					$HTML_calendario.=' style="COLOR:#eeeeee;BACKGROUND-COLOR: #cc3366;"';
					$HTML_calendario.=' id="'.$i.'/'.$mes.'/'.$anno.'" value="'.$this->aula.'" style="cursor:hand" onmouseover="'.$this->onmouseover.'" onmouseout="'.$this->onmouseout.'" onclick="'.$this->onclick.'"';
				}
			}
			$HTML_calendario.='>'.$i.'</TD>'.chr(13);
			if ($sm%7==0){
				$HTML_calendario.='</TR><TR>'.chr(13);
				$sm=0;
			}
			$sm++;
		}
		$HTML_calendario.='</TR></TABLE>'.chr(13);
		return($HTML_calendario);
	}
/*________________________________________________________________________________________________________
		This function returns a string with the HTML code of year and month schedule chosen
		and they are class properties.
________________________________________________________________________________________________________*/
	function JMesAnno($mes,$anno,$JDif,$TBfechas,$sumahoras){
		$fecha="1/".$mes."/".$anno;
		$Jdpl=$this->juliana($fecha)-$JDif; // Calculates start point for julian index
		$ds=$this->_DiaSemana($fecha);
		if ($ds==0) $ds=7;
		$paso=2; // Ratio of color intensity
		$swbi=0; // Add for leap year
		if ($this->bisiesto($anno) && $mes==2)	$swbi=1;
 		$HTML_calendario='<TABLE  border=1 cellspacing=0 cellpadding=1 id="tabla_mesanno" class="'.$this->clase.'">'.chr(13);
		$HTML_calendario.='<TR>'.chr(13);
		$HTML_calendario.='<TH colspan=7 id="'.$mes.'/'.$anno.'"  style="cursor:hand" onclick="TH_'.$this->onclick.'">'.$this->nombre_mes[(int)$mes][0].'</TH></TR>'.chr(13); // Month name
		$HTML_calendario.='<TR>'.chr(13);
		for ($i=1;$i<8;$i++)
			$HTML_calendario.='<TH>'.$this->nombre_dia[$i][0].'</TH>'.chr(13); // Days of week
		$HTML_calendario.='</TR><TR>'.chr(13);
		for ($i=1;$i<$ds;$i++)
			$HTML_calendario.='<TD>&nbsp;</TD>'.chr(13); // Filling firsts days of the week
		$sm=$ds; // Control leap of week
		for ($i=1;$i<=$this->dias_meses[(int)$mes]+$swbi;$i++){
			$HTML_calendario.='<TD align=center ';
			if(isset($TBfechas[$Jdpl])){
				if($TBfechas[$Jdpl]>0){
					$xpor=$TBfechas[$Jdpl]*100/$sumahoras;
					$itcr=255;
					$itc=240-($xpor*$paso);
					if($xpor>=50)
						$colordia="#FFFFFF";
					else
						$colordia="#000000";

					$bgcolordia=sprintf('#%02x%02x%02x',$itcr,$itc,$itc);
					$HTML_calendario.=' style="COLOR:'.$colordia.';BACKGROUND-COLOR: '.$bgcolordia.';"';
					$HTML_calendario.=' id="'.$i.'/'.$mes.'/'.$anno.'" value="'.$this->aula.'" style="cursor:hand" onmouseover="'.$this->onmouseover.'" onmouseout="'.$this->onmouseout.'" onclick="'.$this->onclick.'"';
				}
			}
			$HTML_calendario.='>'.$i.'</TD>'.chr(13);
			if ($sm%7==0){
				$HTML_calendario.='</TR><TR>'.chr(13);
				$sm=0;
			}
			$sm++;
			$Jdpl++;
		}
		$HTML_calendario.='</TR></TABLE>'.chr(13);
		return($HTML_calendario);
	}

/*________________________________________________________________________________________________________
		This function returns day of the week number:
			0=sunday 1=monday, 2=tuesday ... 6=saturday

		Input parameter:
			A string with date format dd/mm/yyyy.
________________________________________________________________________________________________________*/
	function _DiaSemana($fecha){
		list($dia,$mes,$anno)=explode('[/.-]',$fecha);
		$cont=0;
		for ($i=1900;$i<$anno;$i++){
			if ($this->bisiesto($i)) $dias_anuales=366; else	$dias_anuales=365;
			$cont+=$dias_anuales;
		}
		for ($i=1;$i<$mes;$i++){
			if ($i!=2)
				$cont+=$this->dias_meses[$i];
			else{
				if ($this->bisiesto($anno))
					$cont+=29;
				else
					$cont+=28;
			}
		}
		$cont+=$dia+$this->desplazamiento_dias;
		return($cont%7);
	}
//________________________________________________________________________________________________________
//		These function returns true if last year was a leap-year or false if it was not
//
//		Input parameter:
//			A number representing 1 year
//________________________________________________________________________________________________________
function bisiesto($anob){
		if ($anob%4==0) return(true); else return(false);
	}
//________________________________________________________________________________________________________
//		This function returns a string with the HTML code of the lab reservations times.
//________________________________________________________________________________________________________
function HorasDias($CntDia,&$porcenhoras){
	$HTML_calendario="";
	$sw=0;
	$conthoras=0; // Reservations minutes and hours counter
	$maxcolumnas=8;
	$tbampm[0]="a.m.";
	$tbampm[1]="p.m.";

	$HTML_calendario.='<TABLE   border=0 cellspacing=0 cellpadding=0  id="tabla_horas" class="'.$this->clase.'">'.chr(13);
	$HTML_calendario.='<TR>'.chr(13);
	$HTML_calendario.='<TH colspan=3>Horas</TH></TR>'.chr(13); // Literal Horas
	$HTML_calendario.='<TR>'.chr(13);
	$HTML_ampm[0]="";
	$HTML_ampm[1]="";
	$swampm[0]=false;
	$swampm[1]=false;

	if($this->horaresevini<12) $ix=0; else $ix=1;
	for($j=$ix;$j<=1;$j++){
		$HTML_ampm[$j].='<TD style="BACKGROUND-COLOR: #FFFFFF;" valig=top >'.chr(13);
		$HTML_ampm[$j].='<TABLE valig=top cellspacing=0 cellpadding=0  border=1  class="'.$this->clase.'">'.chr(13);
		$HTML_ampm[$j].='<TR>'.chr(13);
		$HTML_ampm[$j].='<TH colspan='.$maxcolumnas.'>'.$tbampm[$j].'</TH></TR>'.chr(13); // Literal Horas
		$HTML_ampm[$j].='<TR>'.chr(13);

		if($j==0){ // A.M.height
			$imin=$this->horaresevini;
			$currenthora=$imin;
			if($this->horaresevfin<=12)
				$imax=$this->horaresevfin;
			else
				$imax=12;
		}
		else{
				if($this->horaresevini<=12)
					$imin=0;
				else
					$imin=$this->horaresevini-12;
				$imax=(int)$this->horaresevfin-12;
				$currenthora=$imin;
		}
		$cols=0;
		$currentminutos=0;
		$currenthorario=$currenthora.":".$currentminutos;
		$intervalo=($imax-$imin+1)*4;
		for ($i=$imin;$i<$intervalo;$i++){
				$cols++;
				if($sw>0) // Brings reservations from A.M.
					$swampm[$j]=true;

				if($currentminutos==0) $currenthorario.="0";
				if(isset($CntDia[$j][$currenthora][$currentminutos])){
					if($CntDia[$j][$currenthora][$currentminutos]==1)
						$sw++;
						$swampm[$j]=true;
				}
				if(isset($CntDia[$j][$currenthora][$currentminutos])){
					if($CntDia[$j][$currenthora][$currentminutos]==0)
						$sw--;
				}
				$HTML_ampm[$j].='<TD ';
				if($sw>0)
					$HTML_ampm[$j].=' style="COLOR:#eeeeee;BACKGROUND-COLOR: #cc3366;"';

				if($sw>0) // Counts 15 minutes fraction as reserved
					$conthoras++;
				$HTML_ampm[$j].=' align=center>&nbsp;'.$currenthorario.'&nbsp;</TD>'.chr(13);
				$currentminutos+=15;
				if($currentminutos==60) {
					$currenthora++;
					$currentminutos=0;
				}
				$currenthorario=$currenthora.":".$currentminutos;
				if (($cols)%$maxcolumnas==0 ) $HTML_ampm[$j].='</TR><TR>'.chr(13);
			}
		$HTML_ampm[$j].='</TR></TABLE>'.chr(13);
		$HTML_ampm[$j].='</TD>'.chr(13);
	}

	if ($swampm[0])
			$HTML_calendario.=$HTML_ampm[0];

	if ($swampm[0] && $swampm[1]){
		$HTML_calendario.='<TD style="BACKGROUND-COLOR: #FFFFFF;" width=25>&nbsp;'.chr(13);
		$HTML_calendario.='</TD>'.chr(13);
	}

	if ($swampm[1])
			$HTML_calendario.=$HTML_ampm[1];

	$HTML_calendario.='</TR>'.chr(13);
	$HTML_calendario.='</TABLE>'.chr(13);

	$numblo=($this->horaresevfin-$this->horaresevini)*4;
	$porcenhoras=floor($conthoras*100/$numblo);
	return($HTML_calendario);
}
/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	Returns a string with days (monday(1), tuesday(2)....sunday(7)), separated by commas
________________________________________________________________________________________________________________*/
function DiasPorMes($mes,$anno,$numerodia){
	$cadenadias="";
		$dia_c=1;
		$fecha=$dia_c."/".$mes."/".$anno;
		$ds=$this->_DiaSemana($fecha);
		if ($ds==0) $ds=7;
		while($ds!=$numerodia){
			$dia_c++;
			$ds++;
			if($ds>7) $ds=1;
		}
		// Calculates number of days of a month
		$diasmaxmes=$this->dias_meses[$mes];
		if ($mes==2){
			if ($this->bisiesto($anno)){
					$diasmaxmes=29;
			}
		}
		while($dia_c<=$diasmaxmes){
			$cadenadias.=$dia_c.";";
			$dia_c+=7;
		}
		return($cadenadias);
}
/*________________________________________________________________________________________________________
	Returns a string with the days of a month of a given week, separated by commas.
________________________________________________________________________________________________________*/
function DiasPorSemanas($mes,$anno,$numerosemana){
	$cadenadias="";
		$dia_c=1;
		$nsem=1;
		$fecha=$dia_c."/".$mes."/".$anno;
		$ds=$this->_DiaSemana($fecha);
		if ($ds==0) $ds=7;
		while($nsem!=$numerosemana){
			$dia_c++;
			$ds++;
			if($ds>7){
				$ds=1;
				$nsem++;
			}
		}
		// Calculates number of days of a month
		$diasmaxmes=$this->dias_meses[$mes];
		if ($mes==2){
			if ($this->bisiesto($anno)){
					$diasmaxmes=29;
			}
		}
		for($i=$ds;$i<=7;$i++){
			if($dia_c>$diasmaxmes) break;
			$cadenadias.=$dia_c.";";
			$dia_c++;
		}
		return($cadenadias);
}
// ____________________________________________________________________________
//	This function returns the number of last week of a month
// ____________________________________________________________________________
function UltimaSemana($mes,$anno){
	$diasmaxmes=$this->dias_meses[$mes];
	if ($mes==2){
		if ($this->bisiesto($anno)){
				$diasmaxmes=29;
		}
	}
	$fecha="1/".$mes."/".$anno;
	$ds=$this->_DiaSemana($fecha);
	if ($ds==0) $ds=7;
	$nwdia=$diasmaxmes+$ds-1;
	$cociente=floor($nwdia/7);
	$resto=$nwdia%7;
	if($resto>0) $cociente++;
	return($cociente);
}
//________________________________________________________________________________________________________
// Function : Dates
// Description :
//		Returns a string of dates saparated by commas. These dates are part of a specific reservation
//	Parameters:
//		- anno_c: Aaspecefic year
//		- mes_desde: Month from  the reservation is considered
//		- mes_hasta: Month to the reservation is consedired
//		- meses: Field with hexadecimal information about reservation months (The information contained in the field of a table with this name)
//		- diario:  The same for days of a month
//		- dias: The same for names of days
//		- semanas: The same for weeks
//________________________________________________________________________________________________________
function Fechas($anno_c,$mes_desde,$mes_hasta,$meses,$diario,$dias,$semanas){
	$cadenafechas="";
	$mascara=0x0001;
	$cadenameses="";
	$meses=$meses>>($mes_desde-1);
	for($i=$mes_desde;$i<=$mes_hasta;$i++){
		if($meses&$mascara>0){
			$cadenameses.=$i.";";
			// Days of the week
			if($dias>0){
				$auxdias=$dias;
				for($j=1;$j<=7;$j++){
					if($auxdias&$mascara>0){
						$cadenadias=$this->DiasPorMes($i,$anno_c,$j);
						$tbdias=explode(";",$cadenadias);
						for ($k=0;$k<sizeof($tbdias)-1;$k++)
							$cadenafechas.=$tbdias[$k]."/".$i."/".$anno_c.";";
					}
					$auxdias=$auxdias>>1;
				}
			}
			// Weeks
			if($semanas>0){
				$auxsemanas=$semanas;
				for($j=1;$j<=6;$j++){
					if($auxsemanas&$mascara>0){
						if($j==6){
							$ulse=$this->UltimaSemana($i,$anno_c);
							$cadenadias=$this->DiasPorSemanas($i,$anno_c,$ulse);
						}
						else
							$cadenadias=$this->DiasPorSemanas($i,$anno_c,$j);
						$tbdias=explode(";",$cadenadias);
						for ($k=0;$k<sizeof($tbdias)-1;$k++)
							$cadenafechas.=$tbdias[$k]."/".$i."/".$anno_c.";";
					}
					$auxsemanas=$auxsemanas>>1;
				}
			}
		}
		$meses=$meses>>1;
	}
	$cadenadiario="";
	for($i=1;$i<32;$i++){
			if($diario&$mascara>0) $cadenadiario.=$i.";";
			$diario=$diario>>1;
	}
	$tbmeses=explode(";",$cadenameses);
	$tbdiario=explode(";",$cadenadiario);
	for ($i=0;$i<sizeof($tbmeses)-1;$i++){
		for ($j=0;$j<sizeof($tbdiario)-1;$j++){
			$cadenafechas.=$tbdiario[$j]."/".$tbmeses[$i]."/".$anno_c.";";
		}
	}
	return($cadenafechas);
}
/*______________________________________________________________________
	Returns the Juliano day for a specific date
	Parameters:
		- string with date in this format "dd/mm/yyyy"
	Returns:
		- Juliano day
_______________________________________________________________________*/
function juliana($fecha) {
	list($dia,$mes,$anno)=explode("[/-]",$fecha);
	$GGG = 1;
    if ($anno <= 1585) $GGG = 0;
    $juliano= -1 * floor(7 * (floor(($mes + 9) / 12) + $anno) / 4);
    $S = 1;
    if (($mes - 9)<0) $S=-1;
    $A = abs($mes - 9);
    $auxjuliano = floor($anno + $S * floor($A / 7));
    $auxjuliano = -1 * floor((floor($auxjuliano / 100) + 1) * 3 / 4);
    $juliano = $juliano + floor(275 * $mes / 9) + $dia + ($GGG * $auxjuliano);
	$juliano =$juliano + 1721027 + 2 * $GGG + 367 * $anno - 0.5;
	return(floor($juliano));
}
} // End of Calendar class
