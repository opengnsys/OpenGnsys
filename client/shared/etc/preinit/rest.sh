#!/bin/bash
#/**
#@file    rest.sh
#@brief   Script para configurar el agente de oglive para atender funciones rest.
#@version 1.0.0
#@author  Juan Manuel Bardallo, SIC Universidad de Huelva
#@date    2022-11-30
#*/

# Configuracion del cliente og3

# Copiar el ogClient symfony
chmod -R 1777 /tmp

# Crear link simbólico
ln -s $OGAGENT/public/ /var/www/html/ogagent
# Crear directorio var para escribir en él
mkdir /var/www/html/var
chown www-data:www-data /var/www/html/var

# Copiar ejecutable php_root y asignar permisos
cp -a $OGAGENT/util/php_root /bin
chmod u=rwx,go=xr,+s /bin/php_root

# Crear base de datos sqlite del cliente
$OGAGENTCONSOLE doctrine:schema:update --force

chown www-data:www-data /var/www/html/var/ogclient.db

# Obtener configuración e informar al server og3 una vez finalice
$OGAGENTCONSOLE GetConfiguration
