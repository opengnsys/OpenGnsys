#!/bin/bash
#/**
#@file    poweroff.sh
#@brief   Script de inicio para cargar el proceso comprobación de clientes inactivos.
#@note    Arranca y configura el proceso "cron".
#@warning License: GNU GPLv3+
#@version 1.0.2
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2011-10-25
#*/


# Si está configurado OpenGnSys ...
if [ -n "$OPENGNSYS" ]; then
    #echo "$MSG_POWEROFFCONF"

    # Crear fichero de configuración por defecto (30 min. de espera).
    POWEROFFCONF=/etc/poweroff.conf
    cat << FIN > $POWEROFFCONF
POWEROFFSLEEP=30
POWEROFFTIME=
FIN

    # Lanzar el proceso "cron".
    cron -l

    # Definir la "crontab" lanzando el proceso de comprobación cada minuto.
    echo "* * * * *   [ -x $OGBIN/poweroffconf ] && $OGBIN/poweroffconf" | crontab -

else
    # FIXME Error: entorno de OpenGnSys no configurado.
    echo "Error: OpenGnSys environment is not configured."   # FIXME: definir mensaje.
    exit 1
fi

