Scripts de gestión de clientes de OpenGNSys Server
==================================================

En este directorio se incluyen los scripts y ficheros necesarios
para configurar los clientes, ejecutables directamente desde el
servidor de OpenGNSys.


upgrade-clients-udeb.sh     (copiar en /opt/opengnsys/bin)
    Script de descarga de paquetes udeb, que serán incluidos en
    el directorio de librerías importadas por los clientes.

udeblist.conf               (copiar en /opt/opengnsys/etc)
    Fichero de configuración que incluye la lista de paquetes
    udeb que deben ser descargados o eliminados.
    El formato de las líneas del fichero es:
         install:paquete
         remove:paquete
