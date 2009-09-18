// ****************************************************************************************************************************************************
//	Aplicación OpenGNSys
//	Autor: José Manuel Alonso.
//	Licencia: Open Source 
//	Fichero: ogAdmServer.cpp
//	Descripción:
//		Este módulo de la aplicación OpenGNSys implementa el cliente del Repositorio.
// ****************************************************************************************************************************************************
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
#include "ogUtil.h"

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
int split_parametros(char **,char *, char * );
int TomaConfiguracion(char* );
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
bool respuesta_peticion(TramaRepos *,const char*,const char*,const char*);
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
