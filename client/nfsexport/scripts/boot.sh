#!/bin/bash
# Scirpt de ejemplo para arancar un sistema operativo instalado.
# (puede usarse como base para el programa de arranque usado por OpenGNSys Admin).

PROG="$(basename $0)"
if [ $# -ne 2 ]; then
    ogRaiseError $OG_ERR_FORMAT "Formato: $PROG ndisco nparticion"
    exit $?
fi

# Procesos previos.
PART=$(ogDiskToDev "$1" "$2") || exit $?
NAME=$(ogGetHostname)
NAME=${NAME:-"pc"}

# Arrancar.
ogEcho info "$PROG: Desmontar todos los sistemas operativos del disco."
ogUnmountAll $1 | exit $?
case "$(ogGetOsType $1 $2)" in
    Windows)
        ogEcho info "$PROG: Activar partición de Windows $PART."
        ogSetPartitionActive $1 $2
        ogEcho info "$PROG: Comprobar sistema de archivos."
        ogCheckFs $1 $2
        NAME=$(ogGetHostname)
        ogEcho info "$PROG: Asignar nombre Windows \"$NAME\"."
        ogSetWindowsName $1 $2 "$NAME"
        ;;
    Linux)
        ogEcho info "$PROG: Asignar nombre Linux \"$NAME\"."
        ETC=$(ogGetPath $1 $2 /etc)
        [ -d "$ETC" ] && echo "$NAME" >$ETC/hostname 2>/dev/null
        if [ -f "$ETC/fstab" ]; then
            ogEcho info "$PROG: Actaualizar fstab con partición raíz \"$PART\"."
            awk -v P="$PART " '{ if ($2=="/") {sub(/^.*$/, P, $1)}
                                 print } ' $ETC/fstab >/tmp/fstab
            mv /tmp/fstab $ETC/fstab
        fi
        ;;
esac
ogEcho info "$PROG: Arrancar sistema operativo."
ogBoot $1 $2

