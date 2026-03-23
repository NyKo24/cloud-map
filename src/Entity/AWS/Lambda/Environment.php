<?php

namespace App\Entity\AWS\Lambda;

use App\Repository\AWS\Lambda\EnvironmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: EnvironmentRepository::class)]
#[ORM\Table(name: 'lambda_environment')]
class Environment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('Variables')]
    private ?array $variables = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('Error')]
    private ?array $error = null;

    #[ORM\OneToOne(inversedBy: 'environment')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LambdaFunction $lambdaFunction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVariables(): ?array
    {
        return $this->variables;
    }

    public function setVariables(?array $variables): static
    {
        $this->variables = $variables;
        return $this;
    }

    public function getError(): ?array
    {
        return $this->error;
    }

    public function setError(?array $error): static
    {
        $this->error = $error;
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