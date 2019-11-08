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
char *idordenador;	 // Identificador del ordenador
char *nombreordenador; // Nombre del ordenador
char *cache; // Tamaño de la caché
char *idproautoexec; // Identificador del procedimiento de autoexec
char *idcentro; // Identificador de la Unidad Organizativa
char *idaula; // Identificador del aula
char IPlocal[LONIP]; // Ip local

char servidoradm[LONPRM]; // Dirección IP del servidor de administración
char puerto[LONPRM]; // Puerto de comunicación
char pathinterface[LONPRM]; // Path donde está la interface entre la administración y el módulo de clonación

char interface[LONFUN]; // Nombre del módulo,función o script de la interface con el módulo de administración
char parametros[LONSTD]; // Parámetros para la llamada
int herror;

BOOLEAN CMDPTES; // Para bucle de comandos pendientes


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
char* tbErroresScripts[]={"Se han generado errores desconocidos. No se puede continuar la ejecución de este módulo",\
		"001-Formato de ejecución incorrecto.",\
		"002-Fichero o dispositivo no encontrado",\
		"003-Error en partición de disco",\
		"004-Partición o fichero bloqueado",\
		"005-Error al crear o restaurar una imagen",\
		"006-Sin sistema operativo",\
		"007-Programa o función BOOLEAN no ejecutable",\
		"008-Error en la creación del archivo de eco para consola remota",\
		"009-Error en la lectura del archivo temporal de intercambio",\
		"010-Error al ejecutar la llamada a la interface de administración",\
		"011-La información retornada por la interface de administración excede de la longitud permitida",\
		"012-Error en el envío de fichero por la red",\
		"013-Error en la creación del proceso hijo",\
		"014-Error de escritura en destino",\
		"015-Sin Cache en el Cliente",\
		"016-No hay espacio en la cache para almacenar fichero-imagen",\
		"017-Error al Reducir el Sistema Archivos",\
		"018-Error al Expandir el Sistema Archivos",\
		"019-Valor fuera de rango o no válido.",\
		"020-Sistema de archivos desconocido o no se puede montar",\
		"021-Error en partición de caché local",\
		"022-El disco indicado no contiene una particion GPT",\
		"023-Error no definido",\
		"024-Error no definido",\
		"025-Error no definido",\
		"026-Error no definido",\
		"027-Error no definido",\
		"028-Error no definido",\
		"029-Error no definido",\
		"030-Error al restaurar imagen - Imagen mas grande que particion",\
		"031-Error al realizar el comando updateCache",\
		"032-Error al formatear",\
		"033-Archivo de imagen corrupto o de otra versión de partclone",\
		"034-Error no definido",\
		"035-Error no definido",\
		"036-Error no definido",\
		"037-Error no definido",\
		"038-Error no definido",\
		"039-Error no definido",\
		"040-Error imprevisto no definido",\
		"041-Error no definido",\
		"042-Error no definido",\
		"043-Error no definido",\
		"044-Error no definido",\
		"045-Error no definido",\
		"046-Error no definido",\
		"047-Error no definido",\
		"048-Error no definido",\
		"049-Error no definido",\
		"050-Error en la generación de sintaxis de transferenica unicast",\
		"051-Error en envio UNICAST de una particion",\
		"052-Error en envio UNICAST de un fichero",\
		"053-Error en la recepcion UNICAST de una particion",\
		"054-Error en la recepcion UNICAST de un fichero",\
		"055-Error en la generacion de sintaxis de transferenica Multicast",\
		"056-Error en envio MULTICAST de un fichero",\
		"057-Error en la recepcion MULTICAST de un fichero",\
		"058-Error en envio MULTICAST de una particion",\
		"059-Error en la recepcion MULTICAST de una particion",\
		"060-Error en la conexion de una sesion UNICAST|MULTICAST con el MASTER",\
		"061-Error no definido",\
		"062-Error no definido",\
		"063-Error no definido",\
		"064-Error no definido",\
		"065-Error no definido",\
		"066-Error no definido",\
		"067-Error no definido",\
		"068-Error no definido",\
		"069-Error no definido",\
		"070-Error al montar una imagen sincronizada.",\
		"071-Imagen no sincronizable (es monolitica).",\
		"072-Error al desmontar la imagen.",\
		"073-No se detectan diferencias entre la imagen basica y la particion.",\
		"074-Error al sincronizar, puede afectar la creacion/restauracion de la imagen.",\
		"Error desconocido "
	};
	#define MAXERRORSCRIPT 74		// Error máximo cometido
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
char* LeeConfiguracion();
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
BOOLEAN CrearImagenBasica(TRAMA *);
BOOLEAN CrearSoftIncremental(TRAMA*);

BOOLEAN InventarioHardware(TRAMA *);
BOOLEAN InventariandoSoftware(TRAMA *,BOOLEAN,char*);
BOOLEAN EjecutarScript(TRAMA *);
BOOLEAN ejecutaArchivo(char*,TRAMA*);

BOOLEAN cuestionCache(char*);
int cargaPaginaWeb(char *);
void muestraMenu(void);
void muestraMensaje(int idx,char*);

BOOLEAN enviaMensajeServidor(SOCKET *,TRAMA *,char);




