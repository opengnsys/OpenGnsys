#!/bin/bash

### NOTA este archivo se sobreescribe al conectarse con el ogSHARE




#/**
#@file    loadenviron.sh
#@brief   Script de carga de la API de funciones de OpenGNSys.
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-10-10
#*/

	GLOBAL="cat /proc/cmdline"
	for i in `${GLOBAL}`
	do
		echo $i | grep "=" > /dev/null && export $i
	done


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

#    export PATH=$OGBIN:$OGAPI:$OGSCRIPTS:$PATH
 #   export LD_LIBRARY_PATH=$OGLIB:$LD_LIBRARY_PATH

    #/// Cargar fichero de idioma.
    LANGFILE=$OGETC/lang.$LANG.conf
    if [ -f $LANGFILE ]; then
	source $LANGFILE
	#for i in $(grep "^[a-zA-Z].*=" $LANGFILE | cut -f1 -d=); do
	for i in $(awk -F= '{if (NF==2) print $1}' $LANGFILE); do
	    export $i
	done
    fi
    #/// Cargar API de funciones.
    echo "$MSG_LOADAPI"
    for i in $OGAPI/*.lib; do
        source $i
    done
    for i in $(typeset -F | cut -f3 -d" "); do
	export -f $i
    done
    # Carga de las API testing
    if [ "$engine" = "testing" ]
    then
    	for i in $OGAPI/*.testing; do
        	source $i 
    	done
    fi
    
    source /tmp/net-eth* 2>/dev/null
    
    # Añadir dependencia de arquitectura
    ARCH=$(ogGetArch)
    if [ -n "$ARCH" ]; then
 #       export PATH=$OGBIN/$ARCH:$PATH
  #      export LD_LIBRARY_PATH=$OGLIB/$ARCH:$LD_LIBRARY_PATH
 	export PATH=$PATH:/sbin:/usr/sbin:/usr/local/sbin:/bin:/usr/bin:/usr/local/bin:/opt/og2fs/2ndfs/opt/drbl/sbin
       	export PATH=$OGSCRIPTS:$PATH:$OGAPI:$OGBIN:$OGBIN/$ARCH
  fi
    # Fichero de registros.
    export OGLOGFILE="$OGLOG/$(ogGetIpAddress).log"
    # FIXME Pruebas para grupos de ordenadores
    #export OGGROUP=$(ogGetGroup)
    export OGGROUP=aula3
fi

#/// Declaración de códigos de error.
export OG_ERR_FORMAT=1		# Formato de ejecución incorrecto.
export OG_ERR_NOTFOUND=2	# Fichero o dispositivo no encontrado.
export OG_ERR_PARTITION=3	# Error en partición de disco.
export OG_ERR_LOCKED=4		# Partición o fichero bloqueado.
export OG_ERR_IMAGE=5		# Error al crear o restaurar una imagen.
export OG_ERR_NOTOS=6		# Sin sistema operativo.
export OG_ERR_NOTEXEC=7		# Programa o función no ejecutable.


