#!/bin/bash
#/**
#@file    loadmodules.sh
#@brief   Script de inicio para cargar módulos complementarios del kernel.
#@version 1.0
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2010-01-26
#@version 1.0.5 - Cargar módulos específicos para el cliente.
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2013-11-11
#*/


echo "${MSG_LOADMODULES:-.}"

# Módulo del ratón.
modprobe psmouse 2>/dev/null
modprobe usbmouse 2>/dev/null

# Cargar módulos específicos del kernel del cliente.
for m in $OGLIB/modules/$(uname -r)/*.ko; do
    [ -r $m ] && insmod $m &>/dev/null
done

