#!/bin/bash
# Proceso general de arranque de OpenGnsys Client.


# Fichero de registro de incidencias (en el servidor; si no, en local).
OPENGNSYS=${OPENGNSYS:-/opt/opengnsys}
OGLOGFILE=${OGLOGFILE:-$OPENGNSYS/log/$(ogGetIpAdderss).log}
if ! touch $OGLOGFILE 2>/dev/null; then
    OGLOGFILE=/var/log/opengnsys.log
fi
LOGLEVEL=5

# Matando plymount para inicir browser o shell
pkill -9 plymouthd

# Arranque de OpenGnsys Client daemon (socket).
echo "${MSG_LAUNCHCLIENT:-.}"
# Indicar fichero de teclado de Qt para el idioma especificado (tipo "es.qmap").
[ -f /usr/local/etc/${LANG%_*}.qmap ] && export QWS_KEYBOARD="TTY:keymap=/usr/local/etc/${LANG%_*}.qmap"

if [ "$ogstatus" != "offline"  ]; then
    python3 /opt/opengnsys/ogClient/main.py
else
    for FILE in index $OGGROUP $(ogGetIpAddress)
    do
	[ -f $OGCAC/menus/$FILE.html ] && OGMENU="$OGCAC/menus/$FILE.html"
    done
    $OPENGNSYS/bin/browser -qws $OGMENU
fi

# Si fallo en cliente y modo "admin", cargar shell; si no, salir.
if [ "$ogactiveadmin" == "true" ]; then
    bash
fi


