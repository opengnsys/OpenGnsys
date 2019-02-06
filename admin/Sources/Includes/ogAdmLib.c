// **************************************************************************************************************************************************
// Libreria: ogAdmLib
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmLib.c
// Descripción: Este fichero implementa una libreria de funciones para uso común de los servicios
// **************************************************************************************************************************************************
// ________________________________________________________________________________________________________
// Función: tomaHora
//
//	Descripción:
//		Devuelve la hora del sistema
//	Parametros:
//		Ninguno
// ________________________________________________________________________________________________________
struct tm * tomaHora()
{
	time_t rawtime;
	time ( &rawtime );
	return(localtime(&rawtime));
}
// ________________________________________________________________________________________________________
// Función: registraLog
//
//	Descripción:
//		Registra los eventos en un fichero log ya sean errores o información
//	Parametros:
//		- fileLog : Ruta completa del archivo de log
//		- msg : Descripción del error
//		- swe: Switch que indica si se debe recuperar además el literal de error del propio sistema operativo
// ________________________________________________________________________________________________________
void registraLog(const char* filelog,const char *msg,int swe)
{
	FILE *flog;
	struct tm * t;

	t = tomaHora();
	flog=fopen(filelog,"at");
	if(swe)
		fprintf (flog,"%02d/%02d/%d %02d:%02d %s: %s\n",t->tm_mday,t->tm_mon+1,t->tm_year+1900,t->tm_hour,t->tm_min,msg,strerror(errno));
	else
		fprintf (flog,"%02d/%02d/%d %02d:%02d %s\n",t->tm_mday,t->tm_mon+1,t->tm_year+1900,t->tm_hour,t->tm_min,msg);
	fclose(flog);
}
// ________________________________________________________________________________________________________
// Función: errorLog
//
//	Descripción:
//		Registra los sucesos de errores preestablecidos en el fichero de log
//	Parametros:
//		- coderr : Código del mensaje de error
//		- swe: Switch que indica si se debe recuperar además el literal de error del propio sistema operativo
// ________________________________________________________________________________________________________
void errorLog(const char *modulo, int coderr, int swe) {
	char msglog[LONSUC];

	sprintf(msglog, "*** Error: %s. Módulo %s", tbErrores[coderr], modulo);
	registraLog(szPathFileLog, msglog, swe);
}
// ________________________________________________________________________________________________________
// Función: errorInfo
//
//	Descripción:
//		Registra los sucesos de errores dinámicos en el fichero de log
//	Parametros:
//		- msgerr : Descripción del error
//		- swe: Switch que indica si se debe recuperar además el literal de error del propio sistema operativo
// ________________________________________________________________________________________________________
void errorInfo(const char *modulo, char *msgerr) {
	char msglog[LONSUC];

	sprintf(msglog, "*** Error: %s. Módulo %s", msgerr, modulo);
	registraLog(szPathFileLog, msglog, FALSE);
}
// ________________________________________________________________________________________________________
// Función: infoLog
//
//	Descripción:
//		Registra los sucesos de información en el fichero de log
//	Parametros:
//		- coderr : Código del mensaje de información
// ________________________________________________________________________________________________________
void infoLog(int codinf) {
	char msglog[LONSUC];

	sprintf(msglog, "*** Info: %s", tbMensajes[codinf]);
	registraLog(szPathFileLog, msglog, FALSE);
}
// ________________________________________________________________________________________________________
// Función: infoDebug
//
//	Descripción:
//		Registra los mensajes de debugs en el fichero de log
//	Parametros:
//		- msgdeb : Descripción del mensaje de información
// ________________________________________________________________________________________________________
void infoDebug(char* msgdeb) {
	char msglog[LONSUC+15];	// Cadena de registro (reserva caracteres para el prefijo).

	sprintf(msglog, "*** Debug: %d-%s", ndebug, msgdeb);
	registraLog(szPathFileLog, msglog, FALSE);
}
//______________________________________________________________________________________________________
// Función: ValidacionParametros
//
//	 Descripción:
// 		Valida que los parametros de ejecución del programa sean correctos
//	Parámetros:
//		- argc:	Número de argumentos
//		- argv:	Puntero a cada argumento
//		- eje:	Tipo de ejecutable (1=Servicio,2=Repositorio o 3=Cliente)
// 	Devuelve:
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
	char modulo[] = "validacionParametros()";

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
					errorLog(modulo, 10, FALSE);
					return (FALSE);
				}
				break;
			case 'l':
				if (argv[i + 1] != NULL)
					strcpy(szPathFileLog, argv[i + 1]);
				else {
					errorLog(modulo, 11, FALSE);
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
				errorLog(modulo, 12, FALSE);
				exit(EXIT_FAILURE);
				break;
			}
		}
	}
	return (TRUE);
}
//______________________________________________________________________________________________________
// Función: reservaMemoria
//
//	Descripción:
//		Reserva memoria para una variable
//	Parámetros:
//		- lon: 	Longitud en bytes de la reserva
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
//		- ptr: 	Puntero al buffer de memoria que se quiere ampliar
//		- lon: 	Longitud en bytes de la amplicación
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
//		- ptr: 	Puntero al buffer de memoria que se quiere liberar
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
// Función: splitCadena
//
//	Descripción:
//			Trocea una cadena según un carácter delimitador
//	Parámetros:
// 			- trozos: Array de punteros a cadenas
// 			- cadena: Cadena a trocear
// 			- chd: Carácter delimitador
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
// Función: sustituir
//
//	Descripción:
//			Sustituye las apariciones de un caracter por otro en una cadena
//	Parámetros:
// 			- cadena: Cadena a convertir
// 			- cho: Caracter a sustituir
// 			- chs: Caracter sustituto
// ________________________________________________________________________________________________________
void sustituir(char *cadena,char cho,char chs)
{
	int x=0;

	while(cadena[x]!=0) {
		if (cadena[x]==cho)
			cadena[x]=chs;
		x++;
	}
}
// ________________________________________________________________________________________________________
// Función: escaparCadena
//
//	Descripción:
//			Sustituye las apariciones de un caracter comila simple ' por \'
//	Parámetros:
// 			- cadena: Cadena a escapar
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
// Función: StrToUpper
//
//	Descripción:
//			Convierta una cadena en mayúsculas
//	Parámetros:
// 			- cadena: Cadena a convertir
// ________________________________________________________________________________________________________
char* StrToUpper(char *cadena)
{
	int x=0;

	while(cadena[x]!=0) {
		if (cadena[x] >= 'a' && cadena[x] <= 'z') {
			cadena[x] -= 32;
		}
		x++;
	}
	return(cadena);
}
// ________________________________________________________________________________________________________
// Función: StrToUpper
//
//	Descripción:
//			Convierta una cadena en mayúsculas
//	Parámetros:
// 			- cadena: Cadena a convertir
// ________________________________________________________________________________________________________
char* StrToLower(char *cadena)
{
	int x=0;

	while(cadena[x]!=0) {
		if (cadena[x] >= 'A' && cadena[x] <= 'Z') {
			cadena[x] += 32;
		}
		x++;
	}
	return(cadena);
}
// ________________________________________________________________________________________________________
// Función: INTROaFINCAD
//
//		Descripción:
// 			Cambia caracteres INTROS por fin de cadena ('\0')  en una trama
//		Parametros:
// 			- parametros: Puntero a los parametros de la trama
// 			- lon: Longitud de la cadena de parametros
// ________________________________________________________________________________________________________
void INTROaFINCAD(TRAMA* ptrTrama)
{
	char *i,*a,*b;

	a=ptrTrama->parametros;
	b=a+ptrTrama->lonprm;
	for(i=a;i<b;i++){ // Cambia los NULOS por INTROS
		if(*i=='\r') *i='\0';
	}
}
// ________________________________________________________________________________________________________
// Función: FINCADaINTRO
//
//		Descripción:
// 			Cambia caracteres fin de cadena ('\0') por INTROS en una trama
//		Parametros:
// 			- parametros: Puntero a los parametros de la trama
// 			- lon: Longitud de la cadena de parametros
// ________________________________________________________________________________________________________
void FINCADaINTRO(TRAMA* ptrTrama)
{
	char *i,*a,*b;

	a=ptrTrama->parametros;
	b=a+ptrTrama->lonprm;
	for(i=a;i<b;i++){ // Cambia los NULOS por INTROS
		if(*i=='\0') *i='\r';
	}
}
// ________________________________________________________________________________________________________
// Función: cuentaIPES
//
//		Descripción:
// 			Cuenta los caracteres "." de las IPES dentro del parámetros iph de una trama
//			con lo cual dividiendo por 3 se puede saber la cantdad de direcciones IPES en la cadena
//		Parametros:
// 			- ipes: Cadena con las IPES separadas por ";"
// ________________________________________________________________________________________________________
int cuentaIPES(char* ipes)
{
	int i,a,b,c=0;

	a=0;
	b=strlen(ipes);
	for(i=a;i<b;i++){ // Cambia los NULOS por INTROS
		if(ipes[i]=='.') c++;
	}
	return(c/3);
}
// ________________________________________________________________________________________________________
// Función: tomaParametro
// 
//		Descripción:
// 			Devuelve el valor de un parametro incluido en una cadena con formatos: "nombre=valor"
// 		Parámetros:
// 			- nombre: Nombre del parámetro a recuperar
// 			- paramestros: Cadena que contiene todos los parámetros
// ________________________________________________________________________________________________________
char* tomaParametro(const char* nombre,TRAMA* ptrTrama)
{
	char *a,*b,*pos;

	a=ptrTrama->parametros;
	b=a+ptrTrama->lonprm;
	for(pos=a;pos<b;pos++){ // Busca valor del parámetro
		if(pos[0]==nombre[0]){
			if(pos[1]==nombre[1]){
				if(pos[2]==nombre[2]){
					if(pos[3]=='='){
						pos+=4;
						return(pos);
					}
				}
			}
		}
	}
	return(NULL);
}
//______________________________________________________________________________________________________
// Función: copiaParametro
//
//	Descripción:
// 		Devuelve una copia del valor de un parámetro
//	Parámetros:
//		- ptrTrama: contenido del mensaje
//		- parametro: Nombre del parámetro
//		- lon: Nombre del parámetro
// 	Devuelve:
//		Un puntero a la cadena que contiene el valor del parámetro
// ________________________________________________________________________________________________________
char* copiaParametro(const char*nombre,TRAMA* ptrTrama)
{
	char *prm,*buffer;
	char modulo[] = "copiaParametro()";


	prm=tomaParametro(nombre,ptrTrama); // Toma identificador de acción
	if(!prm)
		return(NULL);
	buffer = (char*) reservaMemoria(strlen(prm)+1); // Toma memoria para el buffer de lectura.
	if (buffer == NULL) { // No hay memoria suficiente para el buffer
		errorLog(modulo, 3, FALSE);
		return (FALSE);
	}
	strcpy(buffer,prm);
	return(buffer);
}
// ________________________________________________________________________________________________________
// Función: igualIP
//
//	Descripción:
//		Comprueba si una cadena con una dirección IP está incluida en otra que  contienen varias direcciones ipes
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
	int lon;

	posa=strstr(cadenaiph,ipcliente);
	if(posa==NULL) return(FALSE); // No existe la IP en la cadena
	posb=posa; // Iguala direcciones
	while(TRUE){
		posb++;
		if(*posb==';') break;
		if(*posb=='\0') break;
		if(*posb=='\r') break;
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
			if (ret == SOCKET_ERROR)
				return (FALSE);
		}
		lon -= ret;
		idx += ret;
	}
	return (TRUE);
}
// ________________________________________________________________________________________________________
// Función: recibeTrama
//
//	Descripción:
//		Recibe una trama por la red
//	Parametros:
//		- sock : El socket del cliente
//		- trama: El buffer para recibir la trama
//	Devuelve:
//		Un puntero a una estrucutra TRAMA o NULL si ha habido algún error
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
TRAMA* recibeTrama(SOCKET *sock)
{
	int ret,lon,lSize;
	char *buffer,*bufferd,bloque[LONBLK],*hlonprm;
	TRAMA * ptrTrama;

	lon=lSize=0;
	do{
		if(!recData(sock,bloque,LONBLK,&ret)) // Lee bloque
			return(NULL);

		if (lon==0 && lSize==0 && ret==0) // Comprueba trama válida
			return(NULL);

		if(lSize==0){ // Comprueba tipo de trama y longitud total de los parámetros
			if (strncmp(bloque, "@JMMLCAMDJ_MCDJ",15)!=0)
				return(NULL); // No se reconoce la trama
			hlonprm=reservaMemoria(LONHEXPRM+1);
			if(!hlonprm) return(NULL);
			memcpy(hlonprm,&bloque[LONGITUD_CABECERATRAMA],LONHEXPRM);
			lSize=strtol(hlonprm,NULL,16); // Longitud total de la trama con los parametros encriptados
			liberaMemoria(hlonprm);
			buffer=(char*)reservaMemoria(lSize); // Toma memoria para la trama completa
			if(!buffer)
				return(NULL);
		}

		if(ret>0){ // Datos recibidos
			memcpy(&buffer[lon],bloque,ret); // Añade bloque
			lon+=ret;
		}
	}while(lon<lSize);

	ptrTrama=(TRAMA *)reservaMemoria(sizeof(TRAMA));
	if (!ptrTrama)	return(NULL);
	memcpy(ptrTrama,buffer,LONGITUD_CABECERATRAMA); // Copia cabecera de trama
	lon=lSize-(LONGITUD_CABECERATRAMA+LONHEXPRM); // Longitud de los parametros aún encriptados
	bufferd = &buffer[LONGITUD_CABECERATRAMA+LONHEXPRM];
	initParametros(ptrTrama,lon); // Desencripta la trama
	memcpy(ptrTrama->parametros,bufferd,lon);
	liberaMemoria((char*)buffer);
	ptrTrama->lonprm=lon; // Almacena longitud de los parámetros ya desencriptados
	return(ptrTrama);
}
// ________________________________________________________________________________________________________
// Función: recData
//
//	Descripción:
//		Recibe datos por la red a través de un socket
//	Parametros:
//		- sock : El socket por el que se reciben los datos
//		- datos: El buffer donde se almacenan
//		- lon: Cantidad máxima de bites a recibir
//		- ret: Cantidad de bites recibidos (Parámetro de salida)
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
BOOLEAN recData(SOCKET *sock, char* buffer,int lon,int* ret)
{
	*ret = 0;

	while (TRUE) { // Bucle para recibir datos del cliente
		*ret = recv(*sock,buffer, lon, 0);
		if (*ret == 0) // Conexión cerrada por parte del cliente (Graceful close)
			break;
		else {
			if (*ret == SOCKET_ERROR) {
				return (FALSE);
			} else
				// Datos recibidos
				break;
		}
	}
	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: enviaFlag
//
//	Descripción:
// 		Envia una señal de sincronización
//	Parámetros:
//		- socket_c: (Salida) Socket utilizado para el envío (operativo)
//		- ptrTrama: contenido del mensaje
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
BOOLEAN enviaFlag(SOCKET *socket_c,TRAMA *ptrTrama)
{
	char modulo[] = "enviaFlag()";
	if (!mandaTrama(socket_c,ptrTrama)) {
		errorLog(modulo,26,FALSE);
		return (FALSE);
	}
	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: recibeFlag
//
//	Descripción:
// 		Recibe una señal de sincronización
//	Parámetros:
//		- socket_c: Socket utilizadopara la recepción (operativo)
//		- ptrTrama: (Salida) Contenido del mensaje
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
BOOLEAN recibeFlag(SOCKET *socket_c,TRAMA *ptrTrama)
{
	ptrTrama=recibeTrama(socket_c);
	if(!ptrTrama){
		return(FALSE);
	}
	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: URLEncode
//
//	Descripción:
// 		Codifica una cadena en UrlEncode
//	Parámetros:
//		- src: La cadena a decodificar
// 	Devuelve:
//		La cadena decodificada
// ________________________________________________________________________________________________________
char* URLEncode(char *src)
{
	char *dest;
	int i,j=0,lon;

	lon=strlen(src);
	dest=(char*)reservaMemoria(lon*2);	// Reserva buffer  para la cadena
	for(i=0;i<lon;i++){
		if(src[i]==0x20){ // Espacio
			dest[j++] = '%';
			dest[j++] = '2';
			dest[j++] = '0';
		}
		else
			dest[j++] = src[i];
	}
	return(dest);
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

	dest=(char*)reservaMemoria(strlen(src));	// Reserva buffer  para la cadena
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
// ________________________________________________________________________________________________________
// Función: leeArchivo
//
//	Descripción:
//		Lee un archivo
//	Parámetros:
//		fil: Nombre completo del archivo
//	Devuelve:
//		Un puntero al buffer con el contenido leido

//______________________________________________________________________________________________________
char * leeArchivo(char *fil)
{
	FILE *f;
	long lSize;
	char* buffer;

	f=fopen(fil,"rb");
	if (!f)
		return(NULL);
	fseek (f,0,SEEK_END); // Obtiene tamaño del fichero.
	lSize = ftell (f);
	rewind (f);
	buffer = (char*) reservaMemoria(lSize+1); // Toma memoria para el buffer de lectura.
	if (!buffer) // No hay memoria suficiente para el buffer
		return (NULL);
	lSize=fread (buffer,1,lSize,f); // Lee contenido del fichero
	fclose(f);
	return (buffer);
}
// ________________________________________________________________________________________________________
// Función: leeArchivo
//
//	Descripción:
//		Calcula la longitud de un archivo
//	Parámetros:
//		fil: Nombre completo del archivo
//	Devuelve:
//		Un puntero al buffer con el contenido leido

//______________________________________________________________________________________________________
int lonArchivo(char *fil)
{
	FILE *f;
	long lSize;

	f=fopen(fil,"rb");
	if (!f)
		return(0);
	fseek (f,0,SEEK_END); // Obtiene tamaño del fichero.
	lSize = ftell (f);
	fclose(f);
	return (lSize);
}
// ________________________________________________________________________________________________________
// Función: escribeArchivo
//
//	Descripción:
//		Escribe un archivo
//	Parámetros:
//		fil: Nombre completo del archivo
//		buffer: Un puntero al buffer con el contenido a escribir
//	Devuelve:
//______________________________________________________________________________________________________
BOOLEAN escribeArchivo(char *fil,char*buffer)
{
	FILE *f;
	long lSize;

	f=fopen(fil,"wb");
	if (!f){
		return(FALSE);
	}
	lSize=strlen(buffer);
	fwrite(buffer,1,lSize,f); // Escribe el contenido en el fichero
	fclose(f);
	return (TRUE);
}
// ________________________________________________________________________________________________________
// Función: sendArchivo
//
//	Descripción:
//		Envía un archivo por la red
//	Parámetros:
//		sock: Socket para el envío
//		fil: Nombre local completo del archivo
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN sendArchivo(SOCKET *sock,char *fil)
{
	long lSize;
	FILE *f;
	char buffer[LONBLK];

	f = fopen(fil,"rb");
	if(!f) // El fichero no existe
		return(FALSE);

	while(!feof(f)){
		lSize=fread (buffer,1,LONBLK,f); // Lee el contenido del fichero
		if(!sendData(sock,buffer,lSize))
			return (FALSE);
	}
	fclose(f);
	return(TRUE);
}
// ________________________________________________________________________________________________________
// Función: recArchivo
//
//	Descripción:
//		Recibe un archivo por la red
//	Parámetros:
//		sock: Socket para la recepción
//		fil: Nombre local completo del archivo que se creará
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN recArchivo(SOCKET *sock,char *fil)
{
	int lon;
	FILE *f;
	char buffer[LONBLK];

	f = fopen(fil,"wb");
	if(!f) // No se ha podido crear el archivo
		return(FALSE);
	do{
		if(!recData(sock,buffer,LONBLK,&lon))
			return(FALSE);
		// Datos recibidos
		if(lon>0)
			fwrite(buffer,1,lon,f); // Lee el contenido del fichero
	}while(lon>0); // Bucle para recibir datos del cliente
	fclose(f);
	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: initParammetros
//
//	 Descripción:
//		Libera memoria del buffer de los parametros de la trama y vuelve a reservar espacio
//	Parámetros:
//		- parametros : Puntero a la zona donde están los parametros de una trama
//		- lon : Tamaño de la nueva reserva de espacio para los parametros
// 	Devuelve:
//		Un puntero a la nueva zona de memoria o NULL si ha habido algún error
// Especificaciones:
// 		En caso de que el parámetro lon valga cero el tamaño a reservar será el estandar
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
	char modulo[] = "TCPConnect()";

	s = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	if (s == INVALID_SOCKET){
		return (INVALID_SOCKET);
	}
	server.sin_family = AF_INET;
	server.sin_port = htons((short)atoi(port));
	server.sin_addr.s_addr = inet_addr(ips);

	if (connect(s, (struct sockaddr *)&server, sizeof(server)) == INVALID_SOCKET){
		errorLog(modulo,38,TRUE);
		return (INVALID_SOCKET);
	}

	return(s);
}
//______________________________________________________________________________________________________
// Función: AbreConexion
//
//	 Descripción:
//		Abre la conexión entre el cliente y el servidor de administración
//	Parámetros:
//		- Ninguno
// 	Devuelve:
//		Un socket de cliente para comunicaciones
//______________________________________________________________________________________________________
SOCKET abreConexion(void)
{
	int swloop=0;
	SOCKET s;

	while(swloop<MAXCNX){
		s=TCPConnect(servidoradm,puerto);
		if(s!= INVALID_SOCKET){
			return(s);
		}
		swloop++;
		#ifdef  __WINDOWS__
			Sleep(5*1000);
		#else
			sleep(5); // Espera cinco segundos antes de intentar una nueva conexión
		#endif
	}
	return(INVALID_SOCKET);
}
//______________________________________________________________________________________________________
// Función: enviaMensaje
//
//	Descripción:
// 		Envia un mensaje al servidor de Administración
//	Parámetros:
//		- socket_c: (Salida) Socket utilizado para el envío
//		- ptrTrama: contenido del mensaje
//		- tipo: Tipo de mensaje
//				C=Comando, N=Respuesta a un comando, P=Peticion,R=Respuesta a una petición, I=Informacion
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
BOOLEAN enviaMensaje(SOCKET *socket_c,TRAMA *ptrTrama,char tipo)
{
	char modulo[] = "enviaMensaje()";

	*socket_c=abreConexion();
	if(*socket_c==INVALID_SOCKET){
		errorLog(modulo,38,FALSE); // Error de conexión con el servidor
		return(FALSE);
	}
	ptrTrama->arroba='@'; // Cabecera de la trama
	strncpy(ptrTrama->identificador,"JMMLCAMDJ_MCDJ",14);	// identificador de la trama
	ptrTrama->tipo=tipo; // Tipo de mensaje

	if (!mandaTrama(socket_c,ptrTrama)) {
		errorLog(modulo,26,FALSE);
		return (FALSE);
	}
	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: recibeMensaje
//
//	Descripción:
// 		Recibe un mensaje del servidor de Administración
//	Parámetros:
//		- socket_c: Socket utilizadopara la recepción
//		- ptrTrama: (Salida) Contenido del mensaje
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
TRAMA* recibeMensaje(SOCKET *socket_c)
{
	TRAMA* ptrTrama;
	char modulo[] = "recibeMensaje()";

	ptrTrama=recibeTrama(socket_c);
	if(!ptrTrama){
		errorLog(modulo,17,FALSE);
		return(NULL);
	}
	return(ptrTrama);
}

// ________________________________________________________________________________________________________
