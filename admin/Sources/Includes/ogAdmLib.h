// **************************************************************************************************************************************************
// Libreria: ogAdmLib
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmLib.h
// Descripción: Este fichero implementa el archivo de cabecera de la libreria  ogAdmLib
// **************************************************************************************************************************************************
// ________________________________________________________________________________________________________
// Valores definidos
// ________________________________________________________________________________________________________
#define LONSTD 1024	// Longitud de memoria estandar
#define LONINT 16	// Longitud de memoria estandar para un número entero
#define LONFIL 1024	// Longitud de memoria estandar para nombres de archivo completos (incluido path)
#define LONIP 16	// Longitud de memoria estandar para cadenas que contiene una dirección IP
#define LONMAC 16	// Longitud de memoria estandar para cadenas que contiene una dirección MAC
#define LONSQL 8192	// Longitud de memoria estandar para una sentencia SQL
#define LONPRM 4098	// Longitud estandar de los parámetros del fichero de configuración del servicio
#define LONSCP 4098	// Longitud estandar de los parámetros de las tramas
#define LONFUN 512	// Longitud estandar de los nombres de las funciones que procesan las tramas
#define LONSUC 4098	// Longitud de los mensajes de sucesos
#define LONBLK 8192	// Longitud de los paquetes de tramas leidos cada vez
#define MAXPRM 20	// Máximo número de parámeros del fichero de configuración del servicio
#define MAXPAR 128	// Maximo numero de particiones manejadas por el sistema, ahora con GPT es 128
#define MAXLONURL 1024 // Longitud máxima de una dirección url con parámetros

#define LONHEXPRM 5 // Longitud del campo que contiene el tamaño de la cadena de parámetros
#define LONGITUD_CABECERATRAMA 16	// Longitud de la cabecera de las tramas
#define LONGITUD_PARAMETROS 8192	// Longitud estandar de la información de la trama (parámetros)
#define MAXCMD_PARAMETROS  200  // Máximo número de parámetros de una trama

#define MAXIMOS_CLIENTES	4000	// Máximo número de conexiones con ordenadores clientes
#define MAXIMAS_FUNCIONES	LONSTD	// Máximo número de funciones que procesan los mensajes entre servicio y clientes
#define MAXIMAS_LINEAS 3000	// Longitud máxima de lineas en un archivo de comandos

#define AUTOINCORPORACION_OFF	0x0000 // Los ordenadores no se pueden dar de alta automáticamente
#define AUTOINCORPORACION_ONA	0x0001 // Los ordenadores se pueden dar de alta automáticamente si existe el aula
#define AUTOINCORPORACION_ONX	0x0002 // Los ordenadores se pueden dar de alta automáticamentee y si no existe el aula la crea

#define DEBUG_BAJO	1 // Nivel de debug bajo
#define DEBUG_MEDIO	2 // Nivel de debug medio
#define DEBUG_ALTO 3 // Nivel de debug alto
#define DEBUG_MAXIMO 4 // Nivel de debug máximo

#define CLIENTE_OCUPADO	"BSY" // Cliente ocupado
#define CLIENTE_APAGADO	"OFF" // Cliente apagado
#define CLIENTE_INICIANDO "INI" // Cliente iniciando

#define CLIENTE_OPENGNSYS "OPG" // Cliente Opengnsys

#define CLIENTE_WIN "WIN" // Cliente Windows genérico
#define CLIENTE_WNT "WNT" // Windows NT
#define CLIENTE_W2K "W2K" // Windows 2000
#define CLIENTE_WS2 "WS2" // Windows Server 2003
#define CLIENTE_WXP "WXP" // Cliente Windows XP
#define CLIENTE_W95 "W95" // Windows 95
#define CLIENTE_W98 "W98" // Windows 98
#define CLIENTE_WML "WML" // Windows Milenium
#define CLIENTE_MS2 "MS2" // MsDos
#define CLIENTE_WVI "WVI" // Cliente Windows Vista
#define CLIENTE_WI7 "WI7" // Cliente Windows 7

#define CLIENTE_LNX "LNX" // Cliente Linux

#define ACCION_SINRESULTADO 0 // Sin resultado
#define ACCION_EXITOSA	1 // Finalizada con éxito
#define ACCION_FALLIDA	2 // Finalizada con errores

#define ACCION_INICIADA	1 // Acción activa
#define ACCION_DETENIDA	2 // Acción momentanemente parada
#define ACCION_FINALIZADA 3 // Accion finalizada

#define EJECUCION_COMANDO 1
#define EJECUCION_PROCEDIMIENTO 2
#define EJECUCION_TAREA 3
#define EJECUCION_RESERVA 4

