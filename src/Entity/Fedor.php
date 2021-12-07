<?php

namespace App\Entity;

use App\Repository\FedorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FedorRepository::class)
 */
class Fedor
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $orientation;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $secondary_orientation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrientation(): ?bool
    {
        return $this->orientation;
    }

    public function setOrientation(?bool $orientation): self
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function getSecondaryOrientation(): ?string
    {
        return $this->secondary_orientation;
    }

    public function setSecondaryOrientation(?string $secondary_orientation): self
    {
        $this->secondary_orientation = $secondary_orientation;

        return $this;
    }
}
