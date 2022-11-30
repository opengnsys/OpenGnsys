~# rm /etc/resolv.conf
~# echo nameserver 172.16.3.20 > /etc/resolv.conf

~# chmod 1777 /tmp

~# apt update
~# apt install php-xml php-sqlite3 php-cgi 

Para desarrollo instalar "composer"

Configurar lighttpd para procesar php, activar mod_fastcgi y añadir la configuracion fastcgi.server

~# vi /opt/opengnsys/client/lib/httpd/10-cgi.conf
----------------------------------------------------------
server.modules += ( "mod_cgi", "mod_fastcgi" )

$HTTP["url"] =~ "^/cgi-bin/" {
alias.url += ( "/cgi-bin/" => "/usr/lib/cgi-bin/" )
$HTTP["url"] =~ "^/cgi-bin/" {
cgi.assign = ( ".sh" => "/bin/sh" )
}
}

fastcgi.server = ( ".php" => ((
                     "bin-path" => "/usr/bin/php-cgi",
                     "socket" => "/tmp/php.socket",
                     "max-procs" => 1,
                     "bin-environment" => (
                       "PHP_FCGI_CHILDREN" => "16",
                       "PHP_FCGI_MAX_REQUESTS" => "10000"
                     ),
                     "broken-scriptfilename" => "enable"
                 )))

$SERVER["socket"] == ":8000" {
        server.document-root = "/var/www/html/ogagent/"
        url.rewrite-once = ( "^/([^?]*)" => "/index.php/$1")
        ssl.engine = "enable"
        ssl.pemfile = "/etc/lighttpd/ssl/ogagent.pem"
}

----------------------------------------------------------
~# service lighttpd restart


// Añadir a loadenvirom las variables de entorno necesarias
~# vim ./etc/preinit/loadenviron.sh
----------------------------------------------------------
# Variables para usar el servicio OgAgent de Opengnsys 3
export OGAGENT=$OPENGNSYS/ogagent
export OGAGENTCONSOLE="php $OGAGENT/bin/console"


// Retocar el proceso de arranque para copiar el cliente symfony
~# vim ./etc/preinit/otherservices.sh
----------------------------------------------------------
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

----------------------------------------------------------

// Modificar scripts en /opt/opengnsys/client/interfaceAdm
~# vim ./interfaceAdm/InventarioSoftware
----------------------------------------------------------
$OGAGENTCONSOLE SoftwareInventory $file $1 $2
----------------------------------------------------------

~# vim ./interfaceAdm/CrearImagen
----------------------------------------------------------
$OGAGENTCONSOLE CreateImage $1 $2 $3 $OGIMG
----------------------------------------------------------

~# vim ./interfaceAdm/InventarioHardware
----------------------------------------------------------
$OGAGENTCONSOLE HardwareInventory $file
----------------------------------------------------------


// Modificar scripts en /opt/opengnsys/client/scripts
~# vim ./scripts/poweroff
----------------------------------------------------------
# Informar al server og3
source /opt/opengnsys/etc/preinit/loadenviron.sh > /dev/null 2> /dev/null
$OGAGENTCONSOLE SendStatus 0
----------------------------------------------------------
~# vim ./scripts/reboot
----------------------------------------------------------
# Informar al server og3
$OGAGENTCONSOLE SendStatus 0
----------------------------------------------------------

