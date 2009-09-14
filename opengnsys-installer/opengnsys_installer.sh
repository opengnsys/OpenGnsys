#!/bin/bash

#####################################################################
####### Script instalador OpenGnsys
####### autor: Luis Guillén <lguillen@unizar.es>
#####################################################################


WORKDIR=/tmp/opengnsys_installer
LOG_FILE=$WORKDIR/installation.log

# Array con las dependencias
DEPENDENCIES=( subversion php5 mysql-server nfs-kernel-server dhcp3-server udpcast bittorrent apache2 php5 mysql-server php5-mysql tftpd-hpa syslinux tftp-hpa openbsd-inetd update-inetd )

INSTALL_TARGET=/opt/opengnsys

MYSQL_ROOT_PASSWORD="passwordroot"

# conexión al svn
SVN_URL=svn://www.informatica.us.es:3690/eac-hidra
SVN_USER=lguillen
SVN_PASSWORD=potupass

# Datos de base de datos
HIDRA_DATABASE=bdhidra
HIDRA_DB_USER=usuhidra
HIDRA_DB_PASSWD=passusuhidra
HIDRA_DB_CREATION_FILE=eac-hidra/branches/eac-hidra-us/Hidra/doc/hidra-bd.sql

USUARIO=`whoami`

if [ $USUARIO != 'root' ]
then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi


mkdir -p $WORKDIR
pushd $WORKDIR

#####################################################################
####### Algunas funciones útiles de propósito general:
#####################################################################
getDateTime()
{
        echo `date +%Y%m%d-%H%M%S`
}

# Escribe a fichero y muestra por pantalla
echoAndLog()
{
        echo $1
        FECHAHORA=`getDateTime`
        echo "$FECHAHORA;$SSH_CLIENT;$1" >> $LOG_FILE
}

errorAndLog()
{
        echo "ERROR: $1"
        FECHAHORA=`getDateTime`
        echo "$FECHAHORA;$SSH_CLIENT;ERROR: $1" >> $LOG_FILE
}

