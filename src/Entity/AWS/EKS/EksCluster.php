<?php

namespace App\Entity\AWS\EKS;

use App\Entity\CrawlVersion;
use App\Repository\AWS\EKS\EksClusterRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EksClusterRepository::class)]
class EksCluster
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('name')]
    #[Assert\Length(max: 255)]
    #[Groups(['eks_cluster_list_export'])]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    #[SerializedName('arn')]
    #[Assert\Length(max: 2048)]
    #[Groups(['eks_cluster_list_export'])]
    private ?string $arn = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('version')]
    #[Assert\Length(max: 255)]
    #[Groups(['eks_cluster_list_export'])]
    private ?string $version = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('status')]
    #[Assert\Length(max: 255)]
    #[Groups(['eks_cluster_list_export'])]
    private ?string $status = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('platformVersion')]
    #[Assert\Length(max: 255)]
    #[Groups(['eks_cluster_list_export'])]
    private ?string $platformVersion = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[SerializedName('endpoint')]
    #[Groups(['eks_cluster_list_export'])]
    private ?string $endpoint = null;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    #[SerializedName('roleArn')]
    #[Assert\Length(max: 2048)]
    #[Groups(['eks_cluster_list_export'])]
    private ?string $roleArn = null;

    #[ORM\ManyToOne(inversedBy: 'eksClusters')]
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

    public function getArn(): ?string
    {
        return $this->arn;
    }

    public function setArn(?string $arn): static
    {
        $this->arn = $arn;
        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): static
    {
        $this->version = $version;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getPlatformVersion(): ?string
    {
        return $this->platformVersion;
    }

    public function setPlatformVersion(?string $platformVersion): static
    {
        $this->platformVersion = $platformVersion;
        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(?string $endpoint): static
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getRoleArn(): ?string
    {
        return $this->roleArn;
    }

    public function setRoleArn(?string $roleArn): static
    {
        $this->roleArn = $roleArn;
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
