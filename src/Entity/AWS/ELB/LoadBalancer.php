<?php

namespace App\Entity\AWS\ELB;

use App\Entity\CrawlVersion;
use App\Repository\AWS\ELB\LoadBalancerRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LoadBalancerRepository::class)]
class LoadBalancer
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('LoadBalancerArn')]
    #[Assert\Length(max: 255)]
    private ?string $loadBalancerArn = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('LoadBalancerName')]
    #[Assert\Length(max: 255)]
    #[Groups(['load_balancer_list_export'])]
    private ?string $loadBalancerName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('DNSName')]
    #[Assert\Length(max: 255)]
    #[Groups(['load_balancer_list_export'])]
    private ?string $dnsName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Type')]
    #[Assert\Length(max: 255)]
    #[Groups(['load_balancer_list_export'])]
    private ?string $type = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Scheme')]
    #[Assert\Length(max: 255)]
    #[Groups(['load_balancer_list_export'])]
    private ?string $scheme = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('VpcId')]
    #[Assert\Length(max: 255)]
    #[Groups(['load_balancer_list_export'])]
    private ?string $vpcId = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('State')]
    private ?array $state = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[SerializedName('CreatedTime')]
    #[Groups(['load_balancer_list_export'])]
    private ?\DateTimeImmutable $createdTime = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('AvailabilityZones')]
    private ?array $availabilityZones = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('IpAddressType')]
    #[Assert\Length(max: 255)]
    #[Groups(['load_balancer_list_export'])]
    private ?string $ipAddressType = null;

    #[ORM\ManyToOne(inversedBy: 'loadBalancers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLoadBalancerArn(): ?string
    {
        return $this->loadBalancerArn;
    }

    public function setLoadBalancerArn(?string $loadBalancerArn): static
    {
        $this->loadBalancerArn = $loadBalancerArn;
        return $this;
    }

    public function getLoadBalancerName(): ?string
    {
        return $this->loadBalancerName;
    }

    public function setLoadBalancerName(?string $loadBalancerName): static
    {
        $this->loadBalancerName = $loadBalancerName;
        return $this;
    }

    public function getDnsName(): ?string
    {
        return $this->dnsName;
    }

    public function setDnsName(?string $dnsName): static
    {
        $this->dnsName = $dnsName;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function setScheme(?string $scheme): static
    {
        $this->scheme = $scheme;
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

    public function getState(): ?array
    {
        return $this->state;
    }

    public function setState(?array $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function getStateCode(): ?string
    {
        return $this->state['Code'] ?? null;
    }

    public function getCreatedTime(): ?\DateTimeImmutable
    {
        return $this->createdTime;
    }

    public function setCreatedTime(?\DateTimeImmutable $createdTime): static
    {
        $this->createdTime = $createdTime;
        return $this;
    }

    public function getAvailabilityZones(): ?array
    {
        return $this->availabilityZones;
    }

    public function setAvailabilityZones(?array $availabilityZones): static
    {
        $this->availabilityZones = $availabilityZones;
        return $this;
    }

    public function getAvailabilityZoneNames(): array
    {
        if ($this->availabilityZones === null) {
            return [];
        }

        return array_map(
            fn(array $az) => $az['ZoneName'] ?? '',
            $this->availabilityZones
        );
    }

    public function getIpAddressType(): ?string
    {
        return $this->ipAddressType;
    }

    public function setIpAddressType(?string $ipAddressType): static
    {
        $this->ipAddressType = $ipAddressType;
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
