//****************************************************************************************************************************************************
//	Aplicación OpenGNSys
//	Autor: José Manuel Alonso.
//	Licencia: Open Source 
//	Fichero: ogAdmServer.cpp
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
 
#define LEER		0
#define ESCRIBIR	1

#define LONGITUD_SCRIPTSALIDA 1024	// Longitud máima de la informacin devuelta por un script
#define LONGITUD_PARAMETROS 4048	// Longitud m?ima de la informacin de la trama (parametros)
#define LONGITUD_TRAMA LONGITUD_PARAMETROS+11	// Longitud m?ima de la trama completa
#define LONGITUD_CONFIGURACION 1024	// Longitud mxima de las configuraciones de particin
#define MAX_NUM_CSADDRS        20
#define MAX_INTERFACE_LIST     20
#define MAXCNX 5		// Máximos intentos de conexión al servidor de Administración
#define MAXITEMS 100
#define MAXHTMLMNU 4000
#define MAXPARTICIONES 24
#define MAXINFOSO 5 // Numero máximo de nemonicos enla inforamción del S.O. de una partición 
#define MAXARGS 16 // Numero máximo de argumentos enviados a un scripts 
#define LONSTD 512 // Longitud de memoria estandar 
#define LONSTDC 256 // Longitud de memoria estandar corta

#define PUERTOMINUSER 20000
#define PUERTOMAXUSER 60000

#define TRUE 1
#define FALSE 0

#define true 1
#define false 0

#define SOCKET_ERROR            (-1)
#define INVALID_SOCKET  (SOCKET)(~0)

typedef unsigned short  WORD;
typedef  int  BOOL;
typedef char  BYTE;
typedef  int  SOCKET;

typedef struct{		// EstructUra de la trama recibida
	char arroba;	// cabecera de la trama
	char identificador[9];	// identificador de la trama
	char ejecutor;	// ejecutor de la trama 1=el servidor de admistración  2=el cliente 3=el repositorio
	char parametros[LONGITUD_PARAMETROS]; // Contenido de la trama (par?etros)
}TRAMA;

TRAMA trama[1];

char IPlocal[20];		// Ip local
char Servidorhidra[20]; // IP servidor de Administración
char Puerto[20]; 		// Puerto Unicode

char szPathFileCfg[128];
char szPathFileLog[128];

//___________________________________________________________________________________________________
// Variables y estructuras
//___________________________________________________________________________________________________

char cmdshell[LONSTD];
char parametros[LONSTD];
char* argumentos[MAXARGS];
char msglog[LONSTD];
char msgcon[LONSTD];
char filecmdshell[LONSTDC];
char filemenu[LONSTDC];
char fileitem[LONSTDC];
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
	 	
BOOL PROCESO=true;			// Indicador de la actividad del proceso principal
BOOL CACHEEXISTS;			// Indica si existe cache

char HIDRACHEIMAGENES[LONSTDC];	// Path al directorio donde están las imágenes (en la caché)
char HIDRASRVIMAGENES[LONSTDC];	// Path al directorio donde están las imágenes (en el repositorio)
char HIDRASRVCMD[LONSTDC];	// Path del directorio del repositorio donde se depositan los comandos para el cliente 
char HIDRASCRIPTS[LONSTDC];	// Path al directorio donde están los scripts de la interface con la APi de funciones de OpenGnsys (en el cliente )

int HIDRAVER;	// Versión de la apliación de Administración
int TPAR ;	// Tamaño de la particin
	
SOCKET sock;	// Socket

struct s_CabMnu {
	char resolucion[2];			 // Resolucin de pantalla
	char titulo[LONSTDC];						// Titulo del menu
	char coorx[4];					// Coordenada x
	char coory[4];					// Coordenada y
	char modalidad[2];		// modalidad ( numero de items por linea )
	char scoorx[4];				// Coordenada x // Menu privado
	char scoory[4];				// Coordenada y
	char smodalidad[LONSTDC];		// modalidad ( numero de items por linea )
	char htmmenupub[64];	// Nombre del fichero que contiene el html del menu (público)
	char htmmenupri[64];		// Nombre del fichero que contiene el html del menu (privado)
} CabMnu;  // Estructura con los datos de los menús
	
BOOL swmnu=false; // Indicador de menu asignado
	
struct s_Item{
	char idaccionmenu[16];	// Identificador del item a ejecutar
	char urlimg[64];	// Nombre de la imagen de fonfo del botn
	char literal[LONSTDC];	// Literal del item
	char tipoitem[2];	// Tipo de otem ( público o privado)
	char tipoaccion[2];	// Tipo de accin que ejecuta el item
} ;
	
