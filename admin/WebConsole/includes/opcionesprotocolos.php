<?
/**
 * @file: opcionesprotocolos.php
 * @brief: Toma los parametros de mcast y torrent para mostrarlos en las paginas de restaurar imagen (monoliticas y sincronizadas)
 * @date: 2013-11-25
 * @copyright GNU Public License v3+
 */


function mcast_syntax($cmd,$ambito,$idambito)
{
//if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if ($ambito == 4)
{
$cmd->texto='SELECT pormul, ipmul, modomul, velmul, puestos FROM aulas
                WHERE aulas.idaula=' . $idambito ;
}

if ($ambito == 8)
{
$cmd->texto='SELECT pormul, ipmul, modomul, velmul, puestos FROM aulas
                JOIN gruposordenadores ON aulas.idaula=gruposordenadores.idaula
                WHERE gruposordenadores.idgrupo=' . $idambito ;
}

if ($ambito == 16)
{
$cmd->texto='SELECT pormul, ipmul, modomul, velmul, puestos FROM aulas
                JOIN ordenadores ON ordenadores.idaula=aulas.idaula
                WHERE ordenadores.idordenador=' . $idambito ;
}
        $rs=new Recordset;
        $rs->Comando=&$cmd;
        if ($rs->Abrir()){
                $rs->Primero();
                $mcastsyntax = $rs->campos["pormul"] . ':';

                $rs->Siguiente();
                switch ($rs->campos["modomul"])
                {
                        case 1:
                                $mcastsyntax.="half-duplex:";
                                break;
                        default:
                                $mcastsyntax.="full-duplex:";
                                break;
                }
                $rs->Siguiente();
                $mcastsyntax.=$rs->campos["ipmul"] . ':';

                $rs->Siguiente();
                $mcastsyntax.=$rs->campos["velmul"] .'M:';

                $rs->Siguiente();
                $mcastsyntax.=$rs->campos["puestos"] . ':';

        $rs->Cerrar();
        }
        $mcastsyntax.="60";

        return($mcastsyntax);
}


function torrent_syntax($cmd,$ambito,$idambito)
{
if ($ambito == 4)
{
        $cmd->texto='SELECT modp2p, timep2p FROM aulas
                        WHERE aulas.idaula=' . $idambito ;
}
if ($ambito == 8)
{
        $cmd->texto='SELECT modp2p, timep2p FROM aulas
                        JOIN gruposordenadores ON aulas.idaula=gruposordenadores.idaula
                        WHERE gruposordenadores.idgrupo=' . $idambito ;
}
if ($ambito == 16)
{
        $cmd->texto='SELECT modp2p, timep2p FROM aulas
                        JOIN ordenadores ON ordenadores.idaula=aulas.idaula
                        WHERE ordenadores.idordenador=' . $idambito ;
}

$rs=new Recordset;
$rs->Comando=&$cmd;
if ($rs->Abrir()){
        $rs->Primero();
        $torrentsyntax=$rs->campos["modp2p"] . ':';
        $rs->Siguiente();
        $torrentsyntax.=$rs->campos["timep2p"];
        $rs->Siguiente();
        $rs->Cerrar();
}
return($torrentsyntax);
}



?>

