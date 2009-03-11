#!/bin/bash
##########################################################
#####Autoconfigurador v0.0.7 para el la aplicacion Advanced Deploy enViorenment###########
# Liberado bajo licencia GPL <http://www.gnu.org/licenses/gpl.html>################
############# 2008 Antonio JesÃºs Doblas Viso##########################
########### Universidad de Malaga (Spain)############################
##########################################################
source /var/EAC/admin/librerias/Settings.lib
source /var/EAC/admin/librerias/PostConf.lib

	fileconf=/var/EAC/admin/config/EAC.conf
	filetempconf=/tmp/EAC.conf
	DIALOG=whiptail # dialog  whiptail
	TMP="/tmp/output"
########### mensaje de bienvenida ############################
	$DIALOG \
	--title "Entorno Avanzado de Clonacion o Advanced Deploy enVironmet" \
	--msgbox "Asistente de Instalacion y configuracion \n \
	\n \
	El EAC es software que permite automatizar los trabajos rutinarios de: \n \
	************ particionado, \n \
	************ clonacion y restauracion de SO (linux, windows) \n \
	************ Postconfiguracion de los SO restaurados \n \
	************ Gestor de arranque remoto \n \
\n
La vesion original y actual ha sido liberada por : \n \
     *********** Antonio Doblas Viso con Licencia GPL ********** \n \
para su posible desarrollo en ambientes universitarios \
	\n  \
	\n	\

'El meu agraiment: \n \
           al raig de llum de Banyoles' \n \
           a mi familia y amigos   
           a mis colegas del trabajo Jesus Basco Angel Diego Pacheco Franciso Gomez JuanMa Gonzalez Juan Antonio LLamas Gabriel Ochoa y Salvador Peula " 30 80

	
#################### configuracion de la red en offline ######################
		export result=$(dialog --title "Configuracion manual de la red" \
		--form "Desplaza y modifica con los cursores. \n NO PULSAR ENTER hasta rellenar todos los campos" 30 30 20 \
				"IP:" 			1 1 "172.16.72.242" 1 10 	30 30  \
				"Netmask:" 	2 1 "255.255.255.0" 2 10 	30 30  \
				"Gateway"	3 1 "172.16.72.254" 3 10	30 30 \
				"Subred"		4 1 "172.16.72.0"    4  10        30  30 \
				"Broadcast" 	5 1  "172.16.72.255"  5 10	30 30 \
				"DNS"		6 1 "62.36.225.150" 6 10	30 30 \
				"Hostname" 	7 1 "EACadi" 		7 10 	30 30  \
			--stdout)
			/bin/bash /var/EAC/admin/config/EAC_conf_server.sh $result
			export iphost=`echo $result | grep -f1 -d' '`
			echo $iphost
	
	$DIALOG \
		--title "Entorno Avanzado de Clonacion - Advanced Deploy enViorenmet " \
		--msgbox "En los siguientes pasos configuraremos el EAC \n 
				el fichero es /var/EAC/admin/config/EAC.conf \n
				Si te equivocas puedes solucionarlo ejecutando /var/EAC/admin/config/EAC_conf_EAC.sh" 20 60
cat > $filetempconf << EOF
<?php
##########################################################
#####Definicion de constantes v0.0.8 para Advanced Deploy enViorenment###########
# Liberado bajo licencia GPL <http://www.gnu.org/licenses/gpl.html>################
############# 2008 Antonio Jesús Doblas Viso##########################
########### Universidad de Malaga (Spain)############################
##########################################################
EOF

	$DIALOG  --nocancel \
		--title "Entorno Avanzado de Clonacion Advanced Deploy enViorenmet " \
		--inputbox "SQL_HOST o Direccion IP de este servidor \
				introduce"  20 60 "172.16.72.242" \
	2>$TMP
	export sql_host=`cat $TMP` 
	
	cat >> $filetempconf <<EOF
######## Parametros de la base de datos ######################
define ("SQL_HOST", "\$sql_host"); 
define("SQL_HOST_LOCAL", "localhost");
define ("SQL_USER", "eac");
define ("SQL_PASS", "eac");
define ("DATABASE", "eac");
######## Parametros almacenaje ######################
define ("REPO", "/var/EAC/");
EOF

$DIALOG  --nocancel  \
		--title "Entorno Avanzado de Clonacion Advanced Deploy enViorenmet " \
		--inputbox "NAMECOMPANY o nombre del usuario o empresa"  20 60 "Antonio Jesus Doblas Viso" \
		2>$TMP
	export namecompany=`cat $TMP` 
	cat >> $filetempconf <<EOF
define ("NAMECOMPANY", "\$namecompany"); 
EOF


	$DIALOG  --nocancel  \
		--title "Entorno Avanzado de Clonacion Advanced Deploy enViorenmet " \
		--inputbox "McastAddress o Direccion multicast de tu red \
				introduce"  20 60 "239.172.16.72" \
		2>$TMP
	export mcastaddress=`cat $TMP` 
	cat >> $filetempconf <<EOF
define ("McastAddress", "\$mcastaddress"); 
EOF

	$DIALOG  --nocancel  \
		--title "Entorno Avanzado de Clonacion Advanced Deploy enViorenmet " \
		--menu "McastMethod \n Metodo de transferencia multicast"  20 60 11 \
				full-duplex  "" \
				half-duplex ""\
				broadcast ""\
		2>$TMP
	export mcastmethod=`cat $TMP` 
	cat >> $filetempconf <<EOF
