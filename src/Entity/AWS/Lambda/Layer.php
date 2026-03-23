<?php

namespace App\Entity\AWS\Lambda;

use App\Repository\AWS\Lambda\LayerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LayerRepository::class)]
#[ORM\Table(name: 'lambda_layer')]
class Layer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Arn')]
    #[Assert\Length(max: 1024)]
    private ?string $arn = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    #[SerializedName('CodeSize')]
    private ?int $codeSize = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('SigningProfileVersionArn')]
    #[Assert\Length(max: 1024)]
    private ?string $signingProfileVersionArn = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('SigningJobArn')]
    #[Assert\Length(max: 1024)]
    private ?string $signingJobArn = null;

    #[ORM\ManyToOne(targetEntity: LambdaFunction::class, inversedBy: 'layers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LambdaFunction $lambdaFunction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArn(): ?string
    {
        return $this->arn;
    }

    public function setArn(?string $arn): static
    {
        $this->arn = $arn;
        return $this;
    }

    public function getCodeSize(): ?int
    {
        return $this->codeSize;
    }

    public function setCodeSize(?int $codeSize): static
    {
        $this->codeSize = $codeSize;
        return $this;
    }

    public function getSigningProfileVersionArn(): ?string
    {
        return $this->signingProfileVersionArn;
    }

    public function setSigningProfileVersionArn(?string $signingProfileVersionArn): static
    {
        $this->signingProfileVersionArn = $signingProfileVersionArn;
        return $this;
    }

    public function getSigningJobArn(): ?string
    {
        return $this->signingJobArn;
    }

    public function setSigningJobArn(?string $signingJobArn): static
    {
        $this->signingJobArn = $signingJobArn;
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