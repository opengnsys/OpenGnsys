#!/bin/bash
#/**
#@file    metadevs.sh
#@brief   Script de inicio para detectar metadispositivos LVM y RAID.
#@note    Desglose del script "loadenviron.sh".
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-10-10
#@version 0.9.4
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2010-04-19
#*/


# Si está configurado OpenGNSys ...
if [ -n "$OPENGNSYS" ]; then
    echo "$MSG_DETECTLVMRAID"
    # Detectar metadispositivos LVM.
    vgchange -ay >/dev/null 2>&1
    # Detectar metadispositivos RAID.
    dmraid -ay >/dev/null 2>&1
else
    # FIXME Error: entorno de OpenGNSys no configurado.
    echo "Error: OpenGNSys environment is not configured."   # FIXME: definir mensaje.
    exit 1
fi

