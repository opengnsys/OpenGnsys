<?php

namespace App\Entity;

use App\Repository\OgClientRepository;
use Doctrine\ORM\Mapping as ORM;
use const Grpc\STATUS_UNKNOWN;

/**
 * @ORM\Entity(repositoryClass=OgClientRepository::class)
 */
class OgClient
{
    public const STATUS_OFF = 0;
    public const STATUS_INI = 1;
    public const STATUS_OPG = 2;
    public const STATUS_BSY = 3;
    public const STATUS_LNX = 4;
    public const STATUS_MAC = 5;
    public const STATUS_WIN = 6;
    public const STATUS_UNK = 7;

    private const OGSTATUS = ['off', 'initializing','oglive','busy','linux','macos','windows', 'unknown'];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $mac;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $serialNumber;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $configuration;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getStatus(): ?string
    {
        return ogClient::OGSTATUS[$this->status];
    }

    public function setStatus(?int $status): self
    {
        if(!is_numeric($status) || $status < OgClient::STATUS_OFF || $status > OgClient::STATUS_UNK){
            $status  = OgClient::STATUS_UNK;
        }
        $this->status = $status;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getMac(): ?string
    {
        return $this->mac;
    }

    public function setMac(?string $mac): self
    {
        $this->mac = $mac;

        return $this;
    }

    public function getConfiguration(): ?string
    {
        return $this->configuration;
    }


    public function updateIp(): string {
        // Obtener la ip
        $this->ip = trim(shell_exec("ogGetIpAddress"));
        return $this->ip;
    }

    public function updateMac(): string {
        $this->mac = trim(shell_exec("ogGetMacAddress"));
        return $this->mac;
    }

    public function updateConfiguration($background = false): self
    {

        // Obtener la ip
        $this->ip = trim(shell_exec("ogGetIpAddress"));
        $this->mac = trim(shell_exec("ogGetMacAddress"));

        // ejecutar getConfiguration
        /**
         * Salida del script:
        ser=CZC229481H
        disk=1  par=0   cpt=1   fsi=    soi=    tam=234431064   uso=0
        disk=1  par=1   cpt=7   fsi=NTFS        soi=Windows 10 Enterprise 2009 64 bits  tam=234198584   uso=94
        disk=2  par=0   cpt=1   fsi=    soi=    tam=488386584   uso=0
        disk=2  par=1   cpt=7   fsi=EMPTY       soi=    tam=100000000   uso=0
        disk=2  par=2   cpt=83  fsi=EMPTY       soi=    tam=80000000    uso=0
        disk=2  par=3   cpt=    fsi=EMPTY       soi=    tam=0   uso=0
        disk=2  par=4   cpt=ca  fsi=CACHE       soi=    tam=205000000   uso=71
         */
        $config = ogClient::executeScript("/opt/opengnsys/interfaceAdm/getConfiguration", !$background);

        if($config !== null){
            $lines = explode("\n", $config);
            if(count($lines) > 0){
                $serial = explode("ser=", $lines[0]);
                if(count($serial) > 0){
                    $this->serialNumber = $serial[1];
                }
                else{
                    $this->serialNumber = "";
                }
            }
            $disks = [];
            // El resto de lineas son los discos
            for($i = 1; $i < count($lines); $i++) {
                $line = trim(preg_replace('/\s+/', ' ', $lines[$i]));
                if($line !== ""){
                    $disk = $this->getTagValue("disk", $line, "par");
                    $part = $this->getTagValue("par", $line, "cpt");
                    $partCode = $this->getTagValue("cpt", $line, "fsi");
                    $fileSystem = $this->getTagValue("fsi", $line, "soi");
                    $os = $this->getTagValue("soi", $line, "tam");
                    $size = $this->getTagValue("tam", $line, "uso");
                    $usage = $this->getTagValue("uso", $line);

                    if(empty($disks[$disk-1])) {
                        $disks[$disk - 1]["number"] = $disk;
                        $disks[$disk - 1]["partitions"] = [];
                    }
                    $disks[$disk-1]["partitions"][$part]["number"] = $part;
                    $disks[$disk-1]["partitions"][$part]["code"] = $partCode;
                    $disks[$disk-1]["partitions"][$part]["fileSystem"] = $fileSystem;
                    $disks[$disk-1]["partitions"][$part]["os"] = $os;
                    $disks[$disk-1]["partitions"][$part]["size"] = $size;
                    $disks[$disk-1]["partitions"][$part]["usage"] = $usage;

                    // Si la partición es cache, enviamos su contenido también
                    if($partCode === "ca"){
                        $content = shell_exec("du -h \$OGCAC/\$OGIMG/*");
                        // Analizar linea por linea
                        $lines = explode("\n", $content);
                        $content = [];
                        foreach ($lines as $line){
                            $parts = explode("\t", $line);
                            if(count($parts) == 2) {
                                // Primero tamaño espacio fichero
                                $content[] = str_replace(",",".",$parts[0]) . " " . substr($parts[1], strrpos($parts[1], "/")+1);
                            }
                        }
                        $freeSpace = intval(trim(shell_exec("ogGetFreeSize `ogFindCache`")));
                        $content = intval(($freeSpace/1024))." MB,".implode(",", $content);

                        $disks[$disk-1]["partitions"][$part]["content"] = $content;

                    }
                }
            }

            // guardar la configuración en la base de datos
            $this->configuration = json_encode($disks);
        }

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    private function getTagValue($tag, $line, $endTag = ""): string{
        // partimos de la posición de la etiqueta más el signo =
        $start = strpos($line, $tag)+(strlen($tag)+1);
        $end = strlen($line);
        if($endTag !== "") {
            $end = strpos($line, $endTag);
        }

        $result = trim(substr($line, $start, ($end-$start)));

        return $result;
    }

    public static function executeScript($script, $showBrowser = true) {

        $serverIp = trim(shell_exec("ogGetServerIp"));
        // Crear fichero temporal
        $file="/tmp/ogAdmClient";
        file_put_contents($file,"#!/bin/bash\n");
        file_put_contents($file, "set -a\n",FILE_APPEND | LOCK_EX);
        file_put_contents($file, "source /opt/opengnsys/etc/preinit/loadenviron.sh > /dev/null 2> /dev/null\n",FILE_APPEND | LOCK_EX);
        if($showBrowser === true) {
            # Activa navegador para ver progreso
            file_put_contents($file, "coproc /opt/opengnsys/bin/browser -qws http://localhost/cgi-bin/httpd-log.sh > /dev/null 2> /dev/null\n", FILE_APPEND | LOCK_EX);
        }
        // Informar del estado en ejecución
        file_put_contents($file, "\$OGAGENTCONSOLE SendStatus 3\n",FILE_APPEND | LOCK_EX);
        file_put_contents($file, $script."\n",FILE_APPEND | LOCK_EX);
        // Informar del estado oglive
        file_put_contents($file, "\$OGAGENTCONSOLE SendStatus 2\n",FILE_APPEND | LOCK_EX);
        if($showBrowser === true) {
            // TODO - comentar, al hacer kill $COPROC_ID luego no vuelve a recargar la pagina anterior del browser, hay que matar ambos browser y relanzar?
            file_put_contents($file, "pkill -9 browser\n", FILE_APPEND | LOCK_EX);
            file_put_contents($file, "browser -qws https://" . $serverIp . "/opengnsys/varios/menubrowser.php > /dev/null 2> /dev/null &\n", FILE_APPEND | LOCK_EX);
        }
        // TODO prueba
        $exePath="/bin/php_root";
        $result = shell_exec($exePath." ".$file);
        return $result;
    }

    public function setConfiguration(?string $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }
}
