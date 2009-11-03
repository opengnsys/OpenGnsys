#!/bin/bash
# Scirpt de ejemplo para restaurar una imagen.
# (puede usarse como base para el programa de restauración de imágenes usado por OpenGNSys Admin).

TIME1=$SECONDS
PROG="$(basename $0)"
if [ $# -ne 4 ]; then
    ogRaiseError $OG_ERR_FORMAT "Formato: $PROG ndisco nparticion REPO|CACHE imagen"
    exit $?
fi

# Procesar parámetros de entrada
IMGFILE=$(ogGetPath "$1" "$2.img")
if [ "$1" == "CACHE" -o "$1" == "cache" ]; then
    IMGDIR=$(ogGetParentPath "$1" "$2")
    # Si no existe el directorio de la imagen, crearlo.
    if [ -z "$IMGDIR" ]; then
        echo "Creando directorio de imagen \"$1, ${2%/*}\"."
        ogMakeDir "$1" "${2%/*}" || ogRaiseError $OG_ERR_NOTFOUND "$1, ${2%/*}" || exit $?
    fi
    IMGDIR=$(ogGetParentPath "$1" "$2") || ogRaiseError $OG_ERR_NOTFOUND "$1, ${2%/*}" || exit $?
    if [ -z "$IMGFILE" ]; then
        echo "Copiando imagen \"$2\" del repositorio a caché local"
        ogCopyFile "repo" "$2.img" "$IMGDIR" || exit $?
        IMGFILE=$(ogGetPath "cache" "$2.img")
    fi
fi
if [ -z "$IMGFILE" ]; then
    ogRaiseError $OG_ERR_NOTFOUND "$1, $2"
    exit $?
fi
PART=$(ogDiskToDev "$3" "$4") || exit $?

# Restaurar la imagen.
ogEcho info "$PROG: Origen=$IMGFILE, Destino=$PART"
ogRestoreImage "$@" || exit $?
# Restaurar tamaño.
ogEcho info "$PROG: Extender sistema de archivos."
ogExtendFs $3 $4
# Cambiar nombre en sistemas Windows.
if [ "$(ogGetOsType $3 $4)" = "Windows" ]; then
    HOST=$(ogGetHostname)
    HOST=${HOST:-"UNKNOWN"}
    ogEcho info "$PROG: Cambiar nombre Windows a \"$HOST\"."
    ogSetWindowsName $3 $4 "$HOST"
fi
TIME=$[SECONDS-TIME1]
ogEcho info "$PROG: Duración de la operación $[TIME/60]m $[TIME%60]s"

