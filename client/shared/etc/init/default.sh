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

# Cargar idioma.
echo "${MSG_LAUNCHCLIENT:-.}"
# Indicar fichero de teclado de Qt para el idioma especificado (tipo "es.qmap").
[ -f /usr/local/etc/${LANG%_*}.qmap ] && export QWS_KEYBOARD="TTY:keymap=/usr/local/etc/${LANG%_*}.qmap"

source /scripts/client.cfg
VERSION="3.0.0-20190520"    # TEMPORAL
if [ -f "$OPENGNSYS/images/ogagent-oglive_${VERSION}_all.deb" -a "$ogstatus" != "offline"  ]; then
    # Instalar, configurar e iniciar agente.
    dpkg -i "$OPENGNSYS/images/ogagent-oglive_${VERSION}_all.deb"
    sed -i -e "s,remote=.*,remote=https://$(ogGetServerIp)/opengnsys3/backend/web/app_dev.php/," \
           -e "s,client=.*,client=$CLIENTID," \
           -e "s,secret=.*,secret=$CLIENTSECRET," \
           /usr/share/OGAgent/cfg/ogagent.cfg
    ogagent start
    while : ; do
        sleep 60
        [ $(pgrep -fac OGAgent) -eq 0 ] && ogagent restart
    done
elif [ -x "$OPENGNSYS/bin/ogAdmClient" -a "$ogstatus" != "offline"  ]; then
    # Ejecutar servicio cliente.
    $OPENGNSYS/bin/ogAdmClient -f $OPENGNSYS/etc/ogAdmClient.cfg -l $OGLOGFILE -d $LOGLEVEL
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


