Proyecto OpenGNSys
==================

El proyecto OpenGNSys (léase OpenGenesis) reúne el esfuerzo conjunto de varias Universidades Públicas españolas para construir una aplicación que permita una fácil gestión centralizada de ordenadores y servidores. Con ella se permite la distribución, instalación y despliegue de distintos sistemas operativos.

OpenGNSys nace debido a la necesidad de disponer de un conjunto de herramientas libres y abiertas que constituyan un sistema completo de gestión y clonación de equipos, versátil e intuitivo, que pueda ser utilizado tanto en la gestión de aulas de informática, como para reinstalar ordenadores y servidores.

Está basado en una arquitectura cliente/servidor. El ordenador cliente realiza una petición de conexión durante su proceso de arranque y el servidor le devuelve sus datos de red y un menú de inicio. En tal página el usuario puede seleccionar las acciones predefinidas por el administrador, ejecutar dichas acciones automáticamente sin intervención del usuario final o entrar directamente en una interfaz administrativa.

La gestión principal del sistema está basada en una sencilla interfaz web que permite realizar las tareas comunes para gestionar la distribución de software en los distintos clientes. Sin embargo, la estructura de OpenGNSys es lo suficientemente versátil como para adaptarse a las necesidades de las diferentes arquitecturas de redes de ordenadores disponibles en empresas e instituciones.

OpenGNSys está constituido por un conjunto de módulos separados en distintas capas de servicios.

    * La capa inferior se encarga del acceso directo a los dispositivos del cliente y de las funciones del motor de clonación.
    * Una capa intermedia está constituida por un conjunto de herramientas para realizar tareas complejas y personalización del entorno.
    * La capa de administración consta de la interfaz web y de la base de datos de gestión. 

Los procesos específicos de configuración y modificación de datos en cada uno de los clientes pueden realizarse directamente una vez terminado el proceso de volcado de la imagen, sin necesidad de arrancar el sistema operativo correspondiente, accediendo directamente a la información almacenada en los discos. Ésto supone una significativa ventaja sobre otros productos similares, incluso comerciales.

En el estado actual de desarrollo del Proyecto, se permite la clonación y despliegue de sistemas operativos Windows (incluido Windows 7) con sistemas de ficheros FAT32 y NTFS, así como distribuciones Linux con Sistemas de ficheros Ext2, Ext3 y Ext4.

Algunos de los aspectos principales de la futura línea de trabajo de OpenGNSys son la capacidad de gestión de discos redundantes, volúmenes lógicos y tener la posibilidad de clonar y desplegar otros sistemas operativos. Esto ultimo conlleva soportar nuevos tipos de sistemas de ficheros.

La distribución de imágenes y ficheros debe ser lo más flexible posible, implementando distintos protocolos de comunicaciones, como Unixast, Multicast y P2P; e incluso también poder disponer de dicha información de forma off-line (sin necesidad de comunicación con el servidor) accediendo directamente a la caché de datos local de cada cliente o a un dispositivo externo de almacenamiento.

Por definición, OpenGNSys es un proyecto de Software Libre. Como tal, todo el código está licenciado bajo GPLv3 o superior, mientras que la documentación asociada está disponible bajo licencia Creative Commons con Reconocimiento y Compartir Igual.

OpenGNSys es el resultado del proceso de integración de 3 proyectos anteriores desarrollados en diferentes universidades:

    * Brutalix (Universidad de Zaragoza)
    * EAC (Universidad de Málaga)
    * Hidra (Universidad de Sevilla) 

