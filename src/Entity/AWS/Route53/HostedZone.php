<?php

namespace App\Entity\AWS\Route53;

use App\Entity\CrawlVersion;
use App\Repository\AWS\Route53\HostedZoneRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HostedZoneRepository::class)]
class HostedZone
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Id')]
    #[Assert\Length(max: 255)]
    #[Groups(['hosted_zone_list_export'])]
    private ?string $hostedZoneId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Name')]
    #[Assert\Length(max: 255)]
    #[Groups(['hosted_zone_list_export'])]
    private ?string $name = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('CallerReference')]
    #[Assert\Length(max: 255)]
    #[Groups(['hosted_zone_list_export'])]
    private ?string $callerReference = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['hosted_zone_list_export'])]
    private ?string $comment = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['hosted_zone_list_export'])]
    private ?bool $privateZone = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('ResourceRecordSetCount')]
    #[Groups(['hosted_zone_list_export'])]
    private ?int $resourceRecordSetCount = null;

    #[ORM\ManyToOne(inversedBy: 'hostedZones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHostedZoneId(): ?string
    {
        return $this->hostedZoneId;
    }

    public function setHostedZoneId(?string $hostedZoneId): static
    {
        $this->hostedZoneId = $hostedZoneId;
        return $this;
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

    public function getCallerReference(): ?string
    {
        return $this->callerReference;
    }

    public function setCallerReference(?string $callerReference): static
    {
        $this->callerReference = $callerReference;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function isPrivateZone(): ?bool
    {
        return $this->privateZone;
    }

    public function setPrivateZone(?bool $privateZone): static
    {
        $this->privateZone = $privateZone;
        return $this;
    }

    public function getResourceRecordSetCount(): ?int
    {
        return $this->resourceRecordSetCount;
    }

    public function setResourceRecordSetCount(?int $resourceRecordSetCount): static
    {
        $this->resourceRecordSetCount = $resourceRecordSetCount;
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
