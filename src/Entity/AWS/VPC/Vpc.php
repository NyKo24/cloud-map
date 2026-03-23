<?php

namespace App\Entity\AWS\VPC;

use App\Entity\AWS\Tag;
use App\Entity\CrawlVersion;
use App\Enum\AWS\Tenancy;
use App\Enum\AWS\VPC\VpcState;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'vpc')]
class Vpc
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('CidrBlock')]
    #[Assert\Ip(version: 'all', message: 'Invalid CIDR block format')]
    private ?string $cidrBlock = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('DhcpOptionsId')]
    #[Assert\Length(max: 255)]
    private ?string $dhcpOptionsId = null;

    #[ORM\Column(type: 'string', enumType: VpcState::class, nullable: true)]
    #[SerializedName('State')]
    private ?VpcState $state = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('VpcId')]
    #[Assert\Length(max: 255)]
    private ?string $vpcId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('OwnerId')]
    #[Assert\Length(max: 255)]
    private ?string $ownerId = null;

    #[ORM\Column(type: 'string', enumType: Tenancy::class, nullable: true)]
    #[SerializedName('InstanceTenancy')]
    private ?Tenancy $instanceTenancy = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[SerializedName('IsDefault')]
    private ?bool $isDefault = null;

    /**
     * @var Collection<VpcIpv6CidrBlockAssociation>|VpcIpv6CidrBlockAssociation[]
     */
    #[ORM\OneToMany(targetEntity: VpcIpv6CidrBlockAssociation::class, mappedBy: 'vpc', cascade: ['persist', 'remove'])]
    private Collection $ipv6CidrBlockAssociationSet;

    /**
     * @var VpcCidrBlockAssociation[]
     */
    #[ORM\OneToMany(targetEntity: VpcCidrBlockAssociation::class, mappedBy: 'vpc', cascade: ['persist', 'remove'])]
    private Collection $cidrBlockAssociationSet;

    /**
     * @var Collection<Tag>|Tag[]
     * @extends ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: Tag::class, mappedBy: 'vpc', cascade: ['persist', 'remove'])]
    private Collection $tags;

    #[SerializedName('BlockPublicAccessStates')]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?BlockPublicAccessStates $blockPublicAccessStates = null;

    #[ORM\ManyToOne(inversedBy: 'vpcs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function __construct()
    {
        $this->ipv6CidrBlockAssociationSet = new ArrayCollection();
        $this->cidrBlockAssociationSet = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwnerId(): ?string
    {
        return $this->ownerId;
    }

    public function setOwnerId(?string $OwnerId): static
    {
        $this->ownerId = $OwnerId;

        return $this;
    }

    public function getCidrBlock(): ?string
    {
        return $this->cidrBlock;
    }

    public function setCidrBlock(string $CidrBlock): static
    {
        $this->cidrBlock = $CidrBlock;

        return $this;
    }

    public function getDhcpOptionsId(): ?string
    {
        return $this->dhcpOptionsId;
    }

    public function setDhcpOptionsId(?string $DhcpOptionsId): static
    {
        $this->dhcpOptionsId = $DhcpOptionsId;

        return $this;
    }

    public function getInstanceTenancy(): ?Tenancy
    {
        return $this->instanceTenancy;
    }

    public function setInstanceTenancy(?Tenancy $instanceTenancy): void
    {
        $this->instanceTenancy = $instanceTenancy;
    }

    public function isDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(?bool $IsDefault): static
    {
        $this->isDefault = $IsDefault;

        return $this;
    }

    public function getState(): ?VpcState
    {
        return $this->state;
    }

    public function setState(?VpcState $State): static
    {
        $this->state = $State;

        return $this;
    }

    public function getVpcId(): ?string
    {
        return $this->vpcId;
    }

    public function setVpcId(?string $VpcId): static
    {
        $this->vpcId = $VpcId;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tagSet): static
    {
        if (!$this->tags->contains($tagSet)) {
            $this->tags->add($tagSet);
            $tagSet->setVpc($this);
        }

        return $this;
    }

    public function removeTag(Tag $tagSet): static
    {
        if ($this->tags->removeElement($tagSet)) {
            // set the owning side to null (unless already changed)
            if ($tagSet->getVpc() === $this) {
                $tagSet->setVpc(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, VpcIpv6CidrBlockAssociation>
     */
    public function getIpv6CidrBlockAssociationSet(): Collection
    {
        return $this->ipv6CidrBlockAssociationSet;
    }

    public function addIpv6CidrBlockAssociation(VpcIpv6CidrBlockAssociation $ipv6CidrBlockAssociation): static
    {
        if (!$this->ipv6CidrBlockAssociationSet->contains($ipv6CidrBlockAssociation)) {
            $this->ipv6CidrBlockAssociationSet->add($ipv6CidrBlockAssociation);
            $ipv6CidrBlockAssociation->setVpc($this);
        }

        return $this;
    }

    public function removeIpv6CidrBlockAssociation(VpcIpv6CidrBlockAssociation $ipv6CidrBlockAssociation): static
    {
        if ($this->ipv6CidrBlockAssociationSet->removeElement($ipv6CidrBlockAssociation)) {
            // set the owning side to null (unless already changed)
            if ($ipv6CidrBlockAssociation->getVpc() === $this) {
                $ipv6CidrBlockAssociation->setVpc(null);
            }
        }

        return $this;
    }

    public function getCidrBlockAssociationSet(): Collection
    {
        return $this->cidrBlockAssociationSet;
    }

    public function setCidrBlockAssociationSet(Collection $cidrBlockAssociationSet): self
    {
        $this->cidrBlockAssociationSet = $cidrBlockAssociationSet;

        return $this;
    }

    public function addCidrBlockAssociation(VpcCidrBlockAssociation $cidrBlockAssociation): static
    {
        if (!$this->cidrBlockAssociationSet->contains($cidrBlockAssociation)) {
            $this->cidrBlockAssociationSet->add($cidrBlockAssociation);
            $cidrBlockAssociation->setVpc($this);
        }

        return $this;
    }

    public function removeCidrBlockAssociation(VpcCidrBlockAssociation $cidrBlockAssociation): static
    {
        if ($this->cidrBlockAssociationSet->removeElement($cidrBlockAssociation)) {
            // set the owning side to null (unless already changed)
            if ($cidrBlockAssociation->getVpc() === $this) {
                $cidrBlockAssociation->setVpc(null);
            }
        }

        return $this;
    }

    public function getBlockPublicAccessStates(): ?BlockPublicAccessStates
    {
        return $this->blockPublicAccessStates;
    }

    public function setBlockPublicAccessStates(?BlockPublicAccessStates $blockPublicAccessStates): static
    {
        $this->blockPublicAccessStates = $blockPublicAccessStates;

        return $this;
    }

    public function getCrawl(): ?CrawlVersion
    {
        return $this->crawl;
    }

    public function setCrawl(?CrawlVersion $crawl): static
    {
        $this->crawl = $crawl;

        return $this;
    }
}
