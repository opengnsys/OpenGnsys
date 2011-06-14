#!/bin/bash

# TODO Separar esta sección en otro script
# Lanzar servicios complementarios del cliente.
echo -ne "og\nog\n" | passwd root
#Compatibilidad ssh con el boot-tools 1.0.2
/etc/init.d/ssh restart || /usr/sbin/sshd
#setterm -blank 0 -powersave off -powerdown 0 < /dev/console > /dev/console 2>&1
ethtool -s $DEVICE wol g 2>/dev/null

# Fichero de registro de incidencias (en el servidor; si no, en local).
OPENGNSYS=${OPENGNSYS:-/opt/opengnsys}
OGLOGFILE=${OGLOGFILE:-$OPENGNSYS/log/${ogGetIpAdderss},log}
if ! touch $OGLOGFILE 2>/dev/null; then
    OGLOGFILE=/var/log/opengnsys.log
fi
LOGLEVEL=5

#facilitando el entorno Og desde ssh
cp $OPENGNSYS/etc/preinit/loadenviron.sh /etc/profile.d/


# Arranque de OpenGnSys Client.
if [ -x "$OPENGNSYS/bin/ogAdmClient" ]; then
    echo "$MSG_LAUNCHCLIENT"
    [ $ogactiveadmin == "true" ] && boot=admin
    $OPENGNSYS/bin/ogAdmClient -f $OPENGNSYS/etc/ogAdmClient.cfg -l $OGLOGFILE -d $LOGLEVEL
fi

# Si fallo en cliente y modo "admin", cargar shell; si no, salir.
if [ "$boot" == "admin" ]; then
    bash
fi
