// ________________________________________________________________________________________________________
// Función: INTROaFINCAD
//
//		Descripción:
// 			Cambia INTROS por caracteres fin de cadena ('\0') en una cadena
//		Parametros:
//				- parametros : La cadena a explorar
// ________________________________________________________________________________________________________
void INTROaFINCAD(char* parametros) {
	int lon, i;
	lon = strlen(parametros);
	for (i = 0; i < lon; i++) {
		if (parametros[i] == '\r')
			parametros[i] = '\0';
	}
}
// ________________________________________________________________________________________________________
// Funciónn: FINCADaINTRO
//
//		Descripciónn?:
// 			Cambia caracteres fin de cadena ('\0') por INTROS en una cadena
//		Parametros:
//				- parametros : La cadena a explorar
// ________________________________________________________________________________________________________
void FINCADaINTRO(char* a, char *b) {
	char *i;
	for (i = a; i < b; i++) { // Cambia los NULOS por INTROS
		if (*i == '\0')
			*i = '\r';
	}
}

// ________________________________________________________________________________________________________
// Función: AbreConexion
//
//		Descripción: 
//			Crea un socket y lo conecta a una interface de red. Devuelve el socket
//		Parámetros:
//			- ips : La direccin IP con la que se comunicarnel socket
//			- port : Puerto para la  comunicacin
// ________________________________________________________________________________________________________
SOCKET AbreConexion(char *ips, int port) {
	struct sockaddr_in server;
	SOCKET s;

	// Crea el socket y se intenta conectar
	s = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	if (s == SOCKET_ERROR) {
		RegistraLog("Error en la creacin del socket. Modulo: AbreConexion()",
				true);
		return INVALID_SOCKET;
	}

	server.sin_family = AF_INET;
	server.sin_port = htons((short) port);
	server.sin_addr.s_addr = inet_addr(ips);

	if (connect(s, (struct sockaddr *) &server, sizeof(server)) == SOCKET_ERROR) {
		RegistraLog("connect() fallo", true);
		return INVALID_SOCKET;
	}
	return (s);

}


// ________________________________________________________________________________________________________
// Función: cuenta_ipes
//
//		Descripción:
// 			Cuenta las comas (caracter de separacion) de las cadenas de ipes
//		Parámetros:
//			- parametros : La cadena a explorar
// ________________________________________________________________________________________________________
int cuenta_ipes(char* iph) {
	int lon, i, cont = 1;
	lon = strlen(iph);
	for (i = 0; i < lon; i++) {
		if (iph[i] == ';')
			cont++;
	}
	return (cont);
}

// ________________________________________________________________________________________________________
// Función: IgualIP
//
//		 Descripción: 
//			Comprueba si una cadena con una ipe estnincluidad en otra que  contienen varias direcciones ipes separas por punto y coma
//		Parámetros:
//			- cadenaiph: Cadena de IPes
//			- ipcliente: Cadena de la ip a buscar
// ________________________________________________________________________________________________________
BOOLEAN IgualIP(char *cadenaiph, char *ipcliente) {
	char *posa, *posb;
	int lon;

	posa = strstr(cadenaiph, ipcliente);
	if (posa == NULL)
		return (FALSE); // No existe la IP en la cadena
	posb = posa; // Iguala direcciones
	while (TRUE) {
		posb++;
		if (*posb == ';')
			break;
		if (*posb == '\0')
			break;
		if (*posb == '\r')
			break;
	}
	lon = strlen(ipcliente);
	if ((posb - posa) == lon)
		return (TRUE); // IP encontrada !!!!

	return (FALSE);
}

// ________________________________________________________________________________________________________
// Función: RegistraLog
//
//		Descripción:
//			Esta funcin registra los evento de errores en un fichero log
//	 	Parametros:
//			- msg : Mensage de error
//			- swerrno: Switch que indica que recupere literal de error del sistema
// ________________________________________________________________________________________________________
void RegistraLog(const char *msg, int swerrno) {
	struct tm * timeinfo;
	timeinfo = TomaHora();
	FILE *FLog;

	FLog = fopen(szPathFileLog, "at");
	if (swerrno)
		fprintf(FLog, "%02d/%02d/%d %02d:%02d ***%s:%s\n", timeinfo->tm_mday,
				timeinfo->tm_mon + 1, timeinfo->tm_year + 1900,
				timeinfo->tm_hour, timeinfo->tm_min, msg, strerror(errno));
	else
		fprintf(FLog, "%02d/%02d/%d %02d:%02d ***%s\n", timeinfo->tm_mday,
				timeinfo->tm_mon + 1, timeinfo->tm_year + 1900,
				timeinfo->tm_hour, timeinfo->tm_min, msg);
	fclose(FLog);

	// Lo muestra por consola
	/*printf("%02d/%02d/%d %02d:%02d ***%s\n", timeinfo->tm_mday,
			timeinfo->tm_mon + 1,
			timeinfo->tm_year + 1900,
			timeinfo->tm_hour,
			timeinfo->tm_min,
			msg);
	*/
}
// ________________________________________________________________________________________________________
// Función: TomaHora
//
//		Descripción:
//			Esta función toma la hora actual  del sistema y devuelve una estructura conlos datos
// ________________________________________________________________________________________________________
struct tm * TomaHora() {
	time_t rawtime;
	time(&rawtime);
	return (gmtime(&rawtime));
}


// ________________________________________________________________________________________________________
// Funcinn: toma_parametro
// 
//		Descripcinn?:
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
//	Descripción:
//			Trocea una cadena según un carácter delimitador
//	Parámetros:
// 			- trozos: Array de punteros a cadenas
// 			- cadena: Cadena a trocear
// 			- chd: Carácter delimitador
//	Devuelve:
//		Número de trozos en que se divide la cadena
// ________________________________________________________________________________________________________
int split_parametros(char **trozos, char *cadena, char *ch)
{
	int w = 0;
	
	char chd = ch[0];
	trozos[w++] = cadena;
	if(cadena!=NULL){
		while (*cadena != '\0') {
			if (*cadena == chd) {
				*cadena = '\0';
				if (*(cadena + 1) != '\0')
					trozos[w++] = cadena + 1;
			}
			cadena++;
		}
	}
	return (w); // Devuelve el número de trozos
}

//______________________________________________________________________________________________________
// Función: recibe_tramas
//
//	 Descripción:
//		Recibe una trama por la red (TCP)
//	Parámetros:
//		s: socket TCP
//		trama: contenido a  enviar
// 	Devuelve:
//		true si el envío ha sido correcto o false en caso contrario
//______________________________________________________________________________________________________
int recibe_tramas(SOCKET s,TRAMA *trama)
{
	int ret;

	ret = recv(s,(char*)trama,LONGITUD_TRAMA,0);
	if (ret == 0) // Conexin cerrada por parte del cliente (Graceful close)
		return (false);
	else{ 
		if (ret == SOCKET_ERROR){
			return (false);
		}
		else{ // Datos recibidos
			Desencriptar((char*)trama);
			return(true);
		}
	}
}
//__________________________________________________________________________________________________________
//
// Función: Encripta
//
//	 Descripción:
//		Esta función encripta una cadena y la devuelve como parametro
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
// Funci�: Desencripta
//
//	 Descripción:
//		Esta funci� desencripta una cadena y la devuelve como parametro
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