define ("McastMethod", "\$mcastmethod");   // full-duplex, half-duplex  or broadcast
EOF

	$DIALOG  --nocancel  \
		--title "Entorno Avanzado de Clonacion Advanced Deploy enViorenmet " \
		--menu "McastMaxBitrate \n Maxima velocidad de transferencia multicast"  20 60 11 \
				100M  "" \
				90M ""\
				80M ""\
				70M ""\
				60M ""\
				50M ""\
	2>$TMP
	export mcastmaxbitrate=`cat $TMP` 
	cat >> $filetempconf <<EOF
define ("McastMaxBitrate", "\$mcastmaxbitrate");   // 70M
define ("McastControlError", "8x8/128");
EOF

	$DIALOG  --nocancel \
		--title "Entorno Avanzado de Clonacion Advanced Deploy enViorenmet " \
		--inputbox "McastNumberClients \n este parametro indica el numero \
				de equipos al los cuales el servidor de Multicast
				esperara para iniciar el envio"  20 60 "90" \
	2>$TMP
	export mcastnumberclients=`cat $TMP` 
	cat >> $filetempconf <<EOF
define ("McastNumberClients", "\$mcastnumberclients");
EOF

	$DIALOG --nocancel   \
		--title "Entorno Avanzado de Clonacion Advanced Deploy enViorenmet " \
		--inputbox "McastTimeWaitForAllClients \n este parametro indica el numero \
				de segundos que el servidor esperara a que se conecten el numero\
				de equipos definidos en el parametro anterior. Transcurrido este tiempo \
				el servidor comenzara a enviar independientemete de que esten todos los equipos clientes \
				preparados para recibir "  20 60 "360" \
	2>$TMP
	export mcasttimewaitforallclients=`cat $TMP` 
	cat >> $filetempconf <<EOF
define ("McastTimeWaitForAllClients", "\$mcasttimewaitforallclients");
EOF

 
		$DIALOG  --nocancel  \
		--title "Entorno Avanzado de Clonacion - Advanced Deploy enViorenmet " \
		--menu "HostnameMethod  Metodo por el cual los clientes se autonombraran \ 
		en la siguiente ventana se asignara el valor de la variable Si eleges la opcion \
		file este debes de ubicarlo en /var/EAC/config/hostnamefile.txt con el formato \
		de IP;NOMBRE" 20 60 11 \
				variables "" \
				file ""\
				dns ""\
		2>$TMP
		export hostnamemethod=`cat $TMP` 
cat >> $filetempconf <<EOF
######## PARAMETROS ARRANQUE ##### 
define ("HostnameMethod","\$hostnamemethod");   // variables, dns, file
define ("HostnameFile","config/hostnamefile.txt"); 
EOF


		hostnamevariables=$(dialog --title "Configuracion manual de la red" \
		--form "Desplaza y modifica con los cursores. \n NO PULSAR ENTER hasta rellenar todos los campos \
		el unico campo obligatorio es el variable ya que debe ser variable para cada cliente \
		los otros campos son opcionales los puedes dejar en blanco \
		las variables globales las puedes ver en Setting.lib algunas son IP IPcuatro IPtres \
		el formato de la variable debe ser prefijo\\\${variable}sufijo" 40 80 20 \
				"prefijo:" 		1 1 	"adi" 1 10 		30 30  \
				"variable:" 	2 1 	"\\\${IPcuatro}" 2 10 	30 30  \
				"sufijo"		3 1 	"-xp" 3 10		30 30 \
		--stdout)
		export hostnamevariables=`echo $hostnamevariables | tr -d ' '`
		cat >> $filetempconf <<EOF
define ("HostnameVariables","$hostnamevariables");
EOF

		$DIALOG  --nocancel  \
		--title "Entorno Avanzado de Clonacion - Advanced Deploy enViorenmet " \
		--menu "CloneImageNTFS  Herramienta, que por defecto utilizara el EAC cuando \
		la particion a crear sea NTFS si en un momento puntual queremos utilizar la herramienta \
		que no hayamos seleccionado simplemente debemos re exportar la variable CloneImageNTFS \
		con el valor de la herramienta" 20 60 11 \
				partimage "" \
				ntfsclone ""\
		2>$TMP
		export cloneimagentfs=`cat $TMP` 
cat >> $filetempconf <<EOF
######### Configuracion herramientas de clonado #############
define ("CloneImageNTFS","\$cloneimagentfs");   // admite ntfsclone partimage  partimage-ng
EOF

		$DIALOG  --nocancel  \
		--title "Entorno Avanzado de Clonacion - Advanced Deploy enViorenmet " \
		--menu "CloneImageEXT23  Herramienta, que por defecto utilizara el EAC cuando \
		la particion a crear sea EXT2-3 si en un momento puntual queremos utilizar la herramienta \
		que no hayamos seleccionado simplemente debemos re exportar la variable CloneImageEXT23 \
		con el valor de la herramienta" 20 60 11 \
				partimage "" \
		2>$TMP
		export cloneimageext23=`cat $TMP` 
cat >> $filetempconf <<EOF
define ("CloneImageEXT23","\$cloneimageext23");   // admite ntfsclone partimage  partimage-ng
?>
EOF


### parseamos el fichero EAC.conf
CrearPatron sql_host namecompany mcastaddress  mcastmethod mcastmaxbitrate mcastnumberclients  mcasttimewaitforallclients hostnamemethod cloneimagentfs cloneimageext23
sed -f /tmp/patron.tmp $filetempconf > $fileconf
