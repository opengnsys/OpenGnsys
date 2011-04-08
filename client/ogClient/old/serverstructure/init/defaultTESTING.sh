#!/bin/bash

/etc/init.d/ssh restart
#setterm -blank 0 -powersave off -powerdown 0 < /dev/console > /dev/console 2>&1


# Fichero de registro de incidencias (en el servidor; si no, en local).
OPENGNSYS=${OPENGNSYS:-/opt/opengnsys}
OGLOGFILE=${OGLOGFILE:-$OPENGNSYS/log/${ogGetIpAdderss},log}
if ! touch $OGLOGFILE 2>/dev/null; then
    OGLOGFILE=/var/log/opengnsys.log
fi


#TODO  => activacion de modo escritura en REPO
ActiveAdmin=true
if [ $ActiveAdmin == "true" ]
then 
	export boot=admin
	umount /opt/opengnsys/images

	if [ "$ogprotocol" == "nfs" ]
	then 
		mount.nfs ${ROOTSERVER}:/opt/opengnsys/images /opt/opengnsys/images -o nolock
	fi
	
	if [ "$ogprotocol" == "smb" ]
	then 
	echo "montando smb"
		mount.cifs //${ROOTSERVER}/ogimages /opt/opengnsys/images -o user=opengnsys,pass=og
	echo mount.cifs //${ROOTSERVER}/ogimages /opt/opengnsys/images -o user=opengnsys,pass=og
	
	fi
fi
# Arranque de OpenGnSys Client.
if [ -x "$OPENGNSYS/bin/ogAdmClient" ]; then

	
    echo "$MSG_LAUNCHCLIENT modo cliente full"
    $OPENGNSYS/bin/ogAdmClient -f $OPENGNSYS/etc/ogAdmClient.cfg -l $OGLOGFILE -d 5
fi

# Si fallo en cliente y modo "admin", cargar shell; si no, salir.
#if [ "$boot" == "admin" ]; then
    bash
#fi
