#!/bin/bash

echo -ne "og\nog\n" | passwd root

/etc/init.d/ssh restart
#setterm -blank 0 -powersave off -powerdown 0 < /dev/console > /dev/console 2>&1

ethtool -s $DEVICE wol g

# Fichero de registro de incidencias (en el servidor; si no, en local).
OPENGNSYS=${OPENGNSYS:-/opt/opengnsys}
OGLOGFILE=${OGLOGFILE:-$OPENGNSYS/log/${ogGetIpAdderss},log}
if ! touch $OGLOGFILE 2>/dev/null; then
    OGLOGFILE=/var/log/opengnsys.log
fi




if [ $ogactiveadmin == "true" ]
then 
	export boot=admin
fi


# Arranque de OpenGnSys Client.
if [ -x "$OPENGNSYS/bin/ogAdmClient" ]; then
    echo "$MSG_LAUNCHCLIENT"
    $OPENGNSYS/bin/ogAdmClient -f $OPENGNSYS/etc/ogAdmClient.cfg -l $OGLOGFILE -d 5
fi

# Si fallo en cliente y modo "admin", cargar shell; si no, salir.
if [ "$boot" == "admin" ]; then
    bash
fi
