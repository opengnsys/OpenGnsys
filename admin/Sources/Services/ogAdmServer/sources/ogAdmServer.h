// ********************************************************************************************************
// Servicio: ogAdmServer
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmServer.h
// Descripción: Este fichero implementa el servicio de administración general del sistema
// ********************************************************************************************************
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <ctype.h>
#include <errno.h>
#include <unistd.h>
#include <time.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include </usr/include/mysql/mysql.h>
#include "Database.h"
#include "ogAdmLib.h"
// ________________________________________________________________________________________________________
// Variables globales
// ________________________________________________________________________________________________________
char servidoradm[LONPRM];	// Dirección IP del servidor de administración
char puerto[LONPRM];	// Puerto de comunicación
char usuario[LONPRM];	// Usuario de acceso a la base de datos
char pasguor[LONPRM];	// Password del usuario
char datasource[LONPRM];	// Dirección IP del gestor de base de datos
char catalog[LONPRM];	// Nombre de la base de datos
char aulaup[LONPRM];	// Conmutador para registro automático de clientes

typedef struct{ // Estructura usada para guardar información de los clientes
	char ip[LONIP]; // IP del cliente
	char estado[4]; // Tipo de Sistema Operativo en que se encuentra el cliente
	SOCKET sock; // Socket por el que se comunica
}SOCKETCL;
SOCKETCL tbsockets[MAXIMOS_CLIENTES];

BOOLEAN swcSocket; // Switch para indicar si se debe cerrar el socket del cliente

typedef struct{  // Estructura usada para referenciar las funciones que procesan las tramas
	char nf[LONFUN]; // Nombre de la función
	BOOLEAN (*fptr)(SOCKET*,TRAMA*); // Puntero a la función que procesa la trama
}MSGFUN;
MSGFUN tbfuncionesServer[MAXIMAS_FUNCIONES];
// ________________________________________________________________________________________________________
// Prototipo de funciones
// ________________________________________________________________________________________________________

BOOLEAN tomaConfiguracion(char*);
BOOLEAN gestionaTrama(SOCKET*);
BOOLEAN Sondeo(SOCKET*,TRAMA*);
BOOLEAN respuestaSondeo(SOCKET *,TRAMA*);
BOOLEAN InclusionClienteWinLnx(SOCKET*,TRAMA*);
BOOLEAN InclusionCliente(SOCKET*,TRAMA*);
BOOLEAN registraCliente(char *);

BOOLEAN procesoInclusionClienteWinLnx(SOCKET*,TRAMA*,int*,char*);
BOOLEAN procesoInclusionCliente(SOCKET*,TRAMA*);
BOOLEAN clienteExistente(char *,int *);
BOOLEAN clienteDisponible(char *,int *);
BOOLEAN hayHueco(int *);
BOOLEAN actualizaConfiguracion(Database , Table ,char* ,int);
BOOLEAN AutoexecCliente(SOCKET *, TRAMA *);
BOOLEAN recorreProcedimientos(Database ,char* ,FILE*,char*);

BOOLEAN tomaRepositorio(Database ,Table ,char*,int*);
BOOLEAN buscaComandos(char *,TRAMA *,int *);
BOOLEAN DisponibilidadComandos(SOCKET*,TRAMA*);
BOOLEAN respuestaEstandar(TRAMA *,char **,char **,char ** ,Database *,Table *);
BOOLEAN respuestaConsola(SOCKET *,TRAMA *,int);
BOOLEAN enviaComando(TRAMA *ptrTrama,const char*);

BOOLEAN Actualizar(SOCKET *, TRAMA* );
BOOLEAN Purgar(SOCKET *, TRAMA* );

BOOLEAN ConsolaRemota(SOCKET *,TRAMA*);
BOOLEAN RESPUESTA_ConsolaRemota(SOCKET *,TRAMA*);
BOOLEAN EcoConsola(SOCKET *,TRAMA*);

BOOLEAN Comando(SOCKET *,TRAMA *);
BOOLEAN RESPUESTA_Comando(SOCKET *,TRAMA *);

BOOLEAN Arrancar(SOCKET *,TRAMA *);
BOOLEAN Levanta(char*,char*,char*);
BOOLEAN WakeUp(SOCKET *,char*,char*,char*);
void PasaHexBin(char *,char *);
BOOLEAN RESPUESTA_Arrancar(SOCKET *,TRAMA*);
BOOLEAN Apagar(SOCKET *,TRAMA *);
BOOLEAN RESPUESTA_Apagar(SOCKET *,TRAMA *);
BOOLEAN Reiniciar(SOCKET *,TRAMA *);
BOOLEAN RESPUESTA_Reiniciar(SOCKET *,TRAMA *);
BOOLEAN IniciarSesion(SOCKET *,TRAMA *);
BOOLEAN RESPUESTA_IniciarSesion(SOCKET *,TRAMA *);
BOOLEAN CrearImagen(SOCKET *,TRAMA *);
BOOLEAN CrearImagenBasica(SOCKET *,TRAMA *);
BOOLEAN CrearSoftIncremental(SOCKET *,TRAMA *);
BOOLEAN RESPUESTA_CrearImagen(SOCKET *,TRAMA *);
BOOLEAN RESPUESTA_CrearImagenBasica(SOCKET *,TRAMA *);
BOOLEAN RESPUESTA_CrearSoftIncremental(SOCKET *,TRAMA *);
BOOLEAN actualizaCreacionImagen(Database,Table,char*,char*,char*,char*,char*,char*);
BOOLEAN actualizaCreacionSoftIncremental(Database,Table,char*,char*);
BOOLEAN RestaurarImagen(SOCKET *,TRAMA *);
BOOLEAN RestaurarImagenBasica(SOCKET *,TRAMA *);
BOOLEAN RestaurarSoftIncremental(SOCKET *,TRAMA *);
BOOLEAN RESPUESTA_RestaurarImagen(SOCKET *,TRAMA *);
BOOLEAN RESPUESTA_RestaurarImagenBasica(SOCKET *,TRAMA *);
BOOLEAN RESPUESTA_RestaurarSoftIncremental(SOCKET *,TRAMA *);
BOOLEAN actualizaRestauracionImagen(Database,Table,char*,char*,char*,char*,char*);
BOOLEAN Configurar(SOCKET *,TRAMA* );
BOOLEAN RESPUESTA_Configurar(SOCKET *,TRAMA* );
BOOLEAN actualizaConfigurar(Database , Table , char* );
BOOLEAN InventarioHardware(SOCKET *,TRAMA *);
BOOLEAN RESPUESTA_InventarioHardware(SOCKET *,TRAMA *);
BOOLEAN actualizaHardware(Database, Table,char* ,char*,char*,char*);
BOOLEAN cuestionPerfilHardware(Database,Table,char*,char*,int,char*,char*,int *,int);
BOOLEAN actualizaSoftware(Database , Table , char* , char* , char*,char*,char*);
BOOLEAN cuestionPerfilSoftware(Database, Table, char*, char*,int,int,char*,char*,char*,int *,int);

BOOLEAN enviaArchivo(SOCKET *, TRAMA *);
BOOLEAN recibeArchivo(SOCKET *, TRAMA *);
BOOLEAN envioProgramacion(SOCKET *, TRAMA *);

int checkDato(Database,Table,char*,const char*,const char*,const char*);



