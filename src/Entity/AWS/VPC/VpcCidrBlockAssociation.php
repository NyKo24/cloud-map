<?php

namespace App\Entity\AWS\VPC;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'vpc_cidr_block_association')]
class VpcCidrBlockAssociation
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vpc::class, inversedBy: 'cidrBlockAssociationSet')]
    private Vpc $vpc;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('AssociationId')]
    #[Assert\Length(max: 255)]
    private ?string $associationId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('CidrBlock')]
    #[Assert\Ip(version: 'all', message: 'Invalid IPv4 CIDR block format')]
    private ?string $cidrBlock = null;

    #[ORM\ManyToOne(cascade: ['all'])]
    #[SerializedName('CidrBlockState')]
    private ?VpcCidrBlockState $cidrBlockState = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
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

    public function getCidrBlock(): ?string
    {
        return $this->cidrBlock;
    }

    public function setCidrBlock(?string $cidrBlock): void
    {
        $this->cidrBlock = $cidrBlock;
    }

    public function getCidrBlockState(): ?VpcCidrBlockState
    {
        return $this->cidrBlockState;
    }

    public function setCidrBlockState(?VpcCidrBlockState $cidrBlockState): static
    {
        $this->cidrBlockState = $cidrBlockState;

        return $this;
    }
}
