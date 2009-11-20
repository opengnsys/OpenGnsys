#!/opt/opengnsys/bin/bash

OPENGNSYS=${OPENGNSYS:-/opt/opengnsys}
OGLOGFILE=${OGLOGFILE:-/var/log/opengnsys.log}
#### FIXME   EN PRUEBAS
OGLOGFILE=/var/log/opengnsys.log

if [ -x "$OPENGNSYS/bin/ogAdmClient" ]; then
    echo "$MSG_LAUNCHCLIENT"
    $OPENGNSYS/bin/ogAdmClient -f $OPENGNSYS/etc/ogAdmClient.cfg -l $OGLOGFILE
fi

bash
# FIXME   Arranque Browser
#browser -qws $OGSTARTPAGE
