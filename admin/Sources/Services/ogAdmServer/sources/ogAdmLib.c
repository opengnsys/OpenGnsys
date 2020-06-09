// **************************************************************************************************************************************************
// Libreria: ogAdmLib
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmLib.c
// Descripción: Este fichero implementa una libreria de funciones para uso común de los servicios
// **************************************************************************************************************************************************

#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <ctype.h>
#include <sys/types.h>
#include <sys/socket.h>
#include "ogAdmLib.h"

//______________________________________________________________________________________________________
// Función: ValidacionParametros
//
//	 Descripción:
//		Valida que los parametros de ejecución del programa sean correctos
//	Parámetros:
//		- argc:	Número de argumentos
//		- argv:	Puntero a cada argumento
//		- eje:	Tipo de ejecutable (1=Servicio,2=Repositorio o 3=Cliente)
//	Devuelve:
//		- TRUE si los argumentos pasados son correctos
//		- FALSE en caso contrario
//	Especificaciones:
//		La sintaxis de los argumentos es la siguiente
//			-f	Archivo de configuración del servicio
//			-l	Archivo de logs
//			-d	Nivel de debuger (mensages que se escribirán en el archivo de logs)
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN validacionParametros(int argc, char*argv[],int eje) {
	int i;

	switch(eje){
		case 1: // Administrador
			strcpy(szPathFileCfg, "ogAdmServer.cfg"); // Valores por defecto de archivos
			strcpy(szPathFileLog, "ogAdmServer.log"); // de configuración y de logs
			break;
		case 2: // Repositorio
			strcpy(szPathFileCfg, "ogAdmRepo.cfg"); // Valores por defecto de archivos
			strcpy(szPathFileLog, "ogAdmRepo.log"); // de configuración y de logs
			break;
		case 3: // Cliente OpenGnsys
			strcpy(szPathFileCfg, "ogAdmClient.cfg"); // Valores por defecto de archivos
			strcpy(szPathFileLog, "ogAdmClient.log"); // de configuración y de logs
			break;
		case 4: // Servicios DHCP,BOOTP Y TFTP
			strcpy(szPathFileCfg, "ogAdmBoot.cfg"); // Valores por defecto de archivos
			strcpy(szPathFileLog, "ogAdmBoot.log"); // de configuración y de logs
			break;
		case 5: // Agente
			strcpy(szPathFileCfg, "ogAdmAgent.cfg"); // Valores por defecto de archivos
			strcpy(szPathFileLog, "ogAdmAgent.log"); // de configuración y de logs
			break;
		case 6: // Agente
			strcpy(szPathFileCfg, "ogAdmWinClient.cfg"); // Valores por defecto de archivos
			strcpy(szPathFileLog, "ogAdmWinClient.log"); // de configuración y de logs
			break;
		case 7: // Agente
			strcpy(szPathFileCfg, "ogAdmnxClient.cfg"); // Valores por defecto de archivos
			strcpy(szPathFileLog, "ogAdmLnxClient.log"); // de configuración y de logs
			break;
	}

	ndebug = 1; // Nivel de debuger por defecto

	for (i = 1; (i + 1) < argc; i += 2) {
		if (argv[i][0] == '-') {
			switch (tolower(argv[i][1])) {
			case 'f':
				if (argv[i + 1] != NULL)
					strcpy(szPathFileCfg, argv[i + 1]);
				else {
					return (FALSE);
				}
				break;
			case 'l':
				if (argv[i + 1] != NULL)
					strcpy(szPathFileLog, argv[i + 1]);
				else {
					return (FALSE);
				}
				break;
			case 'd':
				if (argv[i + 1] != NULL) {
					ndebug = atoi(argv[i + 1]);
					if (ndebug < 1)
						ndebug = 1; // Por defecto el nivel de debug es 1
				} else
					ndebug = 1; // Por defecto el nivel de debug es 1
				break;
			default:
				exit(EXIT_FAILURE);
				break;
			}
		}
	}
	return (TRUE);
}
// ________________________________________________________________________________________________________
// Función: splitCadena
//
//	Descripción:
//			Trocea una cadena según un carácter delimitador
//	Parámetros:
//			- trozos: Array de punteros a cadenas
//			- cadena: Cadena a trocear
//			- chd: Carácter delimitador
//	Devuelve:
//		Número de trozos en que se divide la cadena
// ________________________________________________________________________________________________________
int splitCadena(char **trozos,char *cadena, char chd)
{
	int w=0;
	if(cadena==NULL) return(w);

	trozos[w++]=cadena;
	while(*cadena!='\0'){
		if(*cadena==chd){
			*cadena='\0';
			if(*(cadena+1)!='\0')
				trozos[w++]=cadena+1;
		}
		cadena++;
	}
	return(w); // Devuelve el número de trozos
}
// ________________________________________________________________________________________________________
// Función: escaparCadena
//
//	Descripción:
//			Sustituye las apariciones de un caracter comila simple ' por \'
//	Parámetros:
//			- cadena: Cadena a escapar
// Devuelve:
//		La cadena con las comillas simples sustituidas por \'
// ________________________________________________________________________________________________________
char* escaparCadena(char *cadena)
{
	int b,c;
	char *buffer;

	buffer = (char*) reservaMemoria(strlen(cadena)*2); // Toma memoria para el buffer de conversión
	if (buffer == NULL) { // No hay memoria suficiente para el buffer
		return (FALSE);
	}

	c=b=0;
	while(cadena[c]!=0) {
		if (cadena[c]=='\''){
			buffer[b++]='\\';
			buffer[b++]='\'';
		}
		else{
			buffer[b++]=cadena[c];
		}
		c++;
	}
	return(buffer);
}

