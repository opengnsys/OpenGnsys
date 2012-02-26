#!/bin/bash
#/**
#@file    loadmodules.sh
#@brief   Script de inicio para cargar módulos complementarios del kernel.
#@version 1.0
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2010-01-26
#*/


echo "${MSG_LOADMODULES:-.}"

# Módulo del ratón.
modprobe psmouse 2>/dev/null


