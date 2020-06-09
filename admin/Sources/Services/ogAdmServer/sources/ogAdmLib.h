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

#define CLIENTE_OCUPADO	"BSY" // Cliente ocupado
#define CLIENTE_APAGADO	"OFF" // Cliente apagado
#define CLIENTE_INICIANDO "INI" // Cliente iniciando

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

#define ANNOREF 2009 // Año de referencia base

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
// Prototipo de funciones
// ________________________________________________________________________________________________________
BOOLEAN validacionParametros(int,char**,int);
char* reservaMemoria(int);
char* ampliaMemoria(char*,int);
void liberaMemoria(void*);
BOOLEAN initParametros(TRAMA*,int);
int splitCadena(char **,char *, char);
char* StrToUpper(char *);
void FINCADaINTRO(TRAMA*);
char *tomaParametro(const char*,TRAMA*);
char *copiaParametro(const char*,TRAMA *);
BOOLEAN contieneIP(char *,char *);
char* rTrim(char *);
BOOLEAN enviaMensaje(SOCKET *,TRAMA *,char);
BOOLEAN mandaTrama(SOCKET*,TRAMA*);
BOOLEAN sendData(SOCKET *, char* ,int );
BOOLEAN enviaTrama(SOCKET *,TRAMA *);
TRAMA* recibeTrama(SOCKET*);
char* escaparCadena(char *cadena);

#include <stddef.h> /* for offsetof. */

#define container_of(ptr, type, member) ({			\
	typeof( ((type *)0)->member ) *__mptr = (ptr);		\
	(type *)( (char *)__mptr - offsetof(type,member) );})

