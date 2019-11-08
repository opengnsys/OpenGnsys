#!/bin/bash
#/**
#@file    loadenviron.sh
#@brief   Script de carga de la API de funciones de OpenGnsys.
#@warning License: GNU GPLv3+
#@version 0.9
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2009-10-10
#@version 1.0.3 - Limpiar código y configuración modo off-line
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2012-01-12
#@version 1.0.5 - Compatibilidad para usar proxy y servidor DNS.
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2014-04-23
#*/

# Idioma por defecto.
export LANG="${LANG:-es_ES}"
locale-gen $LANG

# Directorios del proyecto OpenGnsys.
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

    export PATH=$PATH:/sbin:/usr/sbin:/usr/local/sbin:/bin:/usr/bin:/usr/local/bin:/opt/oglive/rootfs/opt/drbl/sbin
 
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
    # Mensaje de carga del entorno.
    echo "${MSG_LOADAPI:-.}"

    # Cargar mapa de teclado.
    loadkeys ${LANG%_*} >/dev/null

    # Cargar API de funciones.
    for i in $OGAPI/*.lib; do
        source $i
    done

    for i in $(typeset -F | cut -f3 -d" "); do
	export -f $i
    done

    # Cargar configuración del engine.
    [ -f ${OGETC}/engine.cfg ] && source ${OGETC}/engine.cfg
    export OGLOGCOMMAND=${OGLOGCOMMAND:-/tmp/command.log}
    export OGLOGSESSION=${OGLOGSESSION:-/tmp/session.log}
    
    # Cargar las APIs según engine.
    if [ -n "$ogengine" ]; then
    	for i in $OGAPI/*.$ogengine; do
            [ -f $i ] && source $i 
    	done
    fi
    
    # Configuración de la red (modo offline).
    eval $(grep "^DEVICECFG=" /tmp/initrd.cfg 2>/dev/null)
    if [ -n "$DEVICECFG" ]; then
        export DEVICECFG
        [ -f $DEVICECFG ] && source $DEVICECFG
    fi
    
    # FIXME Pruebas para grupos de ordenadores
    export OGGROUP="$group"
    
    ROOTREPO=${ROOTREPO:-"$OGSERVERIMAGES"}
   
    # Fichero de registros.
    export OGLOGFILE="$OGLOG/$(ogGetIpAddress).log"
fi

# Compatibilidad para usar proxy en clientes ogLive.
[ -z "$http_proxy" -a -n "$ogproxy" ] && export http_proxy="$ogproxy" 

# Compatibilidad para usar servidor DNS en clientes ogLive.
if [ ! -f /run/resolvconf/resolv.conf -a -n "$ogdns" ]; then
	mkdir -p /run/resolvconf
	echo "nameserver $ogdns" > /run/resolvconf/resolv.conf
fi

# Declaración de códigos de error.
export OG_ERR_FORMAT=1		# Formato de ejecución incorrecto.
export OG_ERR_NOTFOUND=2	# Fichero o dispositivo no encontrado.
export OG_ERR_PARTITION=3	# Error en partición de disco.
export OG_ERR_LOCKED=4		# Partición o fichero bloqueado.
export OG_ERR_IMAGE=5		# Error al crear o restaurar una imagen.
export OG_ERR_NOTOS=6		# Sin sistema operativo.
export OG_ERR_NOTEXEC=7		# Programa o función no ejecutable.
# Códigos 8-13 reservados por ogAdmClient.h
export OG_ERR_NOTWRITE=14	# No hay acceso de escritura
export OG_ERR_NOTCACHE=15	# No hay particion cache en cliente
export OG_ERR_CACHESIZE=16	# No hay espacio en la cache para almacenar fichero-imagen
export OG_ERR_REDUCEFS=17	# Error al reducir sistema archivos
export OG_ERR_EXTENDFS=18	# Error al expandir el sistema de archivos
export OG_ERR_OUTOFLIMIT=19	# Valor fuera de rango o no válido.
export OG_ERR_FILESYS=20	# Sistema de archivos desconocido o no se puede montar
export OG_ERR_CACHE=21 		# Error en partición de caché local
export OG_ERR_NOGPT=22		# El disco indicado no contiene una particion GPT
export OG_ERR_REPO=23		# Error al montar el repositorio de imagenes

export OG_ERR_IMGSIZEPARTITION=30    # Error al restaurar partición más pequeña que la imagen
export OG_ERR_UPDATECACHE=31	# Error al realizar el comando updateCache
export OG_ERR_DONTFORMAT=32	# Error al formatear
export OG_ERR_IMAGEFILE=33	# Archivo de imagen corrupto o de otra versión de $IMGPROG
export OG_ERR_GENERIC=40 	# Error imprevisto no definido
export OG_ERR_UCASTSYNTAXT=50   # Error en la generación de sintaxis de transferenica UNICAST
export OG_ERR_UCASTSENDPARTITION=51  # Error en envío UNICAST de partición
export OG_ERR_UCASTSENDFILE=52  # Error en envío UNICAST de un fichero
export OG_ERR_UCASTRECEIVERPARTITION=53  # Error en la recepcion UNICAST de una particion
export OG_ERR_UCASTRECEIVERFILE=54   # Error en la recepcion UNICAST de un fichero
export OG_ERR_MCASTSYNTAXT=55   # Error en la generacion de sintaxis de transferenica Multicast.
export OG_ERR_MCASTSENDFILE=56  # Error en envio MULTICAST de un fichero
export OG_ERR_MCASTRECEIVERFILE=57   # Error en la recepcion MULTICAST de un fichero
export OG_ERR_MCASTSENDPARTITION=58  # Error en envio MULTICAST de una particion
export OG_ERR_MCASTRECEIVERPARTITION=59  # Error en la recepcion MULTICAST de una particion
export OG_ERR_PROTOCOLJOINMASTER=60  # Error en la conexion de una sesion UNICAST|MULTICAST con el MASTER

export OG_ERR_DONTMOUNT_IMAGE=70 # Error al montar una imagen sincronizada.
export OG_ERR_DONTSYNC_IMAGE=71 # Imagen no sincronizable (es monolitica)
export OG_ERR_DONTUNMOUNT_IMAGE=72 # Error al desmontar la imagen
export OG_ERR_NOTDIFFERENT=73	# No se detectan diferencias entre la imagen basica y la particion.
export OG_ERR_SYNCHRONIZING=74  # Error al sincronizar, puede afectar la creacion/restauracion de la imagen

export OG_ERR_NOTUEFI=80	# La interfaz UEFI no está activa
