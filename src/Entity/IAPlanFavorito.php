<?php

namespace App\Entity;

use App\Repository\IAPlanFavoritoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IAPlanFavoritoRepository::class)]
#[ORM\Table(name: "ia_plan_favorito")]
class IAPlanFavorito
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenido = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fechaGuardado = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenido(): ?string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): static
    {
        $this->contenido = $contenido;

        return $this;
    }

    public function getFechaGuardado(): ?\DateTimeImmutable
    {
        return $this->fechaGuardado;
    }

    public function setFechaGuardado(\DateTimeImmutable $fechaGuardado): static
    {
        $this->fechaGuardado = $fechaGuardado;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

}
