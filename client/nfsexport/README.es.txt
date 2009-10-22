OpenGNSys Client (NFS root)   README
====================================


Este directorio contiene la estructura principal de datos que
será importada por los cleintes OpenGNSys mediante NFS.

Los subdirectorios se copian íntegramente al servidor bajo
/opt/opengnsys/client, y serán importados por los clientes en
/opt/opengnsys.

La estructura de datos es la siguiente:

- bin       binarios ejecutables por el cliente (compilados
            estáticamente).
- cache     directorio donde se montará la caché local del cliente.
- etc       ficheros de configuración del cliente.
- lib       librerías de funciones.
- lib/engine/bin   directorio donde se copiarán las funciones del
                   motor de clonación.
- images    repositorio de imágenes de sistemas operativos.
- log       registro de incidencias de los clientes.
- scripts   funciones de alto nivel ejecutables por OpenGNSys Browser
            y OpenGNSys Admin.

