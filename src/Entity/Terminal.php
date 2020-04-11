<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerminalRepository")
 */
class Terminal
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @AcmeAssert\IsAHostname
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Ip
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $connectedTo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $connectionState;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ping;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $connectedToLabel;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getConnectedTo(): ?string
    {
        return $this->connectedTo;
    }

    public function setConnectedTo(?string $connectedTo): self
    {
        $this->connectedTo = $connectedTo;

        return $this;
    }

    public function getConnectionState(): ?string
    {
        return $this->connectionState;
    }

    public function setConnectionState(?string $connectionState): self
    {
        $this->connectionState = $connectionState;

        return $this;
    }

    public function getPing(): ?string
    {
        return $this->ping;
    }

    public function setPing(?string $ping): self
    {
        $this->ping = $ping;

        return $this;
    }

    public function getLogged(): ?string
    {
        return $this->logged;
    }

    public function setLogged(?string $logged): self
    {
        $this->logged = $logged;

        return $this;
    }

    public function getConnectedToLabel(): ?string
    {
        return $this->connectedToLabel;
    }

    public function setConnectedToLabel(?string $connectedToLabel): self
    {
        $this->connectedToLabel = $connectedToLabel;

        return $this;
    }
}
