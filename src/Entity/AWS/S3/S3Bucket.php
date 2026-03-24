<?php

namespace App\Entity\AWS\S3;

use App\Entity\CrawlVersion;
use App\Repository\AWS\S3\S3BucketRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: S3BucketRepository::class)]
class S3Bucket
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Name')]
    #[Assert\Length(max: 255)]
    #[Groups(['s3_bucket_list_export'])]
    private ?string $name = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[SerializedName('CreationDate')]
    #[Groups(['s3_bucket_list_export'])]
    private ?\DateTimeImmutable $creationDate = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['s3_bucket_list_export'])]
    private ?string $region = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['s3_bucket_list_export'])]
    private ?bool $encryptionEnabled = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['s3_bucket_list_export'])]
    private ?string $versioningStatus = null;

    #[ORM\ManyToOne(inversedBy: 's3Buckets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCreationDate(): ?\DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(?\DateTimeImmutable $creationDate): static
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;
        return $this;
    }

    public function isEncryptionEnabled(): ?bool
    {
        return $this->encryptionEnabled;
    }

    public function setEncryptionEnabled(?bool $encryptionEnabled): static
    {
        $this->encryptionEnabled = $encryptionEnabled;
        return $this;
    }

    public function getVersioningStatus(): ?string
    {
        return $this->versioningStatus;
    }

    public function setVersioningStatus(?string $versioningStatus): static
    {
        $this->versioningStatus = $versioningStatus;
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
