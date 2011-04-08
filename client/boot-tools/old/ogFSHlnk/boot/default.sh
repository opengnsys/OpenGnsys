#!/opt/opengnsys/bin/bash

set -a

OPENGNSYS=${OPENGNSYS:-/opt/opengnsys}
OGLOGFILE=${OGLOGFILE:-/var/log/opengnsys.log}
#### FIXME   EN PRUEBAS
OGLOGFILE=/var/log/opengnsys.log

/opt/opengnsys/etc/init/load2fs.sh
source /opt/opengnsys/etc/preinit/loadenviron.sh
export PATH=/opt/og2fs/bin:$PATH
export PATH=/opt/og2fs/sbin:$PATH
export PATH=$PATH:/opt/og2fs/opt/drbl/sbin:/usr/local/bin:/usr/local/sbin:/usr/bin:/usr/sbin:/bin:/sbin

if [ -x "$OPENGNSYS/bin/ogAdmClient" ]; then
    echo "$MSG_LAUNCHCLIENT"
    $OPENGNSYS/bin/ogAdmClient -f $OPENGNSYS/etc/ogAdmClient.cfg -l $OGLOGFILE
fi

bash
# FIXME   Arranque Browser
#browser -qws $OGSTARTPAGE
