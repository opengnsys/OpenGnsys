<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\JsonSerializable;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    /**
     * @Ignore()
     */
    private $serializer;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="text")
     */
    private $script;

    /**
     * @ORM\Column(type="string", length=500, nullable=true, options={"default" : ""})
     */
    private $redirectUri;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default" : 0})
     */
    private $serverTaskId;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : false})
     */
    private $sendConfig;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getScript(): ?string
    {
        return $this->script;
    }

    public function setScript(string $script): self
    {
        $this->script = $script;

        return $this;
    }

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri(string $redirectUri): self
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    public function getServerTaskId(): ?int
    {
        return $this->serverTaskId;
    }

    public function setServerTaskId(int $serverTaskId): self
    {
        $this->serverTaskId = $serverTaskId;

        return $this;
    }

    public function getSendConfig(): ?bool
    {
        return $this->sendConfig;
    }

    public function setSendConfig(bool $sendConfig): self
    {
        $this->sendConfig = $sendConfig;

        return $this;
    }

    public function toJson() {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
        return $this->serializer->normalize($this, "json");
    }

}
