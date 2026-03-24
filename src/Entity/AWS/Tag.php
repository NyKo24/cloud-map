<?php

namespace App\Entity\AWS;

use App\Entity\AWS\Lambda\LambdaFunction;
use App\Entity\AWS\VPC\Vpc;
use App\Repository\AWS\TagRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Key')]
    #[Assert\Length(max: 127)]
    private ?string $keyName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Value')]
    #[Assert\Length(max: 256)]
    private ?string $value = null;

    #[ORM\ManyToOne(inversedBy: 'tags')]
    private ?VPC $vpc = null;

    #[ORM\ManyToOne(inversedBy: 'tags')]
    private ?LambdaFunction $lambdaFunction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyName(): ?string
    {
        return $this->keyName;
    }

    public function setKeyName(?string $keyName): static
    {
        $this->keyName = $keyName;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getVpc(): ?VPC
    {
        return $this->vpc;
    }

    public function setVpc(?VPC $vpc): static
    {
        $this->vpc = $vpc;

        return $this;
    }

    public function getLambdaFunction(): ?LambdaFunction
    {
        return $this->lambdaFunction;
    }

    public function setLambdaFunction(?LambdaFunction $lambdaFunction): static
    {
        $this->lambdaFunction = $lambdaFunction;

        return $this;
    }
}
