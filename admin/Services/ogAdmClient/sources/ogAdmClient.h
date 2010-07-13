//****************************************************************************************************************************************************
//	Aplicación OpenGNSys
//	Autor: José Manuel Alonso.
//	Licencia: Open Source 
//	Fichero: ogAdmClient.cpp
//	Descripción:
//		Este módulo de la aplicación OpenGNSys implementa las comunicaciones con el Cliente.
// ****************************************************************************************************************************************************
#include <sys/types.h>
#include <sys/wait.h>
#include <arpa/inet.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <unistd.h>
#include <ctype.h>
#include <time.h>
#include <signal.h>
#include "ogAdmLib.h"


#define LONGITUD_SCRIPTSALIDA 4064	// Longitud máxima de la informacin devuelta por un script
#define LONGITUD_PARAMETROS_TRAMA 4024	// Longitud máxima de la información de la trama (parametros)

#define LONGITUD_CONFIGURACION 1024	// Longitud máxima de las configuraciones de partición


#define MAXITEMS 100
#define MAXHTMLMNU 4000
#define MAXPARTICIONES 24
#define MAXINFOSO 5 // Numero máximo de nemónicos enla inforamción del S.O. de una partición
#define MAXARGS 16 // Numero máximo de argumentos enviados a un scripts 
#define LONSTD 512 // Longitud de memoria estandar 
#define LONSTDC 256 // Longitud de memoria estandar corta



TRAMA trama[1];

char IPlocal[20];		// Ip local
char Servidorhidra[20]; // IP servidor de Administración
char Puerto[20]; 		// Puerto Unicode



//___________________________________________________________________________________________________
// Variables y estructuras
//___________________________________________________________________________________________________

char cmdshell[LONSTD];
char parametros[LONSTD];
char* argumentos[MAXARGS];
char msglog[LONSTD];
char msgcon[LONSTD];
char filecmdshell[LONSTDC];
char urlpag[LONSTDC];
char fileini[LONSTDC];
char filecmd[LONSTDC];

struct excepcion {
	int herror;
	char msg[LONSTDC];
	char modulo[LONSTDC];	
};
struct excepcion e;

int ndebug=1; // Nivel de debuger por defecto

// Nemónicos
int MsDos=1;
int Win98=2;
int Win2K=3;
int WinXP=4; 
int Linux=5;
	 	
BOOLEAN PROCESO=true;			// Indicador de la actividad del proceso principal
BOOLEAN CACHEEXISTS;			// Indica si existe cache

char HIDRACHEIMAGENES[LONSTDC];	// Path al directorio donde están las imágenes (en la caché)
char HIDRASRVIMAGENES[LONSTDC];	// Path al directorio donde están las imágenes (en el repositorio)
char HIDRASRVCMD[LONSTDC];	// Path del directorio del repositorio donde se depositan los comandos para el cliente 
char HIDRASCRIPTS[LONSTDC];	// Path al directorio donde están los scripts de la interface con la APi de funciones de OpenGnsys (en el cliente )
char URLMENU[LONSTDC]; // Url de la pagina de menu para el browser
char URLMSG[LONSTDC]; // Url de la página de mensajed para el browser


int HIDRAVER;	// Versión de la apliación de Administración
int TPAR ;	// Tamaño de la partición
	
SOCKET sock;	// Socket

struct s_CabMnu {
	char resolucion[2];			 // Resolución de pantalla
	char titulo[LONSTDC];						// Título del menú
	char coorx[4];					// Coordenada x
	char coory[4];					// Coordenada y
	char modalidad[2];		// modalidad ( número de items por línea )
	char scoorx[4];				// Coordenada x // Menú privado
	char scoory[4];				// Coordenada y
	char smodalidad[LONSTDC];		// modalidad ( número de items por línea )
	char htmmenupub[64];	// Nombre del fichero que contiene el html del menú (público)
	char htmmenupri[64];		// Nombre del fichero que contiene el html del menú (privado)
} CabMnu;  // Estructura con los datos de los menús
	
BOOLEAN swmnu=false; // Indicador de menú asignado
	
struct s_Item{
	char idaccionmenu[16];	// Identificador del item a ejecutar
	char urlimg[64];	// Nombre de la imagen de fondo del botón
	char literal[LONSTDC];	// Literal del item
	char tipoitem[2];	// Tipo de item ( público o privado)
	char tipoaccion[2];	// Tipo de acción que ejecuta el item
} ;
	
