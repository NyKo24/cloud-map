<?php

namespace App\Entity;

use App\Entity\AWS\AwsAccount;
use App\Entity\AWS\EC2\Ec2Instance;
use App\Entity\AWS\EC2\SecurityGroup;
use App\Entity\AWS\Lambda\LambdaFunction;
use App\Entity\AWS\RDS\RdsInstance;
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

    /**
     * @var Collection<int, Ec2Instance>
     */
    #[ORM\OneToMany(targetEntity: Ec2Instance::class, mappedBy: 'crawl')]
    private Collection $ec2Instances;

    /**
     * @var Collection<int, RdsInstance>
     */
    #[ORM\OneToMany(targetEntity: RdsInstance::class, mappedBy: 'crawl')]
    private Collection $rdsInstances;

    /**
     * @var Collection<int, SecurityGroup>
     */
    #[ORM\OneToMany(targetEntity: SecurityGroup::class, mappedBy: 'crawl')]
    private Collection $securityGroups;

    public function __construct()
    {
        $this->awsAccounts = new ArrayCollection();
        $this->vpcs = new ArrayCollection();
        $this->lambdaFunctions = new ArrayCollection();
        $this->ec2Instances = new ArrayCollection();
        $this->rdsInstances = new ArrayCollection();
        $this->securityGroups = new ArrayCollection();
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

    /**
     * @return Collection<int, Ec2Instance>
     */
    public function getEc2Instances(): Collection
    {
        return $this->ec2Instances;
    }

    public function addEc2Instance(Ec2Instance $ec2Instance): static
    {
        if (!$this->ec2Instances->contains($ec2Instance)) {
            $this->ec2Instances->add($ec2Instance);
            $ec2Instance->setCrawl($this);
        }

        return $this;
    }

    public function removeEc2Instance(Ec2Instance $ec2Instance): static
    {
        if ($this->ec2Instances->removeElement($ec2Instance)) {
            if ($ec2Instance->getCrawl() === $this) {
                $ec2Instance->setCrawl(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RdsInstance>
     */
    public function getRdsInstances(): Collection
    {
        return $this->rdsInstances;
    }

    public function addRdsInstance(RdsInstance $rdsInstance): static
    {
        if (!$this->rdsInstances->contains($rdsInstance)) {
            $this->rdsInstances->add($rdsInstance);
            $rdsInstance->setCrawl($this);
        }

        return $this;
    }

    public function removeRdsInstance(RdsInstance $rdsInstance): static
    {
        if ($this->rdsInstances->removeElement($rdsInstance)) {
            if ($rdsInstance->getCrawl() === $this) {
                $rdsInstance->setCrawl(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SecurityGroup>
     */
    public function getSecurityGroups(): Collection
    {
        return $this->securityGroups;
    }

    public function addSecurityGroup(SecurityGroup $securityGroup): static
    {
        if (!$this->securityGroups->contains($securityGroup)) {
            $this->securityGroups->add($securityGroup);
            $securityGroup->setCrawl($this);
        }

        return $this;
    }

    public function removeSecurityGroup(SecurityGroup $securityGroup): static
    {
        if ($this->securityGroups->removeElement($securityGroup)) {
            if ($securityGroup->getCrawl() === $this) {
                $securityGroup->setCrawl(null);
            }
        }

        return $this;
    }
}
