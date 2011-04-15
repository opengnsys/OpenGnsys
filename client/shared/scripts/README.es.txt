OpenGnSys Client (Scripts)    README
====================================


Este directorio contiene algunos scripts de ejemplo que se utilizan
por el cliente de la consola web de administración, para realizar
las operaciones más comunes de gestión de clientes.

Dichos scripts están desarrollados en BASH y utilizan las funciones
básicas del motor de clonación.

OpenGnSys Client Cloning Engine incluye un completo conjunto de
funiones para que el administrador pueda personalizar sus scripts
de gestión.  Sin embargo, estos ejemplos son completamente
operativos y pueden ser utilizados directamente.


Scripts distribuidos:

- bootLinux                arranca un sistema Linux con partición de inicio.
- bootOs                   arranca un sistema operativo instalado.
- bootWindows              arranca un sistema Windows con partición de inicio.
- createImage              genera una imagen de un sistema operativo.
- createLogicalPartitions  define las particiones primarias del disco.
- createPrimaryPartitions  define las particiones lógicas del disco.
- formatFs                 formatea un sistema de archivos.
- getFsType                muestra el tipo (mnemónico) de una partición.
- getIpAddress             muestra la IP local del cliente.
- getOsVersion             muestra la versión de sistema operativo instalado.
- initCache                inicia o define la caché local.
- listHardwareInfo         lista los dispoisitivos del cliente.
- listPrimaryPartitions    lista las particiones primarias de un disco.
- listSoftwareInfo         lista el software de un sistema operativo.
- menuBrowser              arranque el Browser con un menú preconfigurado.
- poweroff                 desmonta los sistemas de archivos y apaga el equipo.
- reboot                   desmonta los sistemas de archivos y reinicia el equipo.
- restoreImage             restaura una imagen de sistema operativo.

