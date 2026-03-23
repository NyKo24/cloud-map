<?php

namespace App\Entity\AWS\VPC;

use App\Repository\AWS\VPC\VpcCidrBlockStateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: VpcCidrBlockStateRepository::class)]
class VpcCidrBlockState
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[SerializedName('State')]
    private ?string $state = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[SerializedName('StatusMessage')]
    private ?string $statusMessage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getStatusMessage(): ?string
    {
        return $this->statusMessage;
    }

    public function setStatusMessage(?string $statusMessage): static
    {
        $this->statusMessage = $statusMessage;

        return $this;
    }
}
