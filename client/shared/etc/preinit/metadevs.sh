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


# Si está configurado OpenGnsys ...
if [ -n "$OPENGNSYS" ]; then
    echo "$MSG_DETECTLVMRAID"
    # Detectar metadispositivos LVM.
    vgchange -ay &>/dev/null
    # Detectar metadispositivos RAID.
    dmraid -ay &>/dev/null
else
    # FIXME Error: entorno de OpenGnsys no configurado.
    echo "Error: OpenGnsys environment is not configured."   # FIXME: definir mensaje.
    exit 1
fi

