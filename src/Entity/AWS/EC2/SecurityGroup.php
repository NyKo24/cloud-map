<?php

namespace App\Entity\AWS\EC2;

use App\Entity\CrawlVersion;
use App\Repository\AWS\EC2\SecurityGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SecurityGroupRepository::class)]
class SecurityGroup
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('GroupId')]
    #[Assert\Length(max: 255)]
    #[Groups(['security_group_list_export'])]
    private ?string $groupId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('GroupName')]
    #[Assert\Length(max: 255)]
    #[Groups(['security_group_list_export'])]
    private ?string $groupName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[SerializedName('Description')]
    #[Groups(['security_group_list_export'])]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('VpcId')]
    #[Assert\Length(max: 255)]
    #[Groups(['security_group_list_export'])]
    private ?string $vpcId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('OwnerId')]
    #[Assert\Length(max: 255)]
    #[Groups(['security_group_list_export'])]
    private ?string $ownerId = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('Tags')]
    private ?array $tags = null;

    /**
     * @var Collection<int, SecurityGroupRule>
     */
    #[ORM\OneToMany(targetEntity: SecurityGroupRule::class, mappedBy: 'securityGroup', cascade: ['persist'], orphanRemoval: true)]
    private Collection $rules;

    #[ORM\ManyToOne(inversedBy: 'securityGroups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function __construct()
    {
        $this->rules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(?string $groupId): static
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(?string $groupName): static
    {
        $this->groupName = $groupName;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    public function getOwnerId(): ?string
    {
        return $this->ownerId;
    }

    public function setOwnerId(?string $ownerId): static
    {
        $this->ownerId = $ownerId;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return Collection<int, SecurityGroupRule>
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function addRule(SecurityGroupRule $rule): static
    {
        if (!$this->rules->contains($rule)) {
            $this->rules->add($rule);
            $rule->setSecurityGroup($this);
        }

        return $this;
    }

    public function removeRule(SecurityGroupRule $rule): static
    {
        if ($this->rules->removeElement($rule)) {
            if ($rule->getSecurityGroup() === $this) {
                $rule->setSecurityGroup(null);
            }
        }

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

    public function getNameTag(): ?string
    {
        if ($this->tags === null) {
            return null;
        }

        foreach ($this->tags as $tag) {
            if (isset($tag['Key']) && $tag['Key'] === 'Name') {
                return $tag['Value'] ?? null;
            }
        }

        return null;
    }

    public function getIngressRules(): array
    {
        return $this->rules->filter(fn(SecurityGroupRule $rule) => $rule->getDirection() === 'ingress')->toArray();
    }

    public function getEgressRules(): array
    {
        return $this->rules->filter(fn(SecurityGroupRule $rule) => $rule->getDirection() === 'egress')->toArray();
    }
}
