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
    export ENGINECFG=$OGETC/engine.json
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
    eval $(jq -r 'foreach .variables[] as $var (""; "export "+$var.name+"=\""+($var.value|tostring)+"\"")' $ENGINECFG 2>/dev/null)
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
eval $(jq -r 'foreach .errors[] as $err (""; "export "+$err.name+"="+($err.id|tostring))' $ENGINECFG 2>/dev/null)

