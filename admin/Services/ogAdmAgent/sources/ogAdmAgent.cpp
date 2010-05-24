// *************************************************************************
// Aplicación: OPENGNSYS
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2006
// Nombre del fichero: ogAdmServer.php
// Descripción : 
//		
// ****************************************************************************
#include "ogAdmAgent.h"
#include "ogAdmLib.c"
// _____________________________________________________________________________________________________________
// Función: inicializa
//
//		Descripción:
//			Esta función nicializa variables
// _____________________________________________________________________________________________________________
void inicializa()
{
	dias_meses[1]=31;
	dias_meses[2]=28;
	dias_meses[3]=31;
	dias_meses[4]=30;
	dias_meses[5]=31;
	dias_meses[6]=30;
	dias_meses[7]=31;
	dias_meses[8]=31;
	dias_meses[9]=30;
	dias_meses[10]=31;
	dias_meses[11]=30;
	dias_meses[12]=31;

// años tamaño 1 bytes
	HEX_annos[1]=0x01; 
	HEX_annos[2]=0x02; 
	HEX_annos[3]=0x04; 
	HEX_annos[4]=0x08; 
	HEX_annos[5]=0x10; 
	HEX_annos[6]=0x20; 
	HEX_annos[7]=0x40; 
	HEX_annos[8]=0x80; 

	// mese tamaño 2 bytes
	HEX_meses[1]=0x0001; 
	HEX_meses[2]=0x0002;
	HEX_meses[3]=0x0004;
	HEX_meses[4]=0x0008;
	HEX_meses[5]=0x0010;
	HEX_meses[6]=0x0020;
	HEX_meses[7]=0x0040;
	HEX_meses[8]=0x0080;
	HEX_meses[9]=0x0100;
	HEX_meses[10]=0x0200;
	HEX_meses[11]=0x0400;
	HEX_meses[12]=0x0800;

	// dias tamaño 4 bytes
	HEX_dias[1]=0x00000001;
	HEX_dias[2]=0x00000002; 
	HEX_dias[3]=0x00000004; 
	HEX_dias[4]=0x00000008; 
	HEX_dias[5]=0x00000010; 
	HEX_dias[6]=0x00000020; 
	HEX_dias[7]=0x00000040; 
	HEX_dias[8]=0x00000080;
	HEX_dias[9]=0x00000100; 
	HEX_dias[10]=0x00000200; 
	HEX_dias[11]=0x00000400; 
	HEX_dias[12]=0x00000800; 
	HEX_dias[13]=0x00001000;
	HEX_dias[14]=0x00002000;
	HEX_dias[15]=0x00004000;
	HEX_dias[16]=0x00008000;
	HEX_dias[17]=0x00010000;
	HEX_dias[18]=0x00020000;
	HEX_dias[19]=0x00040000;
	HEX_dias[20]=0x00080000;
	HEX_dias[21]=0x00100000;
	HEX_dias[22]=0x00200000;
	HEX_dias[23]=0x00400000;
	HEX_dias[24]=0x00800000;
	HEX_dias[25]=0x01000000;
	HEX_dias[26]=0x02000000;
	HEX_dias[27]=0x04000000;
	HEX_dias[28]=0x08000000;
	HEX_dias[29]=0x10000000;
	HEX_dias[30]=0x20000000;
	HEX_dias[31]=0x40000000;

	// horas tamaño 2 bytes
	HEX_horas[0]=0x0001;
	HEX_horas[1]=0x0002; 
	HEX_horas[2]=0x0004;
	HEX_horas[3]=0x0008;
	
	HEX_horas[4]=0x0010; 
	HEX_horas[5]=0x0020; 
	HEX_horas[6]=0x0040; 
	HEX_horas[7]=0x0080;

	HEX_horas[8]=0x0100; 
	HEX_horas[9]=0x0200; 
	HEX_horas[10]=0x0400; 
	HEX_horas[11]=0x0800;

	// dia de la semana (L,M,X...) tamaño 1 bytes
	HEX_diasemana[1]=0x01;
	HEX_diasemana[2]=0x02;
	HEX_diasemana[3]=0x04;
	HEX_diasemana[4]=0x08;
	HEX_diasemana[5]=0x10;
	HEX_diasemana[6]=0x20;
	HEX_diasemana[7]=0x40;

	// semana tamaño 1 bytes
	HEX_semanas[1]=0x01; 
	HEX_semanas[2]=0x02; 
	HEX_semanas[3]=0x04; 
	HEX_semanas[4]=0x08; 
	HEX_semanas[5]=0x10; 
	HEX_semanas[6]=0x20; 
}
// _____________________________________________________________________________________________________________
// Función: RegistraLog
//
//		Descripción:
//			Esta función registra los evento de errores en un fichero log
//		Parametros:
//			- msg : Mensage de error
//			- swerrno: Switch que indica que recupere literal de error del sistema
// _____________________________________________________________________________________________________________
void RegistraLog(char *msg,int swerrno)
{
	time_t rawtime;
	struct tm * timeinfo;
	char MsgHerror[1000];

	time ( &rawtime );
	timeinfo = gmtime(&rawtime);

	FLog=fopen( "hidraagent.log","at");
	if(swerrno){
	//	fprintf (FLog,"%02d/%02d/%d %02d:%02d ***%s:%s\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year,timeinfo->tm_hour,timeinfo->tm_min,msg,strerror(errno));
		sprintf (MsgHerror,"%02d/%02d/%d %02d:%02d ***%s:%d\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year,timeinfo->tm_hour,timeinfo->tm_min,msg,WSAGetLastError());
	}
	else{
		sprintf (MsgHerror,"%02d/%02d/%d %02d:%02d ***%s\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year+1900,timeinfo->tm_hour,timeinfo->tm_min,msg);
	}
	fprintf (FLog,MsgHerror);
	AddToMessageLog((LPTSTR)MsgHerror);
	fclose(FLog);
}
// _____________________________________________________________________________________________________________
// Función: TomaParametrosReg
//
//		Descripción:
//			Esta función toma los parámetros de conexión del registro
// _____________________________________________________________________________________________________________
int TomaParametrosReg()
{
	if(!ReadRegistryString(HIVE,BASE,"servidorhidra",servidorhidra,20))
		strcpy(servidorhidra,SERVIDORHIDRA);

	if(!ReadRegistryInteger(HIVE,BASE,"puerto",(DWORD *)&puerto))
		puerto=PUERTO_DEFAULT;

	if(!ReadRegistryString(HIVE,BASE,"usuario",usuario,20))
		strcpy(usuario,USUARIO);
	Desencriptar(usuario);

	if(!ReadRegistryString(HIVE,BASE,"pasguor",pasguor,20))
		strcpy(pasguor,PASGUOR);
	Desencriptar(pasguor);

	if(!ReadRegistryString(HIVE,BASE,"datasource",datasource,20))
		strcpy(datasource,DATASOURCE);

	if(!ReadRegistryString(HIVE,BASE,"catalog",catalog,50))
		strcpy(catalog,CATALOG);

	sprintf(cadenaconexion,CADENACONEXION,catalog,datasource); // Crea cadena de conexión

	return(TRUE);
}
// _____________________________________________________________________________________________________________
//
// Función: GestionaProgramacion
//
//		Descripción:
//			Esta función es la encargada de leer la base de datos y comprobar si	existe alguna acción o reserva programada
//		Parametros:
//			- pst : Estructura con la configuración de fecha y hora del sistema
// _____________________________________________________________________________________________________________
int GestionaProgramacion(SYSTEMTIME pst){
	busca_accion(pst.wDay,pst.wMonth,pst.wYear,pst.wHour,pst.wMinute,pst.wDayOfWeek );
	return(0);
}
// _____________________________________________________________________________________________________________
// Función: busca_accion
//
//		 Descripción:
//			Esta función busca en la base de datos,acciones programadas
//		Parametros:
//			- dia : Dia actual del mes
//			- mes : mes en curso
//			- anno : Año en curso
//			- hora : Hora actual
//			- minutos : Minutos actuales
//			- diasemana : Dia de la semana 1=lunes,2=martes ... ( 0 Domingo)
// _____________________________________________________________________________________________________________
int busca_accion(WORD dia,WORD mes,WORD anno,WORD hora,WORD minutos,WORD diasemana)
{
	char sqlstr[1000],ErrStr[200];
	Database db,wdb;
	Table tbl;
	char parametros[LONGITUD_PARAMETROS];
	BYTE swampm,bitsemana;
	int tipoaccion,identificador;
	int ordsem,ordulsem,ordiasem_1,maxdias;
	anno=anno-2003; // Año de comienzo es 2004
	if(hora>11){
		hora-=12;
		swampm=1; // Es pm
	}
	else
		swampm=0; // Es am

	if(diasemana==0) diasemana=7; // El domingo

	// Cuestion semanas
	ordiasem_1=DiadelaSemana(1,mes,anno+2003);
	ordsem=SemanadelMes(ordiasem_1,dia); // Calcula el numero de la semana
	if (mes!=2) // Toma el ultimo dia de ese mes
		maxdias=dias_meses[mes];
	else{
		if (bisiesto(anno+2003))
			maxdias=29;
		else
			maxdias=28;
	}
	ordulsem=SemanadelMes(ordiasem_1,maxdias); // Calcula el numero de ultima semana

	bitsemana=HEX_semanas[ordsem];
	if(ordsem==ordulsem) // Si es la ultima semana del mes
		bitsemana|=HEX_semanas[6];

	if(!db.Open(usuario,pasguor,cadenaconexion)){ // error de conexion
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	sprintf(sqlstr,"SELECT DISTINCT tipoaccion,identificador FROM programaciones WHERE  suspendida=0 AND (annos & %d <> 0) AND (meses & %d<>0) AND ((diario & %d<>0) OR (dias & %d<>0) OR (semanas & %d<>0)) AND (horas & %d<>0) AND ampm=%d AND minutos=%d",HEX_annos[anno],HEX_meses[mes],HEX_dias[dia],HEX_diasemana[diasemana],bitsemana,HEX_horas[hora],swampm,minutos);
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(tbl.ISEOF()){
		return(true);  // No hay acciones programadas
	}
	if(!wdb.Open(usuario,pasguor,cadenaconexion)){ // error de conexion
		wdb.GetErrorErrStr(ErrStr);
		return(false);
	}
	while(!tbl.ISEOF()){ // Busca entre todas las programaciones 
		if(!tbl.Get("tipoaccion",tipoaccion)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		if(!tbl.Get("identificador",identificador)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		if(tipoaccion==EJECUCION_TAREA){ // Es una programación de una tarea
			EjecutarTarea(identificador,0,0,0,wdb,parametros);
		}
		else{
			if(tipoaccion==EJECUCION_TRABAJO){
				EjecutarTrabajo(identificador,wdb,parametros); // Es una programación de un trabajo
			}
			else{
				if(tipoaccion==EJECUCION_RESERVA){
					EjecutarReserva(identificador,wdb,parametros); // Es una programación de un trabajo
				}
			}
		}
		tbl.MoveNext();
	}
	return(true);  
}
// _____________________________________________________________________________________________________________
// Función: bisiesto
//
//		Descripción:
//			Esta función devuelve true si el año pasado como parámetro es bisiesto y false si no lo es
//		Parametros:
//			- anob : un año en formato aaaa
// _____________________________________________________________________________________________________________
bool bisiesto(WORD anob){
	return(anob%4==0);
}
// _____________________________________________________________________________________________________________
// Función: DiadelaSemana
//
//		Descripción:
//			Esta función devuelve el número del día de la semana: 1=Lunes, 2=mártes ... 6=sábado  7=domingo de una fecha determinada
//		Parametros:
//			- dia : Un dia
//			- mes : Un mes
//			- anno : Un año
// _____________________________________________________________________________________________________________
int DiadelaSemana(WORD dia,WORD mes,WORD anno)
{
	int i,cont,dias_anuales;
	int desplazamiento_dias=6;
	int orddiasem;

	cont =0;
	for (i=1900;i<anno;i++){
		if (bisiesto(i)) dias_anuales=366; else	dias_anuales=365;
		cont+=dias_anuales;
	}
	for (i=1;i<mes;i++){
		if (i!=2)
			cont+=dias_meses[i];
		else{
			if (bisiesto(anno))
				cont+=29;
			else
				cont+=28;
		}
	}
	cont+=dia+desplazamiento_dias;
	orddiasem=(cont%7);
	if(orddiasem==0) orddiasem=7;
	return(orddiasem);
}
// _____________________________________________________________________________________________________________
// Función: DiadelaSemana
//
//		Descripción:
//			Esta función devuelve el número de semana perteneciente a un día de ese mes
//		Parametros:
//			- ordiasem_1 : Orden semenal (1,2...) del dia del primer dia del mes que se pasa como parámetro
//			- diames : El mes concreto
// _____________________________________________________________________________________________________________
int SemanadelMes(int ordiasem_1,int diames)
{
	int nwdia,resto,cociente;

	nwdia=diames+ordiasem_1-1;
	cociente=nwdia/7;
	resto=nwdia%7;
	if(resto>0) cociente++;
	return(cociente);
}
// _____________________________________________________________________________________________________________
// Función: Pausa
//
//		Descripción:
//			Hace una pausa en segundos
//		Parametros:
//			- s : Segundos de pausa
// _____________________________________________________________________________________________________________
void Pausa(int s)
{
	int seg=0;
	clock_t comienzo;

    comienzo = clock();
    do{
		seg=(clock()-comienzo)/CLOCKS_PER_SEC;
	}while(seg<s);
}
// _____________________________________________________________________________________________________________
// Función: EjecutarTrabajo
//
//		Descripción: 
//			Registra una acción (Trabajo y la envía para su ejecución 
//		Parámetros:
//			- idtrabajo : Identificador del trabajo
//			- Database: una conexion ADO operativa
//			- parametros: parámetros de la acción
// _____________________________________________________________________________________________________________
int EjecutarTrabajo(int idtrabajo,Database db,char*parametros )
{
	char sqlstr[1000],ErrStr[200];
	Table tbl;
	int cont_tareas=0,lon;
	int  idtarea,idtrabajotarea,idcentro;
	char wambitrabajo[500],ambitrabajo[4000];
	char wparamtrabajo[20],paramtrabajo[1000];
	int  tbTareasidtarea[100],tbTareasidnotificador[100];
	char *tbTareasparametros[100],*tbTareasambitoambitskwrk[100];
	char ambitskwrk[500];

	ambitrabajo[0]=(char)NULL; // Inicialización
	strcpy(paramtrabajo,"tsk="); // Inicialización

	// recupera el identificador del Centro propietario de la tarea
	sprintf(sqlstr,"SELECT idcentro FROM trabajos WHERE idtrabajo=%d",idtrabajo);
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(tbl.ISEOF()) return(true);
	if(!tbl.Get("idcentro",idcentro)){ // Toma dato
		tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
		return(false);
	}
	// Recupera las tareas que forman parte del trabajo
	sprintf(sqlstr,"SELECT * FROM trabajos_tareas WHERE idtrabajo=%d ORDER by orden",idtrabajo);
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(tbl.ISEOF()) return(true);
	// Recorre trabajos-tareas
	while(!tbl.ISEOF()){ 	
		if(!tbl.Get("idtrabajotarea",idtrabajotarea)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbTareasidnotificador[cont_tareas]=idtrabajotarea;

		if(!tbl.Get("idtarea",idtarea)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbTareasidtarea[cont_tareas]=idtarea;

		if(!tbl.Get("parametros",parametros)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		lon=strlen(parametros);
		tbTareasparametros[cont_tareas]=(char*)malloc(lon);
		if(tbTareasparametros[cont_tareas]==NULL)
			return(false); // No hay memoria bastante
		strcpy(tbTareasparametros[cont_tareas],parametros);
		
		if(!tbl.Get("ambitskwrk",ambitskwrk)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		lon=strlen(ambitskwrk);
		tbTareasambitoambitskwrk[cont_tareas]=(char*)malloc(lon);
		strcpy(tbTareasambitoambitskwrk[cont_tareas],ambitskwrk);

		sprintf(wambitrabajo,"%s;",ambitskwrk);
		strcat(ambitrabajo,wambitrabajo);

		sprintf(wparamtrabajo,"%d;",idtrabajotarea);
		strcat(paramtrabajo,wparamtrabajo);

		cont_tareas++;
		tbl.MoveNext();
	}
	lon=strlen(ambitrabajo);
	ambitrabajo[lon-1]=(char)NULL; // Quita la coma final

	lon=strlen(paramtrabajo);
	paramtrabajo[lon-1]=(char)NULL; // Quita la coma final

	char _fechahorareg[100];
    SYSTEMTIME st;
    GetLocalTime(&st);
	sprintf(_fechahorareg,"%d/%d/%d %d:%d:%d",st.wDay,st.wMonth,st.wYear,st.wHour,st.wMinute,st.wSecond);

	sprintf(sqlstr,"INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,ambitskwrk,fechahorareg,estado,resultado,idcentro,parametros,accionid,idnotificador) VALUES (%d,%d,%d,0,0,'%s','%s','%s','%s',%d,'%s',0,0)",EJECUCION_TRABAJO,idtrabajo,PROCESOS,ambitrabajo,_fechahorareg,ACCION_INICIADA,ACCION_SINERRORES,idcentro,paramtrabajo);
	if(!db.Execute(sqlstr)){ // Error al insertar
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	int accionid=0;
	// Toma identificador dela acción
	sprintf(sqlstr,"SELECT @@identity as identificador");
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(!tbl.ISEOF()){ // Si existe registro
		if(!tbl.Get("identificador",accionid)){
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
	}
	int i;
	// Insertar acciones:tareas
	for (i=0;i<cont_tareas;i++){
		if(!EjecutarTarea(tbTareasidtarea[i],accionid,tbTareasidnotificador[i],idcentro,db,parametros)){
			free(tbTareasparametros[i]);
			free(tbTareasambitoambitskwrk[i]);
			return(false);
		}
		free(tbTareasparametros[i]);
		free(tbTareasambitoambitskwrk[i]);
	}
	return(true);
}
// _____________________________________________________________________________________________________________
// Función: EjecutarTarea
//
//		Descripción: 
//			Registra una acción (Tarea) y la envía para su ejecución 
//		Parámetros:
//			- idtarea : Identificador de la tarea
//			- accionid: identificador del trabajo padre (si existe)
//			- idnotificador:  identificador del trabajo_tarea incluido en trabajo padre (si existe)
//			- idcentro: Centro propietario del trabjo padre (si existe este trabajo)
//			- Database: una conexion ADO operativa
//			- parametros: parámetros de la acción
// _____________________________________________________________________________________________________________
int EjecutarTarea(int idtarea,int accionid,int idnotificador,int idcentro,Database db,char *parametros )
{
	char sqlstr[1000],ErrStr[200],ambito;
	Table tbl;
	int cont_comandos=0,lon;
	int  idcomando,idambito,idtareacomando,accionidcmd;
	char wambitarea[20],ambitarea[4000];
	char wparamtarea[20],paramtarea[1000],pids[20];
	int  tbComandosidcomando[100],tbComandosambito[100],tbComandosidnotificador[100],tbComandosidambito[100];
	char *tbComandosparametros[100];

	ambitarea[0]=(char)NULL; // Inicialización
	strcpy(paramtarea,"cmd="); // Inicialización
	if(idcentro==0){
		// recupera el identificador del Centro propietario de la tarea
		sprintf(sqlstr,"SELECT idcentro FROM tareas WHERE idtarea=%d",idtarea);
		if(!db.Execute(sqlstr,tbl)){ // Error al leer
			db.GetErrorErrStr(ErrStr);
			return(false);
		}
		if(tbl.ISEOF()) return(true);
		if(!tbl.Get("idcentro",idcentro)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
	}
	// Recupera los comandos que forman parte de la tarea
	sprintf(sqlstr,"SELECT * FROM tareas_comandos WHERE idtarea=%d ORDER by orden",idtarea);
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(tbl.ISEOF()) return(true);
		
	// Recorre tareas-comandos
	while(!tbl.ISEOF()){ 	
		if(!tbl.Get("idcomando",idcomando)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbComandosidcomando[cont_comandos]=idcomando;

		if(!tbl.Get("ambito",ambito)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbComandosambito[cont_comandos]=ambito;

		if(!tbl.Get("idambito",idambito)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbComandosidambito[cont_comandos]=idambito;


		if(!tbl.Get("parametros",parametros)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}

		lon=strlen(parametros);
		tbComandosparametros[cont_comandos]=(char*)malloc(lon+20);
		if(tbComandosparametros[cont_comandos]==NULL)
			return(false); // No hay memoria bastante

		strcpy(tbComandosparametros[cont_comandos],parametros);
		
		if(!tbl.Get("idtareacomando",idtareacomando)){ // Toma dato
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
		tbComandosidnotificador[cont_comandos]=idtareacomando;

		sprintf(wambitarea,"%d:%d;",ambito,idambito);
		strcat(ambitarea,wambitarea);

		sprintf(wparamtarea,"%d;",idtareacomando);
		strcat(paramtarea,wparamtarea);

		cont_comandos++;
		tbl.MoveNext();
	}
	lon=strlen(ambitarea);
	ambitarea[lon-1]=(char)NULL; // Quita la coma final

	lon=strlen(paramtarea);
	paramtarea[lon-1]=(char)NULL; // Quita la coma final

	char _fechahorareg[100];
    SYSTEMTIME st;
    GetLocalTime(&st);
	sprintf(_fechahorareg,"%d/%d/%d %d:%d:%d",st.wDay,st.wMonth,st.wYear,st.wHour,st.wMinute,st.wSecond);

	sprintf(sqlstr,"INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,ambitskwrk,fechahorareg,estado,resultado,idcentro,parametros,accionid,idnotificador) VALUES (%d,%d,%d,0,0,'%s','%s','%s','%s',%d,'%s',%d,%d)",EJECUCION_TAREA,idtarea,PROCESOS,ambitarea,_fechahorareg,ACCION_INICIADA,ACCION_SINERRORES,idcentro,paramtarea,accionid,idnotificador);
	if(!db.Execute(sqlstr)){ // Error al insertar
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	accionid=0;
	// Toma identificador dela acción
	sprintf(sqlstr,"SELECT @@identity as identificador");
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(!tbl.ISEOF()){ // Si existe registro
		if(!tbl.Get("identificador",accionid)){
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
		}
	}
	int i;
	// Insertar acciones:comandos
	for (i=0;i<cont_comandos;i++){
	    GetLocalTime(&st);
		sprintf(_fechahorareg,"%d/%d/%d %d:%d:%d",st.wDay,st.wMonth,st.wYear,st.wHour,st.wMinute,st.wSecond);
		sprintf(sqlstr,"INSERT INTO acciones (tipoaccion,idtipoaccion,cateaccion,ambito,idambito,fechahorareg,estado,resultado,idcentro,parametros,accionid,idnotificador) VALUES (%d,%d,%d,%d,%d,'%s','%s','%s',%d,'%s',%d,%d)",EJECUCION_COMANDO,tbComandosidcomando[i],PROCESOS,tbComandosambito[i],tbComandosidambito[i],_fechahorareg,ACCION_EXITOSA,ACCION_SINERRORES,idcentro,tbComandosparametros[i],accionid,tbComandosidnotificador[i]);	
		if(!db.Execute(sqlstr)){ // Error al insertar
			db.GetErrorErrStr(ErrStr);
			free(tbComandosparametros[i]);
			return(false);
		}

		// Toma identificador dela acción
		sprintf(sqlstr,"SELECT @@identity as identificador");
		if(!db.Execute(sqlstr,tbl)){ // Error al leer
			db.GetErrorErrStr(ErrStr);
			return(false);
		}

		if(!tbl.ISEOF()){ // Si existe registro
			if(!tbl.Get("identificador",accionidcmd)){
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			return(false);
			}
		}
		sprintf(pids,"ids=%d\r",accionidcmd);
		strcat(tbComandosparametros[i],pids); // Le añade el identificador de la accion
		envia_comando(tbComandosparametros[i]);
		free(tbComandosparametros[i]);
	}
	return(true);
}
// _____________________________________________________________________________________________________________
// Función: EjecutarReserva
//
//		Descripción: 
//			Registra una acción (Tarea) y la envía para su ejecución 
//		Parámetros:
//			- idreserva : Identificador de la reserva
//			- Database: una conexion ADO operativa
//			- parametros: parámetros de la acción
// _____________________________________________________________________________________________________________
int EjecutarReserva(int idreserva,Database db,char*parametros )
{
	char sqlstr[1000],ErrStr[200];
	Table tbl;
	int idaccion;

	sprintf(sqlstr,"SELECT idtarea,idtrabajo FROM reservas WHERE idreserva=%d",idreserva);
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		return(false);
	}
	if(tbl.ISEOF()){
		return(false);  // No hay acciones previas en la  reserva
	}

	if(!tbl.Get("idtarea",idaccion)){ // Toma dato
		tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
		return(false);
	}
	if(idaccion>0)
		EjecutarTarea(idaccion,0,0,0,db,parametros); // Es una reserva con tarea previa

	if(!tbl.Get("idtrabajo",idaccion)){ // Toma dato
		tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
		return(false);
	}
	if(idaccion>0)
		EjecutarTrabajo(idaccion,db,parametros); // Es una reserva con trabajo previo

	return(true);
}
// _____________________________________________________________________________________________________________
// Función: envia_comando
//
//		Descripción: 
//			Envia un comando a la red. Para ello es necesario teneriniciado el servicio hidra.
//		Parámetros:
//			- parametros: parámetros del comando
// _____________________________________________________________________________________________________________
int envia_comando(char* parametros)
{
	SOCKET sClient;
	TRAMA trama;

	sClient = AbreConexion(servidorhidra,puerto);
	if (sClient == (SOCKET)NULL)
		return(FALSE);

	trama.arroba='@';
	strncpy(trama.identificador,"JMMLCAMDJ",9);
	trama.ejecutor=parametros[0];
	strcpy(trama.parametros,(char*)&parametros[1]);
    return(manda_trama(sClient,&trama));
}
// _____________________________________________________________________________________________________________
// Función: AbreConexion
//
//		Descripción: 
//			Crea un socket y lo conecta a una interface de red. Devuelve el socket
//		Parámetros:
//			- ips : La dirección IP con la que se comunicará el socket
//			- port : Puerto para la  comunicación
// _____________________________________________________________________________________________________________
SOCKET AbreConexion(char *ips,int port)
{
    struct sockaddr_in server;
    struct hostent *host = NULL;
	SOCKET s;

	// Crea el socket y se intenta conectar
	s = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	if (s == SOCKET_ERROR){
		return (SOCKET)NULL;
	}
	server.sin_family = AF_INET;
	server.sin_port = htons((short)port);
	server.sin_addr.s_addr = inet_addr(ips);

	if (connect(s, (struct sockaddr *)&server, sizeof(server)) == SOCKET_ERROR){
		RegistraLog("***AGENT***connect() fallo:",true);
		return (SOCKET)NULL;
	}
	return(s);
}
/// _____________________________________________________________________________________________________________
// Función: manda_trama
//
//		Descripción:
//			Esta función envia una trama por la red (TCP) 
//		Parametros:
//			- sock : El socket del host al que se dirige la trama
//			- trama: El contenido de la trama
/// _____________________________________________________________________________________________________________
int manda_trama(SOCKET sock,TRAMA* trama)
{
	int nLeft,idx,ret;

	Encriptar((char*)trama);
	nLeft = strlen((char*)trama);
	idx = 0;
	while(nLeft > 0){
		ret = send(sock,(char*)&trama[idx], nLeft, 0);
		if (ret == 0)
			break;
		else
			if (ret == SOCKET_ERROR){
				RegistraLog("***AGENT***send() fallo al enviar trama:",true);
				return(FALSE);
			}
		nLeft -= ret;
		idx += ret;
	}
	return(TRUE);
}
//******************************************************************************************************************************************
// PROGRAMA PRINCIPAL ( SERVICIO)
//******************************************************************************************************************************************

main()

	while (TRUE){ 
		GetLocalTime(&st);
		pseg=1000*(65-st.wSecond); // Calcula milisegundos de inactividad de la hebra
		Sleep(pseg);
		// Toma la hora
		GetLocalTime(&st);
        GestionaProgramacion(st);
	}
	
