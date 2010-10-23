// ********************************************************************************************************
// Cliernte: ogAdmClient
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmClient.h
// Descripción :Este fichero implementa el cliente general del sistema
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
#include <sys/wait.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <signal.h>
#include "ogAdmLib.h"
// ________________________________________________________________________________________________________
// Variables globales
// ________________________________________________________________________________________________________
char idordenador[LONPRM];	 // Identificador del ordenador
char nombreordenador[LONPRM]; // Nombre del ordenador
char cache[LONPRM]; // Tamaño de la caché
char idproautoexec[LONPRM]; // Identificador del procedimiento de autoexec
char idcentro[LONPRM]; // Identificador de la Unidad Organizativa
char idaula[LONPRM]; // Identificador del aula
char IPlocal[LONIP]; // Ip local

char servidoradm[LONPRM]; // Dirección IP del servidor de administración
char puerto[LONPRM]; // Puerto de comunicación
char pathinterface[LONPRM]; // Path donde está la interface entre la administración y el módulo de clonación

char interface[LONFUN]; // Nombre del módulo,función o script de la interface con el módulo de administración
char parametros[LONSTD]; // Parámetros para la llamada
int herror;

BOOLEAN CMDPTES; // Para bucle de comandos pendientes

pid_t  pidbrowser; // Identificador del proceso que se crea para mostrar una página web con el browser
pid_t  pidbash; // Identificador del proceso que se crea cuando se conmuta el browser

char urlmenu[MAXLONURL]; // Url de la pagina de menu para el browser
char urlmsg[MAXLONURL]; // Url de la página de mensajed para el browser


typedef struct{  // Estructura usada para referenciar las funciones que procesan las tramas
	char nf[LONFUN]; // Nombre de la función
	BOOLEAN (*fptr)(TRAMA*); // Puntero a la función que procesa la trama
}MSGFUN;
MSGFUN tbfuncionesClient[MAXIMAS_FUNCIONES];
// ________________________________________________________________________________________________________
// Tabla de errores de la ejecución de los scripts
// ________________________________________________________________________________________________________
char* tbErroresScripts[]={"Se han generado errores. No se puede continuar la ejecución de este módulo",\
		"001-Formato de ejecución incorrecto.",\
		"002-Fichero o dispositivo no encontrado",\
		"003-Error en partición de disco",\
		"004-Partición o fichero bloqueado",\
		"005-Error al crear o restaurar una imagen",\
		"006-Sin sistema operativo",\
		"007-Programa o función BOOLEANno ejecutable",\
		"008-Error en la creación del archivo de eco para consola remota",\
		"009-Error en la lectura del archivo temporal de intercambio",\
		"010-Error al ejecutar la llamada a la interface de administración",\
		"011-La información retornada por la interface de administración excede de la longitud permitida",\
		"012-Error en el envío de fichero por la red",\
		"013-Error en la creación del proceso hijo",\
		"Error desconocido "
	};
	#define MAXERRORSCRIPT 14		// Error máximo cometido
// ________________________________________________________________________________________________________
// Prototipo de funciones
// ________________________________________________________________________________________________________
BOOLEAN autoexecCliente(TRAMA*);
BOOLEAN RESPUESTA_AutoexecCliente(TRAMA*);
void procesaComandos(TRAMA*);

BOOLEAN tomaConfiguracion(char*);
BOOLEAN tomaIPlocal(void);
void scriptLog(const char *,int );

BOOLEAN gestionaTrama(TRAMA *);
BOOLEAN inclusionCliente();
char* LeeConfiguracion(char*);
BOOLEAN RESPUESTA_InclusionCliente(TRAMA *);

BOOLEAN comandosPendientes(TRAMA*);
BOOLEAN NoComandosPtes(TRAMA *);

BOOLEAN respuestaEjecucionComando(TRAMA *,int,char*);
BOOLEAN Sondeo(TRAMA *);
BOOLEAN Actualizar(TRAMA *);
int Purgar(TRAMA* );

BOOLEAN ConsolaRemota(TRAMA*);

BOOLEAN Arrancar(TRAMA *);
BOOLEAN Apagar(TRAMA *);
BOOLEAN Reiniciar(TRAMA *);
BOOLEAN IniciarSesion(TRAMA *);
BOOLEAN CrearImagen(TRAMA *);
BOOLEAN InventarioHardware(TRAMA *);
BOOLEAN InventariandoSoftware(TRAMA *,BOOLEAN,char*);
BOOLEAN EjecutarScript(TRAMA *);
BOOLEAN ejecutaArchivo(char*,TRAMA*);

BOOLEAN cuestionCache(char*);
int cargaPaginaWeb(char *);
void muestraMenu(void);
void muestraMensaje(int idx,char*);

BOOLEAN enviaMensajeServidor(SOCKET *,TRAMA *,char);




