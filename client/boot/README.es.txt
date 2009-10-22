OpenGNSys Client Boot   README
==============================

En este directorio se incluyen los scripts y ficheros, ejecutables
directamente desde el servidor de OpenGNSys, y que son necesarios
para configurar los procesos de arranque de los clientes.


initrd-generator            (copiar en /opt/opengnsys/bin)
    Script de generación de los básicos de arranque del cliente
    (initrd y kernel).

upgrade-clients-udeb.sh     (copiar en /opt/opengnsys/bin)
    Script de descarga de paquetes udeb, que serán incluidos en
    el directorio de librerías importadas por los clientes.

udeblist.conf               (copiar en /opt/opengnsys/etc)
    Fichero de configuración que incluye la lista de paquetes
    udeb que deben ser descargados o eliminados.
    El formato de las líneas del fichero es:
         install:paquete
         remove:paquete
