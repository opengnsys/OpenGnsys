// ********************************************************************************************************// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero:M_Iconos
// Descripción : 
//		Este fichero implementa las funciones javascript del fichero M_Iconos.php
// ********************************************************************************************************
// Opciones
var Insertar=1
var Eliminar=2
var Modificar=3
var Consultar=4

// Acciones
var Sin_accion=0
var Insertar_registro=1
var Borrar_registro=2
var Modificar_registro=3
var Leer_registro=4

var CurrentFecha=null;
//-------------------------------------------------------------------------------------------------------------
function menu(){
    w=window.parent
    w.location.href="../menu.php"
}
//-------------------------------------------------------------------------------------------------------------
  function Editar(){
	document.fdatos.opcion.value=Modificar
	document.fdatos.accion.value=Leer_registro
	document.fdatos.submit()
  }
//-------------------------------------------------------------------------------------------------------------
  function Agregar() {
      document.fdatos.opcion.value=Insertar
      document.fdatos.accion.value=Sin_accion
      document.fdatos.submit()
  }
//-------------------------------------------------------------------------------------------------------------
  function Borrar(){
	document.fdatos.opcion.value=Eliminar
	document.fdatos.accion.value=Leer_registro
	document.fdatos.submit()
  }
//-------------------------------------------------------------------------------------------------------------
  function Cancelar() {
	document.fdatos.opcion.value=Insertar
	document.fdatos.accion.value=Sin_accion
	document.fdatos.idicono.value=0
	document.fdatos.submit()
  }
//-------------------------------------------------------------------------------------------------------------
function Confirmar() {
    var sw
    sw=parseInt(document.fdatos.opcion.value)
    switch (sw) {
        case Insertar :
            if (comprobar_datos()){
                document.fdatos.accion.value=Insertar_registro
                document.fdatos.submit()
            }
            break
        case Eliminar :
            document.fdatos.accion.value=Borrar_registro
            document.fdatos.submit()
            break
        case Modificar :
            if (comprobar_datos()){
                document.fdatos.accion.value=Modificar_registro
                document.fdatos.submit()
         }
         break
   }
}
//----------------------------------------------------------------------------------------------
function comprobar_datos(){
	
	if (document.fdatos.descripcion.value==""){
		alert("Descripcion es un dato obligatorio")
		document.forms.fdatos.descripcion.focus()
		return(false)
	}
	return(true) 
}
