//****************************************************************************************************************************************************
//	Aplicación OpenGNSys
//	Autor: José Manuel Alonso.
//	Licencia: Open Source 
//	Fichero: ogAdmRepo.h
//	Descripción:
//		Este módulo de la aplicación OpenGNSys implementa las comunicaciones con el Repositorio.
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
#include "ogAdmLib.h"


#define MAXIMOS_CLIENTES  4000 // Máximo número de clientes rembo controlados por el servidor rembo
#define MAXIMAS_MULSESIONES 1000 // Máximo numero de sesiones multicast activas simultaneamente
#define PUERTO_WAKEUP			9	// Puerto por defecto del wake up



typedef  void* LPVOID;



// Estructura para trabajar en cada hebra con el cliente en cuestión
struct  TramaRepos{
	SOCKET sck;
	struct sockaddr_in cliente;
	socklen_t sockaddrsize;
	TRAMA trama;
};

FILE *FLog,*Fconfig;
SOCKET sClient;

char IPlocal[20];		// Ip local
char servidorhidra[20]; // IP servidor HIDRA
char Puerto[20]; 		// Puerto Unicode
int puerto;	// Puerto
char reposcripts[512];	// Path al directorio donde están los scripts

char filecmdshell[250];
char cmdshell[512];

char msglog[250];

char usuario[20];
char pasguor[20];
char datasource[20];
char catalog[50];
int puertorepo;	// Puerto

struct s_inisesionMulticast{ // Estructura usada para guardar información sesiones multicast
	char ides[32]; // Identificador sesión multicast
	char *ipes; // Ipes de los clientes necesarios para la sesión
};
struct s_inisesionMulticast tbsmul[MAXIMAS_MULSESIONES];
//______________________________________________________
static pthread_mutex_t guardia; // Controla acceso exclusivo de hebras 
//______________________________________________________

char PathHidra[250]; // path al directorio base de Hidra
char PathPXE[250]; // path al directorio PXE

char PathComandos[250]; // path al directorio donde se depositan los comandos para los clientes
char PathUsuarios[250]; // path al directorio donde se depositan los ficheros de login de los operadores
char PathIconos[250]; // path al directorio donde se depositan los iconos de los items de los menús

// Prototipos de funciones


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

BOOLEAN ExisteFichero(TramaRepos *);
BOOLEAN EliminaFichero(TramaRepos *);
BOOLEAN LeeFicheroTexto(TramaRepos *);
BOOLEAN mandaFichero(TramaRepos *);
int gestiona_comando(TramaRepos *);
BOOLEAN respuesta_peticion(TramaRepos *,const char*,char*,char*);

int envia_tramas(SOCKET,TRAMA *);
int recibe_tramas(SOCKET ,TRAMA *);
int inclusion_REPO();
int RESPUESTA_inclusionREPO(TRAMA *);
int TomaRestoConfiguracion(TRAMA *);
int RegistraComando(TramaRepos *);
int Apagar(TramaRepos *);
char * Buffer(int );
int TomaPuertoLibre(int *);
void NwGestionaServicioRepositorio(TramaRepos *);
BOOLEAN sesionMulticast(TramaRepos *);
BOOLEAN iniSesionMulticast(char *,char *,char *);
int hay_hueco(int *idx);


