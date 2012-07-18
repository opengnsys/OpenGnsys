// ********************************************************************************************************
// Cliernte: ogAdmLnxClient
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Febrero-2012
// Fecha Última modificación: Febrero-2012
// Nombre del fichero: ogAdmLnxClient.c
// Descripción :Este fichero implementa el cliente windows del sistema
// ********************************************************************************************************
#include "ogAdmLnxClient.h"
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
	IPlocal[0]=CHARNULL;

	numlin = splitCadena(lineas, buffer, '\n');
	for (i = 0; i < numlin; i++){
		splitCadena(dualparametro, lineas[i], '=');

		resul = strcmp(StrToUpper(dualparametro[0]), "SERVIDORADM");
		if (resul == 0)
			strcpy(servidoradm, dualparametro[1]);

		resul = strcmp(StrToUpper(dualparametro[0]), "PUERTO");
		if (resul == 0)
			strcpy(puerto, dualparametro[1]);

		resul = strcmp(StrToUpper(dualparametro[0]), "IPLOCAL");
		if (resul == 0)
			strcpy(IPlocal, dualparametro[1]);
	}

	if (servidoradm[0] == CHARNULL) {
		errorLog(modulo,4, FALSE); // Falta parámetro SERVIDORADM
		return (FALSE);
	}

	if (puerto[0] == CHARNULL) {
		errorLog(modulo,5, FALSE); // Falta parámetro PUERTO
		return (FALSE);
	}
	if (IPlocal[0] == CHARNULL) {
		errorLog(modulo, 92, FALSE); // Falta parámetro IPLOCAL
		return (FALSE);
	}
	return (TRUE);
}
//______________________________________________________________________________________________________
// Función: InclusionClienteWinLnx
//	 Descripción:
//		Abre una sesión en el servidor de administración y registra al cliente en el sistema
//	Parámetros:
//		Ninguno
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN InclusionClienteWinLnx(TRAMA* ptrTrama)
{
	int lon;
	SOCKET socket_c;
	char modulo[] = "InclusionClienteWinLnx()";

	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=InclusionClienteWinLnx\r"); // Nombre de la función a ejecutar en el servidor

	if(!enviaMensajeServidor(&socket_c,ptrTrama,MSG_PETICION)){
		errorLog(modulo,37,FALSE);
		return(FALSE);
	}
	ptrTrama=recibeMensaje(&socket_c);
	if(!ptrTrama){
		errorLog(modulo,22,FALSE);
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
// Función: RESPUESTA_InclusionClienteWinLnx
//
//	Descripción:
//  	Respuesta del servidor de administración a la petición de inicio
//		enviando los datos identificativos del cliente
//	Parámetros:
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
// 	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error
//______________________________________________________________________________________________________
BOOLEAN RESPUESTA_InclusionClienteWinLnx(TRAMA* ptrTrama)
{
	char* res;
	char modulo[] = "RESPUESTA_InclusionClienteWinLnx()";
	int err;
	
	res=copiaParametro("res",ptrTrama); // Resultado del proceso de inclusión
	err=(int)atoi(res); // Código de error devuelto por el servidor
	if(err>0){ // Error en el proceso de inclusión
		errorLog(modulo,41,FALSE);
		errorLog(modulo,err,FALSE);		
		return (FALSE);
	}
	strcpy(idordenador,copiaParametro("ido",ptrTrama)); // Identificador del ordenador
	strcpy(nombreordenador,copiaParametro("npc",ptrTrama));	//  Nombre del ordenador

	if(idordenador==NULL || nombreordenador==NULL){
		errorLog(modulo,40,FALSE);
		return (FALSE);
	}
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
		lon+=sprintf(ptrTrama->parametros+lon,"tpc=%s\r",CLIENTE_LNX); // Activar disponibilidad
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
	}
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
	int lon;
	char *ids,msglog[LONSTD];
	char modulo[] = "Apagar()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	ids=copiaParametro("ids",ptrTrama);

	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_Apagar");
	respuestaEjecucionComando(ptrTrama,0,ids);

	system("shutdown -h now");
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
//		FALSE: En caso de ocurrir algún errorservidoradm
//_____________________________________________________________________________________________________
BOOLEAN Reiniciar(TRAMA* ptrTrama)
{
	int lon;
	char *ids,msglog[LONSTD];
	char modulo[] = "Reiniciar()";

	if (ndebug>=DEBUG_MAXIMO) {
		sprintf(msglog, "%s:%s",tbMensajes[21],modulo);
		infoDebug(msglog);
	}
	ids=copiaParametro("ids",ptrTrama);

	initParametros(ptrTrama,0);
	lon=sprintf(ptrTrama->parametros,"nfn=%s\r","RESPUESTA_Reiniciar");
	respuestaEjecucionComando(ptrTrama,0,ids);
	
	system("shutdown -r now");
	
	return(TRUE);
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
		lon+=sprintf(ptrTrama->parametros+lon,"der=%s\r",tbErrores[res]);// Descripción del error
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
//		Procesa las tramas recibidas.servidoradm
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
			return(tbfuncionesClient[i].fptr(ptrTrama)); // Invoca la función
		}
	}
	errorLog(modulo, 18, FALSE);
	return (FALSE);
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
	/*--------------------------------------------------------------------------------------------------------
		Validación de parámetros de ejecución y fichero de configuración 
	 ---------------------------------------------------------------------------------------------------------*/
	if (!validacionParametros(argc, argv,6)) // Valida parámetros de ejecución
		exit(EXIT_FAILURE);

	if (!tomaConfiguracion(szPathFileCfg)) // Toma parametros de configuración
		exit(EXIT_FAILURE);

	/*--------------------------------------------------------------------------------------------------------
		Carga catálogo de funciones que procesan las tramas 
	 ---------------------------------------------------------------------------------------------------------*/
	int cf = 0;

	strcpy(tbfuncionesClient[cf].nf, "RESPUESTA_InclusionClienteWinLnx");
	tbfuncionesClient[cf++].fptr = &RESPUESTA_InclusionClienteWinLnx;

	strcpy(tbfuncionesClient[cf].nf, "Apagar");
	tbfuncionesClient[cf++].fptr = &Apagar;

	strcpy(tbfuncionesClient[cf].nf, "Reiniciar");
	tbfuncionesClient[cf++].fptr = &Reiniciar;
	
	strcpy(tbfuncionesClient[cf].nf, "Sondeo");
	tbfuncionesClient[cf++].fptr = &Sondeo;	

	/*--------------------------------------------------------------------------------------------------------
		Inicio de sesión
	 ---------------------------------------------------------------------------------------------------------*/
	infoLog(1); // Inicio de sesión
	infoLog(3); // Abriendo sesión en el servidor de Administración;		
	/*--------------------------------------------------------------------------------------------------------
		Inclusión del cliente en el sistema
	 ---------------------------------------------------------------------------------------------------------*/
	if(!InclusionClienteWinLnx(ptrTrama)){	// Ha habido algún problema al abrir sesión
		errorLog(modulo,0,FALSE);
		exit(EXIT_FAILURE);
	}
	infoLog(4); // Cliente iniciado
	procesaComandos(ptrTrama); // Bucle para procesar comandos interactivos
	exit(EXIT_SUCCESS);
}
