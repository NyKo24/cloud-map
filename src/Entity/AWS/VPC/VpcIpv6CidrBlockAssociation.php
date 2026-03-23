<?php

namespace App\Entity\AWS\VPC;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'vpc_ipv6_cidr_block_association')]
class VpcIpv6CidrBlockAssociation
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vpc::class, inversedBy: 'ipv6CidrBlockAssociationSet')]
    private Vpc $vpc;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('AssociationId')]
    #[Assert\Length(max: 255)]
    private ?string $associationId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Ipv6CidrBlock')]
    #[Assert\Ip(version: 'all', message: 'Invalid IPv6 CIDR block format')]
    private ?string $ipv6CidrBlock = null;

    #[ORM\ManyToOne(cascade: ['all'])]
    #[SerializedName('Ipv6CidrBlockState')]
    private ?VpcCidrBlockState $ipv6CidrBlockState = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVpc(): Vpc
    {
        return $this->vpc;
    }

    public function setVpc(?Vpc $vpc): void
    {
        $this->vpc = $vpc;
    }

    public function getAssociationId(): ?string
    {
        return $this->associationId;
    }

    public function setAssociationId(?string $associationId): void
    {
        $this->associationId = $associationId;
    }

    public function getIpv6CidrBlock(): ?string
    {
        return $this->ipv6CidrBlock;
    }

    public function setIpv6CidrBlock(?string $ipv6CidrBlock): void
    {
        $this->ipv6CidrBlock = $ipv6CidrBlock;
    }

    public function getIpv6CidrBlockState(): ?VpcCidrBlockState
    {
        return $this->ipv6CidrBlockState;
    }

    public function setIpv6CidrBlockState(?VpcCidrBlockState $ipv6CidrBlockState): static
    {
        $this->ipv6CidrBlockState = $ipv6CidrBlockState;

        return $this;
    }


}
