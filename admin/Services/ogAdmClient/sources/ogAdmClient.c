//****************************************************************************************************************************************************
//	Aplicación OpenGNSys
//	Autor: José Manuel Alonso.
//	Licencia: Open Source 
//	Fichero: ogAdmClient.c
//	Descripción:
//		Este módulo de la aplicación OpenGNSys implementa las comunicaciones con el Cliente.
// ****************************************************************************************************************************************************
#include "ogAdmClient.h"
//______________________________________________________________________________________________________
// Función: Encripta
//
//	 Descripción:
//		Encripta una cadena 
//	Parámetros:
//		- cadena: Cadena a encriptar
// 	Devuelve:
//		- La cadena encriptada
//______________________________________________________________________________________________________
char* Encriptar(char *cadena)
{
	 return(cadena);
	
	int i,lon;
	char clave; 
	
	clave = 12 & 0xFFU; // La clave elegida entre 0-255, en este caso 12
	lon=strlen(cadena);
	for(i=0;i<lon;i++)
      cadena[i]=((char)cadena[i] ^ clave) & 0xFF; 
	return(cadena);
}
//______________________________________________________________________________________________________
// Función: Desencripta
//
//	 Descripción:
//		Desencripta una cadena 
//	Parámetros:
//		- cadena: Cadena a desencriptar
// 	Devuelve:
//		La cadena desencriptada
//______________________________________________________________________________________________________
char* Desencriptar(char *cadena)
{
	return(cadena);
	
	int i,lon;
	char clave; 
	
	clave = 12 & 0xFFU; // La clave elegida entre 0-255, en este caso 12
	lon=strlen(cadena);
	for(i=0;i<lon;i++)
		cadena[i]=((char)cadena[i] ^ clave) & 0xFF;
	return(cadena);

}
//______________________________________________________________________________________________________
// Función: ValidacionParametros
//
//	 Descripción:
// 		Valida que los parametros de ejecución del programa sean correctos
//	Parámetros:
//		- argc:	Número de argumentos
//		- argv:	Puntero a cada argumento
// 	Devuelve:
//		true si los argumentos pasados son correctos y false en caso contrario
//	Especificaciones:
//		La sintaxis de los argumentos es la siguiente
//			-f	Archivo de configuración del cliente
//			-l	Archivo de logs
//			-d	Nivel de debuger (Mensages que se escribirán en el archivo de logs) 
//______________________________________________________________________________________________________
int ValidacionParametros(int argc,char*argv[])
{
	int i;

	for(i = 1; i < argc; i++){
		if (argv[i][0] == '-'){
			switch (tolower(argv[i][1])){
				case 'f':
					if (argv[i+1]!=NULL)
						strcpy(szPathFileCfg, argv[i+1]);
					else
						return(false);	// Error en el argumento archivo de configuración
					break;
				case 'l':
					if (argv[i+1]!=NULL)
						strcpy(szPathFileLog, argv[i+1]);	// Error en el argumento archivo de log
					else
						return(false);
					break;
				case 'd':
					if (argv[i+1]!=NULL){
						ndebug=atoi(argv[i+1]);
						if(ndebug<1 )
						ndebug=1;	// Por defecto el nivel de debug es 1
					}
					else
						return(false); // Error en el argumento nivel de debug
					break;
 				default:
 					return(false);
					break;
			}
		}
	}
	return(true);
}
//______________________________________________________________________________________________________
// Función: CrearArchivoLog
//
//	 Descripción:
// 		Abre el archivo de log para añadir registros desde el principio y si no existe lo crea de nuevo
//	Parámetros:
//		- szPathFileLog:	Nombre del archivo
// 	Devuelve:
//		true si la acción ha sido correcta y false en caso contrario
//______________________________________________________________________________________________________
int CrearArchivoLog(char* szPathFileLog)
{	
	return(true);
	FILE* FLog;
	FLog=fopen(szPathFileLog,"wt"); // Abre de log para escritura al comienzo
	if(FLog!=NULL){
		fclose(FLog);
		return(true);
	}
	return(false);
}
//______________________________________________________________________________________________________
// Función: LeeFileConfiguracion
//
//	 Descripción:
// 		Lee el fichero de configuración y toma el valor de los parámetros de configuración
//	Parámetros:
//		Ninguno
// 	Devuelve:
//		true si todos los parámetros de configuración son correctos y false en caso contrario
//______________________________________________________________________________________________________
int LeeFileConfiguracion()
{
	long lSize;
	char * buffer,*lineas[100],*dualparametro[2];
	char ch[2];
	int i,numlin,resul;
	FILE* Fsalida;
	
	if(szPathFileCfg==NULL) return(false); // Nombre del fichero de configuración erróneo

	Fsalida = fopen ( szPathFileCfg , "rb" );	// Abre  fichero de configuración
	if (Fsalida==NULL)
		return(false); // Error de apertura del fichero de configuración
	fseek (Fsalida , 0 , SEEK_END);
	lSize = ftell (Fsalida);	// Obtiene tamaño del fichero.
	rewind (Fsalida);	// Coloca puntero de lectura al principio
	
	buffer =(char*)ReservaMemoria(lSize);	// Toma memoria para el buffer de lectura.
	if (buffer == NULL)
		return(false); // Error de reserva de memoria para buffer de lectura
	fread (buffer,1,lSize,Fsalida);	// Lee contenido del fichero
	fclose(Fsalida);

	//inicializar variables globales 
	IPlocal[0]='\0';	// IP local
	Servidorhidra[0]='\0';	// IP servidor de Administración
	Puerto[0]='\0';	// Puerto de comunicaciones con el servidor de administración
	HIDRACHEIMAGENES[0]='\0';	// Path al directorio donde están las imágenes (en la caché)
	HIDRASRVIMAGENES[0]='\0';	// Path al directorio donde están las imágenes (en el repositorio)
	HIDRASRVCMD[0]='\0';	// Path del directorio del repositorio donde se depositan los comandos para el cliente
	HIDRASCRIPTS[0]='\0';	// Path al directorio donde estan los scripts de interface con la API de funciones
	
	strcpy(ch,"\n");	// Carácter delimitador (salto de linea)
	numlin=SplitParametros(lineas,buffer,ch); // Toma lineas del  fichero
	for (i=0;i<numlin;i++){
		strcpy(ch,"=");	// Caracter delimitador
		SplitParametros(dualparametro,lineas[i],ch); // Toma nombre del parametros
		resul=strcmp(dualparametro[0],"ServerIP");
		if(resul==0) 
			strcpy(Servidorhidra,dualparametro[1]);
		else{
			resul=strcmp(dualparametro[0],"Puerto");
			if(resul==0)
				strcpy(Puerto,dualparametro[1]);
			else{
				resul=strcmp(dualparametro[0],"ClientScripts");
				if(resul==0)
					strcpy(HIDRASCRIPTS,dualparametro[1]);
				else{
					resul=strcmp(dualparametro[0],"UrlMenu");
					if(resul==0)
						strcpy(URLMENU,dualparametro[1]);
					else{
						resul=strcmp(dualparametro[0],"UrlMsg");
						if(resul==0)
							strcpy(URLMSG,dualparametro[1]);
						else
							return(false);
					}
				}
			}
		}
	}
	return(true);
}
//______________________________________________________________________________________________________
// Función: Log
//
//	 Descripción:
// 		Registra un mensaje en el archivo de log y lo muestra por la consola
//	Parámetros:
//		- msg: Contenido del mensaje
//______________________________________________________________________________________________________
void Log(char* msg)
{
	time_t rawtime;
	struct tm * timeinfo;
		
	time (&rawtime);
	timeinfo=gmtime(&rawtime);

	FILE* FLog;
	FLog=fopen(szPathFileLog,"at"); // Archivo de log
	if(FLog!=NULL)
		fprintf (FLog,"%02d/%02d/%d %02d:%02d ***%s\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year+1900,timeinfo->tm_hour,timeinfo->tm_min,msg);
	fclose(FLog);	
	// Lo muestra por consola

	sprintf(msgcon,"echo '%02d/%02d/%d %02d:%02d ***%s'\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year+1900,timeinfo->tm_hour,timeinfo->tm_min,msg);
	system(msgcon);

	
}
//______________________________________________________________________________________________________
// Función: UltimoError
//
//	 Descripción:
// 		Almacena el último error producido y lo registra en el log
//	Parámetros:
//		- herror: Código del error
//		- msg: Descripción del error
//		- modulo: Función donde se produjo el error
//______________________________________________________________________________________________________
void UltimoError(int herror,char*modulo)
{
	e.herror=herror;
	if(herror>MAXERROR){
		strcpy(e.msg,tbErrores[MAXERROR]);
	}
	else
		strcpy(e.msg,tbErrores[herror]);	
	strcpy(e.modulo,modulo);	
	sprintf(msglog,"Error en el Servicio: %d.-(%s) en modulo %s",e.herror,e.msg,e.modulo);
	Log(msglog);
}

//______________________________________________________________________________________________________
// Función: UltimoErrorScript
//
//	 Descripción:
// 		Almacena el último error producido al ejecutar un script de la API y lo registra en el log
//	Parámetros:
//		- herror: Código del error
//		- msg: Descripción del error
//		- modulo: Función donde se produjo el error
//______________________________________________________________________________________________________
void UltimoErrorScript(int herror,char*modulo)
{
	e.herror=herror;
	if(herror>MAXERRORSCRIPT){
		strcpy(e.msg,tbErroresScripts[MAXERRORSCRIPT]);
	}
	else
		strcpy(e.msg,tbErroresScripts[herror]);	
	strcpy(e.modulo,modulo);	
	sprintf(msglog,"Error al ejecutar Script %d.-(%s) en modulo %s",e.herror,e.msg,e.modulo);
	Log(msglog);
}
//______________________________________________________________________________________________________
// Función: INTROaFINCAD
//
//	Descripción: 
// 		Cambia los INTROS (\r) por caracteres fin de cadena ('\0') en una cadena
//	Parámetros:
//		- parametros : La cadena a explorar
// ________________________________________________________________________________________________________
void INTROaFINCAD(char* parametros)
{
	int lon,i;
	lon=strlen(parametros);
	for(i=0;i<lon;i++){
		if(parametros[i]=='\r') parametros[i]='\0';
	}
}
//______________________________________________________________________________________________________
// Función: TomaParametro
//
//	Descripción: 
// 		Devuelve el valor de un parametro incluido en la trama.
// 		El formato del protocolo es: "nombre_parametro=valor_parametro"
//	Parámetros:
// 		- nombre_parametro: Es el nombre del parnetro a recuperar
// 		- parametros: Es la matriz que contiene todos los parámetros
//	Devuelve:
//		Un puntero al valor del parámetro
// ________________________________________________________________________________________________________
char * TomaParametro(char* nombre_parametro,char *parametros)
{
	int i=0;
	char* pos;

	for(i=0;i<LONGITUD_PARAMETROS_TRAMA-4;i++){ 
		if(parametros[i]==nombre_parametro[0]){
			if(parametros[i+1]==nombre_parametro[1]){
				if(parametros[i+2]==nombre_parametro[2]){
					if(parametros[i+3]=='='){
						pos=&parametros[i+4];
						return(pos);
					}
				}
			}
		}
	}
	return(NULL);
}
//______________________________________________________________________________________________________
// Función: SplitParametros
//
//	Descripción: 
//		Trocea una cadena según un carnter delimitador, Devuelve el número de trozos
//	Parámetros:
// 		- trozos: Array de punteros a cadenas
// 		- cadena: Cadena a trocear
// 		- ch: Carácter delimitador
//	Devuelve:
//		Número de trozos en que se ha troceado la cadena
// ________________________________________________________________________________________________________
int SplitParametros(char **trozos,char *cadena, char * ch)
{
	int w=0;
	char* token;

	token= strtok(cadena,ch); // Trocea según delimitador
	while( token != NULL ){
		trozos[w++]=token;
		token=strtok(NULL,ch); // Siguiente token
	}
	trozos[w++]=token; 
	return(w-1); // Devuelve el número de trozos
}
//______________________________________________________________________________________________________
// Función: EjecutarScript
//
//	 Descripción:
//		Ejecuta un script de la shell creando un proceso hijo para ello
//	Parámetros:
//		- script: Nombre del script de la  shell
//		- parametros: Parámetros que se le pasarán al script
//		- salida: Recoge la salida por pantalla que genera el script
//		- swasci: Filtra la respuesta del script:
//					 true=Elimina de la respuesta caracteres menores de asci 32
//					 false= No los elimina					
// 	Devuelve:
//		Código de error de la ejecución. ( Ver tabla de código de errores en la documentación)
//	Especificaciones:
//		El parámetro salida recoge la salida por pantalla que se genera en la ejecución del script siempre que
//		sea disinto de NULL, esto es, si al llamar a la función este parámetro es NULL no se recogerá dicha salida. 
//______________________________________________________________________________________________________
int oldEjecutarScript ( char *script,char * parametros,char *salida,int swasci)
{
	int  descr[2];	/* Descriptores de E y S de la turbería */
	int  bytesleidos;	/* Bytes leidos en el mensaje */
	int resul;
	int estado;	
	pid_t  pid;
	char buffer[512];
	pipe (descr);
	int i,nargs;
    
	if(ndebug>2){
		sprintf(msglog,"Ejecución del script: %s",script);
		Log(msglog);
	}
    	
	nargs=SplitParametros(argumentos,parametros," "); // Crea matriz de los argumentos del scripts
	for(i=nargs;i<MAXARGS;i++){
		argumentos[i]=NULL;
	}
    
	if(ndebug>2){
		for(i=0;i<nargs;i++){
			sprintf(msglog,"Parámetro %d del script: %s",i,argumentos[i]);
			Log(msglog);
		}
	}
	
	if((pid=fork())==0){
		/* Proceso hijo que ejecuta el script */
		close (descr[LEER]);
		dup2 (descr[ESCRIBIR], 1);
		close (descr[ESCRIBIR]);
		resul=execv(script,argumentos);
		//resul=execlp (script, script, argumentos[0],argumentos[1],NULL);    
		exit(resul);   
	}
	else {
		if (pid ==-1){
			sprintf(msglog,"***Error en la creación del proceso hijo pid=%d",pid);
			Log(msglog);
			return(-1);
		}
		/* Proceso padre que lee la salida del script */
		close (descr[ESCRIBIR]);
		bytesleidos = read (descr[LEER], buffer, 512);
		while(bytesleidos>0){
			if(salida!=(char*)NULL){ // Si se solicita retorno de información...			
				buffer[bytesleidos]='\0';
				for(i=bytesleidos-1;i>=0;i--){
					if(buffer[i]<32 && swasci) // Caracter Asci menor de 32
						buffer[i]='\0';
				}
				strcat(salida,buffer);
			}
			bytesleidos = read (descr[LEER], buffer, 512);
		}
		close (descr[LEER]);
		if(ndebug>2){
			sprintf(msglog,"Información devuelta %s",salida);
			Log(msglog);
		}
		//kill(pid,SIGQUIT);
		waitpid(pid,&estado,0);  
		resul=WEXITSTATUS(estado);
		if(ndebug>2){
			sprintf(msglog,"Estatus de finalización del script:%d",resul);
			Log(msglog);
		}   
		return(resul);
	}
	return(-1); 
}
//______________________________________________________________________________________________________
// Función: EjecutarScript
//
//	 Descripción:
//		Ejecuta un script de la shell creando un proceso hijo para ello
//	Parámetros:
//		- script: Nombre del script de la  shell
//		- parametros: Parámetros que se le pasarán al script
//		- salida: Recoge la salida por pantalla que genera el script
//		- swasci: Filtra la respuesta del script:
//					 true=Elimina de la respuesta caracteres menores de asci 32
//					 false= No los elimina					
// 	Devuelve:
//		Código de error de la ejecución. ( Ver tabla de código de errores en la documentación)
//	Especificaciones:
//		El parámetro salida recoge la salida desde un fichero que se genera en la ejecución del script siempre que
//		sea distinto de NULL, esto es, si al llamar a la función este parámetro es NULL no se recogerá dicha salida. 
//______________________________________________________________________________________________________
int EjecutarScript ( char *script,char * parametros,char *salida,int swasci)
{
	int  descr[2];	/* Descriptores de E y S de la turbería */
	int resul,bytesleidos,estado;	
	pid_t  pid;
	pipe (descr);
	int i,nargs;
    FILE *Fretorno;
    char buffer[512];
    
	if(ndebug>2){
		sprintf(msglog,"Ejecución del script: %s",script);
		Log(msglog);
	}
    	
	nargs=SplitParametros(argumentos,parametros," "); // Crea matriz de los argumentos del scripts
	for(i=nargs;i<MAXARGS;i++){
		argumentos[i]=NULL;
	}
    
	if(ndebug>2){
		for(i=0;i<nargs;i++){
			sprintf(msglog,"Parámetro %d del script: %s",i,argumentos[i]);
			Log(msglog);
		}
	}
	
	if(salida!=(char*)NULL){ // Si se solicita retorno de información...
		Fretorno = fopen("/tmp/retorno","w" );
		if (Fretorno==NULL){
			return(8); // Error en la eliminación del archivo temporal de intercambio"
		}
		fclose(Fretorno);
	}	
	
	if((pid=fork())==0){
		/* Proceso hijo que ejecuta el script */
		close (descr[LEER]);
		dup2 (descr[ESCRIBIR], 1);
		close (descr[ESCRIBIR]);
		resul=execv(script,argumentos);
		//resul=execlp (script, script, argumentos[0],argumentos[1],NULL);    
		exit(resul);   
	}
	else {
		if (pid ==-1){
			sprintf(msglog,"***Error en la creación del proceso hijo pid=%d",pid);
			Log(msglog);
			return(-1);
		}
		/* Proceso padre que lee la salida del script */
		close (descr[ESCRIBIR]);
		bytesleidos = read (descr[LEER], buffer, 512);
		while(bytesleidos>0){
			bytesleidos = read (descr[LEER], buffer, 512);
		}
		close (descr[LEER]);
		if(salida!=(char*)NULL){ // Si se solicita retorno de información...			
			FILE *Fretorno = fopen("/tmp/retorno","rb" );
			long lSize;
			if (Fretorno!=NULL){
				fseek (Fretorno , 0 , SEEK_END);  // Obtiene tamaño del fichero.
				lSize = ftell (Fretorno);
				rewind (Fretorno);
				if(lSize>LONGITUD_SCRIPTSALIDA){
					lSize=LONGITUD_SCRIPTSALIDA;
					Log("***Aviso, la información de salida excede de la longitud permitida. Puede haberse truncado");
				}
				fread (salida,1,lSize,Fretorno); 	// Lee contenido del fichero
				for(i=lSize-1;i>=0;i--){
					if(salida[i]<32 && swasci) // Caracter Asci menor de 32
					salida[i]='\0';
				}
				fclose(Fretorno);
			}
			else
				return(9); // Error en la eliminación del archivo temporal de intercambio"
			
		}

		if(ndebug>2){
			sprintf(msglog,"Información devuelta %s",salida);
			Log(msglog);
		}
		//kill(pid,SIGQUIT);
		waitpid(pid,&estado,0);  
		resul=WEXITSTATUS(estado);
		if(ndebug>2){
			sprintf(msglog,"Estatus de finalización del script:%d",resul);
			Log(msglog);
		}   
		return(resul);
		}
	return(-1); 
}
//______________________________________________________________________________________________________
// Función: ReservaMemoria
//
//	 Descripción:
//		Reserva memoria para una variable
//	Parámetros:
//		- lon: 	Longitud en bytes de la reserva
// 	Devuelve:
//		Un puntero a la zona de memoria reservada que ha sido previamente rellena con zeros o nulos
//______________________________________________________________________________________________________
char* ReservaMemoria(int lon)
{
	char *mem;
	mem=(char*)malloc(lon);
	if(mem!=NULL)
		memset(mem,0,lon);
	return(mem);
}
//______________________________________________________________________________________________________
// Función: TCPConnect
//
//	 Descripción:
//		Crea un socket y lo conecta a un servidor
//	Parámetros:
//		- ips : La Dirección IP del servidor
//		- port : Puerto para la comunicación
// 	Devuelve:
//		Un socket para comunicaciones por protocolo TCP 
//______________________________________________________________________________________________________
SOCKET TCPConnect(char *ips,char* port)
{
	SOCKET s;
    struct sockaddr_in server;

	s = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	if (s == INVALID_SOCKET){
		return (INVALID_SOCKET);
	}
	server.sin_family = AF_INET;
	server.sin_port = htons((short)atoi(port));
	server.sin_addr.s_addr = inet_addr(ips);

	if (connect(s, (struct sockaddr *)&server, sizeof(server)) == INVALID_SOCKET)
		return (INVALID_SOCKET);
		
	return(s);
}
//______________________________________________________________________________________________________
// Función: TCPClose
//
//	 Descripción:
//		Cierra una conexión establecida a través de un socket 
//	Parámetros:
//		- s : El socket que implementa la conexión
//______________________________________________________________________________________________________
void TCPClose(SOCKET s){
	close(s);
}
//______________________________________________________________________________________________________
// Función: AbreConexionTCP
//
//	 Descripción:
//		Abre la conexión entre el cliente y el  servidor de administración
//	Parámetros:
//		- ips : La Dirección IP del servidor
//		- port : Puerto para la comunicación
// 	Devuelve:
//		Un socket para comunicaciones por protocolo TCP con el servidor de administración
//______________________________________________________________________________________________________
int AbreConexionTCP()
{
	BOOL swloop=true;
	int vez=0;		

	while(swloop){			
		sock=TCPConnect(Propiedades.servidorhidra,Propiedades.puerto); 
		if(sock!= INVALID_SOCKET){
			return(true);
		}
		if(swloop){
			vez++;
			if (vez>MAXCNX){
				swloop=false;
				UltimoError(2,"AbreConexionTCP()");
				return(false);	
			}
		}
		sleep(5); // Espera dos cinco antes de intentar una nueva conexión con el Servidor de Administración
	}
	return(true);
}
//______________________________________________________________________________________________________
// Función: CierraConexionTCP
//
//	 Descripción:
//		Cierra la conexión entre el cliente y el  servidor de administración
//______________________________________________________________________________________________________
void CierraConexionTCP()
{
	TCPClose(sock);
}
//______________________________________________________________________________________________________
// Función: EnviaTramasHidra
//
//	 Descripción:
//		Envía una trama TCP al Servidor de Administración
//	Parámetros:
//		s: socket TCP
//		trama: contenido a  enviar
// 	Devuelve:
//		true si el envío ha sido correcto o false en caso contrario
//______________________________________________________________________________________________________
int EnviaTramasHidra(SOCKET s,TRAMA *trama)
{
	int lon;
	
	trama->arroba='@';	// cabecera de la trama
	strcpy(trama->identificador,"JMMLCAMDJ");	// identificador de la trama
	trama->ejecutor='1';	// Origen del envío  1=el servidor de administración  2=el cliente  3=el repositorio de imágenes
				
	lon=strlen(trama->parametros);	// Compone la trama 
	lon+=sprintf(trama->parametros+lon,"iph=%s\r",Propiedades.IPlocal);	// Ip del ordenador
	lon+=sprintf(trama->parametros+lon,"ido=%s\r",Propiedades.idordenador);	// Identificador del ordenador
	return(TCPWrite(s,trama));
}
//______________________________________________________________________________________________________
// Función: RecibeTramasHidra
//
//	 Descripción:
//		Recibe una trama TCP del servidor de Administración
//	Parámetros:
//		s: socket TCP
//		trama: contenido a  enviar
// 	Devuelve:
//		true si el envío ha sido correcto o false en caso contrario
//______________________________________________________________________________________________________
int RecibeTramasHidra(SOCKET s,TRAMA *trama)
{
	return(TCPRead(s,trama));
}
//______________________________________________________________________________________________________
// Función: TCPWrite
//
//	 Descripción:
//		Envia una trama por la red (TCP) 
//	Parámetros:
//		s: socket TCP
//		trama: contenido a  enviar
// 	Devuelve:
//		true si el envío ha sido correcto o false en caso contrario
//______________________________________________________________________________________________________
int TCPWrite(SOCKET s,TRAMA* trama)
{
	int nLeft,idx,ret;
	
	Encriptar((char*)trama);
	nLeft = strlen((char*)trama);
	idx = 0;
	while(nLeft > 0){
		ret = send(s,(char*)&trama[idx], nLeft, 0);
		if (ret == 0)
			break;
		else
			if (ret == SOCKET_ERROR){
				return(false);
			}
		nLeft -= ret;
		idx += ret;
	}
	return(true);
}
//______________________________________________________________________________________________________
// Función: TCPRead
//
//	 Descripción:
//		Recibe una trama por la red (TCP) 
//	Parámetros:
//		s: socket TCP
//		trama: contenido a  enviar
// 	Devuelve:
//		true si el envío ha sido correcto o false en caso contrario
//______________________________________________________________________________________________________
int TCPRead(SOCKET s,TRAMA* trama)
{
	int ret;

	ret = recv(s,(char*)trama,LONGITUD_TRAMA,0);
	if (ret == 0) // conexión cerrada por parte del cliente (Graceful close)
		return (false);
	else{ 
		if (ret == SOCKET_ERROR){
			return (false);
		}
		else{ // Datos recibidos
			Desencriptar((char*)trama);
			trama->parametros[ret-11]='\0'; // Coloca caracter fin de cadena en trama
			return(true);
		}
	}
	
}
//______________________________________________________________________________________________________
// Función: UDPConnect
//
//	 Descripción:
//		Crea un socket UDP para la comunicación con su repositorio
//	Parámetros:
//		Ninguno
// 	Devuelve:
//		Un socket para comunicaciones por protocolo UDP
//______________________________________________________________________________________________________
SOCKET UDPConnect()
{
	SOCKET socket_c; 

	socket_c = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP);
	if (socket_c == SOCKET_ERROR)
		return (INVALID_SOCKET);
	return(socket_c);
}
//______________________________________________________________________________________________________
// Función: EnviaTramaRepo
//
//	 Descripción:
//		Envía una trama UDP a su repositorio de imágenes
//	Parámetros:
//		s: socket UDP
//		trama: contenido a  enviar
//		iprepo: Dirección IP del repositorio
//		puertorepo: Puerto de destino donde el repositorio espera la trama
// 	Devuelve:
//		true si el envío ha sido correcto o false en caso contrario
//______________________________________________________________________________________________________
int EnviaTramaRepo(SOCKET s,TRAMA* trama, char* iprepo,char *puertorepo)
{
	int ret,lon;
	struct sockaddr_in  addrRepo;
	 
	trama->arroba='@';	// cabecera de la trama
	strcpy(trama->identificador,"JMMLCAMDJ");	// identificador de la trama
	trama->ejecutor='2';	// Origen del envío  1=el servidor  2=el cliente  3=el repositorio de imágenes
				
	lon=strlen(trama->parametros); 	// Compone la trama
	lon+=sprintf(trama->parametros+lon,"iph=%s\r",Propiedades.IPlocal);	// Ip local del ordenador
	lon+=sprintf(trama->parametros+lon,"ido=%s\r",Propiedades.idordenador);	// identificador del ordenador
	
	addrRepo.sin_family = AF_INET;
    addrRepo.sin_port = htons((short)atoi(puertorepo)); //  Puerto del repositorio
    addrRepo.sin_addr.s_addr = inet_addr(iprepo); //  Dirección IP del repositorio
	
	Encriptar((char*)trama); // Encripta la trama
	ret = sendto(s,(char *)trama,lon+11,0,(struct sockaddr *)&addrRepo, sizeof(addrRepo));
    if (ret == SOCKET_ERROR)
		return(false);
	return true;
}
//______________________________________________________________________________________________________
// Función: RecibeTramaRepo
//
//	 Descripción:
//		Recibe una trama UDP de su repositorio de imágenes
//	Parámetros:
//		s: socket UDP con el que se envío anteriormente una trama al repositorio
// 	Devuelve:
//		true si la receción ha sido correcta o false en caso contrario
//______________________________________________________________________________________________________
int RecibeTramaRepo(SOCKET s)
{
	int ret;
	struct sockaddr_in addrRepo;
	
	socklen_t iAddrSize = sizeof(addrRepo);
	ret = recvfrom(s,(char *)trama, LONGITUD_TRAMA,0,(struct sockaddr *)&addrRepo,&iAddrSize);
	if (ret != SOCKET_ERROR){
		Desencriptar((char*)trama);	// Desencripta la trama
		return(true);
	}
	return(false);
}
//______________________________________________________________________________________________________
// Función: CreateTextFile
//
//	Descripción: 
//		Crea un fichero de texto local y escribe en él cierto contenido
//	Parámetros:
//		- nomfile: Nombre del fichero
//		- texto: Texto a escribir en el fichero
//	Devuelve:
//		- La longitud en bytes del contenido escrito
//______________________________________________________________________________________________________
long CreateTextFile(char *nomfile,char *texto)
{
	long lSize;
	FILE *f;
	f = fopen(nomfile,"wt");
	if(!f){ // El fichero por algún motivo no ha podido crearse
		UltimoError(3,"CreateTextFile()");
		return(0);
	}
	lSize=strlen(texto);
	fwrite(texto,1,lSize,f);	// Escribe el contenido del fichero
	fclose(f);
	return(lSize);
}
//______________________________________________________________________________________________________
// Función: ExisteFichero
//
//	Descripción: 
//		Comprueba si un archivo existe en su repositorio
//	Parámetros:
//		- nomfile : Nombre del fichero
//	Devuelve:
//		true si el archivo existe o false en caso contrario
// ________________________________________________________________________________________________________
int ExisteFichero(char *nomfile)
{
	SOCKET udpsock;
	int res;
	
	udpsock=UDPConnect(); 
	if (udpsock == INVALID_SOCKET){ 
		UltimoError(15,"ExisteFichero()");
		return(false);
	}
	sprintf(trama->parametros,"nfn=ExisteFichero\rnfl=%s\r",nomfile);	// Nombre de la función a ejecutar en el  servidor de administración
	if(EnviaTramaRepo(udpsock,trama,Propiedades.iprepo,Propiedades.puertorepo)){
		res=RecibeTramaRepo(udpsock);
		close(udpsock);
		if(res)
			return(GestionTramas(trama));
	}
	else{
		UltimoError(16,"ExisteFichero()");
		return(false);
	}
	return(true);
}
//______________________________________________________________________________________________________
// Función: RemoveFile
//
//	Descripción: 
//		Elimina un fichero del repositorio 
//	Parámetros:
//		- nomfile : Nombre del fichero
//	Devuelve:
//		true si el archivo se ha eliminado correctamente o false en caso contrario
// ________________________________________________________________________________________________________
int RemoveFile(char *nomfile)
{
	SOCKET udpsock;
	int res;
	
	udpsock=UDPConnect(); 
	if (udpsock == INVALID_SOCKET){ 
		UltimoError(15,"RemoveFile()");
		return(false);
	}
	sprintf(trama->parametros,"nfn=EliminaFichero\rnfl=%s\r",nomfile);	// Nombre de la función a ejecutar en el  servidor de administración
	if(EnviaTramaRepo(udpsock,trama,Propiedades.iprepo,Propiedades.puertorepo)){
		res=RecibeTramaRepo(udpsock);
		close(udpsock);
		if(res)
			return(GestionTramas(trama));
	}
	else{
		UltimoError(16,"RemoveFile()");
		return(false);
	}
	return(true);
}
//______________________________________________________________________________________________________
// Función: LoadTextFile
//
//	Descripción: 
//		Lee un fichero del repositorio 
//	Parámetros:
//		- nomfile : Nombre del fichero
//	Devuelve:
//		true si el proceso es correcto y false en caso contrario
//	Especificaciones:
//		En los parametros de la trama se copian el contenido del del archivo de comandos		
// ________________________________________________________________________________________________________
int LoadTextFile(char *nomfile)
{
	SOCKET udpsock;
	int res;
	char *txt;
		
	udpsock=UDPConnect(); 
	if (udpsock == INVALID_SOCKET){ 
		UltimoError(15,"LoadTextFile()");
		return(false);
	}
	sprintf(trama->parametros,"nfn=LeeFicheroTexto\rnfl=%s\r",nomfile);	// Nombre de la función a ejecutar en el  servidor de administración
	if(EnviaTramaRepo(udpsock,trama,Propiedades.iprepo,Propiedades.puertorepo)){
		res=RecibeTramaRepo(udpsock);
		close(udpsock);
		if(res){
			if(GestionTramas(trama)){
				txt=TomaParametro("txt",trama->parametros); // Toma contenido del fichero de  comandos
				strcpy(trama->parametros,txt);
				if(ndebug>4){
					sprintf(msglog,"Archivo de comando:\r%s",trama->parametros);
					Log(msglog);
				}
				return(true); // Devuelve contrenido del fichero
			}
			else{
				UltimoError(3,"LoadTextFile()");
				return(false);				
			}
		}
		else{
			UltimoError(16,"LoadTextFile()");
			return(false);
		}				
	}
	else{
		UltimoError(16,"LoadTextFile()");
		return(false);
	}
}
//______________________________________________________________________________________________________
// Función: ProcesaComandos
//
//	Descripción: 
// 		Espera comando desde el Servidor de Administración para ejecutarlos
//	Parámetros:
//		Ninguno
//	Devuelve:
//		true si el archivo se ha eliminado correctamente o false en caso contrario
// ________________________________________________________________________________________________________
int ProcesaComandos()
{
		sprintf(filecmd,"/comandos/CMD_%s",Propiedades.IPlocal);	// Nombre del fichero de comandos		
		if(ExisteFichero(filecmd))	// Borra fichero de comandos si previamente exista de anteriores procesos
			RemoveFile(filecmd);
		if(!DisponibilidadComandos(true)){	// Notifica  al servidor de Adminsitración su disponibilidad para recibir comandos
			UltimoError(0,"ProcesaComandos()");	
			return(false);	
		}
		PRCCMD=true;
		while(PRCCMD){	// Bucle de espera de comandos interactivos
			if(ExisteFichero(filecmd)){	// Busca fichero de comandos
				Log("Comando recibido desde el Servidor de Administración");
				if(!LoadTextFile(filecmd)){	// Toma comando
					UltimoError(1,"ProcesaComandos()");
					return(false);
				}
				GestionTramas(trama);	// Analiza la trama y ejecuta el comando
				Log("Procesa comandos pendientes");
				ComandosPendientes(); // Bucle para procesar comandos pendientes
				Log("Disponibilidad para comandos interactivos activada ...");				
				if(!DisponibilidadComandos(true)){	// Notifica  al servidor de Administración su disponibilidad para recibir comandos
					UltimoError(0,"ProcesaComandos()");	
					return(false);
				}
				if(!RemoveFile(filecmd)){	// Lo elimina
					UltimoError(0,"ProcesaComandos()");
					return(false);
				}
			}
			sleep(5);	// Espera 5 segundos antes de volver a esperar comandos
		}
		return(true);
}
//______________________________________________________________________________________________________
// Función: DisponibilidadComandos
//
//	Descripción: 
// 		Notifica al servidor su disponibilidad a recibir comandos ( Lgica negativa )
//	Parámetros:
//		- swdis : Indica disponibilidad si es true y NO disponibilidad en caso de ser false
//	Devuelve:
//		true si el proceso es correcto y false en caso contrario
// ________________________________________________________________________________________________________
int DisponibilidadComandos(int swdis)
{
		int lon;

		lon=sprintf(trama->parametros,"nfn=DisponibilidadComandos\r");
		if(!swdis)
			lon+=sprintf(trama->parametros+lon,"swd=0\r");	// No disponible				
		else
			lon+=sprintf(trama->parametros+lon,"swd=1\r");	// Disponible
			
		if(AbreConexionTCP()){
			if(!EnviaTramasHidra(sock,trama)){
				UltimoError(21,"DisponibilidadComandos()"); // No se pudo recuperar la configuración hardware
				return(false);
			}
			if(!RecibeTramasHidra(sock,trama)){
				UltimoError(22,"DisponibilidadComandos()"); // No se pudo recuperar la configuración hardware
				return(false);
			}
			CierraConexionTCP();
			GestionTramas(trama);	// Analiza la trama
		}
		else{
			UltimoError(2,"DisponibilidadComandos()");	
			return(false);
		}
		return(true);
}
//______________________________________________________________________________________________________
// Función: GestionTramas
//
//	Descripción: 
//		Gestiona las tramas recibidas por la red 
//	Parámetros:
//		- trama : Una trama recibida
//	Devuelve:
//		true o false dependiendo del éxito en la ejecución del comandoo si se trata de una trama
//		del Servidor de Administración o bien del resultado de la petición de información al repositorio
// ________________________________________________________________________________________________________
int GestionTramas(TRAMA *trama)
{
	TRAMA *nwtrama=NULL;
	int res;
	char *nombrefuncion;
	INTROaFINCAD(trama->parametros);
	nombrefuncion=TomaParametro("nfn",trama->parametros); 
	nwtrama=(TRAMA*)ReservaMemoria(LONGITUD_TRAMA);	// Reserva buffer  para la trama	devuelta		
	if(!nwtrama){
		UltimoError(1,"GestionTramas()");
		return(false);
	}
	if(ndebug>4){
		sprintf(msglog,"Gestión de tramas.-Función a ejecutar:%s",nombrefuncion);
		Log(msglog);
	}
	// Mensajes entre el cliente y el Servidor de Administración
	res=strcmp(nombrefuncion,"Apagar");
	if(res==0)
		return(Apagar(trama,nwtrama));

	res=strcmp(nombrefuncion,"Arrancar");
	if(res==0)
		return(Arrancar(trama,nwtrama));
			
	res=strcmp(nombrefuncion,"Reiniciar");
	if(res==0)
		return(Reiniciar(trama,nwtrama));
			
	res=strcmp(nombrefuncion,"IniciarSesion");
	if(res==0)
		return(IniciarSesion(trama,nwtrama));

	res=strcmp(nombrefuncion,"RESPUESTA_InclusionCliente");
	if(res==0)
		return(RESPUESTA_InclusionCliente(trama));
			
	res=strcmp(nombrefuncion,"Actualizar");
	if(res==0)
		return(Actualizar());		
		
	res=strcmp(nombrefuncion,"NoComandosPtes");
	if(res==0)
		return(NoComandosPtes());
			
	res=strcmp(nombrefuncion,"Cortesia");
	if(res==0)
		return(Cortesia());			
					
	
	res=strcmp(nombrefuncion,"ExecShell");
	if(res==0)
		return(ExecShell(trama,nwtrama));			
			
	res=strcmp(nombrefuncion,"CrearPerfilSoftware");
	if(res==0)
		return(CrearPerfilSoftware(trama,nwtrama));			

	res=strcmp(nombrefuncion,"RestaurarImagen");
	if(res==0)
		return(RestaurarImagen(trama,nwtrama));			
	
	res=strcmp(nombrefuncion,"TomaConfiguracion");
	if(res==0)
		return(TomaConfiguracion(trama,nwtrama));		
		
	res=strcmp(nombrefuncion,"InventarioHardware");
	if(res==0)
		return(InventarioHardware(trama,nwtrama));		
		
	res=strcmp(nombrefuncion,"InventarioSoftware");
	if(res==0)
		return(InventarioSoftware(trama,nwtrama));		

	res=strcmp(nombrefuncion,"ParticionaryFormatear");
	if(res==0)
		return(ParticionaryFormatear(trama,nwtrama));				
			
	// Mensajes entre el cliente y el repositorio		
	res=strcmp(nombrefuncion,"Respuesta_ExisteFichero");
	if(res==0){
		res=atoi(TomaParametro("res",trama->parametros)); 
		return(res);
	}
			
	res=strcmp(nombrefuncion,"Respuesta_EliminaFichero");
	if(res==0){
		res=atoi(TomaParametro("res",trama->parametros));
		return(res);
	}
		
	res=strcmp(nombrefuncion,"Respuesta_LeeFicheroTexto");
	if(res==0){
		res=atoi(TomaParametro("res",trama->parametros));
		return(res);
	}			

	UltimoError(4,"GestionTramas()");
	return(false);	
}
//______________________________________________________________________________________________________
// Función: Cortesia
//
//	 Descripción:
//		 Respuesta estandar del Servidor de Administración
//	Parámetros:
//		Ninguno
// 	Devuelve:
//		true siempre
//	Especificaciones:
//		Esta función se ejecuta de forma estandar para cerrar la conversación con el Servidor de Administración
//______________________________________________________________________________________________________
int Cortesia(){
	 return(true);
}
//______________________________________________________________________________________________________
// Función: NoComandosPtes
//
//	 Descripción:
//		 Conmuta el switch de los comandos pendientes y lo pone a false
//	Parámetros:
//		Ninguno
// 	Devuelve:
//		true siempre
//	Especificaciones:
//		Cuando se ejecuta esta función se sale del bucle que recupera los comandos pendientes en el servidor y 
//		el cliente pasa a a estar disponible para recibir comandos desde el éste.
//______________________________________________________________________________________________________
int NoComandosPtes(){
	CMDPTES=false; // Corta el bucle de comandos pendientes
	return(true);
}
//______________________________________________________________________________________________________
// Función: TomaIPlocal
//
//	 Descripción:
//		Recupera la IP local
//	Parámetros:
//		Ninguno
// 	Devuelve:
//		Una cadena con el valor de la IP en formato xxx.xxx.xxx.xxx
//	Especificaciones:
//		En caso de no encontrar la IP o generarse algún error se devuelve la dirección 0.0.0.0
//______________________________________________________________________________________________________
int TomaIPlocal()
{
   	int herror;
	
	sprintf(cmdshell,"%s/admGetIpAddress",HIDRASCRIPTS);
	herror=EjecutarScript (cmdshell,NULL,IPlocal,true);	
	if(herror){
		UltimoErrorScript(herror,"TomaIPlocal()"); // Se ha producido algún error
		return(false);
	}
	return(true); 
}
//______________________________________________________________________________________________________
// Función: InclusionCliente
//	 Descripción:
//		Abre una sesión en el servidor de administración y registra al cliente en el sistema
//	Parámetros:
//		Ninguno
// 	Devuelve:
//		true si el registro ha tenido éxito o false en caso contrario
//______________________________________________________________________________________________________
int InclusionCliente()
{ 
	int lon;	
	char *parametroscfg;
	
	parametroscfg=(char*)ReservaMemoria(256);
	if(!parametroscfg){
		UltimoError(1,"InclusionCliente()"); // No se pudo reservar memoria
		return(false);
	}
	
	char *disco=(char*)ReservaMemoria(2);
	sprintf(disco,"1"); // Siempre el disco 1
	parametroscfg=LeeConfiguracion(disco);	// Toma configuración
	
	if(ndebug>3){
		sprintf(msglog,"CONFIGURACION=%s",parametroscfg);
		Log(msglog);
	}
	
	if(!parametroscfg){
		UltimoError(18,"InclusionCliente()"); // No se pudo recuperar la configuración hardware
		return(false);
	}
	lon=sprintf(trama->parametros,"nfn=InclusionCliente\r");	// Nombre de la función a ejecutar en el servidor de Adminsitración 
	lon+=sprintf(trama->parametros+lon,"cfg=%s\r",parametroscfg);	// Configuración de los Sistemas Operativos del cliente
	if(AbreConexionTCP()){
		Log("Enviando peticion de inclusion al Servidor de Administración");
 		if(!EnviaTramasHidra(sock,trama)){
			UltimoError(21,"InclusionCliente()"); // No se pudo recuperar la configuración hardware
			return(false);
		}
		Log("Recibiendo respuesta del Servidor de Administración");
		if(!RecibeTramasHidra(sock,trama)){
			UltimoError(22,"InclusionCliente()"); // No se pudo recuperar la configuración hardware
			return(false);
		}
		CierraConexionTCP();
		if(!GestionTramas(trama)){	// Analiza la trama
			UltimoError(0,"InclusionCliente()");
			return(false);		
		}
		return(true);
	}
	else{
		UltimoError(2,"InclusionCliente()"); // No se pudo conectar con el servidor de administración
		return(false);
	}		
	return(true);				
}
//______________________________________________________________________________________________________
// Función: RESPUESTA_InclusionCliente
//
//	 Descripción:
//  		Respuesta del servidor de administración a la petición de inicio enviando los datos identificativos del cliente y otras configuraciones
//	Parámetros:
//		trama:	Trama recibida por el cliente desde el Servidor de Administración
// 	Devuelve:
//		true si el registro ha tenido éxito o false en caso contrario
//______________________________________________________________________________________________________
int RESPUESTA_InclusionCliente(TRAMA *trama)
{
	strcpy(Propiedades.idordenador,TomaParametro("ido",trama->parametros));	// Identificador del ordenador
	strcpy(Propiedades.nombreordenador,TomaParametro("npc",trama->parametros));	//  Nombre del ordenador
	strcpy(Propiedades.idaula,TomaParametro("ida",trama->parametros));	//  Identificador del aula a la que pertenece
	strcpy(Propiedades.idperfilhard,TomaParametro("ifh",trama->parametros));	// Identificador del perfil hardware del ordenador
	strcpy(Propiedades.servidorhidra,TomaParametro("hrd",trama->parametros));	// Dirección IP del servidor de Administración
	strcpy(Propiedades.puerto,TomaParametro("prt",trama->parametros));		// Puerto de comunicación con el servidor de Administración
	strcpy(Propiedades.iprepo,TomaParametro("ipr",trama->parametros));	// Dirección IP del repositorio
	strcpy(Propiedades.puertorepo,TomaParametro("repr",trama->parametros));	// Puerto de comunicación con el repositorio

	// Guarda items del menú
	char* cabmenu=TomaParametro("cmn",trama->parametros);
	if (cabmenu){
		swmnu=true;
		char *auxCab[15]; 
		SplitParametros(auxCab,cabmenu,"&");	// Caracter separador de los elementos de un item
		strcpy(CabMnu.titulo,auxCab[0]);	// Tìtulo del menú
		strcpy(CabMnu.coorx,auxCab[1]);	// Coordenada x del menú público
		strcpy(CabMnu.coory,auxCab[2]);	// Coordenada y del menú público
		strcpy(CabMnu.modalidad,auxCab[3]);	// Modalidad de columnas del menú público
		strcpy(CabMnu.scoorx,auxCab[4]);	// Coordenada x del menú privado
		strcpy(CabMnu.scoory,auxCab[5]);	// Coordenada y del menú privado
		strcpy(CabMnu.smodalidad,auxCab[6]);	// Modalidad de columnas del menú privado
		strcpy(CabMnu.resolucion,auxCab[7]);	// Resolución de pantalla
	}
	/*char* menu=TomaParametro("mnu",trama->parametros);	 // Menú estandar
	
	char* auxMenu[MAXITEMS],auxItem[10];
	int iMnu=SplitParametros(auxMenu,menu,"?"); // Caracter separador de  los item 
	int i,nitem;
	
	for( i = 0; i<iMnu; i++){
		struct s_Item Item;
		nitem=SplitParametros(auxItem,auxMenu[i],"&");	// Caracter separador de los elementos de un item
		strcpy(Item.idaccionmenu,auxItem[0]);	// Identificador de la acción
		strcpy(Item.urlimg,auxItem[1]);	// Url de la imagen del item
		strcpy(Item.literal,auxItem[2]);	// Literal del item
		strcpy(Item.tipoitem,auxItem[3]);	// Tipo de item ( Público o privado )
		strcpy(Item.tipoaccion,auxItem[4]);	// Tipo de acción ( Procedimiento,Tarea oTrabajo )
		tbMenu[i]=Item;
	}
	contitems=i;	// Número de items totales de los dos menús
	*/
	return(true);
}
//______________________________________________________________________________________________________
// Función: ComandosPendientes
//
//	 Descripción:
// 		 Búsqueda de acciones pendientes en el  servidor de administración
//______________________________________________________________________________________________________
int ComandosPendientes()
{
	CMDPTES=true;
	while(CMDPTES){
		sprintf(trama->parametros,"nfn=ComandosPendientes\r");	// Nombre de la función a ejecutar en el  servidor de administración
		if(AbreConexionTCP()){
			if(!EnviaTramasHidra(sock,trama)){
				UltimoError(21,"ComandosPendientes()"); // No se pudo recuperar la configuración hardware
				return(false);
			}
			if(!RecibeTramasHidra(sock,trama)){
				UltimoError(22,"ComandosPendientes()"); // No se pudo recuperar la configuración hardware
				return(false);
			}
			CierraConexionTCP();
			GestionTramas(trama);	// Analiza la trama
		}
		else{
			UltimoError(2,"ComandosPendientes()"); // No se pudo conectar con el servidor de Administración
			return(false);
		}
	}
	CMDPTES=false;
	return(true);
}
//_____________________________________________________________________________________________________
// Función: Arrancar
//
//	 Descripción:
//		Contesta ante un comando de arrancar
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true siempre
//_____________________________________________________________________________________________________
int Arrancar(TRAMA *trama,TRAMA *nwtrama)
{
	sprintf(nwtrama->parametros,"nfn=RESPUESTA_Arrancar\r");					
	return(RespuestaEjecucionComando(trama,nwtrama,true));	
}
//_____________________________________________________________________________________________________
// Función: Apagar
//
//	 Descripción:
//		Apaga el cliente
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//_____________________________________________________________________________________________________
int Apagar(TRAMA *trama,TRAMA *nwtrama)
{ 
	int res;
	
	sprintf(nwtrama->parametros,"nfn=RESPUESTA_Apagar\r");					
	res=RespuestaEjecucionComando(trama,nwtrama,true);	
	sprintf(cmdshell,"poweroff");
	res=ExecBash(cmdshell);	
	if(!res){
		UltimoErrorScript(10,"Reiniciar()");	// Se ha producido algún error
	}	
	return(res); 		
}
//______________________________________________________________________________________________________
// Función: Reiniciar
//
//	 Descripción:
//		Reinicia el cliente
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//______________________________________________________________________________________________________
int Reiniciar(TRAMA *trama,TRAMA *nwtrama)
{
	int res;
	
	sprintf(nwtrama->parametros,"nfn=RESPUESTA_Reiniciar\r");					
	res=RespuestaEjecucionComando(trama,nwtrama,true);	
	sprintf(cmdshell,"reboot");
	res=ExecBash(cmdshell);	
	if(!res){
		UltimoErrorScript(10,"Reiniciar()");	// Se ha producido algún error
	}	
	return(res); 	
}
//______________________________________________________________________________________________________
// Función: IniciarSesion
//
//	 Descripción:
//		Inicia Sesión en algún sistema operativo instalado en la máquina
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//		*** En ese proceso se devuelve correcto aún sabiendo que no se se sabe si va a funcionar
//			pero esto evita que si se ha lanzado con seguimiento, la tarea no quede sin norificar.
//______________________________________________________________________________________________________
int  IniciarSesion(TRAMA *trama,TRAMA *nwtrama)
{
	int res;
	char *particion=TomaParametro("par",trama->parametros);
	char *disco=(char*)ReservaMemoria(2);
	sprintf(disco,"1"); // Siempre el disco 1

	sprintf(nwtrama->parametros,"nfn=RESPUESTA_IniciarSesion\r");					
	res=RespuestaEjecucionComando(trama,nwtrama,true);
	sprintf(cmdshell,"%s/admBootOs %s %s",HIDRASCRIPTS,disco,particion);
	res=ExecBash(cmdshell);	
	if(!res){
		UltimoErrorScript(10,"IniciarSesion()");	// Se ha producido algún error
	}	
	return(res); 
}
//______________________________________________________________________________________________________
// Función: Actualizar
//
//	 Descripción:
//		Actualiza los datos de un ordenador  como si volviera a solicitar la entrada  en el sistema al  servidor de administración
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//______________________________________________________________________________________________________
int Actualizar()
{ 
	int res;
	
	kill(pidmenu,SIGQUIT);
	MuestraMensaje(1);
	res=InclusionCliente();
	MuestraMenu(URLMENU);
	return(res);
}
//______________________________________________________________________________________________________
// Función: CrearPerfilSoftware
//
//	 Descripción:
//		Genera una imagen de una partición
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//_____________________________________________________________________________________________________
int CrearPerfilSoftware(TRAMA*trama,TRAMA*nwtrama)
{
		int res=0;
		char *wparticion=TomaParametro("par",trama->parametros);	// Partición de donde se crear el perfil
		char *widperfilsoft=TomaParametro("ifs",trama->parametros);	// Perfil software a crear
		char *widperfilhard=TomaParametro("ifh",trama->parametros);	// Perfil hardware del ordenador
		char *wnemonico=TomaParametro("nem",trama->parametros);	// Nemónico del S.O. de la partición
				
		char *disco=(char*)ReservaMemoria(2);
		sprintf(disco,"1"); // Siempre el disco 1
					
		char pathperfil[250];
		sprintf(pathperfil,"%s/%s",HIDRACHEIMAGENES,wnemonico);	// Path del perfil creado	
			
		char fileperfil[64];
		sprintf(fileperfil,"PS%s_PH%s",widperfilsoft,widperfilhard);	// Nombre de la imagen ( del perfil creado)
		
		char filemasterboot[64];
		sprintf(filemasterboot,"PS%s_PH%s.msb",widperfilsoft,widperfilhard);	// Idem para el sector de arranque MBR
		res=CrearPerfil(disco,fileperfil,pathperfil,wparticion,Propiedades.iprepo);
		Log("Finalizada la creacion del perfil software");
		int lon;
		lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_CrearPerfilSoftware\r");	
		lon+=sprintf(nwtrama->parametros+lon,"ifs=%s\r",widperfilsoft);		
		lon+=sprintf(nwtrama->parametros+lon,"ifh=%s\r",widperfilhard);		
		RespuestaEjecucionComando(trama,nwtrama,res);	
		return(res);	
}
//______________________________________________________________________________________________________
// Función: CrearPerfil
//
//	 Descripción:
//		Crea una imagen de una partición
//	Parámetros:
//		-disco		Disco a clonar  1,2,3..
//		-fileimg	Nombre de la imagen
//		-pathimg	Ruta de la imagen
//		-particion	Partición a clonar
//		-iprepo	Dirección IP del repositorio ( Si es la IP local el repositorio será la caché)
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//______________________________________________________________________________________________________
int CrearPerfil(char* disco,char* fileimg,char* pathimg,char* particion,char*iprepo)   
{
	int herror;
	sprintf(cmdshell,"%s/admCreateImage",HIDRASCRIPTS);
	sprintf(parametros,"%s %s %s %s %s","createImage",disco,particion,"REPO",fileimg);
	
	if(ndebug>3){
		sprintf(msglog,"Creando Perfil Software disco:%s, partición:%s, Repositorio:%s, Imagen:%s, Ruta:%s",disco,particion,Propiedades.iprepo,fileimg,"");
		Log(msglog);
	}
	
	herror=EjecutarScript(cmdshell,parametros,NULL,true);
	if(herror){
		UltimoErrorScript(herror,"CrearPerfil()");	 // Se ha producido algún error
		return(false);
	}
	else
		return(true); 
}
//______________________________________________________________________________________________________
// Función: Nemonico
//
//	 Descripción:
//		Devuelve el código de un nemonico de S.O.
//	Parámetros:
//		-nem		Nemonico del S.O.
// 	Devuelve:
//		El código del nemónico
//______________________________________________________________________________________________________
int Nemonico(char* nem)
{
	if(strcmp(nem,"MsDos")==0) 
		return(MsDos);
	if(strcmp(nem,"Win98")==0)
		return(Win98);
	if(strcmp(nem,"Win2K")==0)
		return(Win2K);
	if(strcmp(nem,"WinXP")==0) 
		return( WinXP);
	if(strcmp(nem,"Linux")==0)
		return(Linux);
	return(0);
}
//______________________________________________________________________________________________________
// Función: RestaurarImagen
//
//	 Descripción:
//		Restaura una imagen en una partición
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//______________________________________________________________________________________________________
int RestaurarImagen(TRAMA*trama,TRAMA*nwtrama)
{
		int res=0;
		char *wparticion=TomaParametro("par",trama->parametros);	// partición de donde se crear el perfil
		char *widimagen=TomaParametro("idi",trama->parametros);	// Identificador de la imagen		
		char *widperfilsoft=TomaParametro("ifs",trama->parametros);	// Perfil software a crear
		char *widperfilhard=TomaParametro("ifh",trama->parametros);	// Perfil hardware del  ordenador
		//char *widcentro=TomaParametro("idc",trama->parametros);	// Identificador del Centro
		//char *wtipopar=TomaParametro("tpa",trama->parametros);	// Tipo de partición
		char *wnemonico=TomaParametro("nem",trama->parametros);	// Nemonico del S.O.  contenido en la partición
		//char *wswrestauraimg=TomaParametro("swr",trama->parametros);	// Indica si la imagen a restaurar contiene un S.O. distinto al actual
		//char *widsoftincremental=TomaParametro("icr",trama->parametros);	// Cadena con los identificadores de lsoftware incremental
		char *wpathimagen=TomaParametro("pth",trama->parametros);	// Indica si la imagen se descargar de la caché(cache) o del servidor(net)
		if(wpathimagen=='\0') wpathimagen="1";	// Por defecto de caché
		
		char *disco=(char*)ReservaMemoria(2);
		sprintf(disco,"1"); // Siempre el disco 1		
					
		char *compres=(char*)ReservaMemoria(10);
		sprintf(compres,"gzip"); // Método de compresión		
		
		char *mettran=(char*)ReservaMemoria(10);
		sprintf(mettran,"unicast"); // Método de transferencia por defecto
						
		int idxpath=atoi(wpathimagen);
		if(!CACHEEXISTS) idxpath=2;	// Sin no existe cache siempre desde el servidor
		//if(wswrestauraimg=="O")
		//	res=reparticiona((int)wparticion,wtipopar);	// Reparticiona si la imagen va a una partición distinta a la original
		if(res==0){
			char pathperfil[250];
			if(idxpath==2){
				sprintf(pathperfil,"%s/%s",HIDRASRVIMAGENES,wnemonico);	
			}
			else{
				if(idxpath==1){
					sprintf(pathperfil,"%s/%s",HIDRACHEIMAGENES,wnemonico);					
				}
			}
			char fileperfil[64];
			sprintf(fileperfil,"PS%s_PH%s",widperfilsoft,widperfilhard);	// Nombre del fichero del perfil creado	
			char filemasterboot[64];
			sprintf(filemasterboot,"PS%s_PH%s.msb",widperfilsoft,widperfilhard);	// Idem para el sector de arranque MBR			
			res=RestaurandoImagen(disco,compres,mettran,fileperfil,pathperfil,wparticion,Propiedades.iprepo);
			// Toma la nueva configuración
			char *parametroscfg=LeeConfiguracion(disco);
			Log("Finalizada la restauracion de imagen");

			int lon;			
			lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_RestaurarImagen\r");	
			lon+=sprintf(nwtrama->parametros+lon,"cfg=%s\r",parametroscfg);		
			lon+=sprintf(nwtrama->parametros+lon,"idi=%s\r",widimagen);	
			lon+=sprintf(nwtrama->parametros+lon,"par=%s\r",wparticion);	
			RespuestaEjecucionComando(trama,nwtrama,res);	
			return(res);		
		}
		return(false);
}
//______________________________________________________________________________________________________
// Función: RestaurandoImagen
//
//	 Descripción:
//		Restaura na imagen en una partición
//	Parámetros:
//		-disco		Disco a clonar  1,2,3..
//		-fileimg	Nombre de la imagen
//		-pathimg	Ruta de la imagen
//		-particion	Partición a clonar
//		-iprepo	Dirección IP del repositorio ( Si es la IP local el repositorio será la caché)
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//____________________________________________________________________________________________________
int RestaurandoImagen(char* disco,char* compres,char* mettran,char* fileimg,char* pathimg,char* particion,char*iprepo)   
{
   	int herror;
	
	sprintf(cmdshell,"%s/admRestoreImage",HIDRASCRIPTS);
	sprintf(parametros," %s %s %s %s %s","admRestoreImage","REPO",fileimg,disco,particion);

	if(ndebug>3){
		sprintf(msglog,"Restaurando Imagen disco:%s, partición:%s, Repositorio:%s, Imagen:%s",disco,particion,Propiedades.iprepo,fileimg);
		Log(msglog);
	}
	
	herror=EjecutarScript(cmdshell,parametros,NULL,true);
	if(herror){
		UltimoErrorScript(herror,"RestaurandoImagen()");	// Se ha producido algún error
		return(false);
	}
	else
		return(true); 
}

//______________________________________________________________________________________________________
// Función: ParticionaryFormatear
//
//	 Descripción:
//		Modifica la tabla de particiones del sector de arranque master y formatea particiones
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//____________________________________________________________________________________________________
int ParticionaryFormatear(TRAMA*trama,TRAMA*nwtrama)
{
	int res,i,parfor;
	char* parametroscfg;
	char ch[2],*parhdc[8];
	char *PrimaryPartitions=TomaParametro("ppa",trama->parametros);
	char *LogicalPartitions=TomaParametro("lpa",trama->parametros);
	char *HDCleanPartition=TomaParametro("hdc",trama->parametros);

	char *disco=(char*)ReservaMemoria(2);
	sprintf(disco,"1"); // Siempre el disco 1
	
	Log("Creando o modificando tabla de particiones");
	res=Particionar(disco,PrimaryPartitions,LogicalPartitions); // Creando las particiones
	if(res){
		strcpy(ch,";");	// Caracter delimitador
		parfor=SplitParametros(parhdc,HDCleanPartition,ch);
		for(i = 0; i<parfor; i++){ // Formateando particiones
			res=Formatear(disco,parhdc[i]);
			if(!res) break;
		}
	}
	Log("Finalizado el particionado y formateado de particiones");
	parametroscfg=LeeConfiguracion(disco);	// Toma la nueva configuración
	
	int lon;
	lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_ParticionaryFormatear\r");		
	lon+=sprintf(nwtrama->parametros+lon,"cfg=%s\r",parametroscfg);
	RespuestaEjecucionComando(trama,nwtrama,res);	
	
	return(res);
}
//______________________________________________________________________________________________________
// Función: Particionar
//
//	 Descripción:
//		Modifica la tabla de particiones del sector de arranque master pero SIN formatear ninguna partición
//	Parámetros:
//		- PrParticion: Cadena con la sintaxis de particionado de las particiones primarias
//		- LoParticion: Cadena con la sintaxis de particionado de las particiones secundarias
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//______________________________________________________________________________________________________
int Particionar(char* disco,char* PrParticion,char* LoParticion)
{
	if (strlen(PrParticion)>0){
		if(Particionando(disco,PrParticion,"admCreatePrimaryPartitions")){	// Particiones Primarias
			if (strlen(LoParticion)>0)
				return(Particionando(disco,PrParticion,"admCreateLogicalPartitions"));	// Particiones Logicas
			else
				return(true);
		}
		else
			return(false);
	}
	if (strlen(LoParticion)>0)
		return(Particionando(disco,PrParticion,"admCreateLogicalPartitions"));
	else
		return(false);
}
//______________________________________________________________________________________________________
// Función: Particionando
//
//	 Descripción:
//		Modifica la tabla de particiones del sector de arranque master pero SIN formatear ninguna partición
//	Parámetros:
//		- disco: Disco en el que se modificará la tabla de particiones 1,2,3..
//		- SintaxParticion: Cadena con la sintaxis de particionado de las particiones primarias
//		- script: Nombre del script que se ejecutará 
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//	Especificaciones:
//		Esta función es auxiliar de la anterior y es llamda por esta en dos ocasiones, para las particiones Primarias y Lógicas
//______________________________________________________________________________________________________
int Particionando(char* disco,char* stxParticion,char* script)
{	
	int herror;
	
	sprintf(cmdshell,"%s/%s",HIDRASCRIPTS,script);
	sprintf(parametros," %s %s %s",script,disco,stxParticion);
	if(ndebug>1){
		sprintf(msglog,"Modificando tabla de particiones:%s disco:%s, cadena:%s",script,disco,stxParticion);
		Log(msglog);
	}
	herror=EjecutarScript(cmdshell,parametros,NULL,true);
	if(herror){
		UltimoErrorScript(herror,"Particionar()");	 // Se ha producido algún error
		return(false); 
    }
    else
		return(true); 
}
//______________________________________________________________________________________________________
// Función: Formatear
//
//	 Descripción:
//		Formatea una partición
//	Parámetros:
//		- disco: Número del disco
//		- particion: Número de partición a formatear
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//______________________________________________________________________________________________________
int Formatear(char* disco,char* particion)
{
	int herror;

	sprintf(cmdshell,"%s/admFormatFs",HIDRASCRIPTS);	
	sprintf(parametros," %s %s %s","FormatFs",disco,particion);
	herror=EjecutarScript(cmdshell,parametros,NULL,true);
	if(herror){
	    UltimoErrorScript(herror,"Formatear()");	 // Se ha producido algún error
		return(false); 
    }
	return(true); 
}
//______________________________________________________________________________________________________
// Función: SetCachePartitionSize
//
//	Descripción: 
//		Dimensiona el tamaño de la caché
//	Parámetros:
//		- t : Tamaño a asignar de la caché
//	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
// ________________________________________________________________________________________________________
int SetCachePartitionSize(int t)
{
	return(true);
}
//___________________________________________________________________________________________________
//
//  
//______________________________________________________________________________________________________
// Función: AutoClienteHidra
//
//	Descripción: 
//		Ejecuta un fichero autoexec preparado para  el cliente
// ________________________________________________________________________________________________________
int AutoexecClienteHidra()
{
	sprintf(fileini,"/autoexec/INI_%s",Propiedades.IPlocal);	// Nombre del fichero autoexec		
	if(ExisteFichero(fileini)){
		if(LoadTextFile(fileini)){ // Lee fichero autoexec		
			GestionTramas(trama);	// Analiza la trama
		}
		else{
			UltimoError(6,"AutoexecClienteHidra()");
			return(false);
		}
	}
	return(true);
}
//______________________________________________________________________________________________________
// Función: LeeConfiguracion
//
//	Descripción: 
//		Recupera la configuración de particiones del ordenador 
//	Parámetros:
//		disco:	Disco a analizar 1,2,3..
//	Devuelve:
//		Una cadena con la configuración del cliente (ver manual)
// ________________________________________________________________________________________________________
char* LeeConfiguracion(char* disco)
{
	int herror;
	char *cadenaparticiones;
	char *nomso;
	
	cadenaparticiones=(char*)ReservaMemoria(LONGITUD_SCRIPTSALIDA);
	sprintf(cmdshell,"%s/admListPrimaryPartitions",HIDRASCRIPTS);	
	sprintf(parametros," %s %s","admListPrimaryPartitions",disco);
	herror=EjecutarScript(cmdshell,parametros,cadenaparticiones,true);
	if(herror){
	    UltimoErrorScript(herror,"LeeConfiguracion()");	 // Se ha producido algún error
		return(NULL); 
    }
	struct s_Particiones *tbcfg[MAXPARTICIONES];
	char *duplasparticiones[MAXPARTICIONES],*duplaparticion[2];
	
	int iPar=SplitParametros(duplasparticiones,cadenaparticiones," ");	// Caracter separatorio de los elementos de un item
	int i,j;
	for( i = 0; i<iPar; i++){
		SplitParametros(duplaparticion,duplasparticiones[i],":");
		tbcfg[i]=(struct s_Particiones*)ReservaMemoria(sizeof(struct s_Particiones)); // Toma espacio para tabla de configuraciones
		strcpy(tbcfg[i]->tipopart,duplaparticion[0]); // Tipo de partición
		strcpy(tbcfg[i]->tamapart,duplaparticion[1]); // Tamaño de partición
		sprintf(tbcfg[i]->numpart,"%d",i+1); // Número de partición
		
		for(j=0;j<ntiposo;j++){
			if(strcmp(tiposos[j].tipopart,duplaparticion[0])==0 && strcmp(tiposos[j].tipopart,"LINUX-SWAP")!=0 && strcmp(tiposos[j].tipopart,"EMPTY")!=0){
				nomso=TomaNomSO(disco,i+1);
				if(nomso!=NULL){ // Averigua qué sistema operativo está instalado en la partición
					strcpy(tbcfg[i]->tiposo,tiposos[j].tiposo); // Nombre S.O.
					strcpy(tbcfg[i]->nombreso,nomso); // Nombre completo S.O.
				}
				else{
					strcpy(tbcfg[i]->tiposo,""); // Nombre S.O.
					strcpy(tbcfg[i]->nombreso,""); // Nombre completo S.O.
				}
				break;
			}
		}
	}
	char *cfg=ReservaMemoria(LONGITUD_CONFIGURACION);
	if(!cfg){
		UltimoError(1,"LeeConfiguracion()");
		return(NULL);
	}
	int lon=0;
	for( i = 0; i<iPar; i++){
		lon+=sprintf(cfg+lon,"@cfg\n");
		lon+=sprintf(cfg+lon,"tiposo=%s\n",tbcfg[i]->tiposo);	
		lon+=sprintf(cfg+lon,"tipopart=%s\n",tbcfg[i]->tipopart);
		lon+=sprintf(cfg+lon,"tamapart=%s\n",tbcfg[i]->tamapart);
		lon+=sprintf(cfg+lon,"numpart=%s\n",tbcfg[i]->numpart);		
		lon+=sprintf(cfg+lon,"nombreso=%s\t",tbcfg[i]->nombreso);
	}
	return(cfg);
}
//______________________________________________________________________________________________________
// Función: TomaNomSO
//
//	Descripción: 
// 		Recupera el nombre del sistema operativo instalado en una partición
//	Parámetros:
//		disco:	Disco  1,2,3..
//		particion:	Número de la partición	
//	Devuelve:
//		Una cadena con el nombre del S.O.
// ________________________________________________________________________________________________________
char* TomaNomSO(char*disco,int particion)
{
	int herror,lon;
	char *infosopar;
	char* sover[2];
	char ch[2];
	
	infosopar=(char*)ReservaMemoria(LONGITUD_SCRIPTSALIDA); // Información del S.O. de la partición
	
	sprintf(cmdshell,"%s/admGetOsVersion",HIDRASCRIPTS);	
	sprintf(parametros," %s %s %d","admGetOsVersion",disco,particion);
	herror=EjecutarScript(cmdshell,parametros,infosopar,true);
		if(herror){
	    UltimoErrorScript(herror,"TomaNomSO()");	 // Se ha producido algún error
		return(NULL); 
    }
    if(strlen(infosopar)==0) return(NULL); // NO Existe S.O. en la partición
    strcpy(ch,":");// caracter delimitador (dos puntos)
   	lon=SplitParametros(sover,infosopar,ch);
   	return(sover[1]);
}
//______________________________________________________________________________________________________
// Función: MuestraMenu
//
//	Descripción: 
// 		Muestra menú del cliente
//	Parámetros:
//		ips: Dirección IP del servidor web  que implementa la consola de adminsitración
//	  urp: Path o url de la página web que muestra el menu del cliente
//		iph:	Dirección ip del cliente	
//	Devuelve:
//		Nada
// ________________________________________________________________________________________________________
int MuestraMenu(char *urp)
{
	int herror,nargs,resul;

	sprintf(cmdshell,"%s/admMenuBrowser",HIDRASCRIPTS);
	sprintf(parametros,"%s %s","admMenuBrowser",urp);	
	nargs=SplitParametros(argumentos,parametros," "); // Crea matriz de los argumentos del scripts
	if((pidmenu=fork())==0){
		/* Proceso hijo que ejecuta el script */
		execv(cmdshell,argumentos);
		exit(resul);   
	}
	else {
		if (pidmenu ==-1){
			UltimoErrorScript(herror,"MuestraMenu()");	// Se ha producido algún error
			return(false);	
		}		
	}
	return(true);
}
//______________________________________________________________________________________________________
// Función: InventarioHardware
//
//	Descripción: 
// 		Recupera la configuración de hardware del ordenador 
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//	Especificaciones:
//		Lo que se envía al servidor es una cadena con el formato de salida del script que ejecuta
//		está función. (Vease scripts hidraHardwareInfo)
// ________________________________________________________________________________________________________
int InventarioHardware(TRAMA *trama,TRAMA *nwtrama)
{
	int herror,res;
	char *parametroshrd;
	
	parametroshrd=(char*)ReservaMemoria(LONGITUD_SCRIPTSALIDA);
	sprintf(cmdshell,"%s/admListHardwareInfo",HIDRASCRIPTS);
	herror=EjecutarScript(cmdshell,NULL,parametroshrd,true);
	if(herror){
	    UltimoErrorScript(herror,"InventarioHardware()");	// Se ha producido algún error
    }
    res=(herror==0); // Si se ha producido algún error el resultado de la ejecución de error

  	int lon;
	lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_TomaHardware\r");		
	lon+=sprintf(nwtrama->parametros+lon,"hrd=%s\r",parametroshrd);	
	RespuestaEjecucionComando(trama,nwtrama,res);	
	return(res);
}
//______________________________________________________________________________________________________
// Función: InventarioSoftware
//
//	Descripción: 
// 		Recupera la configuración de software del ordenador 
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
//	Especificaciones:
//		Lo que se envía al servidor es una cadena con el formato de salida del script que ejecuta
//		está función. (Vease scripts hidraSoftwareInfo)
// ________________________________________________________________________________________________________
int InventarioSoftware(TRAMA *trama,TRAMA *nwtrama)
{
	int herror,res;
	char *parametrossft,*infopar;
	char *particion=TomaParametro("par",trama->parametros); // Toma partición
	//char *tipo=TomaParametro("tpl",trama->parametros); // Toma tipo de listado 

	char *disco=(char*)ReservaMemoria(2);
	sprintf(disco,"1"); // Siempre el disco 1

	sprintf(cmdshell,"%s/admListSoftwareInfo",HIDRASCRIPTS);
	sprintf(parametros,"%s %s %s","admListSoftwareInfo",disco,particion);

	parametrossft=(char*)ReservaMemoria(LONGITUD_SCRIPTSALIDA);
	herror=EjecutarScript(cmdshell,parametros,parametrossft,true);
	if(herror){
	    UltimoErrorScript(herror,"InventarioSoftware()");	// Se ha producido algún error
    }
    res=(herror==0); // Si se ha producido algún error el resultado de la ejecución de error
	// Toma tipo de partición
		infopar=(char*)ReservaMemoria(16); //Tipo de partición
		if(res && infopar){
				sprintf(cmdshell,"%s/admGetFsType",HIDRASCRIPTS);	
				sprintf(parametros," %s %s %s","getFsType",disco,particion);
				herror=EjecutarScript(cmdshell,parametros,infopar,true);
				if(herror){
		    		UltimoErrorScript(herror,"InventarioSoftware()");	 // Se ha producido algún error
  	 			}
	}
  int lon;
	lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_TomaSoftware\r");		
	lon+=sprintf(nwtrama->parametros+lon,"sft=%s\r",parametrossft);	
	lon+=sprintf(nwtrama->parametros+lon,"par=%s\r",particion);	
	lon+=sprintf(nwtrama->parametros+lon,"tfs=%s\r",infopar);	
	RespuestaEjecucionComando(trama,nwtrama,res);	
	return(res);
}
//______________________________________________________________________________________________________
// Función: TomaConfiguracion
//
//	Descripción: 
// 		Toma la configuración de particiones de un ordenador
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
// ________________________________________________________________________________________________________
int TomaConfiguracion(TRAMA *trama,TRAMA *nwtrama)
{	
		char* parametroscfg;
		
		char *disco=(char*)ReservaMemoria(2);
		sprintf(disco,"1"); // Siempre el disco 1

		parametroscfg=LeeConfiguracion(disco);
		
		int lon;			
		lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_TomaConfiguracion\r");	
		lon+=sprintf(nwtrama->parametros+lon,"cfg=%s\r",parametroscfg);		
		RespuestaEjecucionComando(trama,nwtrama,true);	
		
		return(true);
}
//______________________________________________________________________________________________________
// Función: ExecShell
//
//	Descripción: 
// 		Ejecuta un script de la Shell
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
// ________________________________________________________________________________________________________
int ExecShell(TRAMA *trama,TRAMA *nwtrama)
{
	int res;

	char* wscript=TomaParametro("scp",trama->parametros); 	// Código del script	
	char* codigo=URLDecode(wscript);	// Decodifica el código recibido con formato URLCode
	
	res=ExecBash(codigo);	
	if(!res){
		UltimoErrorScript(10,"ExecShell()");	// Se ha producido algún error
	}
			
	char *disco=(char*)ReservaMemoria(2);
	sprintf(disco,"1"); // Siempre el disco 1
	char* parametroscfg=LeeConfiguracion(disco);
	int lon;			
	lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_ExecShell\r");	
	lon+=sprintf(nwtrama->parametros+lon,"cfg=%s\r",parametroscfg);	
	RespuestaEjecucionComando(trama,nwtrama,res);	
	return(true);	
}
//______________________________________________________________________________________________________
// Función: ExecBash
//
//	Descripción: 
// 		Ejecuta código bash
//	Parámetros:
//		- codigo: Código a ejecutar
// 	Devuelve:
//		true si el proceso fue correcto o false en caso contrario
// ________________________________________________________________________________________________________
int ExecBash(char*codigo){
	FILE* f;
	long lSize;
	int herror,res;
	
	sprintf(filecmdshell,"%s/%s","/tmp","_hidrascript_");
	f = fopen(filecmdshell,"wt");	// Abre fichero de script
	if(f==NULL)
		res=false; // Error de apertura del fichero de configuración
	else{
		lSize=strlen(codigo);
		fwrite(codigo,1,lSize,f);	// Escribe el código a ejecutar
		fclose(f);
		sprintf(cmdshell,"/bin/chmod");	// Da permiso de ejecución al fichero
		sprintf(parametros," %s %s %s","/bin/chmod","+x",filecmdshell);
		
		herror=EjecutarScript(cmdshell,parametros,NULL,true);
		if(herror){
			UltimoErrorScript(herror,"ExecBash()");	// Se ha producido algún error
			res=false;	
		}
		else{
			sprintf(cmdshell,"%s",filecmdshell);	// Ejecución el fichero de script creado
			//int herror=EjecutarScript(cmdshell,NULL,NULL,true);
			int herror=system(cmdshell);
			if(herror){
				UltimoErrorScript(herror,"ExecBash()");	// Se ha producido algún error
				res=false;	
			}		
		}
	}
	return(res);
}
//______________________________________________________________________________________________________
// Función: URLDecode
//
//	Descripción: 
// 		Decodifica una cadena codificada con UrlEncode
//	Parámetros:
//		- src: La cadena a decodificar
// 	Devuelve:
//		La cadena decodificada
// ________________________________________________________________________________________________________
char* URLDecode(char *src)
{
	const char *p = src;
	char code[3] = {0};
	unsigned long ascii = 0;	
	char *end = NULL;
	char *dest,*cad;

	dest=(char*)ReservaMemoria(strlen(src));	// Reserva buffer  para la cadena			
	cad=dest;
	while(*p){
		if(*p == '%'){
			memcpy(code, ++p, 2);
			ascii = strtoul(code, &end, 16);
			*dest++ = (char)ascii;
			p += 2;
		}
		else
			*dest++ = *p++;
	}
	return(cad);	
}
//______________________________________________________________________________________________________
// Función: RespuestaEjecucionComando
//
//	Descripción: 
// 		Envia una respuesta a una ejecucion de comando al servidor de Administración
//	Parámetros:
//		- trama: Trama recibida con las especificaciones del comando
//		- nwtrama: Nueva trama a enviar al servidor con la respuesta de la acción, si ésta procede
//		-  res:	Resultado de la ejecución (true si la ejecución es correcta y false en caso contrario)	
// 	Devuelve:
//		true si la respuesta se envía correctamente al servidor
// ________________________________________________________________________________________________________
int RespuestaEjecucionComando(TRAMA* trama, TRAMA *nwtrama, int res)
{ 
		int idsuceso=0;	
		char *widsuceso=TomaParametro("ids",trama->parametros);
		if(widsuceso) idsuceso=atoi(widsuceso);	
		int lon;
		lon=strlen(nwtrama->parametros); 
		lon+=sprintf(nwtrama->parametros+lon,"ids=%d\r",idsuceso);	//  Identificador del suceso
		char descrierror[250];
		if (res){ // Resultado satisfactorio
			lon+=sprintf(nwtrama->parametros+lon,"res=%s\r","1");	// Resultado de la ejecucin del comando	
			sprintf(descrierror,"%s "," ");	
			lon+=sprintf(nwtrama->parametros+lon,"der=%s\r",descrierror);	// Dscripcin del error si lo ha habido
		}	
		else{ // Algún error
			lon+=sprintf(nwtrama->parametros+lon,"res=%s\r","2");	// Resultado de la ejecución del comando		
			sprintf(descrierror,"Error.-(%s) en modulo %s",e.msg,e.modulo);
			lon+=sprintf(nwtrama->parametros+lon,"der=%s\r",descrierror);	// Descripción del error si lo ha habido
		}
		if(AbreConexionTCP()){
			if(!EnviaTramasHidra(sock,nwtrama)){
				UltimoError(21,"RespuestaEjecucionComando()"); 
				return(false);
			}
			if(!RecibeTramasHidra(sock,trama)){
				UltimoError(22,"RespuestaEjecucionComando()");
				return(false);	
			}		
			CierraConexionTCP();
			GestionTramas(trama);	// Analiza la trama
		}
		else{
			UltimoError(2,"RespuestaEjecucionComando()");	
			return(false);			
		}
		return(true);
}
//______________________________________________________________________________________________________
// Función: MuestraMensaje
//
//	Descripción: 
// 		Envia una página al browser con un mensaje determinado
//	Parámetros:
//		- idx: Indice de la cadena del mensaje
// ________________________________________________________________________________________________________
void MuestraMensaje(int idx){
	sprintf(urlpag,"%s?msg=%d",URLMSG,idx); // Url de la página de mensajes
	MuestraMenu(urlpag);
}
//***********************************************************************************************************************
// PROGRAMA PRINCIPAL
//***********************************************************************************************************************
int  main(int argc, char *argv[])
{
	//Archivos por defecto
 	strcpy(szPathFileCfg,"ogAdmClient.cfg");
	strcpy(szPathFileLog,"ogAdmClient.log");

	// Validación de argumentos y lectura del fichero de configuración
	if(!ValidacionParametros(argc,argv))
		exit(EXIT_FAILURE);
	else{	
		if(!CrearArchivoLog(szPathFileLog))
			exit(EXIT_FAILURE);
		else
			if(!LeeFileConfiguracion(szPathFileCfg)){ // Toma parámetros de configuracion
				UltimoError(13,"Main()");	
				exit(EXIT_FAILURE);
			}
	}
	// Guarda datos básicos del cliente	
	strcpy(Propiedades.servidorhidra,Servidorhidra);	
	strcpy(Propiedades.puerto,Puerto);	
	strcpy(Propiedades.idordenador,"0");
	if(!TomaIPlocal()){ // Error al recuperar la IP local	
		UltimoError(0,"Main()");	
		exit(EXIT_FAILURE);
	}
	strcpy(Propiedades.IPlocal,IPlocal);	

	Log("Abriendo sesión en el servidor de Administración");		
	if(InclusionCliente()){	// El cliente ha abierto sesión correctamente
		if(strcmp(Propiedades.idordenador,"0")==0){	// Ha habido algún problema al inciar sesión
			UltimoError(0,"Main()");	
			exit(EXIT_FAILURE);
		}
		Log("Cliente iniciado");		
		Log("Ejecución de archivo Autoexec");
		if(!AutoexecClienteHidra()){  // Ejecución fichero autoexec	
			UltimoError(0,"Main()");	
			exit(EXIT_FAILURE);
		}				
		Log("Procesa comandos pendientes");
		ComandosPendientes(); // Bucle para procesar comandos pendientes
		Log("Acciones pendientes procesadas");
		//MuestraMenu(URLMENU);
		Log("Disponibilidad para comandos interactivos activada ...");
		ProcesaComandos(); // Bucle para procesar comando	s interactivos 
		Log("Disponibilidad para comandos interactivos desactivada...");
	}
	else{
		UltimoError(0,"Main()");	
		exit(EXIT_FAILURE);
	}
	exit(0);
}

	


