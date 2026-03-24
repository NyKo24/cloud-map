<?php

namespace App\Entity\AWS\ECS;

use App\Entity\CrawlVersion;
use App\Repository\AWS\ECS\EcsClusterRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EcsClusterRepository::class)]
class EcsCluster
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    #[SerializedName('clusterArn')]
    #[Assert\Length(max: 2048)]
    #[Groups(['ecs_cluster_list_export'])]
    private ?string $clusterArn = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('clusterName')]
    #[Assert\Length(max: 255)]
    #[Groups(['ecs_cluster_list_export'])]
    private ?string $clusterName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('status')]
    #[Assert\Length(max: 255)]
    #[Groups(['ecs_cluster_list_export'])]
    private ?string $status = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('registeredContainerInstancesCount')]
    #[Groups(['ecs_cluster_list_export'])]
    private ?int $registeredContainerInstancesCount = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('runningTasksCount')]
    #[Groups(['ecs_cluster_list_export'])]
    private ?int $runningTasksCount = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('activeServicesCount')]
    #[Groups(['ecs_cluster_list_export'])]
    private ?int $activeServicesCount = null;

    #[ORM\ManyToOne(inversedBy: 'ecsClusters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClusterArn(): ?string
    {
        return $this->clusterArn;
    }

    public function setClusterArn(?string $clusterArn): static
    {
        $this->clusterArn = $clusterArn;
        return $this;
    }

    public function getClusterName(): ?string
    {
        return $this->clusterName;
    }

    public function setClusterName(?string $clusterName): static
    {
        $this->clusterName = $clusterName;
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

    public function getRegisteredContainerInstancesCount(): ?int
    {
        return $this->registeredContainerInstancesCount;
    }

    public function setRegisteredContainerInstancesCount(?int $registeredContainerInstancesCount): static
    {
        $this->registeredContainerInstancesCount = $registeredContainerInstancesCount;
        return $this;
    }

    public function getRunningTasksCount(): ?int
    {
        return $this->runningTasksCount;
    }

    public function setRunningTasksCount(?int $runningTasksCount): static
    {
        $this->runningTasksCount = $runningTasksCount;
        return $this;
    }

    public function getActiveServicesCount(): ?int
    {
        return $this->activeServicesCount;
    }

    public function setActiveServicesCount(?int $activeServicesCount): static
    {
        $this->activeServicesCount = $activeServicesCount;
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
