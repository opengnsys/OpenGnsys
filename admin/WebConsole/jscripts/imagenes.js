// *************************************************************************************************************************************************
// Libreria de scripts de Javascript
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fichero: imagenes.js
// Este fichero implementa las funciones javascript del fichero imagenes.php
// *************************************************************************************************************************************************
//___________________________________________________________________________________________________________
//	
//	Muestra información sobre las imágenes
//___________________________________________________________________________________________________________
function muestra_informacion(){
	var id = $("[id^='menu-images']").attr('id');
	var datos = id.split("_");
	var whref="../varios/informacion_imagenes.php?idimagen="+datos[2]+"&descripcionimagen="+"descripcionimagen";
	window.open(whref,"frame_contenidos")
	ocultar_menu();
}
//________________________________________________________________________________________________________
//	
//	Muestra formulario para gestionar el software incremental incluido en una imagen
//________________________________________________________________________________________________________
function insertar_imagenincremental(){
	reset_contextual(-1,-1);
	var identificador=currentNodo.toma_identificador();
	var descripcionimagen=currentNodo.toma_infonodo();
	var whref="../varios/imagenincremental.php?idimagen="+identificador+"&descripcionimagen="+descripcionimagen;
	window.open(whref,"frame_contenidos")
}
//________________________________________________________________________________________________________
//	
//	Inserta nueva imagen
//________________________________________________________________________________________________________
//
function insertar_imagen(litamb,tipoimg)
{
	if (tipoimg == 0) {
            var id = $("[id^='menu-groups']").attr('id');
	    if (! id.includes("_")) {
                        var id = $("[id^='menu-tipes']").attr('id');
            }
		console.log(id);
	    var datos = id.split("_");
	    tipoimg=datos[1]
	    litamb=litamb+littipo[datos[1]];
	    identificador=datos[2];
	} else {
	    reset_contextual(-1,-1); // Oculta menu contextual
	    var identificador=currentNodo.toma_identificador();
        }
	var whref="../propiedades/propiedades_imagenes.php?opcion="+op_alta+"&grupoid="+identificador+"&litamb="+litamb+"&tipoimg="+tipoimg;
	window.open(whref,"frame_contenidos");
	ocultar_menu();
}
//________________________________________________________________________________________________________
//	
//	Modificar datos de imagen
//________________________________________________________________________________________________________
//
function modificar_imagen()
{
	var id = $("[id^='menu-images']").attr('id');
	var datos = id.split("_");
	var whref="../propiedades/propiedades_imagenes.php?opcion="+op_modificacion+"&tipoimg="+datos[1]+"&identificador="+datos[2];
	window.open(whref,"frame_contenidos");
	ocultar_menu();
}
//________________________________________________________________________________________________________
//	
//	Eliminar una imagen
//________________________________________________________________________________________________________
//
function eliminar_imagen()
{
	var id = $("[id^='menu-images']").attr('id');
	var datos = id.split("_");
	// eliminamos del árbol
	var img=document.getElementById("img_"+datos[2]);
	var ul_grupo=img.parentNode;
	ul_grupo.removeChild(img);


	//console.log(img);
	console.log(ul_grupo);

	var whref="../propiedades/propiedades_imagenes.php?opcion="+op_eliminacion+"&tipoimg="+datos[1]+"&identificador="+datos[2];
	window.open(whref,"frame_contenidos");
	ocultar_menu();
}

// provisional menú contextual //
function mostrar_menu(event, tipo, id, menu_id) {
   var posX, posY, span; // Declaracion de variables

   posX = event.pageX; // Obtenemos pocision X del cursor
   posY = event.pageY; // Obtenemos pocision Y del cursor

   // Flecha que indica submenues
   //span = $('#' + menu_id + " span");
   //span.html("»");

   // Editando el codigo CSS para ciertos elementos

   $('#' + menu_id).css({position: 'absolute',display: 'block',top: posY,left: posX,cursor: 'default',width: '200px',height: 'auto',padding: '2px 9px 2px 2px',listStyle: 'none',listStyleType: 'none'});
$('#' + menu_id + " li ul").css({listStyle:'none',listStyleType:'none',cursor:'default',position:'absolute',left:'212px',marginTop:'-20px',width:'200px',height:'auto',padding:'2px 9px 2px 2px'});

	 console.log($('#' + menu_id));
  // Incluyo el tipo de imagen y el id en el ientificador
  $('#' + menu_id ).attr("id", menu_id + "_" + tipo + "_" + id);

	 console.log($('#' + menu_id+ "_" + tipo + "_" + id));
  }

function ocultar_menu() {
	console.log("ocultar menu");
   $("[id^='menu-images']").attr("id",'menu-images');
   $("[id^='menu-groups']").attr("id",'menu-groups');
   $("[id^='menu-tipes']").attr("id",'menu-tipes');
   $("#menu-images").css({display: 'none'});
   $("#menu-groups").css({display: 'none'});
   $("#menu-tipes").css({display: 'none'});
}
