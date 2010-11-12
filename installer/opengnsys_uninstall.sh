#!/bin/bash
# Desinstalación de OpenGnSys.


# Parar servicio.
echo "Uninstalling OpenGnSys services."
if [ -x /etc/init.d/opengnsys ]; then
    /etc/init.d/opengnsys stop
    update-rc.d -f opengnsys remove
fi
# Eliminar bases de datos.
echo "Erasing OpenGnSys database."
MYSQLROOT="passwordroot"
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
    mysql -u root -p"$MYSQLROOT" <<<"DROP DATABASE ogBDAdmin;" 2>/dev/null
    mysql -u root -p"$MYSQLROOT" <<<"DROP DATABASE ogAdmBD;" 2>/dev/null
    mysql -u root -p"$MYSQLROOT" <<<"DROP USER 'usuog';" 2>/dev/null
    mysql -u root -p"$MYSQLROOT" <<<"DROP USER 'usuog'@'localhost';" 2>/dev/null
fi
# Eliminar ficheros.
echo "Deleting OpenGnSys files."
for dir in /opt/opengnsys/*; do
    if [ "$dir" != "/opt/opengnsys/images" ]; then
        rm -fr "$dir"
    fi
done
rm -f /etc/init.d/opengnsys /etc/default/opengnsys
# Tareas manuales a realizar después de desinstalar.
echo "Manual tasks:"
echo "- You may stop or uninstall manually all other services"
echo "     (DHCP, PXE, TFTP, NFS, Apache, MySQL)."
echo "- Delete repository directory \"/opt/opengnsys/images\""

