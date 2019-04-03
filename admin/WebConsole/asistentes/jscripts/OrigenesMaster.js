// ListarOrigenesMaster.js: Solicita al servidor las particiones e imágenes en cache en el equipo master
//    disponibles para CloneRemotePartition y las devuelve en una lista de selección.
function ListarOrigenesMaster(ip) {
    var ajaxRequest;

    // Soporte a distintos navegadores
    try {
        // Navegadores modernos
        ajaxRequest = new XMLHttpRequest();
    } catch (e) {
        // Internet Explorer
        try {
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
                alert(TbMsg["NOSUPPORT"]);
                return false;
            }
        }
    }

    ajaxRequest.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var ajaxDisplay = document.getElementById('ajaxDiv');
            ajaxDisplay.innerHTML = this.responseText;
        }
    };

    ajaxRequest.open("GET", "includes/asistentes/ListarOrigenesMaster.php?ip=" + ip, true);
    ajaxRequest.send();
}
