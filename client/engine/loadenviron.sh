#!/bin/bash
#/**
#@file    loadenviron.sh
#@brief   Script de carga de la API de funciones de OpenGNSys.
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-09-16
#*/

# FIXME Temporal
export LANG="${LANG:-es_ES}"

#/// Directorios del projecto OpenGNSys.
export OPENGNSYS="${OPENGNSYS:-/opt/opengnsys}"
if [ -d $OPENGNSYS ]; then
    export OGBIN=$OPENGNSYS/bin
    export OGETC=$OPENGNSYS/etc
    export OGLIB=$OPENGNSYS/lib
    export OGAPI=$OGLIB/engine/bin
    export OGSCRIPTS=$OPENGNSYS/scripts
    export OGIMG=$OPENGNSYS/images
    export OGCAC=$OPENGNSYS/cache
    export OGLOG=$OPENGNSYS/log

    export PATH=$OGBIN:$OGAPI:$OGSCRIPTS:$PATH
    export LD_LIBRARY_PATH=$OGLIB:$LD_LIBRARY_PATH

    # Para tener /bin/bash y no haya problemas
    ln -fs $OGBIN/bash /bin/bash

    # Obtener IP del servidor DHCP/NFS
    SERVERIP=$(awk '/dhcp-server-identifier/ {sub(/;/,""); dhcp=$3}
		    END {print dhcp}' \
	 		/var/lib/dhcp3/dhclient.leases)

    export OGLOGFILE=$OGLOG/$OG_IP.log

    # FIXME Pruebas para grupos de ordenadores
    export OGGROUP=aula3

    # Incluimos el modulo del raton
    insmod $OGLIB/modules/psmouse.ko

    # Crear directorio de bloqueos
    mkdir -p /var/lock

    # Montamos el resto de cosas necesarias
    mount -t nfs -o nolock $SERVERIP:/opt/opengnsys/log/clients $OGLOG
    mount -t nfs -o nolock $SERVERIP:/opt/opengnsys/images $OGIMG


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
    ln -fs $OGLIB/fonts /usr/local/Trolltech/QtEmbedded-4.5.1/lib/fonts

    # FIXME Datos de dispositivos PCI en /etc
    ln -fs $OGLIB/pci.ids /etc

    # Cargar paquetes udev
    for i in $OGLIB/udeb/*.udeb; do
        udpkg -i "$i" >/dev/null && echo "$(basename $i) $MSG_INSTALLED"
    done
fi

#/// Declaración de códigos de error.
export OG_ERR_FORMAT=1		# Formato de ejecución incorrecto.
export OG_ERR_NOTFOUND=2	# Fichero o dispositivo no encontrado.
export OG_ERR_PARTITION=3	# Error en partición de disco.
export OG_ERR_LOCKED=4		# Partición o fichero bloqueado.
export OG_ERR_IMAGE=5		# Error al crear o restaurar una imagen.
export OG_ERR_NOTOS=6		# Sin sistema operativo.
export OG_ERR_NOTEXEC=7     # Programa o función no ejecutable.