struct s_Propiedades {
	char idordenador[16]; // Identificador del ordenador
	char nombreordenador[64]; // Nombre del ordenador
	char idaula[16]; // Identificador del aula
	char servidorhidra[16]; // IP  del servidor Opengnsys
	char puerto[16]; // Puerto
	char iprepo[16]; // Dirección IP repositorio
	char puertorepo[16]; // Puerto
	char idperfilhard[16]; // Identificador del perfil hardware
	char IPlocal[16]; // Dirección IP del cliente
	char cache[16]; // Tamaño de la cache
	char ipmulticast[16]; // Dirección IP multicast
	char pormulticast[16]; // Puerto multicast
	char modmulticast[16]; // Modo de transmisión multicast
	char velmulticast[16]; // Velocidad de transmisión multicast

} Propiedades;	  // Estructura con los datos del odenador
	
struct s_Particiones{
	char tiposo[64];				// Tipo de sistema operativo 
	char tipopart[16];			// Tipo de partición
	char tamapart[16];  		// Tamao de la partición
	char numpart[5];  		// Nmero de la partición
	char nombreso[64];    // Nombre del S.O.
};
	
struct s_Hardware{
	char nemonico[4];				// Tipo de sistema operativo 
	char tipo[45];			// Tipo de hardware
	char codigovalor[256];  		// Código o descripción
}
;	
struct tiposo {
  char *tipopart;
  char *tiposo;
  char *nombreso;  
};	

char* tbPathImg[]={"CLIEN","CACHE","REPO"};
char* tbmodmul[]={"","half-duplex","full-duplex"};

struct tiposo tiposos[] = {
		{"BIGDOS", "MsDos","MsDos"},
		{"NTFS","Windows NT Platafom","Windows 2000,XP,2003"},
		{"HNTFS","Windows NT Platafom","Windows 2000,XP,2003"},
		{"FAT32","Windows","Windos 98,SE,Millenium"},			
		{"HFAT32","Windows","Windos 98,SE,Millenium"},			
		{"EXT","Extendida","Extendida"},
		{"EXT4","Linux","Linux"},
		{"EXT3","Linux","Linux"},
		{"EXT2","Linux","Linux"},	
		{"REISERFS","Linux","Linux"},	
		{"JFS","Linux","Linux"},	
		{"XFS","Linux","Linux"},	
		{"VFAT","VFAT","VFAT"},	
		{"HVFAT","VFAT","VFAT"},	
		{"CACHE","CACHE","CACHE"},			
		{"UNKNOWN","UNKNOWN","UNKNOWN"},			
		{"EMPTY","Libre","Libre"},
		{"LINUX-SWAP","","Linux-swap"}};
		
int ntiposo = sizeof (tiposos) / sizeof (struct tiposo);

struct s_Item tbMenu[MAXITEMS];			// Tabla con los items del menu
int contitems;			// Contador items del menu
	
BOOLEAN PRCCMD;		// Indicador de comandos interactivos
BOOLEAN CMDPTES;	// Indicador de comandos pendientes
	
//char modulo[64];	// Nombre de la función donde se produce el error

BOOLEAN aut = false; // Variable para controlar el acceso al menú de administración

pid_t  pidmenu;

char* tbErrores[]={"000-Se han generado errores. No se puede continuar la ejecución de este módulo",\
		"001-No hay memoria suficiente para el buffer",\
		"002-No se puede establecer conexión con el servidor de administración",\
		"003-El fichero especificado no existe o bien no puede crearse o abrirse",\
		"004-Comando Error",\
		"005-El fichero est vacio",\
		"006-Error en la ejecución del fichero autoexec",\
		"007-Error en la recuperacion del Menu principal",\
		"008-No hay espacio reservado para la cache en este disco",\
		"009-Ha ocurrido algún error generando el perfil software",\
		"010-IPlocal, NO se ha definido este parámetro",\
		"011-IPhidra, NO se ha definido este parámetro",\
		"012-Puerto, NO se ha definido este parámetro",\
		"013-NO existe fichero de configuración o contiene un error de sintaxis",\
		"014-Fallo de sintaxis en los parámetros: Debe especificar -f nombre_del_fichero_de_configuración",\
		"015-No se ha podido crear socket para comunicación con el repositorio",\
		"016-No se ha podido comunicar con el repositorio",\
		"017-No existe Menu principal",\
		"018-No se ha podido recuperar la configuración hardware del ordenador",\
		"019-El cliente no se ha podido incluir en el sistema por un fallo en la conexión con el Servidor de Administración",\
		"020-No se ha podido crear la carpeta en el repositorio",\
		"021-Error en el envío de tramas al Servidor de Administración",\
		"022-Error en la recepción de tramas desde el Servidor de Administración",\
		"023-Error al crear Proceso Hijo para  mostrar Menú",\
		"024-Error desconocido",\
		};		
		#define MAXERROR 24		// Error máximo cometido

