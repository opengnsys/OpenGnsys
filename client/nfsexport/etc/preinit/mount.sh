#!/bin/bash
#/**
#@file    mount.sh
#@brief   Script de inicio para montar repositorio de OpenGNSys por NFS.
#@note    Desglose del script "loadenviron.sh".
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-10-10
#*/


# Si está configurado OpenGNSys ...
if [ -n "$OPENGNSYS" ]; then
    # Si arranque por DHCP ...
    if grep -q "ip=dhcp" /proc/cmdline; then
        # Obtener IP del servidor DHCP/NFS
        SERVERIP=$(awk '/dhcp-server-identifier/ {sub(/;/,""); dhcp=$3}
                        END {print dhcp}' /var/lib/dhcp3/dhclient.leases)

        # Modos de arranque: admin (rw), user (ro).
        BOOTMODE=$(awk 'BEGIN {RS=" "; FS="="} $1~/boot/ {print $2}' /proc/cmdline)
        BOOTMODE=${BOOTMODE:-"user"}
        case "$BOOTMODE" in
            admin) MOUNTOPTS="rw,nolock" ;; 
            user)  MOUNTOPTS="ro,nolock" ;; 
            *)     # FIXME: Modo de arranque desconocido
                   echo "$MSG_ERRBOOTMODE"
                   MOUNTOPTS="ro,nolock" ;;
        esac
        # Montamos el resto de cosas necesarias
	printf "$MSG_MOUNTREPO\n" $BOOTMODE
        mount -t nfs -o nolock $SERVERIP:/opt/opengnsys/log/clients $OGLOG
        mount -t nfs -o "$MOUNTOPTS" $SERVERIP:/opt/opengnsys/images $OGIMG
    else
        # FIXME  Modo off-line
	echo "$MSG_OFFLINEMODE"
    fi
else
    # FIXME Error: entorno de OpenGNSys no configurado.
    echo "Error: OpenGNSys environment is not configured."   # FIXME: definir mensaje.
    exit 1
fi

