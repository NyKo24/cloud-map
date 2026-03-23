<?php

namespace App\Entity\AWS\Lambda;

use App\Repository\AWS\Lambda\VpcConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: VpcConfigRepository::class)]
#[ORM\Table(name: 'lambda_vpc_config')]
class VpcConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('SubnetIds')]
    private ?array $subnetIds = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('SecurityGroupIds')]
    private ?array $securityGroupIds = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('VpcId')]
    private ?string $vpcId = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('Ipv6AllowedForDualStack')]
    private ?bool $ipv6AllowedForDualStack = null;

    #[ORM\OneToOne(inversedBy: 'vpcConfig')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LambdaFunction $lambdaFunction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubnetIds(): ?array
    {
        return $this->subnetIds;
    }

    public function setSubnetIds(?array $subnetIds): static
    {
        $this->subnetIds = $subnetIds;
        return $this;
    }

    public function getSecurityGroupIds(): ?array
    {
        return $this->securityGroupIds;
    }

    public function setSecurityGroupIds(?array $securityGroupIds): static
    {
        $this->securityGroupIds = $securityGroupIds;
        return $this;
    }

    public function getVpcId(): ?string
    {
        return $this->vpcId;
    }

    public function setVpcId(?string $vpcId): static
    {
        $this->vpcId = $vpcId;
        return $this;
    }

    public function isIpv6AllowedForDualStack(): ?bool
    {
        return $this->ipv6AllowedForDualStack;
    }

    public function setIpv6AllowedForDualStack(?bool $ipv6AllowedForDualStack): static
    {
        $this->ipv6AllowedForDualStack = $ipv6AllowedForDualStack;
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