#define AMBITO_CENTROS 0x01
#define AMBITO_GRUPOSAULAS 0x02
#define AMBITO_AULAS 0x04
#define AMBITO_GRUPOSORDENADORES 0x08
#define AMBITO_ORDENADORES 0x10

// Código de los tipos de mensajes
#define MSG_COMANDO '1' // Mensaje del tipo comando
#define MSG_NOTIFICACION '2' // Respuesta a la ejecución un comando
#define MSG_PETICION '3' // Petición de cualquier actuación
#define MSG_RESPUESTA '4' // Respuesta a una petición
#define MSG_INFORMACION '5' // Envío de cualquier información sin espera de confirmación o respuesta

#define ANNOREF 2009 // Año de referencia base

#define LONGITUD_SCRIPTSALIDA 131072	// Longitud máxima de la información devuelta por una función de interface
#define MAXARGS 16	// Número máximo de argumentos enviados a un scripts
#define MAXCNX 5	// Máximos intentos de conexión al servidor de Administración

#define PUERTO_WAKEUP	9 // Puerto wake up

#define MAXHARDWARE 128 //	 Máximos elementos hardware a detectar
#define MAXSOFTWARE 8096 //	 Máximos elementos software a detectar
// ________________________________________________________________________________________________________
// Tipos definidos
// ________________________________________________________________________________________________________
typedef unsigned long DWORD;
typedef unsigned short  WORD;
typedef int  BOOLEAN;
typedef char BYTE;
typedef int  SOCKET;
typedef  void* LPVOID;

#define TRUE 1
#define FALSE 0

#define SOCKET_ERROR	(-1)
#define INVALID_SOCKET	(SOCKET)(~0)

#define LEER		0
#define ESCRIBIR	1

#define CHARNULL '\0'

// ________________________________________________________________________________________________________
// Variables globales
// ________________________________________________________________________________________________________
char szPathFileCfg[LONSTD],szPathFileLog[LONSTD];
int ndebug; // Nivel de debuger

