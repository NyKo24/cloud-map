<?php

namespace App\Entity\AWS\VPC;

use App\Repository\AWS\VPC\BlockPublicAccessStatesRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: BlockPublicAccessStatesRepository::class)]
class BlockPublicAccessStates
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 19, nullable: true)]
    private ?string $internetGatewayBlockMode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInternetGatewayBlockMode(): ?string
    {
        return $this->internetGatewayBlockMode;
    }

    public function setInternetGatewayBlockMode(?string $internetGatewayBlockMode): static
    {
        $this->internetGatewayBlockMode = $internetGatewayBlockMode;

        return $this;
    }
}
