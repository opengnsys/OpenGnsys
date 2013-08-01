#!/bin/bash
#/**
#@file    opengnsys_update.sh
#@brief   Script de desinstalación de OpenGnSys
#@warning No se elimina el directorio de imágenes, ni se desinstalan otros programas.
#@version 0.10 - Primera prueba de desinstalación.
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2010/10/28
#@version 1.0 - Eliminar servicios de OpenGnSys.
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2011/03/02
#@version 1.0.2 - Información de desinstalación y correcciones.
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2011/12/22
#@version 1.0.4 - Compatibilidad con otras distribuciones y auto configuración de acceso a BD
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2012/03/28
#@version 1.0.5 - Usar las mismas variables que el script de instalación.
#@author  Ramón Gómez - ETSII Univ. Sevilla
#@date    2013/01/09


####  AVISO: Editar configuración de acceso.
####  WARNING: Edit access configuration
MYSQL_ROOT_PASSWORD="passwordroot"	# Clave de root de MySQL
OPENGNSYS_DATABASE="ogAdmBD"		# Base de datos de administración
OPENGNSYS_DB_USER="usuog"		# Usuario de acceso a la base de datos


####  AVISO: NO EDITAR variables de configuración.
####  WARNING: DO NOT EDIT configuration variables.
OPENGNSYS="/opt/opengnsys"		# Directorio de OpenGnSys
OGIMG="images"				# Directorio de imágenes del repositorio
OPENGNSYS_CLIENT_USER="opengnsys"	# Usuario Samba
OPENGNSYS_OLDDATABASE="ogBDAdmin"	# Antigua base de datos
MYCNF=/tmp/.my.cnf.$$			# Fichero temporal con credenciales de acceso a la BD.


# Sólo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]; then
    echo "ERROR: this program must run under root privileges!!"
    exit 1
fi


# Parar servicio.
echo "Uninstalling OpenGnSys services."
if [ -x /etc/init.d/opengnsys ]; then
    /etc/init.d/opengnsys stop
    if [ -n "$(which update-rc.d 2>/dev/null)" ]; then
        update-rc.d -f opengnsys remove
    else
	chkconfig --del opengnsys
    fi
fi
# Comprobar acceso a la bases de datos.
echo "Erasing OpenGnSys database."
DROP=1
if ! mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<<"quit" 2>/dev/null; then
    stty -echo
    read -p  "- Please, insert MySQL root password: " MYSQL_ROOT_PASSWORD
    echo ""
    stty echo
    if ! mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<<"quit" 2>/dev/null; then
	DROP=0
	echo "Warning: database not erased."
    fi
fi
if test $DROP; then
    # Componer fichero temporal con credenciales de conexión a la base de datos.
    touch $MYCNF
    chmod 600 $MYCNF
    cat << EOT > $MYCNF
[client]
user=root
password=$MYSQL_ROOT_PASSWORD
EOT
    # Eliminar bases de datos.
    mysql --defaults-extra-file=$MYCNF 2> /dev/null << EOT
DROP DATABASE $OPENGNSYS_OLDDATABASE;
DROP DATABASE $OPENGNSYS_DATABASE;
DROP USER '$OPENGNSYS_DB_USER';
DROP USER '$OPENGNSYS_DB_USER'@'localhost';
EOT
    # Borrar el fichero temporal de credenciales.
    rm -f $MYCNF
fi
# Quitar configuración específica de Apache.
[ -n "$(which a2dissite 2>/dev/null)" ] && a2dissite opengnsys
rm -f /etc/{apache2/{sites-available,sites-enabled},httpd/conf.d}/opengnsys*
for serv in apache2 httpd; do
    [ -x /etc/init.d/$serv ] && /etc/init.d/$serv reload
done
# Eliminar ficheros.
echo "Deleting OpenGnSys files."
for dir in $OPENGNSYS/*; do
    if [ "$dir" != "$OPENGNSYS/$OGIMG" ]; then
        rm -fr "$dir"
    fi
done
rm -f /etc/init.d/opengnsys /etc/default/opengnsys /var/log/opengnsys
rm -f /etc/cron.d/{opengnsys,torrentcreator,torrenttracker}
# Elminar recursos de OpenGnSys en Samba.
rm -f /etc/samba/smb-og.conf
perl -ni -e "print unless /smb-og.conf/" /etc/samba/smb.conf
for serv in smbd smb ; do
    [ -x /etc/init.d/$serv ] && /etc/init.d/$serv reload
done
# Eliminar usuario de OpenGnSys.
smbpasswd -x $OPENGNSYS_CLIENT_USER
userdel $OPENGNSYS_CLIENT_USER
# Tareas manuales a realizar después de desinstalar.
echo "Manual tasks:"
echo "- You may stop or uninstall manually all other services"
echo "     (DHCP, PXE, TFTP, NFS/Samba, Apache, MySQL)."
echo "- Delete repository directory \"$OPENGNSYS/$OGIMG\""

