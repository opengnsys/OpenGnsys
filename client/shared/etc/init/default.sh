#!/bin/bash

# TODO Separar esta sección en otro script


# Lanzar servicios complementarios del cliente.
PASS=$(grep "^[ 	]*\(export \)\?OPTIONS=" /scripts/ogfunctions 2>&1 | \
	sed 's/\(.*\)pass=\(\w*\)\(.*\)/\2/')
PASS=${PASS:-"og"}
echo -ne "$PASS\n$PASS\n" | passwd root 2>/dev/null

# Inicio del servidor sshd
/usr/sbin/sshd

# Desactivado apagado de monitor
#setterm -blank 0 -powersave off -powerdown 0 < /dev/console > /dev/console 2>&1

# Activado WOL en la interfaz usada en arranque pxe
ethtool -s $DEVICE wol g 2>/dev/null

# Fichero de registro de incidencias (en el servidor; si no, en local).
OPENGNSYS=${OPENGNSYS:-/opt/opengnsys}
OGLOGFILE=${OGLOGFILE:-$OPENGNSYS/log/${ogGetIpAdderss},log}
if ! touch $OGLOGFILE 2>/dev/null; then
    OGLOGFILE=/var/log/opengnsys.log
fi
LOGLEVEL=5

#Facilitando el entorno Og desde ssh
cp $OPENGNSYS/etc/preinit/loadenviron.sh /etc/profile.d/



########## PRUEBAS
# Crear menú por defecto para el cliente
generateMenuDefault

# Matando plymount para inicir browser o shell
pkill -9 plymouthd

[ -f /opt/opengnsys/scripts/runhttplog.sh ] && /opt/opengnsys/scripts/runhttplog.sh

########## FIN PRUEBAS

# Arranque de OpenGnSys Client daemon (web services).
if [ -x $OPENGNSYS/job_executer/init.d/job_executer ]; then
    echo "Running Opengnsys client daemon (web services)"
    $OPENGNSYS/job_executer/init.d/job_executer restart
fi

# Arranque de OpenGnSys Client daemon (socket).
if [ -x "$OPENGNSYS/bin/ogAdmClient" ]; then
    echo "$MSG_LAUNCHCLIENT"
    [ $ogactiveadmin == "true" ] && boot=admin
    $OPENGNSYS/bin/ogAdmClient -f $OPENGNSYS/etc/ogAdmClient.cfg -l $OGLOGFILE -d $LOGLEVEL
fi

# Si fallo en cliente y modo "admin", cargar shell; si no, salir.
if [ "$boot" == "admin" ]; then
    bash
fi
