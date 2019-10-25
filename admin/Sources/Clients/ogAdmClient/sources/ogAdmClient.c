// ********************************************************************************************************
// Cliernte: ogAdmClient
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Abril-2010
// Nombre del fichero: ogAdmClient.c
// Descripción :Este fichero implementa el cliente general del sistema
// ********************************************************************************************************

#include "ogAdmClient.h"
#include "ogAdmLib.c"
//________________________________________________________________________________________________________
//	Función: tomaConfiguracion
//
//	Descripción:
//		Lee el fichero de configuración del servicio
//	Parámetros:
//		filecfg : Ruta completa al fichero de configuración
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error 
//________________________________________________________________________________________________________
BOOLEAN tomaConfiguracion(char* filecfg)
{
	char modulo[] = "tomaConfiguracion()";

	if (filecfg == NULL || strlen(filecfg) == 0) {
		errorLog(modulo, 1, FALSE); // Fichero de configuración del cliente vacío
		return (FALSE);
	}
	FILE *fcfg;
	int lSize;
	char * buffer, *lineas[MAXPRM], *dualparametro[2];
	int i, numlin, resul;

	fcfg = fopen(filecfg, "rt");
	if (fcfg == NULL) {
		errorLog(modulo, 2, FALSE); // No existe fichero de configuración del cliente
		return (FALSE);
	}

	fseek(fcfg, 0, SEEK_END);
	lSize = ftell(fcfg); // Obtiene tamaño del fichero.
	rewind(fcfg);
	buffer = (char*) reservaMemoria(lSize+1); // Toma memoria para el buffer de lectura.
	if (buffer == NULL) { // No hay memoria suficiente para el buffer
		errorLog(modulo, 3, FALSE);
		return (FALSE);
	}
	lSize=fread(buffer, 1, lSize, fcfg); // Lee contenido del fichero
	buffer[lSize]=CHARNULL;
	fclose(fcfg);

	/* Inicializar variables globales */
	servidoradm[0]=CHARNULL;
	puerto[0] = CHARNULL;
	pathinterface[0]=CHARNULL;
	urlmenu[0]=CHARNULL;
	urlmsg[0]=CHARNULL;

	numlin = splitCadena(lineas, buffer, '\n');
	for (i = 0; i < numlin; i++) {
		splitCadena(dualparametro, lineas[i], '=');

		resul = strcmp(StrToUpper(dualparametro[0]), "SERVIDORADM");
		if (resul == 0)
			strcpy(servidoradm, dualparametro[1]);

		resul = strcmp(StrToUpper(dualparametro[0]), "PUERTO");
		if (resul == 0)
			strcpy(puerto, dualparametro[1]);

		resul = strcmp(StrToUpper(dualparametro[0]), "PATHINTERFACE");
		if (resul == 0)
			strcpy(pathinterface, dualparametro[1]);

		resul = strcmp(StrToUpper(dualparametro[0]), "URLMENU");
		if (resul == 0)
			strcpy(urlmenu, dualparametro[1]);

		resul = strcmp(StrToUpper(dualparametro[0]), "URLMSG");
		if (resul == 0)
			strcpy(urlmsg, dualparametro[1]);
	}

	if (servidoradm[0] == CHARNULL) {
		liberaMemoria(buffer);
		errorLog(modulo,4, FALSE); // Falta parámetro SERVIDORADM
		return (FALSE);
	}

	if (puerto[0] == CHARNULL) {
		liberaMemoria(buffer);
		errorLog(modulo,5, FALSE); // Falta parámetro PUERTO
		return (FALSE);
	}
	if (pathinterface[0] == CHARNULL) {
		liberaMemoria(buffer);
		errorLog(modulo,56, FALSE); // Falta parámetro PATHINTERFACE
		return (FALSE);
	}

	if (urlmenu[0] == CHARNULL) {
		liberaMemoria(buffer);
		errorLog(modulo,89, FALSE); // Falta parámetro URLMENU
		return (FALSE);
	}
	if (urlmsg[0] == CHARNULL) {
		liberaMemoria(buffer);
		errorLog(modulo,90, FALSE); // Falta parámetro URLMSG
		return (FALSE);
	}
	liberaMemoria(buffer);
	return (TRUE);
}
//______________________________________________________________________________________________________
// Función: FinterfaceAdmin
//
//	 Descripción:
//		Esta función es la puerta de comunicación entre el módulo de administración y el motor de clonación.
//		La Aplicación de administración utiliza una interface para ejecutar funciones del motor de clonación;
//		esta interface llamará a la API del motor con lo que cambiando el comportamiento de esta interface
//		podremos hacer llamadas a otras API de clonación y de esta manera probar distintos motores.
//
//	Parámetros:
//		- script: Nombre del módulo,función o script de la interface
//		- parametros: Parámetros que se le pasarán a la interface
//		- salida: Recoge la salida que genera la llamada a la interface

// 	Devuelve:
//		Código de error de la ejecución al módulo , función o script de la interface
//
//	Especificaciones:
//		El parámetro salida recoge la salida desde un fichero que se genera en la ejecución del script siempre que
//		sea distinto de NULL, esto es, si al llamar a la función este parámetro es NULL no se recogerá dicha salida.
//		Este fichero tiene una ubicación fija: /tmp/_retinterface
//______________________________________________________________________________________________________

int FinterfaceAdmin( char *script,char* parametros,char* salida)
{
    FILE *f;
	int lSize,nargs,i,resul;
    char msglog[LONSTD],*argumentos[MAXARGS];
	char modulo[] = "FinterfaceAdmin()";


	if (ndebug>= DEBUG_MEDIO) {
		sprintf(msglog, "%s:%s", tbMensajes[8], script);
		infoDebug(msglog);
	}

	/* Crea matriz de los argumentos */
	nargs=splitCadena(argumentos,parametros,32);
	for(i=nargs;i<MAXARGS;i++){
		argumentos[i]=NULL;
	}

	/* Muestra matriz de los argumentos */
	for(i=0;i<nargs;i++){
		if (ndebug>= DEBUG_ALTO) {
			sprintf(msglog, "%s: #%d-%s", tbMensajes[9],i+1,argumentos[i]);
			infoDebug(msglog);
		}
	}
	/* Elimina fichero de retorno */
	if(salida!=(char*)NULL){
		f = fopen("/tmp/_retinterface_","w" );
		if (f==NULL){  // Error de eliminación
			scriptLog(modulo,10);
			resul=8;
			scriptLog(modulo,resul);
			return(resul);
		}
		fclose(f);
	}
	/* Compone linea de comando */
	if(parametros){
		strcat(script," ");
		strcat(script,parametros);
	}
	/* LLamada función interface */
	resul=system(script);
	if(resul){
		scriptLog(modulo,10);
		scriptLog(modulo,resul);
		return(resul);
	}
	/* Lee fichero de retorno */
	if(salida!=(char*)NULL){
		f = fopen("/tmp/_retinterface_","rb" );
		if (f==NULL){ // Error de apertura
			scriptLog(modulo,10);
			resul=9;
			scriptLog(modulo,resul);
			return(resul);
		}
		else{
			fseek (f ,0,SEEK_END);  // Obtiene tamaño del fichero.
			lSize = ftell (f);
			rewind (f);
			if(lSize>LONGITUD_SCRIPTSALIDA){
				scriptLog(modulo,10);
				resul=11;
				scriptLog(modulo,resul);
				return(resul);
			}
			fread (salida,1,lSize,f); 	// Lee contenido del fichero
			rTrim(salida);
			fclose(f);
		}
	}
	/* Muestra información de retorno */
	if(salida!=(char*)NULL){
		if(ndebug>2){
			sprintf(msglog,"Información devuelta %s",salida);
			infoDebug(msglog);
		}
	}
	return(resul);
}
//______________________________________________________________________________________________________
// Función: interfaceAdmin
//
//	 Descripción:
//		Esta función es la puerta de comunicación entre el módulo de administración y el motor de clonación.
//		La Aplicación de administración utiliza una interface para ejecutar funciones del motor de clonación;
//		esta interface llamará a la API del motor con lo que cambiando el comportamiento de esta interface
//		podremos hacer llamadas a otras API de clonación y de esta manera probar distintos motores.
//
//	Parámetros:
//		- script: Nombre del módulo,función o script de la interface
//		- parametros: Parámetros que se le pasarán a la interface
//		- salida: Recoge la salida que genera la llamada a la interface

// 	Devuelve:
//		Código de error de la ejecución al módulo , función o script de la interface
//
//	Especificaciones:
//		El parámetro salida recoge la salida desde el procedimiento hijo que se genera en la ejecución de éste
//		siempre que sea distinto de NULL, esto es, si al llamar a la función este parámetro es NULL no se
//		recogerá dicha salida.
//______________________________________________________________________________________________________