char* tbErroresScripts[]={"000-Se han generado errores. No se puede continuar la ejecución de este módulo",\
		"001-Formato de ejecución incorrecto.",\
		"002-Fichero o dispositivo no encontrado",\
		"003-Error en partición de disco",\
		"004- Partición o fichero bloqueado",\
		"005-Error al crear o restaurar una imagen",\
		"006-Sin sistema operativo",\
		"007-Programa o función no ejecutable",\
		"008-Error en la eliminación del archivo temporal de intercambio",\
		"009-Error en la lectura del archivo temporal de intercambio",\
		"010-Error al ejecutar código de la shell",\
		"011-Error desconocido",	
		};		
	#define MAXERRORSCRIPT 11		// Error máximo cometido

// Prototipos de funciones
char* Desencriptar(char *);
char* Encriptar(char *);
int ValidacionParametros(int,char**);
int CrearArchivoLog(char*);
int LeeFileConfiguracion();
void Log(char*);
void UltimoError(int,char*);
void UltimoErrorScript(int,char*);

int EjecutarScript (char*,char* ,char*,int);
char* ReservaMemoria(int);
int EjecutarCodigo (char*,char* ,char*,int);

SOCKET TCPConnect(char *,char* );
void TCPClose(SOCKET);
int AbreConexionTCP(void);
void CierraConexionTCP(void);
int EnviaTramasHidra(SOCKET,TRAMA*);

int TCPWrite(SOCKET ,TRAMA*);

SOCKET UDPConnect();
int EnviaTramaRepo(SOCKET,TRAMA*,char*,char*);
int RecibeTramaRepo(SOCKET,int);

long CreateTextFile(char*,char*);
int ExisteFichero(char*);
int RemoveFile(char *);
int LoadTextFile(char *);

int ProcesaComandos();
int DisponibilidadComandos(int);
int GestionTramas(TRAMA *);

int Cortesia();
int NoComandosPtes();
int TomaIPlocal();
int InclusionCliente();
int RESPUESTA_InclusionCliente(TRAMA*);
int ComandosPendientes(void);
int Arrancar(TRAMA *,TRAMA *);
int Apagar(TRAMA*,TRAMA*);
int Reiniciar(TRAMA*,TRAMA*);
int IniciarSesion(TRAMA*,TRAMA*);
int Actualizar();
int Sondeo();
int CrearPerfilSoftware(TRAMA*,TRAMA*);
int CrearPerfil(char*,char*,char*,char*,char*);
int Nemonico(char*);
int RestaurarImagen(TRAMA*,TRAMA*);
int RestaurandoImagen(char* ,char *,char* ,char *,char *,char *,char *);

int ParticionaryFormatear(TRAMA*,TRAMA*);
int Particionar(char*,char*,char* );
int Particionando(char*,char*,char*);
int Formatear(char*,char*);
int SetCachePartitionSize(int);
int AutoexecClienteHidra(void);
char* LeeConfiguracion(char*);
char* TomaNomSO(char*,int);
int InventarioHardware(TRAMA *,TRAMA *);
int InventarioSoftware(TRAMA *,TRAMA *);
int TomaConfiguracion(TRAMA *,TRAMA *);
int RespuestaEjecucionComando(TRAMA* , TRAMA *, int);
int ExecShell(TRAMA *,TRAMA *);
int ConsolaRemota(TRAMA *,TRAMA *);
int ExecBash(char*);
char* URLDecode(char*);
char* URLEncode(char *);
int MuestraMenu(char*);
void MuestraMensaje(int,char*);
int cuestionCache(char*);
int sesionMulticast(char *,char *,char *);
