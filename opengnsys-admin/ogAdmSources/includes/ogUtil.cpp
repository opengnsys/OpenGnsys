#include "ogUtil.h"
//__________________________________________________________________________________________________________
//
// Función: Encripta
//
//	 Descripción:
//		Esta Función encripta una cadena y la devuelve como parametro
//__________________________________________________________________________________________________________
char * Encriptar(char *cadena)
{
	return(cadena); // vuelve sin encriptar
	
	int i,lon;
	char clave; 
	
	clave = 12 & 0xFFU; // La clave elegida entre 0-255, en este caso 12
	lon=strlen(cadena);
	for(i=0;i<lon;i++)
      cadena[i]=((char)cadena[i] ^ clave) & 0xFF; 
	return(cadena);
}
//__________________________________________________________________________________________________________
//
// Función: Desencripta
//
//	 Descripción:
//		Esta Función desencripta una cadena y la devuelve como parametro
//__________________________________________________________________________________________________________
char * Desencriptar(char *cadena)
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
// ________________________________________________________________________________________________________
// Función: RegistraLog
//
//		Descripción:
//			Esta Función registra los evento de errores en un fichero log
//		Parametros:
//			- msg : Mensage de error
//			- swerrno: Switch que indica que recupere literal de error del sistema
// ________________________________________________________________________________________________________
void RegistraLog(const char *msg,int swerrno)
{
	time_t rawtime;
	struct tm * timeinfo;

	time ( &rawtime );
	timeinfo = gmtime(&rawtime);

	FLog=fopen(szPathFileLog,"at");
	if(swerrno)
		fprintf (FLog,"%02d/%02d/%d %02d:%02d ***%s:%s\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year+1900,timeinfo->tm_hour,timeinfo->tm_min,msg,strerror(errno));
	else
		fprintf (FLog,"%02d/%02d/%d %02d:%02d ***%s\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year+1900,timeinfo->tm_hour,timeinfo->tm_min,msg);
	fclose(FLog);
}
// ________________________________________________________________________________________________________
// Función: toma_parametro
// 
//		Descripción:
// 			Esta funci? devuelve el valor de un parametro incluido en la trmInfo.
// 			El formato del protocolo es: "nombre_parametro=valor_parametro"
// 		Par?etros:
// 			- nombre_parametro: Es el nombre del par?etro a recuperar
// 			- parametros: Es la matriz que contiene todos los par?etros
// ________________________________________________________________________________________________________
char * toma_parametro(const char* nombre_parametro,char *parametros)
{
	int i=0;
	char* pos;

	for(i=0;i<LONGITUD_PARAMETROS-4;i++){ 
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
// ________________________________________________________________________________________________________
// Función: split_parametros
//
//		Descripción:
//			Esta Función trocea una cadena segn un caracter delimitador, Devuelve el nmero de trozos
// 		Parámetros:
// 			- trozos: Array de punteros a cadenas
// 			- cadena: Cadena a trocear
// 			- ch: caracter delimitador
// ________________________________________________________________________________________________________
int split_parametros(char **trozos,char *cadena, char * ch){
	int i=0;
	char* token;

	token= strtok(cadena,ch); // Trocea segn delimitador
	while( token != NULL ){
		trozos[i++]=token;
		token=strtok(NULL,ch); // Siguiente token
	}
	trozos[i++]=token; 
	return(i-1); // Devuelve el numero de trozos
}
// ________________________________________________________________________________________________________
// Función: INTROaFINCAD
//
//		Descripcinn?:
// 			Cambia INTROS por caracteres fin de cadena ('\0') en una cadena
//		Parametros:
//				- parametros : La cadena a explorar
// ________________________________________________________________________________________________________
void INTROaFINCAD(char* parametros)
{
	int lon,i;
	lon=strlen(parametros);
	for(i=0;i<lon;i++){ // Cambia los INTROS por NULOS
		if(parametros[i]=='\r') parametros[i]='\0';
	}
}
// ________________________________________________________________________________________________________
// Función: INTROaFINCAD
//
//		Descripcinn?:
// 			Cambia INTROS por caracteres fin de cadena ('\0') en una cadena
//		Parametros:
//				- parametros : La cadena a explorar
// ________________________________________________________________________________________________________
void FINCADaINTRO(char* a,char *b)
{
	char *i;
	for(i=a;i<b;i++){ // Cambia los NULOS por INTROS
		if(*i=='\0') *i='\r';
	}
}

