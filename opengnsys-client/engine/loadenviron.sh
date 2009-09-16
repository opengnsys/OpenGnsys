#!/bin/bash
#/**
#@file    loadenviron.sh
#@brief   Script de carga de la API de funciones.
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-09-16
#*/

# FIXME Temporal
export LANG=${LANG:-es_ES}

# Directorios del projecto OpenGNSys.
OPENGNSYS=${OPENGNSYS:-"/opt/opengnsys"}
if [ -d $OPENGNSYS ]; then
    export OGBIN=$OPENGNSYS/bin
    export OGETC=$OPENGNSYS/etc
    export OGLIB=$OPENGNSYS/lib
    export OGAPI=$OGLIB/engine/bin
    export OGIMG=$OPENGNSYS/images
    export OGCAC=$OPENGNSYS/cache
    export OGLOG=$OPENGNSYS/log

    export PATH=$OGBIN:$OGAPI:$PATH
    export LD_LIBRARY_PATH=$OGLIB:$LD_LIBRARY_PATH


    export OG_DHCP_SERVER=`grep -h dhcp-server-identifier /var/lib/dhcp3/dhclient.* | sed 's/[^0-9]*\(.*\);/\1/' | head -1`
    export OG_SERVER_IP=$OG_DHCP_SERVER
    export OG_IP=`grep -h fixed-address /var/lib/dhcp3/dhclient.* | sed 's/[^0-9]*\(.*\);/\1/' | head -1`

    export OGLOGFILE=$OGLOG/$OG_IP.log

    # FIXME Pruebas para grupos de ordenadores
    export OGGROUP=aula3

    # Incluimos el modulo del raton
    insmod $OGLIB/modules/psmouse.ko

    # Crear directorio de bloqueos
    mkdir -p /var/lock

    # Montamos el resto de cosas necesarias
    mount -t nfs -onolock $DHCP_SERVER:/opt/opengnsys/log/clients $OGLOG
    mount -t nfs -onolock $DHCP_SERVER:/opt/opengnsys/images $OGIMG
    if [ mount -t nfs -onolock $DHCP_SERVER:/opt/opengnsys/cache $OGIMG ]; then
        export OGCACHE=1;
    else
        export OGCACHE=0;
    fi

    #/// Cargar API de funciones y fichero de idioma.
    for i in $OGAPI/*.lib; do
        source $i 
    done
    for i in $(typeset -F | cut -f3 -d" "); do
	export -f $i
    done
    LANGFILE=$OGETC/lang.$LANG.conf
    if [ -f $LANGFILE ]; then
	source $LANGFILE
	for i in $(grep "^[a-zA-Z].*=" $LANGFILE | cut -f1 -d=); do
	    export $i
	done
    fi

    # FIXME Necesario temporalmente
    mkdir -p /usr/local/Trolltech/QtEmbedded-4.5.1/lib/
    ln -s $OGLIB/fonts /usr/local/Trolltech/QtEmbedded-4.5.1/lib/fonts
fi

#/// Declaración de códigos de error.
export OG_ERR_FORMAT=1		# Formato de ejecucion incorrecto.
export OG_ERR_NOTFOUND=2	# Fichero o dispositivo no encontrado.
export OG_ERR_PARTITION=3	# Error en particion de disco.
export OG_ERR_LOCKED=4		# Particion o fichero bloqueado.
export OG_ERR_IMAGE=5		# Error al crear o restaurar una imagen.
export OG_ERR_NOTOS=6		# Sin sistema operativo.
export OG_ERR_NOTEXEC=7         # Programa o funcion no ejecutable.


