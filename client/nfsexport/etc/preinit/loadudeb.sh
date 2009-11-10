#!/bin/bash
#/**
#@file    loadudeb.sh
#@brief   Script de inicio para cargar paquetes udeb en el cliente.
#@note    Desglose del script "loadenviron.sh".
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-10-10
#*/


# Si está configurado OpenGNSys ...
if [ -n "$OPENGNSYS" ]; then
    # Cargar paquetes udev
    echo "$MSG_LOADUDEBS"
    for i in $OGLIB/udeb/*.udeb; do
        udpkg -i "$i" >/dev/null || printf "$MSG_ERRLOADUDEB\n" $(basename $i)
    done
else
    # FIXME Error: entorno de OpenGNSys no configurado.
    echo "Error: OpenGNSys environment is not configured."   # FIXME: definir mensaje.
    exit 1
fi

