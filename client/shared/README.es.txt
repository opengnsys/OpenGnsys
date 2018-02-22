OpenGsSys Client    README
==========================


Este directorio contiene la estructura principal de datos que será
importada por los cleintes OpenGnsys mediante Samba (o NFS en las
primeras versiones del Proyecto).

Los subdirectorios se copian íntegramente al servidor bajo
/opt/opengnsys/client y serán importados por los clientes en
/opt/opengnsys.

La estructura de datos es la siguiente:

- bin       scripts o binarios ejecutables por el cliente (compilados
            estáticamente).
- cache     directorio donde se montará la caché local del cliente.
- etc       ficheros de configuración del cliente.
- lib       librerías de funciones.
   - engine/bin   ficheros con las funciones del motor de clonación.
   - httpd        ficheros de configuración del servicio lighttpd.
   - modules      módulos extra para el Kernel del cliente.
   - qtlib        librerías Qt complementarias del Browser.
   - qtplugins    plugins Qt para el Browser.
- images    repositorio de imágenes de sistemas operativos.
- log       registro de incidencias de los clientes.
- scripts   funciones de alto nivel ejecutables por OpenGnsys Browser
            y OpenGnsys Admin.