# comprueba si el elemento pasado en $2 esta en el array $1
isInArray()
{
	if [ $# -ne 2 ]; then
		errorAndLog "isInArray(): invalid number of parameters"
		exit 1
	fi

	echoAndLog "isInArray(): checking if $2 is in $1"
	local deps
	eval "deps=( \"\${$1[@]}\" )"
	elemento=$2

	local is_in_array=1
	# copia local del array del parametro 1
	for (( i = 0 ; i < ${#deps[@]} ; i++ ))
	do
		if [ "${deps[$i]}" = "${elemento}" ]; then
			echoAndLog "isInArray(): $elemento found in array"
			is_in_array=0
		fi
	done

	if [ $is_in_array -ne 0 ]; then
		echoAndLog "isInArray(): $elemento NOT found in array"
	fi

	return $is_in_array

}

#####################################################################
####### Funciones de manejo de paquetes Debian
#####################################################################

checkPackage()
{
	package=$1
	if [ -z $package ]; then
		errorAndLog "checkPackage(): parameter required"
		exit 1
	fi
	echoAndLog "checkPackage(): checking if package $package exists"
	dpkg -L $package &>/dev/null
	if [ $? -eq 0 ]; then
		echoAndLog "checkPackage(): package $package exists"
		return 0
	else
		echoAndLog "checkPackage(): package $package doesn't exists"
		return 1
	fi
}

# recibe array con dependencias
# por referencia deja un array con las dependencias no resueltas
# devuelve 1 si hay alguna dependencia no resuelta
checkDependencies()
{
	if [ $# -ne 2 ]; then
		errorAndLog "checkDependencies(): invalid number of parameters"
		exit 1
	fi

	echoAndLog "checkDependencies(): checking dependences"
	uncompletedeps=0

	# copia local del array del parametro 1
	local deps
	eval "deps=( \"\${$1[@]}\" )"

	declare -a local_notinstalled

	for (( i = 0 ; i < ${#deps[@]} ; i++ ))
	do
		checkPackage ${deps[$i]}
		if [ $? -ne 0 ]; then
			local_notinstalled[$uncompletedeps]=$package
			let uncompletedeps=uncompletedeps+1
		fi
	done

	# relleno el array especificado en $2 por referencia
	for (( i = 0 ; i < ${#local_notinstalled[@]} ; i++ ))
	do
		eval "${2}[$i]=${local_notinstalled[$i]}"
	done

	# retorna el numero de paquetes no resueltos
	echoAndLog "checkDependencies(): dependencies uncompleted: $uncompletedeps"
	return $uncompletedeps
}

# Recibe un array con las dependencias y lo instala
installDependencies()
{
	if [ $# -ne 1 ]; then
		errorAndLog "installDependencies(): invalid number of parameters"
		exit 1
	fi
	echoAndLog "installDependencies(): installing uncompleted dependencies"

	# copia local del array del parametro 1
	local deps
	eval "deps=( \"\${$1[@]}\" )"

	local string_deps=""
	for (( i = 0 ; i < ${#deps[@]} ; i++ ))
	do
		string_deps="$string_deps ${deps[$i]}"
	done

	if [ -z "${string_deps}" ]; then
		errorAndLog "installDependencies(): array of dependeces is empty"
		exit 1
	fi

	OLD_DEBIAN_FRONTEND=$DEBIAN_FRONTEND
	export DEBIAN_FRONTEND=noninteractive

	echoAndLog "installDependencies(): now ${string_deps} will be installed"
	apt-get -y install --force-yes ${string_deps}
	if [ $? -ne 0 ]; then
		errorAndLog "installDependencies(): error installing dependencies"
		return 1
	fi

	DEBIAN_FRONTEND=$OLD_DEBIAN_FRONTEND
	echoAndLog "installDependencies(): dependencies installed"
}

#####################################################################
####### Funciones para el manejo de bases de datos
#####################################################################

# This function set password to root
mysqlSetRootPassword()
{
	if [ $# -ne 1 ]; then
		errorAndLog "mysqlSetRootPassword(): invalid number of parameters"
		exit 1
	fi

	local root_mysql=$1
	echoAndLog "mysqlSetRootPassword(): setting root password in MySQL server"
	/usr/bin/mysqladmin -u root password ${root_mysql}
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlSetRootPassword(): error while setting root password in MySQL server"
		return 1
	fi
	echoAndLog "mysqlSetRootPassword(): root password saved!"
	return 0
}

# comprueba si puede conectar con mysql con el usuario root
function mysqlTestConnection()
{
	if [ $# -ne 1 ]; then
		errorAndLog "mysqlTestConnection(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	echoAndLog "mysqlTestConnection(): checking connection to mysql..."
	echo "" | mysql -uroot -p"${root_password}"
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlTestConnection(): connection to mysql failed, check root password and if daemon is running!"
		return 1
	else
		echoAndLog "mysqlTestConnection(): connection success"
		return 0
	fi
}

# comprueba si la base de datos existe
function mysqlDbExists()
{
	if [ $# -ne 2 ]; then
		errorAndLog "mysqlDbExists(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2
	echoAndLog "mysqlDbExists(): checking if $database exists..."
	echo "show databases" | mysql -uroot -p"${root_password}" | grep "^${database}$"
	if [ $? -ne 0 ]; then
		echoAndLog "mysqlDbExists():database $database doesn't exists"
		return 1
	else
		echoAndLog "mysqlDbExists():database $database exists"
		return 0
	fi
}

function mysqlCheckDbIsEmpty()
{
	if [ $# -ne 2 ]; then
		errorAndLog "mysqlCheckDbIsEmpty(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2
	echoAndLog "mysqlCheckDbIsEmpty(): checking if $database is empty..."
	num_tablas=`echo "show tables" | mysql -uroot -p"${root_password}" "${database}" | wc -l`
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlCheckDbIsEmpty(): error executing query, check database and root password"
		exit 1
	fi

	if [ $num_tablas -eq 0 ]; then
		echoAndLog "mysqlCheckDbIsEmpty():database $database is empty"
		return 0
	else
		echoAndLog "mysqlCheckDbIsEmpty():database $database has tables"
		return 1
	fi

}


function mysqlImportSqlFileToDb()
{
	if [ $# -ne 3 ]; then
		errorAndLog "mysqlImportSqlFileToDb(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2
	local sqlfile=$3

	if [ ! -f $sqlfile ]; then
		errorAndLog "mysqlImportSqlFileToDb(): Unable to locate $sqlfile!!"
		return 1
	fi

	echoAndLog "mysqlImportSqlFileToDb(): importing sql file to ${database}..."
	mysql -uroot -p"${root_password}" "${database}" < $sqlfile
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlImportSqlFileToDb(): error while importing $sqlfile in database $database"
		return 1
	fi
	echoAndLog "mysqlImportSqlFileToDb(): file imported to database $database"
	return 0
}

# Crea la base de datos
function mysqlCreateDb()
{
	if [ $# -ne 2 ]; then
		errorAndLog "mysqlCreateDb(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local database=$2

	echoAndLog "mysqlCreateDb(): creating database..."
	mysqladmin -u root --password="${root_password}" create $database
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlCreateDb(): error while creating database $database"
		return 1
	fi
	errorAndLog "mysqlCreateDb(): database $database created"
	return 0
}


function mysqlCheckUserExists()
{
	if [ $# -ne 2 ]; then
		errorAndLog "mysqlCheckUserExists(): invalid number of parameters"
		exit 1
	fi

	local root_password="${1}"
	local userdb=$2

	echoAndLog "mysqlCheckUserExists(): checking if $userdb exists..."
	echo "select user from user where user='${userdb}'\\G" |mysql -uroot -p"${root_password}" mysql | grep user
	if [ $? -ne 0 ]; then
		echoAndLog "mysqlCheckUserExists(): user doesn't exists"
		return 1
	else
		echoAndLog "mysqlCheckUserExists(): user already exists"
		return 0
	fi

}

# Crea un usuario administrativo para la base de datos
function mysqlCreateAdminUserToDb()
{
	if [ $# -ne 4 ]; then
		errorAndLog "mysqlCreateAdminUserToDb(): invalid number of parameters"
		exit 1
	fi

	local root_password=$1
	local database=$2
	local userdb=$3
	local passdb=$4

	echoAndLog "mysqlCreateAdminUserToDb(): creating admin user ${userdb} to database ${database}"

	cat > $WORKDIR/create_${database}.sql <<EOF
GRANT USAGE ON *.* TO '${userdb}'@'localhost' IDENTIFIED BY '${passdb}' ;
GRANT ALL PRIVILEGES ON ${database}.* TO '${userdb}'@'localhost' WITH GRANT OPTION ;
FLUSH PRIVILEGES ;
EOF
	mysql -u root --password=${root_password} < $WORKDIR/create_${database}.sql
	if [ $? -ne 0 ]; then
		errorAndLog "mysqlCreateAdminUserToDb(): error while creating user in mysql"
		rm -f $WORKDIR/create_${database}.sql
		return 1
	else
		echoAndLog "mysqlCreateAdminUserToDb(): user created ok"
		rm -f $WORKDIR/create_${database}.sql
		return 0
	fi
}


#####################################################################
####### Funciones para el manejo de Subversion
#####################################################################

svnCheckoutCode()
{
	if [ $# -lt 2 ]; then
		errorAndLog "svnCheckoutCode(): invalid number of parameters"
		exit 1
	fi

	local url=$1
	local user=$2
	if [ $# -gt 2 ]; then
		local password=$3
	else
		local password=""
	fi

	echoAndLog "svnCheckoutCode(): downloading subversion code..."

	/usr/bin/svn co "${url}" --username "${user}" --password "${password}"
	if [ $? -ne 0 ]; then
		errorAndLog "svnCheckoutCode(): error getting code from ${url}, verify your user and password"
		return 1
	fi
	echoAndLog "svnCheckoutCode(): subversion code downloaded"
	return 0
}

#####################################################################
####### Funciones específicas de la instalación de Opengnsys
#####################################################################

function openGnsysInstallHidraApacheConf()
{
	if [ $# -ne 2 ]; then
		errorAndLog "openGnsysInstallHidraApacheConf(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base=$1
	local path_apache2_confd=$2
	local path_web_hidra=${path_opengnsys_base}/www

	echoAndLog "openGnsysInstallHidraApacheConf(): creating apache2 config file.."

	# genera configuración
	cat > $WORKDIR/apache.conf <<EOF
# Hidra web interface configuration for Apache

Alias /hidra ${path_web_hidra}

<Directory ${path_web_hidra}>
	Options -Indexes FollowSymLinks
	DirectoryIndex acceso.php
</Directory>
EOF

	if [ ! -d $path_apache2_confd ]; then
		errorAndLog "openGnsysInstallHidraApacheConf(): path to apache2 conf.d can not found, verify your server installation"
		rm -f $WORKDIR/apache.conf
		return 1
	fi
	cp $WORKDIR/apache.conf $path_opengnsys_base/etc
	ln -s $path_opengnsys_base/etc/apache.conf $path_apache2_confd/hidra.conf
	if [ $? -ne 0 ]; then
		errorAndLog "openGnsysInstallHidraApacheConf(): config file can't be linked to apache conf, verify your server installation"
		rm -f $WORKDIR/apache.conf
		return 1
	else
		echoAndLog "openGnsysInstallHidraApacheConf(): config file created and linked, restart your apache daemon"
		rm -f $WORKDIR/apache.conf
		return 0
	fi
}

# Crea la estructura base de la instalación de opengnsys
openGnsysInstallCreateDirs()
{
	if [ $# -ne 1 ]; then
		errorAndLog "openGnsysInstallCreateDirs(): invalid number of parameters"
		exit 1
	fi

	local path_opengnsys_base=$1

	echoAndLog "openGnsysInstallCreateDirs(): creating directory paths in $path_opengnsys_base"

	mkdir -p $path_opengnsys_base
	mkdir -p $path_opengnsys_base/bin
	mkdir -p $path_opengnsys_base/client
	mkdir -p $path_opengnsys_base/etc
	mkdir -p $path_opengnsys_base/lib
	mkdir -p $path_opengnsys_base/log
	mkdir -p $path_opengnsys_base/www
	mkdir -p $path_opengnsys_base/tftpboot/ogclients
	mkdir -p $path_opengnsys_base/images

	if [ $? -ne 0 ]; then
		errorAndLog "openGnsysInstallCreateDirs(): error while creating dirs. Do you have write permissions?"
		return 1
	fi

	echoAndLog "openGnsysInstallCreateDirs(): directory paths created"
	return 0
}

#####################################################################
####### Proceso de instalación
#####################################################################


# Proceso de instalación de opengnsys
declare -a notinstalled
checkDependencies DEPENDENCIES notinstalled
if [ $? -ne 0 ]; then
	installDependencies notinstalled
	if [ $? -ne 0 ]; then
		echoAndLog "Error while installing some dependeces, please verify your server installation before continue"
		exit 1
	fi
fi

isInArray notinstalled "mysql-server"
if [ $? -eq 0 ]; then
	mysqlSetRootPassword ${MYSQL_ROOT_PASSWORD}
fi

openGnsysInstallCreateDirs ${INSTALL_TARGET}
if [ $? -ne 0 ]; then
	errorAndLog "Error while creating directory paths!"
	exit 1
fi

svnCheckoutCode $SVN_URL $SVN_USER $SVN_PASSWORD
if [ $? -ne 0 ]; then
	errorAndLog "Error while getting code from svn"
	exit 1
fi

mysqlTestConnection ${MYSQL_ROOT_PASSWORD}
if [ $? -ne 0 ]; then
	errorAndLog "Error while connection to mysql"
	exit 1
fi
mysqlDbExists ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
if [ $? -ne 0 ]; then
	echoAndLog "Creating hidra database"
	mysqlCreateDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
	if [ $? -ne 0 ]; then
		errorAndLog "Error while creating hidra database"
		exit 1
	fi
else
	echoAndLog "Hidra database exists, ommiting creation"
fi

mysqlCheckUserExists ${MYSQL_ROOT_PASSWORD} ${HIDRA_DB_USER}
if [ $? -ne 0 ]; then
	echoAndLog "Creating user in database"
	mysqlCreateAdminUserToDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE} ${HIDRA_DB_USER} "${HIDRA_DB_PASS}"
	if [ $? -ne 0 ]; then
		errorAndLog "Error while creating hidra user"
		exit 1
	fi

fi

mysqlCheckDbIsEmpty ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
if [ $? -eq 0 ]; then
	echoAndLog "Creating tables..."
	if [ -f $WORKDIR/$HIDRA_DB_CREATION_FILE ]; then
		mysqlImportSqlFileToDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE} $WORKDIR/$HIDRA_DB_CREATION_FILE
	else
		errorAndLog "Unable to locate $WORKDIR/$HIDRA_DB_CREATION_FILE!!"
		exit 1
	fi
fi

echoAndLog "Installing web files..."
# copiando paqinas web
cp -pr eac-hidra/branches/eac-hidra-us/Hidra/webhidra/* $INSTALL_TARGET/www   #*/ comentario para doxigen

# creando configuracion de apache2
openGnsysInstallHidraApacheConf $INSTALL_TARGET /etc/apache2/conf.d
if [ $? -ne 0 ]; then
	errorAndLog "Error while creating hidra apache config"
	exit 1
fi

popd
#rm -rf $WORKDIR
echoAndLog "Process finalized!"

## Pendiente1 por revisar LUIS
#########################################################
##squelo instalacion modulo EAC en web hidra (paginas web + tablas en db hidra)################
########################################################

WORKDIR=/tmp/opengnsys_installer
pushd $WORKDIR



### revisar la ubicacion de los ficheros.
## fichero sql de eac
cp -pr /root/workspace/eac-hidra/branches/eac-hidra-uma/EAC/webeac/ /tmp/opengnsys_installer/eac-hidra/branches/eac-hidra-uma/EAC/
EAC_DB_CREATION_FILE=eac-hidra/branches/eac-hidra-uma/EAC/admin/config/database/TablasEacforHidra.sql

## paginas web de eac
cp /root/workspace/eac-hidra/branches/eac-hidra-uma/EAC/admin/config/database/TablasEacforHidra.sql /tmp/opengnsys_installer/eac-hidra/branches/eac-hidra-uma/EAC/admin/config/database/TablasEacforHidra.sql
# modificacion del menu de hidra para aceptar modulo EAC
cp -pr /root/workspace/eac-hidra/branches/eac-hidra-uma/EAC/webeac/barramenu.php /tmp/opengnsys_installer/eac-hidra/branches/eac-hidra-uma/EAC/webeac/barramenu.php
###



#mysqlDbExists ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
#if [ $? -ne 0 ]; then
#	echoAndLog "Creating eac database"
#	mysqlCreateDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
#	if [ $? -ne 0 ]; then
#		errorAndLog "Error while updating HIDRA database"
#		exit 1
#	fi
#else
#	echoAndLog "hidra database exists, ommiting creation"
#fi

#mysqlCheckUserExists ${MYSQL_ROOT_PASSWORD} ${HIDRA_DB_USER}
#if [ $? -ne 0 ]; then
#	echoAndLog "Creating user in database hidra"
#	mysqlCreateAdminUserToDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE} ${HIDRA_DB_USER} "${EAC_DB_PASS}"
#	if [ $? -ne 0 ]; then
#		errorAndLog "Error while creating eac user"
#		exit 1
#	fi

#fi

#mysqlCheckDbIsEmpty ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE}
#if [ $? -eq 0 ]; then
#	echoAndLog "Creating tables..."
#	if [ -f $WORKDIR/$EAC_DB_CREATION_FILE ]; then
		mysqlImportSqlFileToDb ${MYSQL_ROOT_PASSWORD} ${HIDRA_DATABASE} $WORKDIR/$EAC_DB_CREATION_FILE
#	else
#		errorAndLog "Unable to locate $WORKDIR/$EAC_DB_CREATION_FILE!!"
#		exit 1
#	fi
#fi

echoAndLog "Installing web files..."
# copiando paqinas web
cp -pr eac-hidra/branches/eac-hidra-uma/EAC/webeac $INSTALL_TARGET/www/principal/  #*/
cp -pr eac-hidra/branches/eac-hidra-uma/EAC/webeac/barramenu.php $INSTALL_TARGET/www/barramenu.php  #*/
######## fin esquelos modulo eac ##########33


## Pendiente2 a revisar por Luis
############################################################
### Esqueleto para el Servicio pxe y contenedor tftpboot ##############
###########################################################
basetftp=/var/lib/tftpboot
basetftpaux=/tftpboot
basetftpog=/opt/opengnsys/tftpboot
# creamos los correspondientes enlaces hacia nuestro contenedor.
ln -s ${basetftp} ${basetftpog}
ln -s ${basetftpaux} ${basetftpog}

function TestPxe () {
	cd /tmp
	echo "comprobando servidio pxe ..... Espere"
	tftp -v localhost -c get pxelinux.0 /tmp/pxelinux.0 && echo "servidor tftp OK" || echo "servidor tftp KO"
	cd /
}
# reiniciamos demonio internet
/etc/init.d/openbsd-inetd start

##preparcion contendor tftpboot
	cp -pr /usr/lib/syslinux/ ${basetftpboot}/syslinux
	cp /usr/lib/syslinux/pxelinux.0 $basetftpboot
# prepamos el directorio de la configuracion de pxe
	mkdir -p ${basetftpboot}/pxelinux.cfg
	touch ${basetftpboot}/pxelinux.cfg/default


	# comprobamos el servicio tftp
sleep 1
TestPxe

## damos perfimos de lectura a usuario web.
chown -R www-data:www-data /var/lib/tftpboot
######### fin revisar2 contenedor tftp


## Revisar3. configurador de parametros para los clientes.
#/**
#         ogInterfazCustomServer
#@brief   Interfaz para ogCustomServer y openGnsys.conf
#@note    Requisitos: SO en el contendor tftpboot
#@warning Queda comprobarlo para opengnesys).
#@version 0.1 - version inicial proporcionada por EAC - No testeado para OpenGNSys.
#@author  Antonio Doblas, EVLT Universidad de Malaga
#@date    2009/07/25
#*/
function ogInterfazCustomServer() {
source /opt/opengnsys/client/engine/System.lib
source /opt/opengnsys/client/engine/PostConf.lib

	fileconf=/opt/opengnsys/etc/openGnsys.conf
	filetempconf=/tmp/openGnsys.conf
	DIALOG=whiptail # dialog  whiptail
	TMP="/tmp/output"
########### mensaje de bienvenida ############################
	$DIALOG \
	--title "openGnsys" \
	--msgbox "Opengnsys \n
	Asistente de Instalacion y configuracion v 0.9    \n \
	\n \
	Descripcion opengnsys: \n \
	************ particionado, \n \
	************ clonacion y restauracion de SO (linux, windows) \n \
	************ Postconfiguracion de los SO restaurados \n \
	************ Gestor de arranque remoto \n \
\n
\n	\

 " 30 80


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
			#llamada a CustomEACServer
			CustomEACServer $result
			export iphost=`echo $result | grep -f1 -d' '`
			echo $iphost
			#export dnshost=`echo $result | grep -f6 -d' '`
			#echo "nameserver $dnshost > /var/EAC/nfsroot/etc/resolv.con"

	$DIALOG \
		--title "Entorno Avanzado de Clonacion - Advanced Deploy enViorenmet " \
		--msgbox "En los siguientes pasos configuraremos el EAC \n
				el fichero es /var/EAC/admin/config/EAC.conf \n
				Si te equivocas puedes solucionarlo ejecutando como root \m
				# source /var/EAC/admin/librerias/EACInstall \n
				# InterfazCustomEACserver" 20 60
cat > $filetempconf << EOF
<?php
##########################################################
#### Configuracion opengnsys############################
##########################################################
EOF

	$DIALOG  --nocancel \
		--title "opengnsys" \
		--inputbox "SQL_HOST o Direccion IP de este servidor \
				introduce"  20 60 "172.16.72.242" \
	2>$TMP
	export sql_host=`cat $TMP`

	cat >> $filetempconf <<EOF
######## Parametros de la base de datos ######################
define ("SQL_HOST", "\$sql_host");
define("SQL_HOST_LOCAL", "localhost");
define ("SQL_USER", "opengnsys");
define ("SQL_PASS", "opengnsys");
define ("DATABASE", "opengnsys");
######## Parametros almacenaje ######################
define ("REPO", "/opt/opengnsys/");
EOF

$DIALOG  --nocancel  \
		--title "opengnsys " \
		--inputbox "NAMECOMPANY o nombre del usuario o empresa"  20 60 "Antonio Jesus Doblas Viso" \
		2>$TMP
	export namecompany=`cat $TMP`
	cat >> $filetempconf <<EOF
define ("NAMECOMPANY", "\$namecompany");
EOF

$DIALOG  --nocancel  \
		--title "opengnsys " \
		--inputbox "Servidor DNS para tus clientes \
				introduce"  20 60 "62.36.225.150" \
		2>$TMP
	export clientdns=`cat $TMP`
	echo "nameserver $clientdns" > /opt/opengnsys/client/etc/resolv.conf

$DIALOG  --nocancel  \
		--title "opengnsys " \
		--inputbox "servidor proxy para tus clinetes , no pongas http://\
				introduce"  20 60 "172.16.72.242:3128" \
		2>$TMP
	export clientproxy=`cat $TMP`
	cat >> $filetempconf <<EOF
define ("ClientProxy", "http://\$clientproxy");
EOF

$DIALOG  --nocancel  \
		--title "opengnsys " \
		--inputbox "servidor ntp o de hora para tus clinetes \
				introduce"  20 60 "hora.alumnos.universidad.es" \
		2>$TMP
	export clientntp=`cat $TMP`
	cat >> $filetempconf <<EOF
define ("ClientNtp", "\$clientntp");
EOF



	$DIALOG  --nocancel  \
		--title "opengnsys " \
		--inputbox "McastAddress o Direccion multicast de tu red \
				introduce"  20 60 "239.172.16.72" \
		2>$TMP
	export mcastaddress=`cat $TMP`
	cat >> $filetempconf <<EOF
define ("McastAddress", "\$mcastaddress");
EOF

	$DIALOG  --nocancel  \
		--title "opengnsys " \
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
		--title "opengnsys " \
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
		--title "opengnsys " \
		--inputbox "McastNumberClients \n este parametro indica el numero \
				de equipos al los cuales el servidor de Multicast
				esperara para iniciar el envio"  20 60 "90" \
	2>$TMP
	export mcastnumberclients=`cat $TMP`
	cat >> $filetempconf <<EOF
define ("McastNumberClients", "\$mcastnumberclients");
EOF

	$DIALOG --nocancel   \
		--title "opengnsys " \
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
		--title "opengnsys " \
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
				partclone " "\
		2>$TMP
		export cloneimagentfs=`cat $TMP`
cat >> $filetempconf <<EOF
######### Configuracion herramientas de clonado #############
define ("CloneImageNTFS","\$cloneimagentfs");   // admite ntfsclone partimage  partimage-ng
EOF

		$DIALOG  --nocancel  \
		--title "opengnsys " \
		--menu "CloneImageEXT23  Herramienta, que por defecto utilizara el EAC cuando \
		la particion a crear sea EXT2-3 si en un momento puntual queremos utilizar la herramienta \
		que no hayamos seleccionado simplemente debemos re exportar la variable CloneImageEXT23 \
		con el valor de la herramienta" 20 60 11 \
				partimage "" \
				partclone " " \
		2>$TMP
		export cloneimageext23=`cat $TMP`
cat >> $filetempconf <<EOF
define ("CloneImageEXT23","\$cloneimageext23");   // admite ntfsclone partimage  partimage-ng
?>
EOF


### parseamos el fichero openGnsys.conf
CrearPatron sql_host namecompany clientdns clientproxy clientntp mcastaddress  mcastmethod mcastmaxbitrate mcastnumberclients  mcasttimewaitforallclients hostnamemethod cloneimagentfs cloneimageext23
sed -f /tmp/patron.tmp $filetempconf > $fileconf
echo "Actualizado la BD"
/usr/bin/php -r 'include("/opt/opengnsys/librerias/DBProcess.php"); InsertClassrom("Default",$_SERVER["ipsubnet"],$_SERVER["ipmask"],$_SERVER["ipbroadcast"],$_SERVER["ipgateway"],$_SERVER["sql_host"], $_SERVER["sql_host"]);'
echo "fin proceso"
## damos los permisos al usuario web
chown -R www-data:www-data /var/EAC/admin/startpage
chmod -R 777 /var/EAC/admin/startpage
$DIALOG \
		--title "fin de proceso " \
		--msgbox "Se ha configurdo el fichero /var/EAC/admin/config/EAC.conf \n
				Si te equipos, ejecuta como root: source /var/EAC/admin/librerias/EACInstall.lib \n
				y luego # InterfazCustomEACserver" 20 60



}
############### fin pendiente3 a revisar por Luis



####### peendiente4 revisar por Luis
## configura ip(ip,dns,hostanme,hosts..) dhcp, nfs, default del tftpboot.
## OJO: esta función modifica también la IP, testear si es necesario modificar o simplemente obtener los datos y reutilizarlos en los parametros de los servicios

#/**
#         ogCustomServer
#@brief   Configura los servicios del opengnsys Server para un quipo que no esté en producción. el dhcp lo deja configurado pero desabilitado
#@arg  \c str_IP         IP del servidor Opengnsys
#@arg  \c str netmask    netmask del servidor OpenGnsys
#@arg  \c str gateway    ip del gateway para el servidor
#@arg  \c str subnet     subred del servidor Opengnsys
#@arg  \c str broadcast  direccion broadcast de la red
#@arg  \c str dns        direccion del servidor dns
#@arg  \c str hostname   hostname del servidor Opengnsys
#@note    Requisitos: SO en el contendor tftpboot
#@warning Queda comprobarlo para opengnesys).
#@version 0.1 - version inicial proporcionada por EAC - No testeado para OpenGNSys.
#@author  Antonio Doblas, EVLT Universidad de Malaga
#@date    2009/07/25
#*/

function ogCustomServer() {

if [ $# = 0 ]
then
	echo "configurador del servicio tftpboot, dhcpd y nfs para el entrono opengnsy"
	echo "admite como parametro:        str_IP          str_netmask   str_gateway     str_subnet  str_broadcast    str_dns       str_hostname"
	echo "ejemplo: EAC_conf_server.sh   172.16.72.242 255.255.255.0   172.16.72.254   172.16.72.0   172.16.72.255  150.214.40.11 EACadi"
fi
if [ $# = 7 ]
then
	export iphost=$1
	export ipmask=$2
	export ipgateway=$3
	export ipsubnet=$4
	export ipbroadcast=$5
	ipdns=$6
	name=$7
	ip3octetos=`echo $1 | awk -F. '{print $1 "." $2 "." $3}'`



	echo "configurando la interfaz de rez para $iphost con netmask $ipmask y hostname $name"

cat > /etc/network/interfaces <<EOF
auto lo
iface lo inet loopback
auto eth0
iface eth0 inet static
address $iphost
netmask $ipmask
gateway $ipgateway
EOF

	/etc/init.d/networking restart

	echo "configurando /etc/hosts.allow para $ipsubred"
cat > /etc/hosts.allow <<EOF
all:$ipsubred/$ipmask
EOF

	echo "configurando /etc/hosts "
cat > /etc/hosts <<EOF
127.0.0.1		localhost
$iphost		$name
EOF

	echo "configurando /etc/hostname "
cat > /etc/hostname <<EOF
$name
EOF
	/etc/init.d/hostname.sh start

echo "configurando el servidor dns"
cat > /etc/resolv.conf <<EOF
nameserver $ipdns
EOF

	echo "configurando el servidor dhcp"
cat > /etc/dhcp3/dhcpd.conf <<EOF
option routers                  $ipgateway;
option broadcast-address        $ipbroadcast;
option subnet-mask              $ipmask;
option domain-name-servers      $ipdns;
ddns-update-style none;
not-autoritative;
subnet $ipsubnet netmask $ipmask  {
#range ${ip3octetos}.20 ${ip3octetos}.30;
next-server ${iphost};
filename "pxelinux.0";
host example1 {
        hardware ethernet 00:13:77:66:4e:60;
        fixed-address $ip3octetos.152;
        }
host example2 {
	hardware ethernet 00:13:8f:cf:64:73;
	fixed-address $ip3octetos.153;
}
}
EOF

	# /etc/init.d/dhcp3-server restart

	echo "configurando el servidor nfs"
cat > /etc/exports <<EOF
/opt/opengnsys/client ${ipsubnet}/${ipmask}(ro,no_subtree_check,no_root_squash,async)
/opt/opengnsys/log/client ${ipsubnet}/${ipmask}(ro,no_subtree_check,no_root_squash,sync)
/opt/opengnsys/images ${ipsubnet}/${ipmask}(rw,no_subtree_check,no_root_squash,sync,crossmnt)
/opt/opengnsys/tftpboot/ogClients/ogClientNfs ${ipsubnet}/${ipmask}(ro,no_subtree_check,no_root_squash,sync)
EOF
	/etc/init.d/nfs-kernel-server restart


	echo "configuramos el fichero default del pxelinux.cfg"
cd /opt/opengnsys/tftpboot
#buscamos los kernel y los initrd del contenedor tftpboot
vmlinuznfs=`find -type f -name vmlinuz* | grep "ogClientNfs"`
initrdnfs=`find -type f -name initrd* | grep "ogClientNfs"`
vmlinuzUltraLight=`find -type f -name vmlinuz* | grep "ogClientUltraLight"`
initrdUltraLight=`find -type f -name initrd* | grep "ogClientUltraLight"`

vmlinuzRam=`find -type f -name vmlinuz* | grep "ogClientRam"`
initrdRam=`find -type f -name initrd* | grep "ogClientRam"`

cat > /var/EAC/tftpboot/pxelinux.cfg/default <<EOF
DEFAULT ogClientUltraLight

LABEL ogClientUltraLight
KERNEL ${vmlinuzUltraLight}
APPEND initrd=${initrdUltraLight} ip=dhcp ro vga=788 irqpoll acpi=on


LABEL ogClientsNfs
#KERNEL EACBootAgent/stable/vmlinuz-2.6.28-11-server
KERNEL ${vmlinuznfs}
#APPEND root=/dev/nfs initrd=EACBootAgent/stable/initrd.img-2.6.28-11-server nfsroot=${iphost}:/var/EAC/nfsroot/stable ip=dhcp ro vga=788 irqpoll nolapic allowed_drive_mask=0 acpi=off pci=nomsi EACregistred=NO
APPEND root=/dev/nfs initrd=${initrdnfs} nfsroot=${iphost}:/var/lib/tftpboot/ogClients/ogClientNfs ip=dhcp ro vga=788 irqpoll nolapic  acpi=off pci=nomsi EACregistred=NO


LABEL ogClientRam
KERNEL ${vmlinuzRam}
APPEND initrd=${initrdRam} ip=dhcp ro vga=788 irqpoll acpi=on


label dos
  kernel floppies/memdisk
  append initrd=/floppies/dos.img

LABEL 1
LOCALBOOT 0

LABEL 11
kernel syslinux/chain.c32
append hd0 1


LABEL 12
kernel syslinux/chain.c32
append hd0 2

LABEL 13
kernel syslinux/chain.c32
append hd0 3

LABEL 2
kernel syslinux/chain.c32
append hd1 0


LABEL mbr
	LOCALBOOT 0

PROMPT 1
TIMEOUT 18
EOF


#######################################################
## configurar mysql o mostrar al final informacion sobre esto
echo " comprobar de mysql /etc/mysql/my.cnf"
echo "[mysqld]"
echo "bin-address = 0.0.0.0 "
echo "skip-name-resolve"


#reiniciamos otros servicios
/etc/init.d/mysql restart
/etc/init.d/apache2 restart
fi
}












####################################################
######### Pasando la informacion del subverion a la estructura final /opt/opengnsys
#######################################################

## copiando doc a /opt/opengnsys
#cp -pr eac-hidra/trunk/opengnsys-doc/* $path_opengnsys_base #*/
cp /tmp/opengnsys_installer/eac-hidra/trunk/opengnsys-doc/* /opt/opengnsys


# copiar los modelos de startpages /opt/opengnsys/client/etc/startpages



popd
