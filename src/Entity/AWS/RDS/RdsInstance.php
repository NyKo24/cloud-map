<?php

namespace App\Entity\AWS\RDS;

use App\Entity\CrawlVersion;
use App\Repository\AWS\RDS\RdsInstanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RdsInstanceRepository::class)]
class RdsInstance
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('DBInstanceIdentifier')]
    #[Assert\Length(max: 255)]
    #[Groups(['rds_instance_list_export'])]
    private ?string $dbInstanceIdentifier = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('DBInstanceClass')]
    #[Assert\Length(max: 255)]
    #[Groups(['rds_instance_list_export'])]
    private ?string $dbInstanceClass = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Engine')]
    #[Assert\Length(max: 255)]
    #[Groups(['rds_instance_list_export'])]
    private ?string $engine = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('EngineVersion')]
    #[Assert\Length(max: 255)]
    #[Groups(['rds_instance_list_export'])]
    private ?string $engineVersion = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('DBInstanceStatus')]
    #[Assert\Length(max: 255)]
    #[Groups(['rds_instance_list_export'])]
    private ?string $dbInstanceStatus = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('MasterUsername')]
    #[Assert\Length(max: 255)]
    #[Groups(['rds_instance_list_export'])]
    private ?string $masterUsername = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('AllocatedStorage')]
    #[Groups(['rds_instance_list_export'])]
    private ?int $allocatedStorage = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('AvailabilityZone')]
    #[Assert\Length(max: 255)]
    #[Groups(['rds_instance_list_export'])]
    private ?string $availabilityZone = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[SerializedName('MultiAZ')]
    #[Groups(['rds_instance_list_export'])]
    private ?bool $multiAZ = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('StorageType')]
    #[Assert\Length(max: 255)]
    #[Groups(['rds_instance_list_export'])]
    private ?string $storageType = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[SerializedName('StorageEncrypted')]
    #[Groups(['rds_instance_list_export'])]
    private ?bool $storageEncrypted = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('DBSubnetGroupName')]
    #[Assert\Length(max: 255)]
    private ?string $dbSubnetGroupName = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('Endpoint')]
    #[Groups(['rds_instance_list_export'])]
    private ?array $endpoint = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[SerializedName('PubliclyAccessible')]
    #[Groups(['rds_instance_list_export'])]
    private ?bool $publiclyAccessible = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('VpcSecurityGroups')]
    private ?array $vpcSecurityGroups = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('TagList')]
    private ?array $tagList = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[SerializedName('InstanceCreateTime')]
    #[Groups(['rds_instance_list_export'])]
    private ?\DateTimeImmutable $instanceCreateTime = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('DBInstanceArn')]
    #[Assert\Length(max: 255)]
    private ?string $dbInstanceArn = null;

    #[ORM\ManyToOne(inversedBy: 'rdsInstances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDbInstanceIdentifier(): ?string
    {
        return $this->dbInstanceIdentifier;
    }

    public function setDbInstanceIdentifier(?string $dbInstanceIdentifier): static
    {
        $this->dbInstanceIdentifier = $dbInstanceIdentifier;
        return $this;
    }

    public function getDbInstanceClass(): ?string
    {
        return $this->dbInstanceClass;
    }

    public function setDbInstanceClass(?string $dbInstanceClass): static
    {
        $this->dbInstanceClass = $dbInstanceClass;
        return $this;
    }

    public function getEngine(): ?string
    {
        return $this->engine;
    }

    public function setEngine(?string $engine): static
    {
        $this->engine = $engine;
        return $this;
    }

    public function getEngineVersion(): ?string
    {
        return $this->engineVersion;
    }

    public function setEngineVersion(?string $engineVersion): static
    {
        $this->engineVersion = $engineVersion;
        return $this;
    }

    public function getDbInstanceStatus(): ?string
    {
        return $this->dbInstanceStatus;
    }

    public function setDbInstanceStatus(?string $dbInstanceStatus): static
    {
        $this->dbInstanceStatus = $dbInstanceStatus;
        return $this;
    }

    public function getMasterUsername(): ?string
    {
        return $this->masterUsername;
    }

    public function setMasterUsername(?string $masterUsername): static
    {
        $this->masterUsername = $masterUsername;
        return $this;
    }

    public function getAllocatedStorage(): ?int
    {
        return $this->allocatedStorage;
    }

    public function setAllocatedStorage(?int $allocatedStorage): static
    {
        $this->allocatedStorage = $allocatedStorage;
        return $this;
    }

    public function getAvailabilityZone(): ?string
    {
        return $this->availabilityZone;
    }

    public function setAvailabilityZone(?string $availabilityZone): static
    {
        $this->availabilityZone = $availabilityZone;
        return $this;
    }

    public function isMultiAZ(): ?bool
    {
        return $this->multiAZ;
    }

    public function setMultiAZ(?bool $multiAZ): static
    {
        $this->multiAZ = $multiAZ;
        return $this;
    }

    public function getStorageType(): ?string
    {
        return $this->storageType;
    }

    public function setStorageType(?string $storageType): static
    {
        $this->storageType = $storageType;
        return $this;
    }

    public function isStorageEncrypted(): ?bool
    {
        return $this->storageEncrypted;
    }

    public function setStorageEncrypted(?bool $storageEncrypted): static
    {
        $this->storageEncrypted = $storageEncrypted;
        return $this;
    }

    public function getDbSubnetGroupName(): ?string
    {
        return $this->dbSubnetGroupName;
    }

    public function setDbSubnetGroupName(?string $dbSubnetGroupName): static
    {
        $this->dbSubnetGroupName = $dbSubnetGroupName;
        return $this;
    }

    public function getEndpoint(): ?array
    {
        return $this->endpoint;
    }

    public function setEndpoint(?array $endpoint): static
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getEndpointAddress(): ?string
    {
        return $this->endpoint['Address'] ?? null;
    }

    public function getEndpointPort(): ?int
    {
        return $this->endpoint['Port'] ?? null;
    }

    public function isPubliclyAccessible(): ?bool
    {
        return $this->publiclyAccessible;
    }

    public function setPubliclyAccessible(?bool $publiclyAccessible): static
    {
        $this->publiclyAccessible = $publiclyAccessible;
        return $this;
    }

    public function getVpcSecurityGroups(): ?array
    {
        return $this->vpcSecurityGroups;
    }

    public function setVpcSecurityGroups(?array $vpcSecurityGroups): static
    {
        $this->vpcSecurityGroups = $vpcSecurityGroups;
        return $this;
    }

    public function getTagList(): ?array
    {
        return $this->tagList;
    }

    public function setTagList(?array $tagList): static
    {
        $this->tagList = $tagList;
        return $this;
    }

    public function getInstanceCreateTime(): ?\DateTimeImmutable
    {
        return $this->instanceCreateTime;
    }

    public function setInstanceCreateTime(?\DateTimeImmutable $instanceCreateTime): static
    {
        $this->instanceCreateTime = $instanceCreateTime;
        return $this;
    }

    public function getDbInstanceArn(): ?string
    {
        return $this->dbInstanceArn;
    }

    public function setDbInstanceArn(?string $dbInstanceArn): static
    {
        $this->dbInstanceArn = $dbInstanceArn;
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
        if ($this->tagList === null) {
            return null;
        }

        foreach ($this->tagList as $tag) {
            if (isset($tag['Key']) && $tag['Key'] === 'Name') {
                return $tag['Value'] ?? null;
            }
        }

        return null;
    }
}
