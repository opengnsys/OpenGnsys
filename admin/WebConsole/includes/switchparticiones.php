	<?
function nombreSO($tipopart,$tiposo,$nombreso){
	switch($tipopart){
					case "BIGDOS": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Msdos,Windows 95</span>';
						break;
					case "HBIGDOS": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Msdos,Windows 95</span>';
						break;
					case "FAT32": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Windows 98, Millenium</span>';
						break;
					case "HFAT32": 
						if(empty($tiposo))
								$nombreso='<span style="COLOR:red">Windows 98, Millenium<span style="COLOR:green;font-weight:600">&nbsp;(Partici� oculta)</span></span>';
						else
								$nombreso.='<span style="COLOR:green;font-weight:600">&nbsp;(Partici� oculta)</span>';
						break;
					case "NTFS": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Windows XP, Windows 2000, Windows 2003</span>';
						break;;
					case "HNTFS": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Windows XP, Windows 2000, Windows 2003<span style="COLOR:green;font-weight:600">&nbsp;(Partici� oculta)</span></span>';
						else
								$nombreso.='<span style="COLOR:green;font-weight:600">&nbsp;(Partición� oculta)</span>';
						break;
					case "EXT2": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Linux (EXT2)</span>';
						break;
					case "EXT3": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Linux (EXT3)</span>';
						break;
					case "EXT4": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Linux (EXT4)</span>';
						break;
					case "VFAT": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">VFAT</span>';
						break;
					case "HVFAT": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">HVFAT)</span>';
						break;
					case "UNKNOWN": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:blue">UNKNOWN</span>';
						break;
					case "CACHE": 
						if(empty($tiposo))
							$nombreso='<span style="COLOR:green">CACHE</span>';
						break;
					case "LINUX-SWAP": 
						$nombreso='<span style="COLOR:blue">Linux-swap</span>';
						break;
		}
	return($nombreso);
}
?>