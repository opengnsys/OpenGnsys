#!/bin/bash
# Proceso general de arranque de OpenGnSys Client.


# Fichero de registro de incidencias (en el servidor; si no, en local).
OPENGNSYS=${OPENGNSYS:-/opt/opengnsys}
OGLOGFILE=${OGLOGFILE:-$OPENGNSYS/log/$(ogGetIpAdderss).log}
if ! touch $OGLOGFILE 2>/dev/null; then
    OGLOGFILE=/var/log/opengnsys.log
fi
LOGLEVEL=5

# Matando plymount para inicir browser o shell
pkill -9 plymouthd

# Arranque de OpenGnSys Client daemon (web services).
if [ -x $OPENGNSYS/job_executer/init.d/job_executer ]; then
    echo "Running Opengnsys client daemon (web services)"
    $OPENGNSYS/job_executer/init.d/job_executer restart
fi

# Arranque de OpenGnSys Client daemon (socket).
if [ -x "$OPENGNSYS/bin/ogAdmClient" ]; then
    echo "${MSG_LAUNCHCLIENT:-.}"
    [ $ogactiveadmin == "true" ] && boot="admin"
    # Indicar fichero de teclado de Qt para el idioma especificado (tipo "es.qmap").
    [ -f /usr/local/etc/${LANG%_*}.qmap ] && export QWS_KEYBOARD="TTY:keymap=/usr/local/etc/${LANG%_*}.qmap"
    # Ejecutar servicio cliente.
    $OPENGNSYS/bin/ogAdmClient -f $OPENGNSYS/etc/ogAdmClient.cfg -l $OGLOGFILE -d $LOGLEVEL
fi

# Si fallo en cliente y modo "admin", cargar shell; si no, salir.
if [ "$boot" == "admin" ]; then
    bash
fi


