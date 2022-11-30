#Configuración del servidor Opengnsys 3

##Oglive
En el ogLive es necesario instalar los siguientes módulos php:

*~# apt install php-xml php-sqlite3 php-cgi*

##Servidor Opengnsys

Copiar este proyecto en /opt/opengnsys/client/lib/ogagent

###Servidor lighttpd para el cliente

Configurar lighttpd para procesar php, activar mod_fastcgi, mod_rewrite y añadir la configuracion fastcgi.server


*~# vim /etc/lighttpd/conf-enabled/10-cgi.conf*
```
server.modules += ( "mod_cgi", "mod_fastcgi", "mod_rewrite" )

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
```
###Ficheros de configuración del entorno del cliente

Modificar los siguientes ficheros en el servidor OG

*~# vim /opt/opengnsys/client/etc/preinit/loadenviron.sh:32*
```
# Variables para usar el servicio OgAgent de Opengnsys 3
export OGAGENT=$OPENGNSYS/lib/ogagent
export OGAGENTCONSOLE="php $OGAGENT/bin/console"
```


*~# vim /opt/opengnsys/client/etc/preinit/rest.sh*
```
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
```

###Scripts de interfaceAdm del cliente

*~# vim /opt/opengnsys/client/interfaceAdm/CrearImagen:91*
```
# Comunicar resultado al servidor og3
$OGAGENTCONSOLE CreateImage $1 $2 $3 $OGIMG
```
*~# vim /opt/opengnsys/client/interfaceAdm/InventarioSoftware:20*
```
# Enviar informacion al servidor og3
$OGAGENTCONSOLE SoftwareInventory $file $1 $2
```

*~# vim /opt/opengnsys/client/interfaceAdm/InventarioHardware:7*
```
# Enviar informacion al servidor og3
$OGAGENTCONSOLE HardwareInventory $file
```
###Scripts del cliente

*~# vim /opt/opengnsys/client/scripts/runhttplog.sh:8*
```
# Crear directorio ssl y fichero .pem para https
mkdir /etc/lighttpd/ssl
openssl req -new -x509 -keyout /etc/lighttpd/ssl/ogagent.pem -out /etc/lighttpd/ssl/ogagent.pem -days 365 -nodes -subj '/CN=localhost'
```

*~# vim /opt/opengnsys/client/scripts/poweroff:33*
```
# Informar al server og3
source /opt/opengnsys/etc/preinit/loadenviron.sh > /dev/null 2> /dev/null
$OGAGENTCONSOLE SendStatus 0
```
*~# vim /opt/opengnsys/client/scripts/reboot:47*
```
# Informar al server og3
$OGAGENTCONSOLE SendStatus 0
```






