<?php
//________________________________________________________________________________________________________
//
//	Php Language file: nada_esp.php
//	Language: English
//________________________________________________________________________________________________________
	$TbMsg=array();
	$TbMsg[0]="DEVICE USED TO ACCESS OPENGNSYS";
	$TbMsg[1]="IP - Device";
	$TbMsg[2]="Type - Device";
	$TbMsg[3]="Operating System";
	$TbMsg[4]="System Version";
	$TbMsg[5]="Browser";
	$TbMsg[6]="Browser Version";
        $TbMsg["TIP"]="Consejo del día";

        // Los mensajes pueden tener imágenes asociadas llamadas images/tipOfDay_N.png
        $TipOfDay=Array();
        $TipOfDay[0]="OpenGnsys client can create and restore images from all repositories in organization unit.";
        $TipOfDay[1]="OpenGnsys can manage UEFI computers form version 1.1.1 (Espeto).";
        $TipOfDay[2]="<a href='https://opengnsys.es' class='help_menu' target='blank'>New OpenGnsys website</a> aimed at the user, where you will easily find:   \n".
                     "<ul>\n".
                     "  <li>Download OpenGnsys last version.</li>\n".
                     "  <li>User Manual</li>\n".
                     "  <li>Installation documentation</li>\n".
                     "  <li>Success stories</li>\n".
                     "</ul>\n<br>\n";
        $TipOfDay[3]="OpenGnsys allows users to install several ogLive, being able to select on each computer the one that best recognizes your hardware.";
        $TipOfDay[4]="OpenGnsys allows independent hosting of images from different organizational units within the same repository.";
        $TipOfDay[5]="For facilitate OpenGnsys migration, there are scripts to export and import data.";
        $TipOfDay[6]="RemotePC combines OpenGnsys with UDS to offer remote access to classroom computers outside teaching hours.";
        $TipOfDay[7]="<b>Course Online</b><p>All members of organizations that are federated in the RedIRIS Identity Service can access the course 'OpenGnsys Basic Course 1.1.0' in the <a href='https://docencia-net.cv.uma.es' class='help_menu' target='blank'>Training Platform of the Docencia-Net group.</p>";
        $TipOfDay[8]="The new OpenGnsys agent for the operating system allows users to send messages and execute commands on the computer.";
        $TipOfDay[9]="On the OpenGnsys web project you can find <a href='https://opengnsys.es/trac/wiki/EjemploPracticos' class='help_menu' target='blank'>practical examples and recipes</a>, such as the postconfiguration required for Windows activation with KMS.";
	$TipOfDay[10]="OpenGnsys support Nvme disks, you must use ogLive bionic 5.0.";
	$TipOfDay[11]="To avoid multicast transfer conflicts, it is necessary to configure different multicast ports in the properties of each class.";
	$TipOfDay[12]="You can define nice priority to seed torrent files in <br> /etc/default/opengnsys. The recommended values are:\n".
		     "<ul>\n".
		     "  <li> 8 for Admin Server or Repo without Torrent.</li>\n".
		     "  <li> 0 for Admin Server and Repo with Torrent.</li>\n".
		     "  <li>-8 for Repo with Torrent.</li>\n".
		     "</ul>\n<br>\n";
