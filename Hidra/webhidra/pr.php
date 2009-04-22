<?php
	//phpInfo();
	$socket = socket_create (AF_INET, SOCK_STREAM,SOL_TCP);
	if (false==$socket ) {
		echo "<br>error al crear socket";
		echo "<br>codigo de error=".socket_last_error($socket);
		echo "<br>descricion del error:".socket_strerror(socket_last_error($socket));
	}
	else{
		$result = socket_connect ($socket,"10.1.15.5",2005);
		if (!$result ) {
			echo "<br>error al conectar";
			echo "<br>codigo de error=".socket_last_error($socket);
			echo "<br>descricion del error:".socket_strerror(socket_last_error($socket));
		}
		else
			echo "exito========================";
	}

?>