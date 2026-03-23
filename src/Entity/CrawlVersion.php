<?php

namespace App\Entity;

use App\Entity\AWS\AwsAccount;
use App\Entity\AWS\Lambda\LambdaFunction;
use App\Entity\AWS\VPC\Vpc;
use App\Repository\CrawlVersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CrawlVersionRepository::class)]
class CrawlVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $version = null;

    #[ORM\ManyToOne(inversedBy: 'crawls')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    /**
     * @var Collection<int, AwsAccount>
     */
    #[ORM\OneToMany(targetEntity: AwsAccount::class, mappedBy: 'crawl')]
    private Collection $awsAccounts;

    /**
     * @var Collection<int, Vpc>
     */
    #[ORM\OneToMany(targetEntity: Vpc::class, mappedBy: 'crawl')]
    private Collection $vpcs;

    /**
     * @var Collection<int, LambdaFunction>
     */
    #[ORM\OneToMany(targetEntity: LambdaFunction::class, mappedBy: 'crawl')]
    private Collection $lambdaFunctions;

    public function __construct()
    {
        $this->awsAccounts = new ArrayCollection();
        $this->vpcs = new ArrayCollection();
        $this->lambdaFunctions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection<int, AwsAccount>
     */
    public function getAwsAccounts(): Collection
    {
        return $this->awsAccounts;
    }

    public function addAwsAccount(AwsAccount $awsAccount): static
    {
        if (!$this->awsAccounts->contains($awsAccount)) {
            $this->awsAccounts->add($awsAccount);
            $awsAccount->setCrawl($this);
        }

        return $this;
    }

    public function removeAwsAccount(AwsAccount $awsAccount): static
    {
        if ($this->awsAccounts->removeElement($awsAccount)) {
            // set the owning side to null (unless already changed)
            if ($awsAccount->getCrawl() === $this) {
                $awsAccount->setCrawl(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vpc>
     */
    public function getVpcs(): Collection
    {
        return $this->vpcs;
    }

    public function addVpc(Vpc $vpc): static
    {
        if (!$this->vpcs->contains($vpc)) {
            $this->vpcs->add($vpc);
            $vpc->setCrawl($this);
        }

        return $this;
    }

    public function removeVpc(Vpc $vpc): static
    {
        if ($this->vpcs->removeElement($vpc)) {
            // set the owning side to null (unless already changed)
            if ($vpc->getCrawl() === $this) {
                $vpc->setCrawl(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LambdaFunction>
     */
    public function getLambdaFunctions(): Collection
    {
        return $this->lambdaFunctions;
    }

    public function addLambdaFunction(LambdaFunction $lambdaFunction): static
    {
        if (!$this->lambdaFunctions->contains($lambdaFunction)) {
            $this->lambdaFunctions->add($lambdaFunction);
            $lambdaFunction->setCrawl($this);
        }

        return $this;
    }

    public function removeLambdaFunction(LambdaFunction $lambdaFunction): static
    {
        if ($this->lambdaFunctions->removeElement($lambdaFunction)) {
            if ($lambdaFunction->getCrawl() === $this) {
                $lambdaFunction->setCrawl(null);
            }
        }

        return $this;
    }
}
