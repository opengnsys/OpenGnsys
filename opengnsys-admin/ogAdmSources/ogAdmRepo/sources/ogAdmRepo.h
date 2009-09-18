// *************************************************************************************************************
// Aplicacin HIDRA
// Copyright 2003-2007 Jos Manuel Alonso. Todos los derechos reservados.
// Fichero: hidrarepos.h
// 
//	Descripcin:
//	 Fichero de cabecera de hidrapxedhcp.cpp
// **************************************************************************************************************
#include <sys/types.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <unistd.h>
#include <ctype.h>
#include <time.h>
#include </usr/include/mysql/mysql.h>
#include <pthread.h>
#include <signal.h>
#include "Database.h"
#include "encriptacion.h"

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
SOCKET sClient;

char IPlocal[20];		// Ip local
char servidorhidra[20]; // IP servidor HIDRA
char Puerto[20]; 		// Puerto Unicode
int puerto;	// Puerto

char filecmdshell[250];
char cmdshell[512];

char msglog[250];

char usuario[20];
char pasguor[20];
char datasource[20];
char catalog[50];
int puertorepo;	// Puerto

//______________________________________________________
static pthread_mutex_t guardia; // Controla acceso exclusivo de hebras 
//______________________________________________________

char PathHidra[1024]; // path al directorio base de Hidra
char PathComandos[1024]; // path al directorio donde se depositan los comandos para los clientes
char PathUsuarios[1024]; // path al directorio donde se depositan los ficheros de login de los operadores
char PathIconos[1024]; // path al directorio donde se depositan los iconos de los items de los mens

// Prototipos de funciones
void RegistraLog(const char *,int );
int split_parametros(char **,char *, char * );
int TomaConfiguracion(char* );
void INTROaFINCAD(char* );
void FINCADaINTRO(char*,char*);
char * toma_parametro(char* ,char *);
int ClienteExistente(TramaRepos *);
LPVOID GestionaServicioRepositorio(LPVOID);
int	Actualizar(TramaRepos*);
int Arrancar(TramaRepos *);
int Wake_Up(SOCKET,char *);
void PasaHexBin( char *,char *);
int levanta(char *);
int FicheroOperador(TramaRepos *);
int IconoItem(TramaRepos *);

bool ExisteFichero(TramaRepos *);
bool EliminaFichero(TramaRepos *);
bool LeeFicheroTexto(TramaRepos *);
int gestiona_comando(TramaRepos *);
bool respuesta_peticion(TramaRepos *,const char*,char*,char*);
bool RecibePerfilSoftware(TramaRepos *trmInfo);
bool EnviaPerfilSoftware(TramaRepos *trmInfo);
SOCKET Abre_conexion(char *,int);
int envia_tramas(SOCKET,TRAMA *);
int recibe_tramas(SOCKET ,TRAMA *);
int inclusion_REPO();
int RESPUESTA_inclusionREPO(TRAMA *);
int TomaRestoConfiguracion(TRAMA *);
int RegistraComando(TramaRepos *);
int Apagar(TramaRepos *);
char * Buffer(int );
int TomaPuertoLibre(int *);
int ejecutarscript ( char *,char * ,char *);
void NwGestionaServicioRepositorio(TramaRepos *);
int ExecShell(char*,char *);
