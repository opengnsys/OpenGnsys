#!/bin/bash

id=$1
script=$2
redirect_uri=$3

echo $id 
echo $script
echo $redirect_uri

IP=$HTTP_HOST

# buscar la configuracion del cliente en su fichero cfg
eval $(cat /opt/opengnsys/etc/ogAdmClient.cfg)

SCRIPT_FILE="/var/tmp/ogAdmClient"
OUTPUT_FILE="/var/tmp/agent_output.log"
ERROR_FILE="/var/tmp/agent_error.log"
LOG_FILE="/var/tmp/agent.log"

# Ejecutar el comando que nos llega y obtener la salida
#OUTPUT=$(/opt/opengnsys/lib/httpd/createTmpShell.sh 2>&1)
echo "#!/bin/bash" > $SCRIPT_FILE
echo ". /etc/profile > /dev/null" >> $SCRIPT_FILE
echo "sendStatusToServer \"busy\"" >> $SCRIPT_FILE
# Mostrar log de consola
echo "pkill -9 browser" >> $SCRIPT_FILE
echo "/opt/opengnsys/bin/browser -qws $UrlMsg &" >> $SCRIPT_FILE
echo -e "${script}" >> $SCRIPT_FILE
echo "pkill -9 browser" >> $SCRIPT_FILE
echo "/opt/opengnsys/bin/browser -qws $UrlMenu &" >> $SCRIPT_FILE
echo "sendStatusToServer \"initializing\"" >> $SCRIPT_FILE
echo "exit \$?" >> $SCRIPT_FILE
#OUTPUT=$(/usr/lib/cgi-bin/executeTmpShell.sh 2>&1)
#sudo su -p - www-data -c '/var/tmp/script.sh' > /var/tmp/output.log  2> /var/tmp/error.log
./exec_root > $OUTPUT_FILE 2> $ERROR_FILE

STATUS=$?

#executeTmpShell.sh)
#OUTPUT=$(sudo $command 2>&1)
echo "OUTPUT: "
cat $OUTPUT_FILE
echo "ERRORS: "
cat $ERROR_FILE

output=`cat $OUTPUT_FILE`
error=`cat $ERROR_FILE`

echo $redirect_uri".json?client=$IP&trace=$id&status=$STATUS&output=$output" >> $LOG_FILE
wget --no-check-certificate --post-data="client=$IP&trace=$id&status=$STATUS&output=$output&error=$error" $redirect_uri".json"
