// ******************************************************************************************************
// Aplicaci� HIDRA
// Copyright 2004 Jos�Manuel Alonso. Todos los derechos reservados.
// Fichero: Database.h
//	Descripci�:
//	 	Fichero de cabecera de la clase Database para implementar funciones de manipulaci�
//		de bases de datos sobre un Servidor Mysql
// ******************************************************************************************************
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include </usr/include/mysql/mysql.h>
// __________________________________________________________________________
class Database; 
class Table;
// __________________________________________________________________________
class Database
{
public:
	MYSQL *m_Cnn;
	char m_ErrStr[500];
	Database();
	bool Open(char* UserName, char* Pwd,char* server,char*Database);
	bool OpenTbl(int Mode, char* CmdStr, Table& Tbl);
	bool Close(void);
	bool Execute(char* CmdStr);
	bool Execute(char* CmdStr, Table& Tbl);
	void GetErrorErrStr(char* ErrStr);
};
// __________________________________________________________________________
class Table{
	char* tomadato(const char* FieldName);
public:
	bool eof,bof;
	MYSQL_RES	* m_Rec ;
	MYSQL_FIELD *fields;
	unsigned int num_fields;
	MYSQL_ROW	row ;
	MYSQL_ROW_OFFSET ptr;
	my_ulonglong numreg;
	char m_ErrStr[500];
	Table();
	void GetErrorErrStr(char* ErrStr);
	bool ISEOF();
	bool MoveNext();
	bool MovePrevious();
	bool MoveFirst();
	bool MoveLast();

	bool Get(const char* FieldName, char* FieldValue);
	bool Get(const char* FieldName,int &FieldValue);
	bool Get(const char* FieldName,char &FieldValue);
};
// __________________________________________________________________________
class Herror
{
public:
	int nError; // C�igo del error
	char dError[500]; // Descripci� del error
};