// ________________________________________________________________________________________________________
// Función: igualIP
//
//	Descripción:
//		Comprueba si una cadena con una dirección IP está incluida en otra que	contienen varias direcciones ipes
//		separadas por punto y coma
//	Parámetros:
//		- cadenaiph: Cadena de direcciones IPES
//		- ipcliente: Cadena de la IP a buscar
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
BOOLEAN contieneIP(char *cadenaiph,char *ipcliente)
{
	char *posa,*posb;
	int lon, i;

	posa=strstr(cadenaiph,ipcliente);
	if(posa==NULL) return(FALSE); // No existe la IP en la cadena
	posb=posa; // Iguala direcciones
	for (i = 0; i < LONIP; i++) {
		if(*posb==';') break;
		if(*posb=='\0') break;
		if(*posb=='\r') break;
		posb++;
	}
	lon=strlen(ipcliente);
	if((posb-posa)==lon) return(TRUE); // IP encontrada
	return(FALSE);
}
// ________________________________________________________________________________________________________
// Función: rTrim
//
//		 Descripción:
//			Elimina caracteres de espacios y de asci menor al espacio al final de la cadena
//		Parámetros:
//			- cadena: Cadena a procesar
// ________________________________________________________________________________________________________
char* rTrim(char *cadena)
{
	int i,lon;

	lon=strlen(cadena);
	for (i=lon-1;i>=0;i--){
		if(cadena[i]<32)
			cadena[i]='\0';
		else
			return(cadena);
	}
	return(cadena);
}
//______________________________________________________________________________________________________
// Función: reservaMemoria
//
//	Descripción:
//		Reserva memoria para una variable
//	Parámetros:
//		- lon:	Longitud en bytes de la reserva
//	Devuelve:
//		Un puntero a la zona de memoria reservada que ha sido previamente rellena con zeros o nulos
//______________________________________________________________________________________________________
char* reservaMemoria(int lon)
{
	char *mem;

	mem=(char*)malloc(lon);
	if(mem!=NULL)
		memset(mem,0,lon);
	return(mem);
}
//______________________________________________________________________________________________________
// Función: ampliaMemoria
//
//	Descripción:
//		Amplia memoria para una variable
//	Parámetros:
//		- ptr:	Puntero al buffer de memoria que se quiere ampliar
//		- lon:	Longitud en bytes de la amplicación
//	Devuelve:
//		Un puntero a la zona de memoria reservada que ha sido previamente rellena con zeros o nulos
//______________________________________________________________________________________________________
char* ampliaMemoria(char* ptr,int lon)
{
	char *mem;

	mem=(char*)realloc(ptr,lon*sizeof(char*));
	if(mem!=NULL)
		return(mem);
	return(NULL);
}
//______________________________________________________________________________________________________
// Función: liberaMemoria
//
//	Descripción:
//		Libera memoria para una variable
//	Parámetros:
//		- ptr:	Puntero al buffer de memoria que se quiere liberar
//	Devuelve:
//		Nada
//______________________________________________________________________________________________________
void liberaMemoria(void* ptr)
{
	if(ptr){
		free (ptr);
	}
}
// ________________________________________________________________________________________________________
// Función: sendData
//
//	Descripción:
//		Envía datos por la red a través de un socket
//	Parametros:
//			- sock : El socket por donde se envía
//			- datos: El contenido a enviar
//			- lon: Cantidad de bites a enviar
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
BOOLEAN sendData(SOCKET *sock, char* datos,int lon)
{
	int idx,ret;
	idx = 0;
	while (lon > 0) {
		ret = send(*sock,&datos[idx],lon, 0);
		if (ret == 0) { // Conexión cerrada por parte del cliente (Graceful close)
			break;
		}
		else{
			if (ret == -1)
				return (FALSE);
		}
		lon -= ret;
		idx += ret;
	}
	return (TRUE);
}
// ________________________________________________________________________________________________________
// Función: mandaTrama
//
//	Descripción:
//		Envía una trama por la red
//	Parametros:
//			- sock : El socket del host al que se dirige la trama
//			- trama: El contenido de la trama
//			- lon: Longitud de la parte de parametros de la trama que se va a mandar
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
BOOLEAN mandaTrama(SOCKET *sock, TRAMA* ptrTrama)
{
	int lonprm;
	char *buffer,hlonprm[LONHEXPRM+1];
	BOOLEAN res;

	lonprm=strlen(ptrTrama->parametros);
	sprintf(hlonprm,"%05X",LONGITUD_CABECERATRAMA+LONHEXPRM+lonprm); // Convierte en hexadecimal la longitud

	buffer=reservaMemoria(LONGITUD_CABECERATRAMA+LONHEXPRM+lonprm); // Longitud total de la trama
	if(buffer==NULL)
		return(FALSE);
	memcpy(buffer,ptrTrama,LONGITUD_CABECERATRAMA); // Copia cabecera de trama
	memcpy(&buffer[LONGITUD_CABECERATRAMA],hlonprm,LONHEXPRM); // Copia longitud de la trama
	memcpy(&buffer[LONGITUD_CABECERATRAMA+LONHEXPRM],ptrTrama->parametros,lonprm);
	res=sendData(sock,buffer,LONGITUD_CABECERATRAMA+LONHEXPRM+lonprm);
	liberaMemoria(buffer);
	return (res);
}

//______________________________________________________________________________________________________
// Función: initParammetros
//
//	 Descripción:
//		Libera memoria del buffer de los parametros de la trama y vuelve a reservar espacio
//	Parámetros:
//		- parametros : Puntero a la zona donde están los parametros de una trama
//		- lon : Tamaño de la nueva reserva de espacio para los parametros
//	Devuelve:
//		Un puntero a la nueva zona de memoria o NULL si ha habido algún error
// Especificaciones:
//		En caso de que el parámetro lon valga cero el tamaño a reservar será el estandar
//______________________________________________________________________________________________________
BOOLEAN initParametros(TRAMA* ptrTrama,int lon)
{
	if(lon==0) lon=LONGITUD_PARAMETROS;
	ptrTrama->parametros=(char*)ampliaMemoria(ptrTrama->parametros,lon);
	if(!ptrTrama->parametros)
		return(FALSE);
	else
		return(TRUE);
}
