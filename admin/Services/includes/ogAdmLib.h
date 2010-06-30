
#define LONPRM 512
#define LONGITUD_PARAMETROS 4000	// Longitud mínima de la información de la trama (parametros)
#define LONGITUD_CABECERATRAMA	11 // Longitud mínima de la trama completa
#define LONGITUD_TRAMA LONGITUD_PARAMETROS+LONGITUD_CABECERATRAMA	// Longitud mínima de la trama completa

#define LEER		0
#define ESCRIBIR	1

#define TRUE 1
#define FALSE 0

#define true 1
#define false 0

#define ACCION_EXITOSA		"1" // Finalizada con éxito
#define ACCION_FALLIDA		"2" // Finalizada con errores
#define ACCION_TERMINADA	"3" // Finalizada manualmente con indicación de éxito
#define ACCION_ABORTADA		"4" // Finalizada manualmente con indicación de errores
#define ACCION_SINERRORES	"5" // Activa y sin ningn error
#define ACCION_CONERRORES	"6" // Activa y con algn error

#define ACCION_DETENIDA		"0" // Acción momentanemente parada
#define ACCION_INICIADA			"1" // Acción activa
#define ACCION_FINALIZADA 	"2" // Acción finalizada

#define SOCKET_ERROR            (-1)
#define INVALID_SOCKET  (SOCKET)(~0)
#define MAXCNX 5		// Mximos intentos de conexin al servidor HIDRA

#define PUERTOMINUSER 40000
#define PUERTOMAXUSER 60000

#define MAX_NUM_CSADDRS        20
#define MAX_INTERFACE_LIST     20

#define COMILLAS_SIMPES 0x27
#define DOBLES_COMILLAS 0x22
#define BARRA_INVERTIDA 0x5c

#define LITAMBITO_CENTROS		"centros"
#define LITAMBITO_GRUPOSAULAS		"gruposaulas"
#define LITAMBITO_AULAS			"aulas"
#define LITAMBITO_GRUPOSORDENADORES	"gruposordenadores"
#define LITAMBITO_ORDENADORES		"ordenadores"

#define MAXCMD_PARAMETROS  200  // Máximo número de parámetros de una trama de comandos
#define MAXIMOS_SOCKETS    4000 // Máximo número de conexiones con ordenadores clientes
#define MAXIMOS_SRVRMB		200 // Máximo número de servidores rembo
#define MAXLON_PARAMETROSIPH  3000 // Máxima longitud de un parametro iph

#define MAXHARDWARE 128 //	 MÁXIMOS ELEMENTOS HARDSWARE A DETECTAR
#define MAXSOFTWARE 2048 //	 MÁXIMOS ELEMENTOS SOFTWARE A DETECTAR

#define PROCESOS 0x01

#define EJECUCION_PROCEDIMIENTO	0x0000 // Acción Procedimiento
#define EJECUCION_COMANDO	0x0001 // Acción Comando
#define EJECUCION_TAREA		0x0002 // Acción Tarea
#define EJECUCION_TRABAJO		0x0003 // Acción Trabajo
#define EJECUCION_RESERVA   0x0004//Acción Reserva

#define EJECUTOR_servidorHIDRA	0x0001 // Ejecutor Servidor hidra
#define EJECUTOR_clienteREMBO	0x0002 // Ejecutor cliente rembo
#define EJECUTOR_servidorREMBO	0x0003 // Ejecutor Servidor rembo

#define CLIENTE_REMBO	"RMB" // Sistema operativo Rembo
#define CLIENTE_OCUPADO	"BSY" // Cliente ocupado
#define CLIENTE_APAGADO	"OFF" // Cliente apagado
#define CLIENTE_INICIANDO	"INI" // Cliente iniciando

// Variables y estructuras

typedef struct{		// EstructUra de la trama recibida
	char arroba;	// cabecera de la trama
	char identificador[9];	// identificador de la trama
	char ejecutor;	// ejecutor de la trama 1=el servidor rembo  2=el cliente rembo
	char parametros[LONGITUD_PARAMETROS]; // Contenido de la trama (par?etros)
}TRAMA;

char szPathFileCfg[512];
char szPathFileLog[512];

typedef unsigned long DWORD;
typedef unsigned short  WORD;
typedef  int  BOOLEAN;
typedef char  BYTE;
typedef  int  SOCKET;

// Prototipos de funciones

void INTROaFINCAD(char* );
void FINCADaINTRO(char*,char*);
SOCKET AbreConexion(char *,int);
int cuenta_ipes(char*);
int IgualIP(char *,char *);
void RegistraLog(const char *,int);
struct tm * TomaHora();
char * toma_parametro(const char* ,char *);
char* copia_parametro(const char*,char *);
int SplitParametros(char**,char*, char*);
int recibe_trama(SOCKET sock,TRAMA* trama);
char* Encriptar(char *);
char * Desencriptar(char *);

