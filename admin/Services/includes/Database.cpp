// ******************************************************************************************************
// Aplicación HIDRA
// Copyright 2004 Jos�Manuel Alonso. Todos los derechos reservados.
// Fichero: Database.cpp
//	Descripción:
//		Fichero de implementaci� de la clase Database para funciones de manipulaci�
//		de bases de datos sobre un Servidor Mysql
// ******************************************************************************************************
#include "Database.h"
// __________________________________________________________________________
void ErrorHandler(Herror hr, char* ErrStr)
{
	sprintf(ErrStr,"Error:\n");
	sprintf(ErrStr,"%sCode = %d\n",ErrStr ,hr.nError);
	sprintf(ErrStr,"%sDescription = %s",ErrStr, (char*) hr.dError);
}
// __________________________________________________________________________
Database::Database()
{
	m_Cnn=NULL;
	sprintf(m_ErrStr,"NULL POINTER");
}
// __________________________________________________________________________
void Database::GetErrorErrStr(char* ErrStr)
{
	sprintf(ErrStr,"%s",m_ErrStr);
}
// __________________________________________________________________________
void Table::GetErrorErrStr(char* ErrStr)
{
	sprintf(ErrStr,"%s",m_ErrStr);
}
// __________________________________________________________________________
bool Database::Open(char* UserName, char* Pwd,char* server,char*Bd)
{
	Herror hr;
	m_Cnn=mysql_init(NULL);
	if(m_Cnn==NULL){
		hr.nError=0;
		strcpy(hr.dError,"Error en la Creación del objeto MYSQL");
		ErrorHandler(hr,m_ErrStr);
		return(false); // Fallo de inicializaci�
	}
	
	if(!mysql_real_connect(m_Cnn, server,UserName,Pwd,Bd, MYSQL_PORT,NULL,0)){
		mysql_error(m_Cnn);
		hr.nError=mysql_errno(m_Cnn);
		strcpy(hr.dError,mysql_error(m_Cnn));
		ErrorHandler(hr,m_ErrStr);
		return(false); // Fallo de conexi�
	}
	hr.nError=0;
	strcpy(hr.dError,"Success");
	ErrorHandler(hr,m_ErrStr);
	return (true);
}
// __________________________________________________________________________
bool Database::Close()
{
		mysql_close(m_Cnn);
		return(true);
}
// __________________________________________________________________________
bool Database::Execute(char* CmdStr)
{
	Herror hr;
	if (mysql_query(m_Cnn,CmdStr)){ // Ejecuta la consulta
		mysql_error(m_Cnn);
		hr.nError=mysql_errno(m_Cnn);
		strcpy(hr.dError,mysql_error(m_Cnn));
		ErrorHandler(hr,m_ErrStr);
		mysql_close(m_Cnn);
		return(false); // Fallo de conexión
	}
	hr.nError=0;
	strcpy(hr.dError,"Success");
	ErrorHandler(hr,m_ErrStr);
	return (true);
}
// __________________________________________________________________________
bool Database::Execute(char* CmdStr, Table& Tbl)
{
	Herror hr;
	if (mysql_query(m_Cnn,CmdStr)) { // Ejecuta la consulta
		mysql_error(m_Cnn);
		hr.nError=mysql_errno(m_Cnn);
		strcpy(hr.dError,mysql_error(m_Cnn));
		ErrorHandler(hr,m_ErrStr);
		mysql_close(m_Cnn);
		return(false); // Fallo de conexi�
	}

	hr.nError=0;
	strcpy(hr.dError,"Success");
	ErrorHandler(hr,m_ErrStr);

	Tbl.m_Rec = mysql_store_result(m_Cnn) ; // Toma el recordset
	if(Tbl.m_Rec){
		Tbl.row=mysql_fetch_row(Tbl.m_Rec);
		Tbl.fields = mysql_fetch_fields(Tbl.m_Rec);
		Tbl.num_fields = mysql_num_fields(Tbl.m_Rec);
		Tbl.numreg=mysql_num_rows(Tbl.m_Rec);
		Tbl.eof=Tbl.numreg==0; // Consulta vacia
	}
	return (true);
}
// __________________________________________________________________________
Table::Table()
{
	m_Rec=NULL;
}
// __________________________________________________________________________
bool Table::ISEOF()
{
	return(eof);
}
// __________________________________________________________________________
bool Table::Get(const char* FieldName, char *FieldValue)
{
	char * aux;
	aux=tomadato(FieldName);
	if(aux)
		strcpy(FieldValue,aux);
	else
		strcpy(FieldValue,"");
	return(true);
}
// __________________________________________________________________________
bool Table::Get(const char* FieldName,int &FieldValue)
{
	char *aux;
	aux=tomadato(FieldName);
	if(aux)
		FieldValue=atoi(aux);
	else
		FieldValue=0;
	return(true);
}
// __________________________________________________________________________
bool Table::Get(const char* FieldName,char &FieldValue)
{
	char *aux;
	aux=tomadato(FieldName);
	FieldValue=aux[0];
	return(true);
}
// __________________________________________________________________________
char* Table::tomadato(const char* FieldName)
{
	Herror hr;
	unsigned int i;

	for(i = 0; i < num_fields; i++){
		if(strcmp((char*)fields[i].name,FieldName)==0){
			sprintf(m_ErrStr,"Success");
			return((char*)row[i]);
		}
	}
	hr.nError=-1;
	strcpy(hr.dError,"El nombre del campo no existe");
	ErrorHandler(hr,m_ErrStr);
	return(NULL); // No existe el nombre del campo en la tabla
}
// __________________________________________________________________________

bool Table::MoveNext()
{
	eof=false;
	row=mysql_fetch_row(m_Rec);
	if(row==NULL){
		if(!mysql_eof(m_Rec)) 
			return(false); // Fallo de lectura
		else
			eof=true; // Fin de fichero
	}
	return (true);
}
// __________________________________________________________________________
bool Table::MoveFirst()
{
	my_ulonglong auxnumreg;
	
	auxnumreg=0;
	mysql_data_seek(m_Rec,auxnumreg);
	return (MoveNext());
}
// __________________________________________________________________________
bool Table::MoveLast()
{
	my_ulonglong auxnumreg;
	auxnumreg=numreg;
	auxnumreg--;
	if(auxnumreg<0) auxnumreg=0; // Principio de fichero
	mysql_data_seek(m_Rec,auxnumreg);
	return (MoveNext());
	return (true);
}
