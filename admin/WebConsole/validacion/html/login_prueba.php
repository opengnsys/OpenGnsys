<?php
$path_parts = pathinfo(__FILE__);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>OpenGnsys :::: Inicio de equipo - Menú de opciones</title>
<style>
        body {
        margin:0;
        padding:0;
        background-color:#FFFFFF;
}
#contenedor{
        position:absolute;
        /*width:1270px;
        height:1014px;
	*/
        margin:0;
        padding:0;
/*
        left: 50%;
        top: 50%;
        margin-top: -512px;
        margin-left: -640px;
*/
        overflow:hidden;
}
.boton{
        margin:0 0 0 40px;
}
#form1{
        margin:120px 0 0 200px;
}
img {
        vertical-align:middle;
}
a:link, a:visited { text-decoration: none; color:#ffffff; }
a:hover, a:active { color:#ffff00; }
/*#menu{
        width:800px;
        height:600px;

}*/
h1{
        margin:120px auto 0px auto;
        padding:0;
        width:624px;
        height:0px;
        /*background:url(../menus/imagenes/titulo.png);*/
}

.tituloElementoLista {
        padding:0 0 0 10px;
}
.descripcionElementoLista {
        color:#acd6ff;
        position:absolute;
        left:360px;
        padding-top:15px;
}
</style>

</head>

<body>
	<div id="contenedor" style="margin: 10px 300px 15px 300px">
	<div align="center">
		<h1>
		<div style="float:left">
			<img src="../validacion/html/images/opengnsys.png" />
		</div>
		Validaci&oacute;n de usuario</h1>
	</div>
        <div id="menu" style="margin: 0px auto 0px auto">
                <h1></h1>
                <form id="form1" name="form1" method="post" action="../validacion/access_controller.php">
			<div>
				usuario:
			</div>
			<div>
        	        	<input type="text" name="login" id="login">
			</div>
	                <br />
			<div>
				password:
			</div>
			<div>
				<input type="password" name="password" id="password">
			</div>
                	<br />
                	<p><span class="boton"><input type="submit" name="access_button" id="access_button" value="Acceder" /></span></p>
        		<input name="action" type="hidden" value="validate" />
    		</form>
    		<span class="descripcionElementoLista"><?php if(isset($_error)) echo $_error; ?></span>
        </div>
    </div>
</body>
</html>