struct s_Propiedades {
	char idordenador[16];				 // Identificador del ordenador
	char nombreordenador[64];		 // Nombre del ordenador
	char idaula[16];								// Identificador del aula
	char servidorhidra[64];				// IP  del servidor HUDRA
	char puerto[16];								// Puerto
	char iprepo[16];								// Direción IP repositorio	
	char puertorepo[16];								// Puerto	
	char idperfilhard[16];					// Identificador del perfil hardware
	char IPlocal[16];						// Ip local
} Propiedades;	  // Estructura con los datos del odenador
	
struct s_Particiones{
	char tiposo[64];				// Tipo de sistema operativo 
	char tipopart[16];			// Tipo de particin
	char tamapart[16];  		// Tamao de la particin
	char numpart[5];  		// Nmero de la particin
	char nombreso[64];    // Nombre del S.O.
};
	
struct s_Hardware{
	char nemonico[4];				// Tipo de sistema operativo 
	char tipo[45];			// Tipo de hardware
	char codigovalor[256];  		// Codigo o descripcion
}
;	
struct tiposo {
  char *tipopart;
  char *tiposo;
  char *nombreso;  
};	
struct tiposo tiposos[] = {
		{"BIGDOS", "MsDos","MsDos"},
		{"NTFS","Windows NT Platafom","Windows 2000,XP,2003"},
		{"FAT32","Windows","Windos 98,SE,Millenium"},			
		{"EXT","Extendida","Extendida"},
		{"EXT3","Linux","Linux"},
		{"EXT2","Linux","Linux"},	
		{"VFAT","VFAT","VFAT"},	
		{"CACHE","CACHE","CACHE"},			
		{"UNKNOWN","UNKNOWN","UNKNOWN"},			
		{"EMPTY","Libre","Libre"},
		{"LINUX-SWAP","","Linux-swap"}};
		
int ntiposo = sizeof (tiposos) / sizeof (struct tiposo);

struct s_Item tbMenu[MAXITEMS];			// Tabla con los items del menu
int contitems;			// Contador items del menu
	
BOOL PRCCMD;		// Indicador de comandos interactivos
BOOL CMDPTES;	// Indicador de comandos pendientes
	
//char modulo[64];	// Nombre de la funcin donde se produce el error

BOOL aut = false; // Variable para controlar el acceso al menu de administracion


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
		"023-Error desconocido",\
		};		
		#define MAXERROR 22		// Error máximo cometido

char* tbErroresScripts[]={"000-Se han generado errores. No se puede continuar la ejecución de este módulo",\
		"001-Formato de ejecución incorrecto.",\
		"002-Fichero o dispositivo no encontrado",\
		"003-Error en partición de disco",\
		"004- Partición o fichero bloqueado",\
		"005-Error al crear o restaurar una imagen",\
		"006-Sin sistema operativo",\
		"007- Programa o función no ejecutable",\
		"008-Error desconocido",\
		};		
	#define MAXERRORSCRIPT 7		// Error máximo cometido

// Prototipos de funciones
char* Desencriptar(char *);
char* Encriptar(char *);
int ValidacionParametros(int,char**);
int CrearArchivoLog(char*);
int LeeFileConfiguracion();
void Log(char*);
void UltimoError(int,char*);
void UltimoErrorScript(int,char*);

void INTROaFINCAD(char*);
char* TomaParametro(char*,char*);
int SplitParametros(char**,char*, char*);

int EjecutarScript (char*,char* ,char*,int);
char* ReservaMemoria(int);

SOCKET TCPConnect(char *,char* );
void TCPClose(SOCKET);
int AbreConexionTCP(void);
void CierraConexionTCP(void);
int EnviaTramasHidra(SOCKET,TRAMA*);
int RecibeTramasHidra(SOCKET,TRAMA*);
int TCPWrite(SOCKET ,TRAMA*);
int TCPRead(SOCKET ,TRAMA*);
SOCKET UDPConnect();
int EnviaTramaRepo(SOCKET,TRAMA*,char*,char*);
int RecibeTramaRepo(SOCKET);

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
int Actualizar();
int CrearPerfilSoftware(TRAMA*,TRAMA*);
int CrearPerfil(char*,char*,char*,char*,char*);
int Nemonico(char*);
int RestaurarImagen(TRAMA*,TRAMA*);
int RestaurandoImagen(char*,char*,char*,char*,char*,char*,char*); 
int ParticionaryFormatear(TRAMA*,TRAMA*);
int Particionar(char*,char*,char* );
int Particionando(char*,char*,char*);
int Formatear(char*,char*);
int SetCachePartitionSize(int);
int AutoexecClienteHidra(void);
char* LeeConfiguracion(char*);
char* TomaNomSO(char*,int);
int InventarioHardware(TRAMA *,TRAMA *);
int TomaConfiguracion(TRAMA *,TRAMA *);
int RespuestaEjecucionComando(TRAMA* , TRAMA *, int);
int ExecShell(TRAMA *,TRAMA *);
char* URLDecode(char*);