int interfaceAdmin( char *script,char* parametros,char* salida)
{
	int  descr[2];	/* Descriptores de E y S de la turbería */
	int  bytesleidos;	/* Bytes leidos en el mensaje */
	int estado;
	pid_t  pid;
	char buffer[LONBLK];	// Buffer de lectura de fichero
	pipe (descr);
	int i,nargs,resul;
	int lon;		// Longitud de cadena
	char msglog[LONSUC];	// Mensaje de registro de sucesos
	char *argumentos[MAXARGS];
	char modulo[] = "interfaceAdmin()";
	if (ndebug>= DEBUG_MEDIO) {
		sprintf(msglog, "%s:%s", tbMensajes[8], script);
		infoDebug(msglog);
	}

	/* Crea matriz de los argumentos */
	nargs=splitCadena(argumentos,parametros,32);
	for(i=nargs;i<MAXARGS;i++){
		argumentos[i]=NULL;
	}
	/* Muestra matriz de los argumentos */
	for(i=1;i<nargs;i++){
		if (ndebug>= DEBUG_ALTO) {
			// Truncar la cadena si es mayor que el tamaño de la línea de log.
			sprintf(msglog, "%s: #%d-", tbMensajes[9], i+1);
			lon = strlen (msglog);
			if (lon + strlen (argumentos[i]) < LONSUC) {
				strcat (msglog, argumentos[i]);
			}
			else
			{
				strncat (msglog, argumentos[i], LONSUC - lon - 4);
				strcat (msglog, "...");
			}
			infoDebug(msglog);
		}
	}

	if((pid=fork())==0)
	{
		//_______________________________________________________________

		/* Proceso hijo que ejecuta la función de interface */

		close (descr[LEER]);
		dup2 (descr[ESCRIBIR], 1);
		close (descr[ESCRIBIR]);
		resul=execv(script,argumentos);
		//resul=execlp (script, script, argumentos[0],argumentos[1],NULL);
		exit(resul);

		/* Fin de proceso hijo */
		//_______________________________________________________________
	}
	else
	{
		//_______________________________________________________________

		/* Proceso padre que espera la ejecución del hijo */

		if (pid ==-1){ // Error en la creación del proceso hijo
			scriptLog(modulo,10);
			resul=13;
			scriptLog(modulo,resul);
			return(resul);
		}
		close (descr[ESCRIBIR]);
		bytesleidos = read (descr[LEER], buffer, LONBLK-1);
		while(bytesleidos>0){
			if(salida!=(char*)NULL){ // Si se solicita retorno de información...
				buffer[bytesleidos]='\0';
				// Error si se supera el tamaño máximo de cadena de salida.
				if(strlen(buffer)+strlen(salida)>LONGITUD_SCRIPTSALIDA){
					scriptLog(modulo,10);
					resul=11;
					scriptLog(modulo,resul);
					return(resul);
				}
				rTrim(buffer);
				strcat(salida,buffer);
			}
			bytesleidos = read (descr[LEER], buffer, LONBLK-1);
		}
		close (descr[LEER]);
		//kill(pid,SIGQUIT);
		waitpid(pid,&estado,0);
		resul=WEXITSTATUS(estado);
		if(resul){
			scriptLog(modulo,10);
			scriptLog(modulo,resul);
			return(resul);
		}
		/* Fin de proceso padre */
		//_______________________________________________________________
	}

	/* Muestra información de retorno */
	if(salida!=(char*)NULL){
		if(ndebug>2){
			// Truncar la cadena si es mayor que el tamaño de la línea de log.
			strcpy(msglog,"Informacion devuelta ");
			lon = strlen (msglog);
			if (lon + strlen (salida) < LONSUC) {
				strcat (msglog, salida);
			}
			else
			{
				strncat (msglog, salida, LONSUC-lon-4);
				strcat (msglog, "...");
			}
			infoDebug(msglog);
		}
	}
	return(resul);
}
//______________________________________________________________________________________________________
// Función: scriptLog
//
//	Descripción:
//		Registra los sucesos de errores de scripts en el fichero de log
//	Parametros:
//		- modulo: Módulo donde se produjo el error
//		- coderr : Código del mensaje de error del script
//______________________________________________________________________________________________________
void scriptLog(const char *modulo,int coderr)
{
	char msglog[LONSUC];

	if(coderr<MAXERRORSCRIPT)
		errorInfo(modulo,tbErroresScripts[coderr]); // Se ha producido algún error registrado
	else{
		sprintf(msglog,"%s: %d",tbErroresScripts[MAXERRORSCRIPT],coderr);
		errorInfo(modulo,msglog);
	}
}
//______________________________________________________________________________________________________
// Función: TomaIPlocal
//
//	 Descripción:
//		Recupera la IP local
//	Parámetros:
//		Ninguno
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//	Especificaciones:
//		En caso de no encontrar la IP o generarse algún error la IP local sería 0.0.0.0
//______________________________________________________________________________________________________
BOOLEAN tomaIPlocal()
{
	char modulo[] = "tomaIPlocal()";

	// Para debug
	//strcpy(IPlocal,"10.1.15.203");
	//return(TRUE);

	sprintf(interface,"%s/getIpAddress",pathinterface);
	herror=interfaceAdmin(interface,NULL,IPlocal);
	if(herror){
		errorLog(modulo,85,FALSE);
		return(FALSE);
	}
	return(TRUE);
}
//______________________________________________________________________________________________________
//
// Función: cuestionCache
//
//	 Descripción:
//		Procesa la cache en caso de existir.
//	Parámetros:
//		tam : Tamaño de la cache
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN cuestionCache(char* tam)
{
	return(TRUE);
	//>>>>>>>>>>>>>>>>>>>>>>>>>>
	char msglog[LONSTD];
	char modulo[] = "cuestionCache()";

	sprintf(interface,"%s/%s",pathinterface,"procesaCache");
	sprintf(parametros,"%s %s","procesaCache",tam);

	herror=interfaceAdmin(interface,parametros,NULL);
	if(herror){
		sprintf(msglog,"%s",tbErrores[88]);
		errorInfo(modulo,msglog);
		return(FALSE);
	}

	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: cargaPaginaWeb
//
//	Descripción:
// 		Muestra una pégina web usando el browser
//	Parámetros:
//	  urp: Dirección url de la página
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
int cargaPaginaWeb(char *url)
{
	pid_t pidbrowser; // Identificador del proceso que se crea para mostrar una página web con el browser
	int resul=0;
	char* argumentos[4];
	char modulo[] = "cargaPaginaWeb()";

	// Destruye los procesos del Browser y lanza uno nuevo.
	system("pkill -9 browser");

	sprintf(interface,"/opt/opengnsys/bin/browser");
	sprintf(parametros,"browser -qws %s",url);

	splitCadena(argumentos,parametros,' '); // Crea matriz de los argumentos del scripts
	argumentos[3]=NULL;
	if((pidbrowser=fork())==0){
		/* Proceso hijo que ejecuta el script */
		resul=execv(interface,argumentos);
		exit(resul);
	}
	else {
		if (pidbrowser ==-1){
			scriptLog(modulo,10);
			resul=13;
			scriptLog(modulo,resul);
			return(resul);
		}
	}
	return(resul);
}
//________________________________________________________________________________________________________
//	Función: muestraMenu
//
//	Descripción:
//		Muestra el menu inicial del cliente
//	Parámetros:
//		Ninguno
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//________________________________________________________________________________________________________
void muestraMenu()
{
	cargaPaginaWeb(urlmenu);
}
//______________________________________________________________________________________________________
// Función: muestraMensaje
//
//	Descripción:
// 		Muestra un mensaje en pantalla
//	Parámetros:
//		- idx: Indice del mensaje
//		- msg: Descripción Mensaje
// ________________________________________________________________________________________________________
void muestraMensaje(int idx,char*msg)
{
	char *msgpan,url[250];
	
	if(msg){
		msgpan=URLEncode(msg);
		sprintf(url,"%s?msg=%s",urlmsg,msgpan); // Url de la página de mensajes
		liberaMemoria(msgpan);
	}
	else
		sprintf(url,"%s?idx=%d",urlmsg,idx); // Url de la página de mensajes
	cargaPaginaWeb(url);
}
//______________________________________________________________________________________________________
// Función: InclusionCliente
//	 Descripción:
//		Abre una sesión en el servidor de administración y registra al cliente en el sistema
//	Parámetros:
//		Ninguno
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN inclusionCliente(TRAMA* ptrTrama)
{
	int lon;		// Longitud de cadena
	char msglog[LONSUC];	// Mensaje de registro de sucesos
	char *cfg;		// Datos de configuración
	SOCKET socket_c;
	char modulo[] = "inclusionCliente()";

	cfg=LeeConfiguracion();
	
	if(!cfg){ // No se puede recuperar la configuración del cliente
		errorLog(modulo,36,FALSE);
		errorLog(modulo,37,FALSE);
		return(FALSE);
	}
	if (ndebug>= DEBUG_ALTO) {
		// Truncar la cadena si es mayor que el tamaño de la línea de log.
		sprintf(msglog, "%s", tbMensajes[14]);
		lon = strlen (msglog);
		if (lon + strlen (cfg) < LONSUC) {
			strcat (msglog, cfg);
		}
		else
		{
			strncat (msglog, cfg, LONSUC - lon - 4);
			strcat (msglog, "...");
		}
		infoDebug(msglog);
	}
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=InclusionCliente\r"); // Nombre de la función a ejecutar en el servidor
	lon+=sprintf(ptrTrama->parametros+lon,"cfg=%s\r",cfg); // Configuración de los Sistemas Operativos del cliente
	liberaMemoria(cfg);
	
	if(!enviaMensajeServidor(&socket_c,ptrTrama,MSG_PETICION)){
		errorLog(modulo,37,FALSE);
		return(FALSE);
	}
	ptrTrama=recibeMensaje(&socket_c);
	if(!ptrTrama){
		errorLog(modulo,45,FALSE);
		return(FALSE);
	}

	close(socket_c);

	if(!gestionaTrama(ptrTrama)){	// Análisis de la trama
		errorLog(modulo,39,FALSE);
		return(FALSE);
	}

	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: RESPUESTA_InclusionCliente
//
//	Descripción:
//  	Respuesta del servidor de administración a la petición de inicio
//		enviando los datos identificativos del cliente y otras configuraciones
//	Parámetros:
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN RESPUESTA_InclusionCliente(TRAMA* ptrTrama)
{
	char* res;
	char modulo[] = "RESPUESTA_InclusionCliente()";

	res=copiaParametro("res",ptrTrama); // Resultado del proceso de inclusión
	if(atoi(res)==0){ // Error en el proceso de inclusión
		liberaMemoria(res);
		errorLog(modulo,41,FALSE);
		return (FALSE);
	}
	liberaMemoria(res);

	idordenador=copiaParametro("ido",ptrTrama); // Identificador del ordenador
	nombreordenador=copiaParametro("npc",ptrTrama);	//  Nombre del ordenador
	cache=copiaParametro("che",ptrTrama); // Tamaño de la caché reservada al cliente
	idproautoexec=copiaParametro("exe",ptrTrama); // Procedimento de inicio (Autoexec)
	idcentro=copiaParametro("idc",ptrTrama); // Identificador de la Unidad Organizativa
	idaula=copiaParametro("ida",ptrTrama); // Identificador del aula

	if(idordenador==NULL || nombreordenador==NULL){
		errorLog(modulo,40,FALSE);
		return (FALSE);
	}
	return(TRUE);
}
//______________________________________________________________________________________________________
//
// Función: LeeConfiguracion
//	 Descripción:
//		Abre una sesión en el servidor de administración y registra al cliente en el sistema
//	Parámetros:
//		Ninguno
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________

char* LeeConfiguracion()
{
	char* parametroscfg;
	char modulo[] = "LeeConfiguracion()";
	int herrorcfg;

	// Reservar memoria para los datos de cofiguración.
	parametroscfg=(char*)reservaMemoria(LONGITUD_SCRIPTSALIDA);
	if(!parametroscfg){
		errorLog(modulo,3,FALSE);
		return(NULL);
	}
	// Ejecutar script y obtener datos.
	sprintf(interface,"%s/%s",pathinterface,"getConfiguration");
	herrorcfg=interfaceAdmin(interface,NULL,parametroscfg);

	if(herrorcfg){ // No se puede recuperar la configuración del cliente
		liberaMemoria(parametroscfg);
		errorLog(modulo,36,FALSE);
		return(NULL);
	}
	return(parametroscfg);
}
//________________________________________________________________________________________________________
//	Función: autoexecCliente
//
//	Descripción:
//		Solicita procedimiento de autoexec para el cliebnte
//	Parámetros:
//		Ninguno
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//________________________________________________________________________________________________________
BOOLEAN autoexecCliente(TRAMA* ptrTrama)
{
	SOCKET socket_c;
	char modulo[] = "autoexecCliente()";

	initParametros(ptrTrama,0);
	sprintf(ptrTrama->parametros,"nfn=AutoexecCliente\rexe=%s\r",idproautoexec);

	if(!enviaMensajeServidor(&socket_c,ptrTrama,MSG_PETICION)){
		errorLog(modulo,42,FALSE);
		return(FALSE);
	}
	ptrTrama=recibeMensaje(&socket_c);
	if(!ptrTrama){
		errorLog(modulo,45,FALSE);
		return(FALSE);
	}

	close(socket_c);

	if(!gestionaTrama(ptrTrama)){	// Análisis de la trama
		errorLog(modulo,39,FALSE);
		return(FALSE);
	}

	return(TRUE);
}
//________________________________________________________________________________________________________
//	Función: autoexecCliente
//
//	Descripción:
//		Ejecuta un script de autoexec personalizado en todos los inicios para el cliente
//	Parámetros:
//		Ninguno
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//________________________________________________________________________________________________________
BOOLEAN RESPUESTA_AutoexecCliente(TRAMA* ptrTrama)
{
	SOCKET socket_c;
	char *res,*nfl;
	char modulo[] = "RESPUESTA_AutoexecCliente()";

	res=copiaParametro("res",ptrTrama);
	if(atoi(res)==0){ // Error en el proceso de autoexec
		liberaMemoria(res);
		return (FALSE);
	}
	liberaMemoria(res);

	nfl=copiaParametro("nfl",ptrTrama);
	initParametros(ptrTrama,0);
	sprintf(ptrTrama->parametros,"nfn=enviaArchivo\rnfl=%s\r",nfl);
	liberaMemoria(nfl);

	/* Envía petición */
	if(!enviaMensajeServidor(&socket_c,ptrTrama,MSG_PETICION)){
		errorLog(modulo,42,FALSE);
		return(FALSE);
	}
	/* Nombre del archivo destino (local)*/
	char fileautoexec[LONPRM];
	sprintf(fileautoexec,"/tmp/_autoexec_%s",IPlocal);

	/* Recibe archivo */
	if(!recArchivo(&socket_c,fileautoexec)){
		errorLog(modulo,58, FALSE);
		close(socket_c);
		return(FALSE);
	}

	close(socket_c);

	/* Ejecuta archivo */
	ejecutaArchivo(fileautoexec,ptrTrama);
	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: comandosPendientes
//
//	 Descripción:
// 		 Búsqueda de acciones pendientes en el servidor de administración
//	Parámetros:
//		Ninguno
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN comandosPendientes(TRAMA* ptrTrama)
{
	SOCKET socket_c;
	char modulo[] = "comandosPendientes()";

	CMDPTES=TRUE;
	initParametros(ptrTrama,0);

	while(CMDPTES){
		sprintf(ptrTrama->parametros,"nfn=ComandosPendientes\r");
		if(!enviaMensajeServidor(&socket_c,ptrTrama,MSG_PETICION)){
			errorLog(modulo,42,FALSE);
			return(FALSE);
		}
		ptrTrama=recibeMensaje(&socket_c);
		if(!ptrTrama){
			errorLog(modulo,45,FALSE);
			return(FALSE);
		}

 		close(socket_c);

		if(!gestionaTrama(ptrTrama)){	// Análisis de la trama
			errorLog(modulo,39,FALSE);
			return(FALSE);
		}
	}
	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: NoComandosPtes
//
//	 Descripción:
//		 Conmuta el switch de los comandos pendientes y lo pone a false
//	Parámetros:
//		- ptrTrama: contenido del mensaje
// 	Devuelve:
//		TRUE siempre
//	Especificaciones:
//		Cuando se ejecuta esta función se sale del bucle que recupera los comandos pendientes en el
//		servidor y el cliente pasa a a estar disponible para recibir comandos desde el éste.
//______________________________________________________________________________________________________
BOOLEAN NoComandosPtes(TRAMA* ptrTrama)
{
	CMDPTES=FALSE; // Corta el bucle de comandos pendientes
	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: ProcesaComandos
//
//	Descripción:
// 		Espera comando desde el Servidor de Administración para ejecutarlos
//	Parámetros:
//		Ninguno
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
void procesaComandos(TRAMA* ptrTrama)
{
	int lon;
	SOCKET socket_c;
	char modulo[] = "procesaComandos()";

	initParametros(ptrTrama,0);
	while(TRUE){
		lon=sprintf(ptrTrama->parametros,"nfn=DisponibilidadComandos\r");
		lon+=sprintf(ptrTrama->parametros+lon,"tpc=%s\r",CLIENTE_OPENGNSYS); // Activar disponibilidad
		if(!enviaMensajeServidor(&socket_c,ptrTrama,MSG_INFORMACION)){
			errorLog(modulo,43,FALSE);
			return;
		}
		infoLog(19); // Disponibilidad de cliente activada
		ptrTrama=recibeMensaje(&socket_c);
		if(!ptrTrama){
			errorLog(modulo,46,FALSE);
			return;
		}

		close(socket_c);

		if(!gestionaTrama(ptrTrama)){	// Análisis de la trama
			errorLog(modulo,39,FALSE);
			return;
		}
		if(!comandosPendientes(ptrTrama)){
			errorLog(modulo,42,FALSE);
		}
	}
}
//______________________________________________________________________________________________________
// Función: Actualizar
//
//	 Descripción:
//		Actualiza los datos de un ordenador como si volviera a solicitar la entrada
//		en el sistema al  servidor de administración
//	Parámetros:
//		ptrTrama: contenido del mensajede
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN Actualizar(TRAMA* ptrTrama)
{
	char msglog[LONSTD];	// Mensaje de log
	char *cfg;		// Cadena de datos de configuración
	int lon;		// Longitud de cadena
	char modulo[] = "Actualizar()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	muestraMensaje(1,NULL);
	if(!comandosPendientes(ptrTrama)){
		errorLog(modulo,84,FALSE);
		return(FALSE);
	}

	cfg=LeeConfiguracion();
	herror=0;
	if(!cfg){ // No se puede recuperar la configuración del cliente
		errorLog(modulo,36,FALSE);
		herror=3;
	}
	// Envia Configuracion al servidor
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_Configurar");
	lon+=sprintf(ptrTrama->parametros+lon,"cfg=%s\r",cfg); // Configuración de los Sistemas Operativos del cliente
	respuestaEjecucionComando(ptrTrama,herror,0);

	muestraMenu();

	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: Purgar
//
//	 Descripción:
//		Detiene la ejecución del browser
//	Parámetros:
//		ptrTrama: contenido del mensajede
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
int Purgar(TRAMA* ptrTrama)
{

	exit(EXIT_SUCCESS);
}
//______________________________________________________________________________________________________
// Función: Sondeo
//
//	 Descripción:
//		Envía al servidor una confirmación de que está dentro del sistema
//	Parámetros:
//		ptrTrama: contenido del mensajede
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN Sondeo(TRAMA* ptrTrama)
{
	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: ConsolaRemota
//
//	Descripción:
// 		Ejecuta un comando de la Shell y envia el eco al servidor (Consola remota)
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN ConsolaRemota(TRAMA* ptrTrama)
{
	SOCKET socket_c;
	char *nfn,*scp,*aux,ecosrc[LONPRM],ecodst[LONPRM],msglog[LONSTD];;
	char modulo[] = "ConsolaRemota()";

	/* Nombre del archivo de script */
	char filescript[LONPRM];
	sprintf(filescript,"/tmp/_script_%s",IPlocal);
	
	aux=copiaParametro("scp",ptrTrama);
	scp=URLDecode(aux);
	escribeArchivo(filescript,scp);
	liberaMemoria(aux);
	liberaMemoria(scp);
	
	nfn=copiaParametro("nfn",ptrTrama);
	sprintf(interface,"%s/%s",pathinterface,nfn);
	sprintf(ecosrc,"/tmp/_econsola_%s",IPlocal);
	sprintf(parametros,"%s %s %s",nfn,filescript,ecosrc);
	herror=interfaceAdmin(interface,parametros,NULL);
	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
	}
	else{
		/* Envía fichero de inventario al servidor */
		sprintf(ecodst,"/tmp/_Seconsola_%s",IPlocal); // Nombre que tendra el archivo en el Servidor
		initParametros(ptrTrama,0);
		sprintf(ptrTrama->parametros,"nfn=recibeArchivo\rnfl=%s\r",ecodst);
		if(!enviaMensajeServidor(&socket_c,ptrTrama,MSG_COMANDO)){
			errorLog(modulo,42,FALSE);
			return(FALSE);
		}
		 /* Espera señal para comenzar el envío */
		liberaMemoria(ptrTrama);
		recibeFlag(&socket_c,ptrTrama);
		/* Envía archivo */
		if(!sendArchivo(&socket_c,ecosrc)){
			errorLog(modulo,57, FALSE);
			herror=12; // Error de envío de fichero por la red
		}
		close(socket_c);
	}
	liberaMemoria(nfn);
	return(TRUE);
}
//_____________________________________________________________________________________________________
// Función: Comando
//
//	 Descripción:
//		COmando personalizado enviado desde el servidor
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//_____________________________________________________________________________________________________
BOOLEAN Comando(TRAMA* ptrTrama)
{
	char *ids,*nfn,msglog[LONSTD];
	char modulo[] = "Comando()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	nfn=copiaParametro("nfn",ptrTrama);
	ids=copiaParametro("ids",ptrTrama);

	sprintf(interface,"%s/%s",pathinterface,nfn);
	herror=interfaceAdmin(interface,NULL,NULL);
	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
	}
	/* Envia respuesta de ejecucución del comando */
	initParametros(ptrTrama,0);
	sprintf(ptrTrama->parametros,"nfn=RESPUESTA_%s\r",nfn);
	respuestaEjecucionComando(ptrTrama,herror,ids);
	liberaMemoria(nfn);
	liberaMemoria(ids);
	return(TRUE);
}
//_____________________________________________________________________________________________________
// Función: Arrancar
//
//	 Descripción:
//		Responde a un comando de encendido por la red
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//_____________________________________________________________________________________________________
BOOLEAN Arrancar(TRAMA* ptrTrama)
{
	int lon;
	char *ids,msglog[LONSTD];
	char modulo[] = "Arrancar()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}

	ids=copiaParametro("ids",ptrTrama);

	/* Envia respuesta de ejecucución del script */
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_Arrancar");
	lon+=sprintf(ptrTrama->parametros+lon,"tpc=%s\r",CLIENTE_OPENGNSYS);
	respuestaEjecucionComando(ptrTrama,0,ids);
	liberaMemoria(ids);
	return(TRUE);
}
//_____________________________________________________________________________________________________
// Función: Apagar
//
//	 Descripción:
//		Apaga el cliente
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//_____________________________________________________________________________________________________
BOOLEAN Apagar(TRAMA* ptrTrama)
{
	char *ids,*nfn,msglog[LONSTD];
	char modulo[] = "Apagar()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	nfn=copiaParametro("nfn",ptrTrama);
	ids=copiaParametro("ids",ptrTrama);

	initParametros(ptrTrama,0);
	sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_Apagar");
	respuestaEjecucionComando(ptrTrama,0,ids);

	sprintf(interface,"%s/%s",pathinterface,nfn);
	herror=interfaceAdmin(interface,NULL,NULL);
	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		liberaMemoria(nfn);
		liberaMemoria(ids);			
		errorInfo(modulo,msglog);
		return(FALSE);
	}
	liberaMemoria(nfn);
	liberaMemoria(ids);	
	return(TRUE);
}
//_____________________________________________________________________________________________________
// Función: Reiniciar
//
//	 Descripción:
//		Apaga el cliente
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//_____________________________________________________________________________________________________
BOOLEAN Reiniciar(TRAMA* ptrTrama)
{
	char *nfn,*ids,msglog[LONSTD];
	char modulo[] = "Reiniciar()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	nfn=copiaParametro("nfn",ptrTrama);
	ids=copiaParametro("ids",ptrTrama);

	initParametros(ptrTrama,0);
	sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_Reiniciar");
	respuestaEjecucionComando(ptrTrama,0,ids);

	sprintf(interface,"%s/%s",pathinterface,nfn);
	herror=interfaceAdmin(interface,NULL,NULL);
	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		liberaMemoria(nfn);
		liberaMemoria(ids);			
		errorInfo(modulo,msglog);
		return(FALSE);
	}
	liberaMemoria(nfn);
	liberaMemoria(ids);		
	return(TRUE);
}
//_____________________________________________________________________________________________________
// Función: IniciarSesion
//
//	 Descripción:
//		Inicia sesión en el Sistema Operativo de una de las particiones
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//_____________________________________________________________________________________________________
BOOLEAN IniciarSesion(TRAMA* ptrTrama)
{
	char *nfn,*ids,*disk,*par,msglog[LONSTD];
	char modulo[] = "IniciarSesion()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	nfn=copiaParametro("nfn",ptrTrama);
	ids=copiaParametro("ids",ptrTrama);
	disk=copiaParametro("dsk",ptrTrama);
	par=copiaParametro("par",ptrTrama);
	
	initParametros(ptrTrama,0);
	sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_IniciarSesion");
	respuestaEjecucionComando(ptrTrama,0,ids);
	liberaMemoria(ids);			

	sprintf(interface,"%s/%s",pathinterface,nfn);
	sprintf(parametros,"%s %s %s",nfn,disk,par);
	liberaMemoria(par);			
	
	herror=interfaceAdmin(interface,parametros,NULL);

	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		liberaMemoria(nfn);
		errorInfo(modulo,msglog);
		return(FALSE);
	}
	liberaMemoria(nfn);
	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: CrearImagen
//
//	 Descripción:
//		Crea una imagen de una partición
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN CrearImagen(TRAMA* ptrTrama)
{
	int lon;
	char *nfn,*dsk,*par,*cpt,*idi,*ipr,*nci,*ids,msglog[LONSTD];
	char modulo[] = "CrearImagen()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}

	dsk=copiaParametro("dsk",ptrTrama); // Disco
	par=copiaParametro("par",ptrTrama); // Número de partición
	cpt=copiaParametro("cpt",ptrTrama); // Código de la partición
	idi=copiaParametro("idi",ptrTrama); // Identificador de la imagen
	nci=copiaParametro("nci",ptrTrama); // Nombre canónico de la imagen
	ipr=copiaParametro("ipr",ptrTrama); // Ip del repositorio

	nfn=copiaParametro("nfn",ptrTrama);
	ids=copiaParametro("ids",ptrTrama);
	muestraMensaje(7,NULL);

	if(InventariandoSoftware(ptrTrama,FALSE,"InventarioSoftware")){ // Crea inventario Software previamente
		muestraMensaje(2,NULL);
		sprintf(interface,"%s/%s",pathinterface,nfn);
		sprintf(parametros,"%s %s %s %s %s",nfn,dsk,par,nci,ipr);
		herror=interfaceAdmin(interface,parametros,NULL);
		if(herror){
			sprintf(msglog,"%s:%s",tbErrores[86],nfn);
			errorInfo(modulo,msglog);
			muestraMensaje(10,NULL);
		}
		else
			muestraMensaje(9,NULL);
	}
	else{
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
	}

	/* Envia respuesta de ejecución de la función de interface */
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_CrearImagen");
	lon+=sprintf(ptrTrama->parametros+lon,"idi=%s\r",idi); // Identificador de la imagen
	lon+=sprintf(ptrTrama->parametros+lon,"dsk=%s\r",dsk); // Número de disco
	lon+=sprintf(ptrTrama->parametros+lon,"par=%s\r",par); // Número de partición de donde se creó
	lon+=sprintf(ptrTrama->parametros+lon,"cpt=%s\r",cpt); // Tipo o código de partición
	lon+=sprintf(ptrTrama->parametros+lon,"ipr=%s\r",ipr); // Ip del repositorio donde se alojó
	respuestaEjecucionComando(ptrTrama,herror,ids);
	
	liberaMemoria(dsk);	
	liberaMemoria(par);	
	liberaMemoria(cpt);	
	liberaMemoria(idi);	
	liberaMemoria(nci);	
	liberaMemoria(ipr);	
	liberaMemoria(nfn);	
	liberaMemoria(ids);	

	muestraMenu();
	
	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: CrearImagenBasica
//
//	 Descripción:
//		Crea una imagen básica a travers dela sincronización
//	Parámetros:
//		ptrTrama: contenido del mensaje
//
//	FDevuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN CrearImagenBasica(TRAMA* ptrTrama)
{
	int lon;
	char *nfn,*dsk,*par,*cpt,*idi,*nci,*rti,*ipr,*msy,*whl,*eli,*cmp,*bpi,*cpc,*bpc,*nba,*ids,msglog[LONSTD];
	char modulo[] = "CrearImagenBasica()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	nfn=copiaParametro("nfn",ptrTrama);
	dsk=copiaParametro("dsk",ptrTrama); // Disco
	par=copiaParametro("par",ptrTrama); // Número de partición
	cpt=copiaParametro("cpt",ptrTrama); // Tipo de partición
	idi=copiaParametro("idi",ptrTrama); // Identificador de la imagen
	nci=copiaParametro("nci",ptrTrama); // Nombre canónico de la imagen
	rti=copiaParametro("rti",ptrTrama); // Ruta de origen de la imagen
	ipr=copiaParametro("ipr",ptrTrama); // Ip del repositorio

	msy=copiaParametro("msy",ptrTrama); // Método de sincronización
	
	whl=copiaParametro("whl",ptrTrama); // Envío del fichero completo si hay diferencias		
	eli=copiaParametro("eli",ptrTrama); // Elimiar archivos en destino que no estén en origen	
	cmp=copiaParametro("cmp",ptrTrama); // Comprimir antes de enviar

	bpi=copiaParametro("bpi",ptrTrama); // Borrar la imagen antes de crearla
	cpc=copiaParametro("cpc",ptrTrama); // Copiar también imagen a la cache
	bpc=copiaParametro("bpc",ptrTrama); // Borrarla de la cache antes de copiarla en ella
	nba=copiaParametro("nba",ptrTrama); // No borrar archivos en destino

	muestraMensaje(7,NULL); // Creando Inventario Software
	if(InventariandoSoftware(ptrTrama,FALSE,"InventarioSoftware")){ // Crea inventario Software previamente
		muestraMensaje(30,NULL);// Creando Imagen Básica, por favor espere...
		sprintf(interface,"%s/%s",pathinterface,nfn);
		sprintf(parametros,"%s %s %s %s %s %s%s%s %s%s%s%s %s %s",nfn,dsk,par,nci,ipr,whl,eli,cmp,bpi,cpc,bpc,nba,msy,rti);
		herror=interfaceAdmin(interface,parametros,NULL);
		if(herror){
			sprintf(msglog,"%s:%s",tbErrores[86],nfn);
			errorInfo(modulo,msglog);
			muestraMensaje(29,NULL);// Ha habido algún error en el proceso de creación de imagen básica
		}
		else
			muestraMensaje(28,NULL);// El proceso de creación de imagen básica ha terminado correctamente
	}
	else{
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
	}

	ids=copiaParametro("ids",ptrTrama); // Identificador de la sesión

	/* Envia respuesta de ejecución de la función de interface */
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_CrearImagenBasica");
	lon+=sprintf(ptrTrama->parametros+lon,"idi=%s\r",idi); // Identificador de la imagen
	lon+=sprintf(ptrTrama->parametros+lon,"dsk=%s\r",dsk); // Número de disco
	lon+=sprintf(ptrTrama->parametros+lon,"par=%s\r",par); // Número de partición de donde se creó
	lon+=sprintf(ptrTrama->parametros+lon,"cpt=%s\r",cpt); // Tipo o código de partición
	lon+=sprintf(ptrTrama->parametros+lon,"ipr=%s\r",ipr); // Ip del repositorio donde se alojó
	respuestaEjecucionComando(ptrTrama,herror,ids);
		
	liberaMemoria(nfn);	
	liberaMemoria(dsk);	
	liberaMemoria(par);	
	liberaMemoria(cpt);	
	liberaMemoria(idi);	
	liberaMemoria(nci);	
	liberaMemoria(rti);	
	liberaMemoria(ipr);	

	liberaMemoria(msy);	
	
	liberaMemoria(whl);	
	liberaMemoria(eli);	
	liberaMemoria(cmp);	

	liberaMemoria(bpi);	
	liberaMemoria(cpc);	
	liberaMemoria(bpc);	
	liberaMemoria(nba);
	liberaMemoria(ids);		
	
	muestraMenu();

	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: CrearSoftIncremental
//
//	 Descripción:
//		Crea una software incremental comparando una partición con una imagen básica
//	Parámetros:
//		ptrTrama: contenido del mensaje
//
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN CrearSoftIncremental(TRAMA* ptrTrama)
{
	int lon;
	char *nfn,*dsk,*par,*idi,*idf,*ipr,*nci,*rti,*ncf,*msy,*whl,*eli,*cmp,*bpi,*cpc,*bpc,*nba,*ids,msglog[LONSTD];
	char modulo[] = "CrearSoftIncremental()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	nfn=copiaParametro("nfn",ptrTrama);

	dsk=copiaParametro("dsk",ptrTrama); // Disco
	par=copiaParametro("par",ptrTrama); // Número de partición
	idi=copiaParametro("idi",ptrTrama); // Identificador de la imagen
	nci=copiaParametro("nci",ptrTrama); // Nombre canónico de la imagen
	rti=copiaParametro("rti",ptrTrama); // Ruta de origen de la imagen
	ipr=copiaParametro("ipr",ptrTrama); // Ip del repositorio
	idf=copiaParametro("idf",ptrTrama); // Identificador de la imagen diferencial
	ncf=copiaParametro("ncf",ptrTrama); // Nombre canónico de la imagen diferencial

	msy=copiaParametro("msy",ptrTrama); // Método de sincronización

	whl=copiaParametro("whl",ptrTrama); // Envío del fichero completo si hay diferencias	
	eli=copiaParametro("eli",ptrTrama); // Elimiar archivos en destino que no estén en origen	
	cmp=copiaParametro("cmp",ptrTrama); // Comprimir antes de enviar

	bpi=copiaParametro("bpi",ptrTrama); // Borrar la imagen antes de crearla
	cpc=copiaParametro("cpc",ptrTrama); // Copiar también imagen a la cache
	bpc=copiaParametro("bpc",ptrTrama); // Borrarla de la cache antes de copiarla en ella
	nba=copiaParametro("nba",ptrTrama); // No borrar archivos en destino

	muestraMensaje(7,NULL); // Creando Inventario Software
	if(InventariandoSoftware(ptrTrama,FALSE,"InventarioSoftware")){ // Crea inventario Software previamente
		muestraMensaje(25,NULL);// Creando Imagen Incremental, por favor espere...
		sprintf(interface,"%s/%s",pathinterface,nfn);
		sprintf(parametros,"%s %s %s %s %s %s %s%s%s %s%s%s%s %s %s",nfn,dsk,par,nci,ipr,ncf,whl,eli,cmp,bpi,cpc,bpc,nba,msy,rti);

		herror=interfaceAdmin(interface,parametros,NULL);
		if(herror){
			sprintf(msglog,"%s:%s",tbErrores[86],nfn);
			errorInfo(modulo,msglog);
			muestraMensaje(27,NULL);// Ha habido algún error en el proceso de creación de imagen básica
		}
		else
			muestraMensaje(26,NULL);// El proceso de creación de imagen incremental ha terminado correctamente
	}
	else{
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
	}

	ids=copiaParametro("ids",ptrTrama); // Identificador de la sesión

	/* Envia respuesta de ejecución de la función de interface */
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_CrearSoftIncremental");
	lon+=sprintf(ptrTrama->parametros+lon,"idf=%s\r",idf); // Identificador de la imagen incremental
	lon+=sprintf(ptrTrama->parametros+lon,"dsk=%s\r",dsk); // Número de disco
	lon+=sprintf(ptrTrama->parametros+lon,"par=%s\r",par); // Número de partición
	respuestaEjecucionComando(ptrTrama,herror,ids);
	
	liberaMemoria(nfn);	
	liberaMemoria(dsk);	
	liberaMemoria(par);	
	liberaMemoria(idi);	
	liberaMemoria(nci);	
	liberaMemoria(rti);	
	liberaMemoria(ipr);	
	liberaMemoria(idf);	
	liberaMemoria(ncf);	
	liberaMemoria(msy);	
	liberaMemoria(whl);
	liberaMemoria(eli);
	liberaMemoria(cmp);
	liberaMemoria(bpi);	
	liberaMemoria(cpc);	
	liberaMemoria(bpc);	
	liberaMemoria(nba);
	liberaMemoria(ids);		
	
	muestraMenu();

	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: RestaurarImagen
//
//	 Descripción:
//		Restaura una imagen en una partición
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En bpccaso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN RestaurarImagen(TRAMA* ptrTrama)
{
	int lon;
	char *nfn,*dsk,*par,*idi,*ipr,*ifs,*cfg,*nci,*ids,*ptc,msglog[LONSTD];
	char modulo[] = "RestaurarImagen()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}

	dsk=copiaParametro("dsk",ptrTrama);
	par=copiaParametro("par",ptrTrama);
	idi=copiaParametro("idi",ptrTrama);
	ipr=copiaParametro("ipr",ptrTrama);
	nci=copiaParametro("nci",ptrTrama);
	ifs=copiaParametro("ifs",ptrTrama);
	ptc=copiaParametro("ptc",ptrTrama);

	nfn=copiaParametro("nfn",ptrTrama);
	ids=copiaParametro("ids",ptrTrama);
	muestraMensaje(3,NULL);
	sprintf(interface,"%s/%s",pathinterface,nfn);
	sprintf(parametros,"%s %s %s %s %s %s",nfn,dsk,par,nci,ipr,ptc);
	herror=interfaceAdmin(interface,parametros,NULL);
	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
		muestraMensaje(12,NULL);
	}
	else
		muestraMensaje(11,NULL);

	/* Obtener nueva configuración */
	cfg=LeeConfiguracion();
	if(!cfg){ // No se puede recuperar la configuración del cliente
		errorLog(modulo,36,FALSE);
	}

	/* Envia respuesta de ejecución de la función de interface */
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_RestaurarImagen");
	lon+=sprintf(ptrTrama->parametros+lon,"idi=%s\r",idi); // Identificador de la imagen
	lon+=sprintf(ptrTrama->parametros+lon,"dsk=%s\r",dsk); // Número de disco
	lon+=sprintf(ptrTrama->parametros+lon,"par=%s\r",par); // Número de partición
	lon+=sprintf(ptrTrama->parametros+lon,"ifs=%s\r",ifs); // Identificador del perfil software
	lon+=sprintf(ptrTrama->parametros+lon,"cfg=%s\r",cfg); // Configuración de discos
	respuestaEjecucionComando(ptrTrama,herror,ids);
	
	liberaMemoria(nfn);	
	liberaMemoria(dsk);	
	liberaMemoria(par);	
	liberaMemoria(idi);
	liberaMemoria(nci);	
	liberaMemoria(ipr);	
	liberaMemoria(ifs);	
	liberaMemoria(cfg);	
	liberaMemoria(ptc);	
	liberaMemoria(ids);			

	muestraMenu();

	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: RestaurarImagenBasica
//
//	 Descripción:
//		Restaura una imagen básica en una partición
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN RestaurarImagenBasica(TRAMA* ptrTrama)
{
	int lon;
	char *nfn,*dsk,*par,*idi,*ipr,*met,*nci,*rti,*ifs,*cfg,*msy,*whl,*eli,*cmp,*tpt,*bpi,*cpc,*bpc,*nba,*ids,msglog[LONSTD];
	char modulo[] = "RestaurarImagenBasica()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	dsk=copiaParametro("dsk",ptrTrama);
	par=copiaParametro("par",ptrTrama);
	idi=copiaParametro("idi",ptrTrama);
	ipr=copiaParametro("ipr",ptrTrama);
	met=copiaParametro("met",ptrTrama); // Método de clonación 0= desde caché 1= desde repositorio
	nci=copiaParametro("nci",ptrTrama);
	rti=copiaParametro("rti",ptrTrama); // Ruta de origen de la imagen
	ifs=copiaParametro("ifs",ptrTrama);

	tpt=copiaParametro("tpt",ptrTrama); // Tipo de trasnmisión unicast o multicast	
	msy=copiaParametro("msy",ptrTrama); // Metodo de sincronizacion 

	whl=copiaParametro("whl",ptrTrama); // Envío del fichero completo si hay diferencias	
	eli=copiaParametro("eli",ptrTrama); // Elimiar archivos en destino que no estén en origen	
	cmp=copiaParametro("cmp",ptrTrama); // Comprimir antes de enviar

	bpi=copiaParametro("bpi",ptrTrama); // Borrar la imagen antes de crearla
	cpc=copiaParametro("cpc",ptrTrama); // Copiar también imagen a la cache
	bpc=copiaParametro("bpc",ptrTrama); // Borrarla de la cache antes de copiarla en ella
	nba=copiaParametro("nba",ptrTrama); // No borrar archivos en destino

	nfn=copiaParametro("nfn",ptrTrama);
	ids=copiaParametro("ids",ptrTrama);
	muestraMensaje(31,NULL);
	sprintf(interface,"%s/%s",pathinterface,nfn);
	sprintf(parametros,"%s %s %s %s %s %s %s%s%s %s%s%s%s %s %s %s",nfn,dsk,par,nci,ipr,tpt,whl,eli,cmp,bpi,cpc,bpc,nba,met,msy,rti);
	herror=interfaceAdmin(interface,parametros,NULL);
	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
		muestraMensaje(33,NULL);
	}
	else
		muestraMensaje(32,NULL);

	/* Obtener nueva configuración */
	cfg=LeeConfiguracion();
	if(!cfg){ // No se puede recuperar la configuración del cliente
		errorLog(modulo,36,FALSE);
	}

	/* Envia respuesta de ejecución de la función de interface */
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_RestaurarImagenBasica");
	lon+=sprintf(ptrTrama->parametros+lon,"idi=%s\r",idi); // Identificador de la imagen
	lon+=sprintf(ptrTrama->parametros+lon,"dsk=%s\r",dsk); // Número de disco
	lon+=sprintf(ptrTrama->parametros+lon,"par=%s\r",par); // Número de partición
	lon+=sprintf(ptrTrama->parametros+lon,"ifs=%s\r",ifs); // Identificador del perfil software
	lon+=sprintf(ptrTrama->parametros+lon,"cfg=%s\r",cfg); // Configuración de discos
	respuestaEjecucionComando(ptrTrama,herror,ids);

	liberaMemoria(nfn);
	liberaMemoria(dsk);
	liberaMemoria(par);
	liberaMemoria(idi);
	liberaMemoria(nci);
	liberaMemoria(rti);
	liberaMemoria(ifs);
	liberaMemoria(cfg);
	liberaMemoria(ipr);
	liberaMemoria(met);

	liberaMemoria(tpt);
	liberaMemoria(msy);

	liberaMemoria(whl);
	liberaMemoria(eli);
	liberaMemoria(cmp);

	liberaMemoria(bpi);
	liberaMemoria(cpc);
	liberaMemoria(bpc);
	liberaMemoria(nba);
	liberaMemoria(ids);

	muestraMenu();

	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: RestaurarSoftIncremental
//
//	 Descripción:
//		Restaura software incremental en una partición
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN RestaurarSoftIncremental(TRAMA* ptrTrama)
{
	int lon;
	char *nfn,*dsk,*par,*idi,*ipr,*met,*ifs,*nci,*rti,*idf,*ncf,*msy,*whl,*eli,*cmp,*tpt,*bpi,*cpc,*bpc,*nba,*ids,msglog[LONSTD];
	char modulo[] = "RestaurarSoftIncremental()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	dsk=copiaParametro("dsk",ptrTrama);
	par=copiaParametro("par",ptrTrama);
	idi=copiaParametro("idi",ptrTrama);
	idf=copiaParametro("idf",ptrTrama);
	ipr=copiaParametro("ipr",ptrTrama);
	met=copiaParametro("met",ptrTrama); // Método de clonación 0= desde caché 1= desde repositorio
	ifs=copiaParametro("ifs",ptrTrama);
	nci=copiaParametro("nci",ptrTrama);
	rti=copiaParametro("rti",ptrTrama); // Ruta de origen de la imagen
	ncf=copiaParametro("ncf",ptrTrama);

	tpt=copiaParametro("tpt",ptrTrama); // Tipo de trasnmisión unicast o multicast	
	msy=copiaParametro("msy",ptrTrama); // Metodo de sincronizacion 

	whl=copiaParametro("whl",ptrTrama); // Envío del fichero completo si hay diferencias	
	eli=copiaParametro("eli",ptrTrama); // Elimiar archivos en destino que no estén en origen	
	cmp=copiaParametro("cmp",ptrTrama); // Comprimir antes de enviar

	bpi=copiaParametro("bpi",ptrTrama); // Borrar la imagen antes de crearla
	cpc=copiaParametro("cpc",ptrTrama); // Copiar también imagen a la cache
	bpc=copiaParametro("bpc",ptrTrama); // Borrarla de la cache antes de copiarla en ella
	nba=copiaParametro("nba",ptrTrama); // No borrar archivos en destino

	nfn=copiaParametro("nfn",ptrTrama);
	ids=copiaParametro("ids",ptrTrama);
	muestraMensaje(31,NULL);
	sprintf(interface,"%s/%s",pathinterface,nfn);
	sprintf(parametros,"%s %s %s %s %s %s %s %s%s%s %s%s%s%s %s %s %s",nfn,dsk,par,nci,ipr,ncf,tpt,whl,eli,cmp,bpi,cpc,bpc,nba,met,msy,rti);
	herror=interfaceAdmin(interface,parametros,NULL);
	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
		muestraMensaje(35,NULL);
	}
	else
		muestraMensaje(34,NULL);

	/* Envia respuesta de ejecución de la función de interface */
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_RestaurarSoftIncremental");
	lon+=sprintf(ptrTrama->parametros+lon,"idi=%s\r",idf); // Identificador de la imagen incremental (Forzada a idi)
	lon+=sprintf(ptrTrama->parametros+lon,"dsk=%s\r",dsk); // Número de disco
	lon+=sprintf(ptrTrama->parametros+lon,"par=%s\r",par); // Número de partición
	lon+=sprintf(ptrTrama->parametros+lon,"ifs=%s\r",ifs); // Identificador del perfil software

	respuestaEjecucionComando(ptrTrama,herror,ids);
	
	liberaMemoria(nfn);	
	liberaMemoria(dsk);	
	liberaMemoria(par);	
	liberaMemoria(idi);	
	liberaMemoria(idf);	
	liberaMemoria(nci);	
	liberaMemoria(rti);	
	liberaMemoria(ncf);	
	liberaMemoria(ifs);	
	liberaMemoria(ipr);	
	liberaMemoria(met);

	liberaMemoria(tpt);	
	liberaMemoria(msy);	

	liberaMemoria(whl);	
	liberaMemoria(eli);	
	liberaMemoria(cmp);	

	liberaMemoria(bpi);	
	liberaMemoria(cpc);	
	liberaMemoria(bpc);	
	liberaMemoria(nba);
	liberaMemoria(ids);		
	
	muestraMenu();

	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: Configurar
//
//	 Descripción:
//		Configura la tabla de particiones y formatea
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN Configurar(TRAMA* ptrTrama)
{
	int lon;
	char *nfn,*dsk,*cfg,*ids,msglog[LONSTD];
	char modulo[] = "Configurar()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}

	dsk=copiaParametro("dsk",ptrTrama);
	cfg=copiaParametro("cfg",ptrTrama);
	/* Sustituir caracteres */
	sustituir(cfg,'\n','$');
	sustituir(cfg,'\t','#');

	nfn=copiaParametro("nfn",ptrTrama);
	ids=copiaParametro("ids",ptrTrama);
	muestraMensaje(4,NULL);
	sprintf(interface,"%s/%s",pathinterface,nfn);
	sprintf(parametros,"%s %s %s",nfn,dsk,cfg);

	herror=interfaceAdmin(interface,parametros,NULL);
	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
		muestraMensaje(13,NULL);
	}
	else
		muestraMensaje(14,NULL);

	cfg=LeeConfiguracion();
	if(!cfg){ // No se puede recuperar la configuración del cliente
		errorLog(modulo,36,FALSE);
		return(FALSE);
	}

	/* Envia respuesta de ejecución del comando*/
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_Configurar");
	lon+=sprintf(ptrTrama->parametros+lon,"cfg=%s\r",cfg); // Configuración de los Sistemas Operativos del cliente
	respuestaEjecucionComando(ptrTrama,herror,ids);
	
	liberaMemoria(dsk);
	liberaMemoria(cfg);
	liberaMemoria(nfn);
	liberaMemoria(ids);

	muestraMenu();

	return(TRUE);
}
// ________________________________________________________________________________________________________
// Función: InventarioHardware
//
//	Descripción:
//		Envia al servidor el nombre del archivo de inventario de su hardware para posteriormente
//		esperar que éste lo solicite y enviarlo por la red.
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN InventarioHardware(TRAMA* ptrTrama)
{
	int lon;
	SOCKET socket_c;
	char *nfn,*ids,msglog[LONSTD],hrdsrc[LONPRM],hrddst[LONPRM];
	char modulo[] = "InventarioHardware()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}

	nfn=copiaParametro("nfn",ptrTrama);
	ids=copiaParametro("ids",ptrTrama);
	muestraMensaje(6,NULL);

	sprintf(interface,"%s/%s",pathinterface,nfn);
	sprintf(hrdsrc,"/tmp/Chrd-%s",IPlocal); // Nombre que tendra el archivo de inventario
	sprintf(parametros,"%s %s",nfn,hrdsrc);
	herror=interfaceAdmin(interface,parametros,NULL);
	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
		muestraMensaje(18,NULL);
	}
	else{
		/* Envía fichero de inventario al servidor */
		sprintf(hrddst,"/tmp/Shrd-%s",IPlocal); // Nombre que tendra el archivo en el Servidor
		initParametros(ptrTrama,0);
		sprintf(ptrTrama->parametros,"nfn=recibeArchivo\rnfl=%s\r",hrddst);
		if(!enviaMensajeServidor(&socket_c,ptrTrama,MSG_COMANDO)){
			liberaMemoria(nfn);
			liberaMemoria(ids);
			errorLog(modulo,42,FALSE);
			return(FALSE);
		}
		 /* Espera señal para comenzar el envío */
		liberaMemoria(ptrTrama);
		recibeFlag(&socket_c,ptrTrama);
		/* Envía archivo */
		if(!sendArchivo(&socket_c,hrdsrc)){
			errorLog(modulo,57, FALSE);
			herror=12; // Error de envío de fichero por la red
		}
		close(socket_c);
		muestraMensaje(17,NULL);
	}

	/* Envia respuesta de ejecución de la función de interface */
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_InventarioHardware");
	lon+=sprintf(ptrTrama->parametros+lon,"hrd=%s\r",hrddst);
	respuestaEjecucionComando(ptrTrama,herror,ids);
	liberaMemoria(nfn);
	liberaMemoria(ids);
	
	muestraMenu();

	return(TRUE);
}
// ________________________________________________________________________________________________________
// Función: InventarioSoftware
//
//	Descripción:
//		Crea el inventario software de un sistema operativo instalado en una partición.
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN InventarioSoftware(TRAMA* ptrTrama)
{
	char *nfn,*ids,msglog[LONSTD];
	char modulo[] = "InventarioSoftware()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	nfn=copiaParametro("nfn",ptrTrama);
	ids=copiaParametro("ids",ptrTrama);
	muestraMensaje(7,NULL);
	InventariandoSoftware(ptrTrama,TRUE,nfn);
	respuestaEjecucionComando(ptrTrama,herror,ids);
	liberaMemoria(nfn);
	liberaMemoria(ids);		
	muestraMenu();
	return(TRUE);
}
// ________________________________________________________________________________________________________
//
// Función: InventariandoSoftware
//
//	Descripción:
//		Envia al servidor el nombre del archivo de inventario de su software para posteriormente
//		esperar que éste lo solicite y enviarlo por la red.
//	Parámetros:
//		ptrTrama: contenido del mensaje
//		sw: switch que indica si la función es llamada por el comando InventarioSoftware(true) o CrearImagen(false)
//		nfn: Nombre de la función del Interface que implementa el comando
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN InventariandoSoftware(TRAMA* ptrTrama,BOOLEAN sw,char *nfn)
{
	int lon;
	SOCKET socket_c;
	char *dsk,*par,msglog[LONSTD],sftsrc[LONPRM],sftdst[LONPRM];
	char modulo[] = "InventariandoSoftware()";

	dsk=copiaParametro("dsk",ptrTrama); // Disco
	par=copiaParametro("par",ptrTrama);

	sprintf(interface,"%s/%s",pathinterface,nfn);
	sprintf(sftsrc,"/tmp/CSft-%s-%s",IPlocal,par); // Nombre que tendra el archivo de inventario
	sprintf(parametros,"%s %s %s %s",nfn,dsk,par,sftsrc);

	herror=interfaceAdmin(interface,parametros,NULL);
	herror=0;
	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
		muestraMensaje(20,NULL);
	}
	else{
		/* Envía fichero de inventario al servidor */
		sprintf(sftdst,"/tmp/Ssft-%s-%s",IPlocal,par); // Nombre que tendra el archivo en el Servidor
		initParametros(ptrTrama,0);

		sprintf(ptrTrama->parametros,"nfn=recibeArchivo\rnfl=%s\r",sftdst);
		if(!enviaMensajeServidor(&socket_c,ptrTrama,MSG_COMANDO)){
			errorLog(modulo,42,FALSE);
			liberaMemoria(dsk);
			liberaMemoria(par);				
			return(FALSE);
		}
		/* Espera señal para comenzar el envío */
		liberaMemoria(ptrTrama);
		if(!recibeFlag(&socket_c,ptrTrama)){
			errorLog(modulo,17,FALSE);
		}
		/* Envía archivo */
		if(!sendArchivo(&socket_c,sftsrc)){
			errorLog(modulo,57, FALSE);
			herror=12; // Error de envío de fichero por la red
		}
		close(socket_c);
		muestraMensaje(19,NULL);
	}
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_InventarioSoftware");
	lon+=sprintf(ptrTrama->parametros+lon,"par=%s\r",par);
	lon+=sprintf(ptrTrama->parametros+lon,"sft=%s\r",sftdst);
	if(!sw)
		respuestaEjecucionComando(ptrTrama,herror,"0");

	liberaMemoria(dsk);
	liberaMemoria(par);			
	return(TRUE);
}
// ________________________________________________________________________________________________________
// Función: EjecutarScript
//
//	Descripción:
//		Ejecuta código de script
//	Parámetros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN EjecutarScript(TRAMA* ptrTrama)
{
	int lon;
	char *nfn,*aux,*ids,*scp,*cfg,msglog[LONSTD];
	char modulo[] = "EjecutarScript()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	aux=copiaParametro("scp",ptrTrama);
	scp=URLDecode(aux);


	muestraMensaje(8,NULL);
	/* Nombre del archivo de script */
	char filescript[LONPRM];
	sprintf(filescript,"/tmp/_script_%s",IPlocal);
	escribeArchivo(filescript,scp);
	nfn=copiaParametro("nfn",ptrTrama);
	sprintf(interface,"%s/%s",pathinterface,nfn);
	sprintf(parametros,"%s %s",nfn,filescript);
	herror=interfaceAdmin(interface,parametros,NULL);
	if(herror){
		sprintf(msglog,"%s:%s",tbErrores[86],nfn);
		errorInfo(modulo,msglog);
		muestraMensaje(21,NULL);
	}
	else
		muestraMensaje(22,NULL);

	// Toma configuración de particiones
	cfg=LeeConfiguracion();
	if(!cfg){ // No se puede recuperar la configuración del cliente
		errorLog(modulo,36,FALSE);
		herror=36;
	}

	ids=copiaParametro("ids",ptrTrama);

	//herror=ejecutarCodigoBash(scp);
	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_EjecutarScript");
	lon+=sprintf(ptrTrama->parametros+lon,"cfg=%s\r",cfg); // Configuración de los Sistemas Operativos del cliente
	respuestaEjecucionComando(ptrTrama,herror,ids);
	
	liberaMemoria(nfn);
	liberaMemoria(ids);		
	liberaMemoria(aux);		
	liberaMemoria(scp);	
	liberaMemoria(cfg);

	muestraMenu();

	return(TRUE);
}
//______________________________________________________________________________________________________
// Función: respuestaEjecucionComando
//
//	Descripción:
// 		Envia una respuesta a una ejecucion de comando al servidor de Administración
//	Parámetros:
//		- ptrTrama: contenido del mensaje
//		- res: Resultado de la ejecución (Código de error devuelto por el script ejecutado)
//		- ids: Identificador de la sesion (En caso de no haber seguimiento es NULO)
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
BOOLEAN respuestaEjecucionComando(TRAMA* ptrTrama,int res,char *ids)
{
	int lon;
	SOCKET socket_c;
	char modulo[] = "respuestaEjecucionComando()";

	lon=strlen(ptrTrama->parametros);
	if(ids){ // Existe seguimiento
		lon+=sprintf(ptrTrama->parametros+lon,"ids=%s\r",ids); // Añade identificador de la sesión
	}
	if (res==0){ // Resultado satisfactorio
		lon+=sprintf(ptrTrama->parametros+lon,"res=%s\r","1");
		lon+=sprintf(ptrTrama->parametros+lon,"der=%s\r","");
	}
	else{ // Algún error
		lon+=sprintf(ptrTrama->parametros+lon,"res=%s\r","2");
		if(res>MAXERRORSCRIPT)
			lon+=sprintf(ptrTrama->parametros+lon,"der=%s (Error de script:%d)\r",tbErroresScripts[0],res);// Descripción del error
		else
			lon+=sprintf(ptrTrama->parametros+lon,"der=%s\r",tbErroresScripts[res]);// Descripción del error
	}
	if(!(enviaMensajeServidor(&socket_c,ptrTrama,MSG_NOTIFICACION))){
		errorLog(modulo,44,FALSE);
		return(FALSE);
	}

	close(socket_c);
	return(TRUE);
}
// ________________________________________________________________________________________________________
// Función: gestionaTrama
//
//	Descripción:
//		Procesa las tramas recibidas.
//	Parametros:
//		ptrTrama: contenido del mensaje
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
BOOLEAN gestionaTrama(TRAMA *ptrTrama)
{
	int i, res;
	char *nfn;
	char modulo[] = "gestionaTrama()";

	INTROaFINCAD(ptrTrama);
	nfn = copiaParametro("nfn", ptrTrama); // Toma nombre de función
	for (i = 0; i < MAXIMAS_FUNCIONES; i++) { // Recorre funciones que procesan las tramas
		res = strcmp(tbfuncionesClient[i].nf, nfn);
		if (res == 0) { // Encontrada la función que procesa el mensaje
			liberaMemoria(nfn);
			return(tbfuncionesClient[i].fptr(ptrTrama)); // Invoca la función
		}
	}

	liberaMemoria(nfn);

	/* Sólo puede ser un comando personalizado
	if (ptrTrama->tipo==MSG_COMANDO)
		return(Comando(ptrTrama));
	 */
	errorLog(modulo, 18, FALSE);
	return (FALSE);
}
//________________________________________________________________________________________________________
//	Función: ejecutaArchivo
//
//	Descripción:
//		Ejecuta los comando contenido en un archivo (cada comando y sus parametros separados por un
//		salto de linea.
//	Parámetros:
//		filecmd: Nombre del archivo de comandos
//		ptrTrama: Puntero a una estructura TRAMA usada en las comunicaciones por red (No debe ser NULL)
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//________________________________________________________________________________________________________
BOOLEAN ejecutaArchivo(char* filecmd,TRAMA *ptrTrama)
{
	char* buffer,*lineas[MAXIMAS_LINEAS];
	int i,numlin;
	char modulo[] = "ejecutaArchivo()";

	buffer=leeArchivo(filecmd);
	if(buffer){
		numlin = splitCadena(lineas, buffer, '@');
		initParametros(ptrTrama,0);
		for (i = 0; i < numlin; i++) {
			if(strlen(lineas[i])>0){
				strcpy(ptrTrama->parametros,lineas[i]);
				//strcat(ptrTrama->parametros,"\rMCDJ@");	// Fin de trama
				if(!gestionaTrama(ptrTrama)){	// Análisis de la trama
					errorLog(modulo,39,FALSE);
					//return(FALSE);
				}
			}
		}
	}
	liberaMemoria(buffer);
	return(TRUE);
}

BOOLEAN EjecutaComandosPendientes(TRAMA* ptrTrama)
{
	return(TRUE);
}

//______________________________________________________________________________________________________
// Función: enviaMensajeServidor
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
BOOLEAN enviaMensajeServidor(SOCKET *socket_c,TRAMA *ptrTrama,char tipo)
{
	int lon;
	char modulo[] = "enviaMensajeServidor()";

	*socket_c=abreConexion();
	if(*socket_c==INVALID_SOCKET){
		errorLog(modulo,38,FALSE); // Error de conexión con el servidor
		return(FALSE);
	}
	ptrTrama->arroba='@'; // Cabecera de la trama
	strncpy(ptrTrama->identificador,"JMMLCAMDJ_MCDJ",14);	// identificador de la trama
	ptrTrama->tipo=tipo; // Tipo de mensaje
	lon=strlen(ptrTrama->parametros); // Compone la trama
	lon+=sprintf(ptrTrama->parametros+lon,"iph=%s\r",IPlocal);	// Ip del ordenador
	lon+=sprintf(ptrTrama->parametros+lon,"ido=%s\r",idordenador);	// Identificador del ordenador
	lon+=sprintf(ptrTrama->parametros+lon,"npc=%s\r",nombreordenador);	// Nombre del ordenador
	lon+=sprintf(ptrTrama->parametros+lon,"idc=%s\r",idcentro);	// Identificador del centro
	lon+=sprintf(ptrTrama->parametros+lon,"ida=%s\r",idaula);	// Identificador del aula

	if (!mandaTrama(socket_c,ptrTrama)) {
		errorLog(modulo,26,FALSE);
		return (FALSE);
	}
	return(TRUE);
}
// ********************************************************************************************************
// PROGRAMA PRINCIPAL (CLIENTE)
// ********************************************************************************************************
int main(int argc, char *argv[])
{
	TRAMA *ptrTrama;
	char modulo[] = "main()";

	ptrTrama=(TRAMA *)reservaMemoria(sizeof(TRAMA));
	if (ptrTrama == NULL) { // No hay memoria suficiente para el bufer de las tramas
		errorLog(modulo, 3, FALSE);
		exit(EXIT_FAILURE);
	}
	/*--------------------------------------------------------------------------------------------------------
		Validación de parámetros de ejecución y fichero de configuración 
	 ---------------------------------------------------------------------------------------------------------*/
	if (!validacionParametros(argc, argv,3)) // Valida parámetros de ejecución
		exit(EXIT_FAILURE);

	if (!tomaConfiguracion(szPathFileCfg)) // Toma parametros de configuración
		exit(EXIT_FAILURE);
	/*--------------------------------------------------------------------------------------------------------
		Carga catálogo de funciones que procesan las tramas 
	 ---------------------------------------------------------------------------------------------------------*/
	int cf = 0;

	strcpy(tbfuncionesClient[cf].nf, "RESPUESTA_AutoexecCliente");
	tbfuncionesClient[cf++].fptr = &RESPUESTA_AutoexecCliente;

	strcpy(tbfuncionesClient[cf].nf, "RESPUESTA_InclusionCliente");
	tbfuncionesClient[cf++].fptr = &RESPUESTA_InclusionCliente;

	strcpy(tbfuncionesClient[cf].nf, "NoComandosPtes");
	tbfuncionesClient[cf++].fptr = &NoComandosPtes;

	strcpy(tbfuncionesClient[cf].nf, "Actualizar");
	tbfuncionesClient[cf++].fptr = &Actualizar;

	strcpy(tbfuncionesClient[cf].nf, "Purgar");
	tbfuncionesClient[cf++].fptr = &Purgar;

	strcpy(tbfuncionesClient[cf].nf, "ConsolaRemota");
	tbfuncionesClient[cf++].fptr = &ConsolaRemota;

	strcpy(tbfuncionesClient[cf].nf, "Sondeo");
	tbfuncionesClient[cf++].fptr = &Sondeo;

	strcpy(tbfuncionesClient[cf].nf, "Arrancar");
	tbfuncionesClient[cf++].fptr = &Arrancar;

	strcpy(tbfuncionesClient[cf].nf, "Apagar");
	tbfuncionesClient[cf++].fptr = &Apagar;

	strcpy(tbfuncionesClient[cf].nf, "Reiniciar");
	tbfuncionesClient[cf++].fptr = &Reiniciar;

	strcpy(tbfuncionesClient[cf].nf, "IniciarSesion");
	tbfuncionesClient[cf++].fptr = &IniciarSesion;

	strcpy(tbfuncionesClient[cf].nf, "CrearImagen");
	tbfuncionesClient[cf++].fptr = &CrearImagen;

	strcpy(tbfuncionesClient[cf].nf, "CrearImagenBasica");
	tbfuncionesClient[cf++].fptr = &CrearImagenBasica;

	strcpy(tbfuncionesClient[cf].nf, "CrearSoftIncremental");
	tbfuncionesClient[cf++].fptr = &CrearSoftIncremental;

	strcpy(tbfuncionesClient[cf].nf, "RestaurarImagen");
	tbfuncionesClient[cf++].fptr = &RestaurarImagen;

	strcpy(tbfuncionesClient[cf].nf, "RestaurarImagenBasica");
	tbfuncionesClient[cf++].fptr = &RestaurarImagenBasica;

	strcpy(tbfuncionesClient[cf].nf, "RestaurarSoftIncremental");
	tbfuncionesClient[cf++].fptr = &RestaurarSoftIncremental;


	strcpy(tbfuncionesClient[cf].nf, "Configurar");
	tbfuncionesClient[cf++].fptr = &Configurar;

	strcpy(tbfuncionesClient[cf].nf, "EjecutarScript");
	tbfuncionesClient[cf++].fptr = &EjecutarScript;

	strcpy(tbfuncionesClient[cf].nf, "InventarioHardware");
	tbfuncionesClient[cf++].fptr = &InventarioHardware;

	strcpy(tbfuncionesClient[cf].nf, "InventarioSoftware");
	tbfuncionesClient[cf++].fptr = &InventarioSoftware;

	strcpy(tbfuncionesClient[cf].nf, "EjecutaComandosPendientes");
	tbfuncionesClient[cf++].fptr = &EjecutaComandosPendientes;

	/*--------------------------------------------------------------------------------------------------------
		Toma dirección IP del cliente 	
	 ---------------------------------------------------------------------------------------------------------*/
	if(!tomaIPlocal()){ // Error al recuperar la IP local
		errorLog(modulo,0,FALSE);
		exit(EXIT_FAILURE);
	}
	/*--------------------------------------------------------------------------------------------------------
		Inicio de sesión
	 ---------------------------------------------------------------------------------------------------------*/
	infoLog(1); // Inicio de sesión
	infoLog(3); // Abriendo sesión en el servidor de Administración;		
	/*--------------------------------------------------------------------------------------------------------
		Inclusión del cliente en el sistema
	 ---------------------------------------------------------------------------------------------------------*/
	if(!inclusionCliente(ptrTrama)){	// Ha habido algún problema al abrir sesión
		errorLog(modulo,0,FALSE);
		exit(EXIT_FAILURE);
	}
	infoLog(4); // Cliente iniciado

	/*--------------------------------------------------------------------------------------------------------
		Procesamiento de la cache
	 ---------------------------------------------------------------------------------------------------------*/
	infoLog(23); // Abriendo sesión en el servidor de Administración;
	if(!cuestionCache(cache)){
		errorLog(modulo,0,FALSE);
		exit(EXIT_FAILURE);
	}
	/*--------------------------------------------------------------------------------------------------------
		Ejecución del autoexec
	 ---------------------------------------------------------------------------------------------------------*/
	if(atoi(idproautoexec)>0){  // Ejecución de procedimiento Autoexec
		infoLog(5);
		if(!autoexecCliente(ptrTrama)){  // Ejecución fichero autoexec
			errorLog(modulo,0,FALSE);
			exit(EXIT_FAILURE);
		}
	}
	/*--------------------------------------------------------------------------------------------------------
		Comandos pendientes
	 ---------------------------------------------------------------------------------------------------------*/
	infoLog(6); // Procesa comandos pendientes
	if(!comandosPendientes(ptrTrama)){  // Ejecución de acciones pendientes
		errorLog(modulo,0,FALSE);
		exit(EXIT_FAILURE);
	}
	infoLog(7); // Acciones pendientes procesadas
	/*--------------------------------------------------------------------------------------------------------
		Bucle de recepción de comandos
	 ---------------------------------------------------------------------------------------------------------*/
	muestraMenu();
	procesaComandos(ptrTrama); // Bucle para procesar comandos interactivos
	/*--------------------------------------------------------------------------------------------------------
		Fin de la sesión
	 ---------------------------------------------------------------------------------------------------------*/
	exit(EXIT_SUCCESS);
}
