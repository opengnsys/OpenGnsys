#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <time.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <arpa/inet.h>

#define LONGITUD_PARAMETROS 4048	// Longitud m?ima de la informacin de la trama (parametros)
#define LONGITUD_TRAMA		LONGITUD_PARAMETROS+8	// Longitud m?ima de la trama completa

#define MAXIMOS_CLIENTES  4000 // M?imo numero de clientes rembo controlados por el servidor rembo
#define MAXCNX 5		// Mximos intentos de conexin al servidor HIDRA
#define PUERTO_WAKEUP			9	// Puerto por defecto del wake up

#define PUERTOMINUSER 20000
#define PUERTOMAXUSER 60000

#define LEER		0
#define ESCRIBIR	1

#define TRUE 1
#define FALSE 0

#define true 1
#define false 0

#define SOCKET_ERROR            (-1)
#define INVALID_SOCKET  (SOCKET)(~0)

typedef unsigned long DWORD;
typedef unsigned short  WORD;
typedef  int  BOOL;
typedef char  BYTE;
typedef  int  SOCKET;
typedef  void* LPVOID;

typedef struct{		// EstructUra de la trama recibida
	char arroba;	// cabecera de la trama
	char identificador[9];	// identificador de la trama
	char ejecutor;	// ejecutor de la trama 1=el servidor rembo  2=el cliente rembo
	char parametros[LONGITUD_PARAMETROS]; // Contenido de la trama (par?etros)
}TRAMA;

// Estructura para trabajar en cada hebra con el cliente en cuestion
struct  TramaRepos{
	SOCKET sck;
	struct sockaddr_in cliente;
	socklen_t sockaddrsize;
	TRAMA trama;
};


char szPathFileCfg[128],szPathFileLog[128];
FILE *FLog,*Fconfig;

char * Desencriptar(char *);
char * Encriptar(char *);
void RegistraLog(const char *,int );
char * toma_parametro(const char* ,char *);
int split_parametros(char **,char *, char *);
void INTROaFINCAD(char* );
void FINCADaINTRO(char*,char*);
