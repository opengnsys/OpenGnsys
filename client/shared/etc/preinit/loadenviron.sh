#!/bin/bash
#/**
#@file    loadenviron.sh
#@brief   Script de carga de la API de funciones de OpenGNSys.
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-10-10
#*/

# Idioma por defecto.
export LANG="${LANG:-es_ES}"

# Directorios del projecto OpenGnSys.
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

    export PATH=$PATH:/sbin:/usr/sbin:/usr/local/sbin:/bin:/usr/bin:/usr/local/bin:/opt/og2fs/2ndfs/opt/drbl/sbin
    export PATH=$OGSCRIPTS:$PATH:$OGAPI:$OGBIN
   
    # Exportar parámetros del kernel.
    for i in $(cat /proc/cmdline); do
        echo $i | grep -q "=" && export $i
    done
   
    # Cargar fichero de idioma.
    LANGFILE=$OGETC/lang.${LANG%@*}.conf
    if [ -f $LANGFILE ]; then
	source $LANGFILE
	for i in $(awk -F= '{if (NF==2) print $1}' $LANGFILE); do
	    export $i
	done
    fi
    echo "$MSG_LOADAPI"

    # Cargar mapa de teclado.
    loadkeys ${LANG%_*} >/dev/null

    # Cargar API de funciones.
    for i in $OGAPI/*.lib; do
        source $i
    done

    for i in $(typeset -F | cut -f3 -d" "); do
	export -f $i
    done

    # Carga de las API segun engine
    if [ -n "$ogengine" ]
    then
    	for i in $OGAPI/*.$ogengine; do
            [ -f $i ] && source $i 
    	done
    fi
   
    # Fichero de registros.
    export OGLOGFILE="$OGLOG/$(ogGetIpAddress).log"
    
    # FIXME Pruebas para grupos de ordenadores
    #export OGGROUP=$(ogGetGroup)
    export OGGROUP="$group"
fi

# Declaración de códigos de error.
export OG_ERR_FORMAT=1		# Formato de ejecución incorrecto.
export OG_ERR_NOTFOUND=2	# Fichero o dispositivo no encontrado.
export OG_ERR_PARTITION=3	# Error en partición de disco.
export OG_ERR_LOCKED=4		# Partición o fichero bloqueado.
export OG_ERR_IMAGE=5		# Error al crear o restaurar una imagen.
export OG_ERR_NOTOS=6		# Sin sistema operativo.
export OG_ERR_NOTEXEC=7		# Programa o función no ejecutable.