typedef struct{		// Estructura de las tramas
	char arroba;	// Caracter arroba siempre
	char identificador[14];	// Identificador de la trama, siempre JMMLCAMDJ_MCDJ
	char tipo;	// Tipo de mensaje
	long lonprm;	// Longitud en hexadecimal de los parámetros
	char *parametros; // Parámetros de la trama
}TRAMA;
// ________________________________________________________________________________________________________
// Tabla de errores
// ________________________________________________________________________________________________________
const char* tbErrores[]={"Se han generado errores. No se puede continuar la ejecución de este módulo",\
		"001-El nombre del fichero de configuración del programa está vacío",\
		"002-No existe fichero de configuración del programa",\
		"003-No hay memoria suficiente para el buffer",\
		"004-Error en el fichero de configuración del programa. No se ha definido el parámetro SERVIDORADM",\
		"005-Error en el fichero de configuración del programa. No se ha definido el parámetro PUERTO",\
		"006-Error en el fichero de configuración del programa. No se ha definido el parámetro USUARIO",\
		"007-Error en el fichero de configuración del programa. No se ha definido el parámetro PASSWORD",\
		"008-Error en el fichero de configuración del programa. No se ha definido el parámetro DATASOURCE",\
		"009-Error en el fichero de configuración del programa. No se ha definido el parámetro CATALOG",\
		"010-Error en los parámetros de ejecución del programa. Debe especificar el fichero de configuración",\
		"011-Error en los parámetros de ejecución del programa. Debe especificar el fichero de log",\
		"012-Error de sintaxis en los parámetros de ejecución del programa: Debe especificar -f nombre_del_fichero_de_configuración_del_programa -l nombre_del_fichero_de_log_del_programa -d nivel de debug",\
		"013-Error al crear socket ***socket() fallo",\
		"014-Error al enlazar socket al interface ***bind() fallo",\
		"015-Error al acceptar conexión de clientes ***accept() fallo",\
		"016-Error al crear hebra de cliente en módulo main()",\
		"017-Error al recibir trama ***recv() fallo",\
		"018-No se reconoce el mensaje enviado",\
		"019-Trama recibida NO válida",\
		"020-No se puede establecer conexión con la base de datos",\
		"021-No se han podido recuperar los datos de la consulta o bien insertar, modificar o eliminar datos",\
		"022-El cliente no se ha sido dado de alta en la base de datos del sistema. Se rechaza su petición de inclusión",\
		"023-Ha habido algún problema en la incorporación automática del cliente",\
		"024-Ha habido algún problema en la actualización de la configuración del cliente",\
		"025-La tabla de clientes está llena, no pueden registrarse más clientes en el sistema",\
		"026-Error al enviar trama ***send() fallo",\
		"027-No se encuentra Repositorio del cliente",\
		"028-Ha ocurrido algún error al tomar las particiones",\
		"029-Ha ocurrido algún problema en el proceso de inclusión del cliente. Se rechaza su petición",\
		"030-Ha ocurrido algún problema en el proceso de respuesta al comando",\
		"031-No se ha encontrado la acción a notificar es posible que se haya eliminado el registro",\
		"032-Ha ocurrido algún problema en el envío del comando",\
		"033-Error en el fichero de configuración del programa. No se ha definido el parámetro PATHSCRIPTS",\
		"034-Error en el fichero de configuración del programa. No se ha definido el parámetro URLMENU",\
		"035-Error en el fichero de configuración del programa. No se ha definido el parámetro URLMSG",\
		"036-No se ha podido recuperar la configuración de las particiones del disco",\
		"037-Ha ocurrido algún problema en el proceso de inclusión del cliente",\
		"038-No se ha podido establecer conexión con el Servidor de Administración",\
		"039-Ha ocurrido algún problema al procesar la trama recibida",\
		"040-Se han recibido parámetros con valores no válidos",\
		"041-Ha ocurrido algún problema en el proceso de inclusión del cliente",\
		"042-Ha ocurrido algún problema al enviar una petición de comandos o tareas pendientes al Servidor de Administración",\
		"043-Ha ocurrido algún problema al enviar una petición de comandos interactivos al Servidor de Administración",\
		"044-Ha ocurrido algún problema al enviar una respuesta de comandos al servidor",\
		"045-Ha ocurrido algún problema al recibir una petición de comandos o tareas pendientes desde el Servidor de Administración",\
		"046-Ha ocurrido algún problema al recibir un comando interactivo desde el Servidor de Administración",\
		"047-El cliente no está registrado en la tabla de sockest del sistema",\
		"048-Error al configurar opción BROADCAST para socket: setsockopt(SO_BROADCAST)",\
		"049-Error al enviar trama magic packet",\
		"050-Ha ocurrido algún problema al enviar un fichero por la red",\
		"051-Error en el fichero de configuración del programa. No se ha definido el parámetro PATHLOGFIL",\
		"052-No se puede crear archivo temporal para ejecución de Comandos",\
		"053-Ha ocurrido algún problema al procesar el Inventario Hardware del cliente",\
		"054-Existe un tipo de hardware que no está registrado",\
		"055-Ha ocurrido algún problema al actualizar el hardware del cliente",\
		"056-Error en el fichero de configuración del programa. No se ha definido el parámetro PATHINTERFACE",\
		"057-Ha ocurrido algún problema al enviar un archivo por la red",\
		"058-Ha ocurrido algún problema al recibir un archivo por la red",\
		"059-Error al crear la hebra DHCP o BOOTP",\
		"060-Error al crear la hebra TFTP",\
		"061-Error al crear socket para servicio DHCP",\
		"062-Error al enlazar socket con interface para servicio DHCP",\
		"063-No hay puertos libres para la hebra del servicio",\
		"064-Error al crear estructura de control para protocolo DHCP",\
		"065-Error al recibir mensaje DHCP. Se para el servicio",\
		"066-Error al crear la hebra cliente DHCP",\
		"067-Error al crear socket para servicio BOOTP",\
		"068-Error al enlazar socket con interface para servicio BOOTP",\
		"069-Error al crear estructura de control para protocolo BOOTP",\
		"070-Error al recibir mensaje BOOTP. Se para el servicio",\
		"071-Error al crear la hebra cliente BOOTP",\
		"072-Error al crear socket para servicio TFTP",\
		"073-Error al enlazar socket con interface para servicio TFTP",\
		"074-Error al crear estructura de control para protocolo TFTP",\
		"075-Error al recibir mensaje TFTP. Se para el servicio",\
		"076-Error al crear la hebra cliente TFTP",\
		"077-No se encontró opción DHCP",\
		"078-ERROR TFTP",\
		"079-Error al recibir mensaje TFTP en hebra cliente",\
		"080-Error al recibir mensaje DHCP",\
		"081-Error al crear socket de usuario para hebra",\
		"082-Ha ocurrido algún problema al procesar el Inventario software del cliente",\
		"083-Ha ocurrido algún problema al actualizar el software del cliente",\
		"084-Ha ocurrido algún problema al reiniciar la sesión del cliente",\
		"085-No se ha podido recuperar la dirección IP del cliente",\
		"086-Error al ejecutar el comando",\
		"087-Error al leer o escribir el contenido del archivo de eco de consola remota",\
		"088-Ha habido algún problerma al procesar la caché",\
		"089-Error en el fichero de configuración del programa. No se ha definido el parámetro URLMENU",\
		"090-Error en el fichero de configuración del programa. No se ha definido el parámetro URLMSG",\
		"091-Ha habido algún problema al enviar un mensaje de tipo petición al Servidor",\
		"092-Error en el fichero de configuración del programa. No se ha definido el parámetro IPLOCAL",\
		"093-No se puede cargar la librería Windows para trabajar con sockets",\
		"094-Ha habido algún problerma al procesar la actualización después de crear una imagen",\
		"095-Ha habido algún problerma al procesar la actualización después de restaurar una imagen",\
		"096-Ha habido algún problerma al procesar la actualización después de crear un software incremental",\
		"097-Este fichero de log está obsoleto, este proceso usa ahora syslog para gestionar los mensajes de log",\
};
// ________________________________________________________________________________________________________
// Tabla de mensajes
// ________________________________________________________________________________________________________
const char* tbMensajes[]={"",\
		"001-Inicio de sesion",\
		"002-Petición de inclusión de cliente",\
		"003-Abriendo sesión en el servidor de Administración",\
		"004-Cliente iniciado",\
		"005-Ejecución de archivo Autoexec",\
		"006-Procesa comandos pendientes",\
		"007-Acciones pendientes procesadas",\
		"008-Ejecución del script",\
		"009-Parámetro del script",\
		"010-Ha ocurrido algún error en la creación del proceso hijo",\
		"011-Aviso: La información de salida del script excede de la longitud permitida. Puede haberse truncado",\
		"012-Información devuelta por el script",\
		"013-Estatus de finalización del script",\
		"014-Configuración de particiones",\
		"015-Enviando petición de inclusión en el sistema al Servidor de Administración",\
		"016-Recibiendo respuesta de inclusión desde el Servidor de Administración",\
		"017-Enviando petición de comandos o tareas pendientes al Servidor de Administración",\
		"018-Recibiendo respuesta de comandos o tareas pendientes desde el Servidor de Administración",\
		"019-Disponibilidad de comandos activada",\
		"020-Disponibilidad de comandos desactivada",\
		"021-Ejecución de comando",\
		"022-Sin eco",\
		"023-Procesando caché",\
		"024-Repositorio iniciado",\

};
// ________________________________________________________________________________________________________
// Prototipo de funciones
// ________________________________________________________________________________________________________
struct tm * tomaHora();
void registraLog(const char *,const char *,int );
void errorLog(const char *,int ,int);
#define og_log(err, swe)   errorLog(__FUNCTION__, err, swe)
void errorInfo(const char *,char *);
#define og_info(err)  errorInfo(__FUNCTION__, err)
void infoLog(int);
void infoDebug(char*);
BOOLEAN validacionParametros(int,char**,int);
char* reservaMemoria(int);
char* ampliaMemoria(char*,int);
void liberaMemoria(void*);
BOOLEAN initParametros(TRAMA*,int);
int splitCadena(char **,char *, char);
void sustituir(char *,char ,char );
char* StrToUpper(char *);
char* StrToLower(char *);
void INTROaFINCAD(TRAMA*);
void FINCADaINTRO(TRAMA*);
int cuentaIPES(char*);
char *tomaParametro(const char*,TRAMA*);
char *copiaParametro(const char*,TRAMA *);
BOOLEAN contieneIP(char *,char *);
char* rTrim(char *);
SOCKET TCPConnect(char *,char *);
SOCKET abreConexion(void);
BOOLEAN enviaMensaje(SOCKET *,TRAMA *,char);
TRAMA* recibeMensaje(SOCKET *);
BOOLEAN mandaTrama(SOCKET*,TRAMA*);
BOOLEAN sendData(SOCKET *, char* ,int );
BOOLEAN enviaTrama(SOCKET *,TRAMA *);
TRAMA* recibeTrama(SOCKET*);
BOOLEAN recData(SOCKET *,char*,int,int*);
BOOLEAN sendFlag(SOCKET *, char* ,int );
BOOLEAN recibeFlag(SOCKET*,TRAMA*);
char* URLEncode(char *);
char* URLDecode(char *);
char* leeArchivo(char*);
int lonArchivo(char *);
BOOLEAN escribeArchivo(char *,char*);
BOOLEAN sendArchivo(SOCKET *,char *);
BOOLEAN recArchivo(SOCKET *,char *);
SOCKET TCPConnect(char *,char*);
int tomaPuerto(SOCKET);

#include <stddef.h> /* for offsetof. */

#define container_of(ptr, type, member) ({			\
	typeof( ((type *)0)->member ) *__mptr = (ptr);		\
	(type *)( (char *)__mptr - offsetof(type,member) );})

