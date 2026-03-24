<?php

namespace App\Entity\AWS\EC2;

use App\Repository\AWS\EC2\SecurityGroupRuleRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SecurityGroupRuleRepository::class)]
class SecurityGroupRule
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('IpProtocol')]
    #[Assert\Length(max: 255)]
    private ?string $ipProtocol = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('FromPort')]
    private ?int $fromPort = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('ToPort')]
    private ?int $toPort = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $cidrIp = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $cidrIpv6 = null;

    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\Choice(choices: ['ingress', 'egress'])]
    private ?string $direction = null;

    #[ORM\ManyToOne(inversedBy: 'rules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SecurityGroup $securityGroup = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpProtocol(): ?string
    {
        return $this->ipProtocol;
    }

    public function setIpProtocol(?string $ipProtocol): static
    {
        $this->ipProtocol = $ipProtocol;
        return $this;
    }

    public function getFromPort(): ?int
    {
        return $this->fromPort;
    }

    public function setFromPort(?int $fromPort): static
    {
        $this->fromPort = $fromPort;
        return $this;
    }

    public function getToPort(): ?int
    {
        return $this->toPort;
    }

    public function setToPort(?int $toPort): static
    {
        $this->toPort = $toPort;
        return $this;
    }

    public function getCidrIp(): ?string
    {
        return $this->cidrIp;
    }

    public function setCidrIp(?string $cidrIp): static
    {
        $this->cidrIp = $cidrIp;
        return $this;
    }

    public function getCidrIpv6(): ?string
    {
        return $this->cidrIpv6;
    }

    public function setCidrIpv6(?string $cidrIpv6): static
    {
        $this->cidrIpv6 = $cidrIpv6;
        return $this;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function setDirection(?string $direction): static
    {
        $this->direction = $direction;
        return $this;
    }

    public function getSecurityGroup(): ?SecurityGroup
    {
        return $this->securityGroup;
    }

    public function setSecurityGroup(?SecurityGroup $securityGroup): static
    {
        $this->securityGroup = $securityGroup;
        return $this;
    }

    public function getPortRange(): string
    {
        if ($this->ipProtocol === '-1') {
            return 'All';
        }

        if ($this->fromPort === $this->toPort) {
            return (string) $this->fromPort;
        }

        return $this->fromPort . '-' . $this->toPort;
    }

    public function getSource(): ?string
    {
        return $this->cidrIp ?? $this->cidrIpv6;
    }
}
