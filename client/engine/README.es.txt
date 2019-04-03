
OpenGnsys Client Cloning Engine  README
=======================================

En este directorio se incluirán las funciones del motor de
clonación de OpenGnsys y la documentación asociada.

Este directorio estará localizado en el directorio del servidor
/opt/opengnsys/client/lib/engine/bin

Las funciones serán accesibles por el cliente en el directorio
/opt/opengnsys/lib/engine/bin

OpenGnsys Client Cloning Engine se distribuye en un conjunto de
librerías que incluyen funciones BASH que deben ser exportadas
al entorno del cliente.

Librerías:

- Boot.lib         funciones de arranque y posconfiguración de
                   sistemas operativos.
- Cache.lib        funciones de gestión de la caché local del cliente.
- Disk.lib         funciones de control de dispositivos de disco.
- File.lib         funciones de manipulación de ficheros.
- FileSystem.lib   funciones de gestión de sistemas de ficheros.
- Image.lib        funciones de administración de imágenes de
                   sistemas operativos.
- Inventory.lib    funciones de control de inventario e informes.
- Net.lib          funciones básicas de control de acceso a la red.
- Postconf.lib     funciones de post-configuración de sistemas
                   operativos.
- Protocol.lib     funciones de implementación de protocolos de
                   comunicaciones.
- Registry.lib     funciones de gestión del registro de Windows.
- Rsync.lib        funciones de sincronización de ficheros.
- String.lib       funciones de control de cadena.
- System.lib       funciones básicas del sistema.

