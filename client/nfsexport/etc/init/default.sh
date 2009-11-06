#!/opt/opengnsys/bin/bash

OPENGNSYS=${OPENGNSYS:-/opt/opengnsys}
if [ -x "$OPENGNSYS/bin/ogAdmClient" ]; then
    $OPENGNSYS/bin/ogAdmClient -f $OPENGNSYS/etc/ogAdmClient.cfg -l /var/log/ogAdmClient.log
fi

bash
