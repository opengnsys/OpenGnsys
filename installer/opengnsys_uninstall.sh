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


####  AVISO: Editar configuración de acceso.
####  WARNING: Edit access configuration
MYSQLROOT="passwordroot"	# Clave de root de MySQL
DATABASE="ogAdmBD"		# Base de datos de administración
DBUSER="usuog"			# Usuario de acceso a la base de datos


####  AVISO: NO EDITAR variables de configuración.
####  WARNING: DO NOT EDIT configuration variables.
OPENGNSYS="/opt/opengnsys"	# Directorio de OpenGnSys
OGIMG="images"			# Directorio de imágenes del repositorio
CLIENTUSER="opengnsys"		# Usuario Samba
OLDDATABASE="ogBDAdmin"		# Antigua base de datos


# Sólo ejecutable por usuario root
if [ "$(whoami)" != 'root' ]; then
    echo "ERROR: this program must run under root privileges!!"
    exit 1
fi


# Parar servicio.
echo "Uninstalling OpenGnSys services."
if [ -x /etc/init.d/opengnsys ]; then
    /etc/init.d/opengnsys stop
    if test which update-rc.d 2>/dev/null; then
        update-rc.d -f opengnsys remove
    else
	chkconfig --del opengnsys
    fi
fi
# Eliminar bases de datos.
echo "Erasing OpenGnSys database."
DROP=1
if ! mysql -u root -p"$MYSQLROOT" <<<"quit" 2>/dev/null; then
    stty -echo
    read -p  "- Please, insert MySQL root password: " MYSQLROOT
    echo ""
    stty echo
    if ! mysql -u root -p"$MYSQLROOT" <<<"quit" 2>/dev/null; then
	DROP=0
	echo "Warning: database not erased."
    fi
fi
if test $DROP; then
    mysql -u root -p"$MYSQLROOT" <<<"DROP DATABASE $OLDDATABASE;" 2>/dev/null
    mysql -u root -p"$MYSQLROOT" <<<"DROP DATABASE $DATABASE;" 2>/dev/null
    mysql -u root -p"$MYSQLROOT" <<<"DROP USER '$DBUSER';" 2>/dev/null
    mysql -u root -p"$MYSQLROOT" <<<"DROP USER '$DBUSER'@'localhost';" 2>/dev/null
fi
# Quitar configuración específica de Apache.
test which a2dissite 2>/dev/null && a2dissite opengnsys
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
smbpasswd -x $CLIENTUSER
userdel $CLIENTUSER
# Tareas manuales a realizar después de desinstalar.
echo "Manual tasks:"
echo "- You may stop or uninstall manually all other services"
echo "     (DHCP, PXE, TFTP, NFS/Samba, Apache, MySQL)."
echo "- Delete repository directory \"$OPENGNSYS/$OGIMG\""

