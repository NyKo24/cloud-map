<?php

namespace App\Entity\AWS\EC2;

use App\Entity\CrawlVersion;
use App\Repository\AWS\EC2\Ec2InstanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: Ec2InstanceRepository::class)]
class Ec2Instance
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('InstanceId')]
    #[Assert\Length(max: 255)]
    #[Groups(['ec2_instance_list_export'])]
    private ?string $instanceId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('InstanceType')]
    #[Assert\Length(max: 255)]
    #[Groups(['ec2_instance_list_export'])]
    private ?string $instanceType = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('State')]
    #[Groups(['ec2_instance_list_export'])]
    private ?array $state = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('PlatformDetails')]
    #[Assert\Length(max: 255)]
    #[Groups(['ec2_instance_list_export'])]
    private ?string $platformDetails = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Architecture')]
    #[Assert\Length(max: 255)]
    #[Groups(['ec2_instance_list_export'])]
    private ?string $architecture = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('PrivateIpAddress')]
    #[Assert\Length(max: 45)]
    #[Groups(['ec2_instance_list_export'])]
    private ?string $privateIpAddress = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('PublicIpAddress')]
    #[Assert\Length(max: 45)]
    #[Groups(['ec2_instance_list_export'])]
    private ?string $publicIpAddress = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('VpcId')]
    #[Assert\Length(max: 255)]
    #[Groups(['ec2_instance_list_export'])]
    private ?string $vpcId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('SubnetId')]
    #[Assert\Length(max: 255)]
    #[Groups(['ec2_instance_list_export'])]
    private ?string $subnetId = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[SerializedName('LaunchTime')]
    #[Groups(['ec2_instance_list_export'])]
    private ?\DateTimeImmutable $launchTime = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('ImageId')]
    #[Assert\Length(max: 255)]
    #[Groups(['ec2_instance_list_export'])]
    private ?string $imageId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('KeyName')]
    #[Assert\Length(max: 255)]
    #[Groups(['ec2_instance_list_export'])]
    private ?string $keyName = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('Placement')]
    private ?array $placement = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('Monitoring')]
    private ?array $monitoring = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('SecurityGroups')]
    private ?array $securityGroups = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('Tags')]
    private ?array $tags = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('PrivateDnsName')]
    #[Assert\Length(max: 255)]
    private ?string $privateDnsName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('PublicDnsName')]
    #[Assert\Length(max: 255)]
    private ?string $publicDnsName = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[SerializedName('EbsOptimized')]
    private ?bool $ebsOptimized = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[SerializedName('EnaSupport')]
    private ?bool $enaSupport = null;

    #[ORM\ManyToOne(inversedBy: 'ec2Instances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstanceId(): ?string
    {
        return $this->instanceId;
    }

    public function setInstanceId(?string $instanceId): static
    {
        $this->instanceId = $instanceId;
        return $this;
    }

    public function getInstanceType(): ?string
    {
        return $this->instanceType;
    }

    public function setInstanceType(?string $instanceType): static
    {
        $this->instanceType = $instanceType;
        return $this;
    }

    public function getState(): ?array
    {
        return $this->state;
    }

    public function setState(?array $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function getStateName(): ?string
    {
        return $this->state['Name'] ?? null;
    }

    public function getStateCode(): ?int
    {
        return $this->state['Code'] ?? null;
    }

    public function getPlatformDetails(): ?string
    {
        return $this->platformDetails;
    }

    public function setPlatformDetails(?string $platformDetails): static
    {
        $this->platformDetails = $platformDetails;
        return $this;
    }

    public function getArchitecture(): ?string
    {
        return $this->architecture;
    }

    public function setArchitecture(?string $architecture): static
    {
        $this->architecture = $architecture;
        return $this;
    }

    public function getPrivateIpAddress(): ?string
    {
        return $this->privateIpAddress;
    }

    public function setPrivateIpAddress(?string $privateIpAddress): static
    {
        $this->privateIpAddress = $privateIpAddress;
        return $this;
    }

    public function getPublicIpAddress(): ?string
    {
        return $this->publicIpAddress;
    }

    public function setPublicIpAddress(?string $publicIpAddress): static
    {
        $this->publicIpAddress = $publicIpAddress;
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

    public function getSubnetId(): ?string
    {
        return $this->subnetId;
    }

    public function setSubnetId(?string $subnetId): static
    {
        $this->subnetId = $subnetId;
        return $this;
    }

    public function getLaunchTime(): ?\DateTimeImmutable
    {
        return $this->launchTime;
    }

    public function setLaunchTime(?\DateTimeImmutable $launchTime): static
    {
        $this->launchTime = $launchTime;
        return $this;
    }

    public function getImageId(): ?string
    {
        return $this->imageId;
    }

    public function setImageId(?string $imageId): static
    {
        $this->imageId = $imageId;
        return $this;
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

    public function getPlacement(): ?array
    {
        return $this->placement;
    }

    public function setPlacement(?array $placement): static
    {
        $this->placement = $placement;
        return $this;
    }

    public function getMonitoring(): ?array
    {
        return $this->monitoring;
    }

    public function setMonitoring(?array $monitoring): static
    {
        $this->monitoring = $monitoring;
        return $this;
    }

    public function getSecurityGroups(): ?array
    {
        return $this->securityGroups;
    }

    public function setSecurityGroups(?array $securityGroups): static
    {
        $this->securityGroups = $securityGroups;
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

    public function getPrivateDnsName(): ?string
    {
        return $this->privateDnsName;
    }

    public function setPrivateDnsName(?string $privateDnsName): static
    {
        $this->privateDnsName = $privateDnsName;
        return $this;
    }

    public function getPublicDnsName(): ?string
    {
        return $this->publicDnsName;
    }

    public function setPublicDnsName(?string $publicDnsName): static
    {
        $this->publicDnsName = $publicDnsName;
        return $this;
    }

    public function isEbsOptimized(): ?bool
    {
        return $this->ebsOptimized;
    }

    public function setEbsOptimized(?bool $ebsOptimized): static
    {
        $this->ebsOptimized = $ebsOptimized;
        return $this;
    }

    public function isEnaSupport(): ?bool
    {
        return $this->enaSupport;
    }

    public function setEnaSupport(?bool $enaSupport): static
    {
        $this->enaSupport = $enaSupport;
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
}
