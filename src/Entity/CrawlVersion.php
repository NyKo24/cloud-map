<?php

namespace App\Entity;

use App\Entity\AWS\AwsAccount;
use App\Entity\AWS\EC2\Ec2Instance;
use App\Entity\AWS\EC2\SecurityGroup;
use App\Entity\AWS\CloudWatch\CloudWatchAlarm;
use App\Entity\AWS\ECS\EcsCluster;
use App\Entity\AWS\EKS\EksCluster;
use App\Entity\AWS\ELB\LoadBalancer;
use App\Entity\AWS\IAM\IamRole;
use App\Entity\AWS\IAM\IamUser;
use App\Entity\AWS\Lambda\LambdaFunction;
use App\Entity\AWS\RDS\RdsInstance;
use App\Entity\AWS\Route53\HostedZone;
use App\Entity\AWS\S3\S3Bucket;
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

    /**
     * @var Collection<int, S3Bucket>
     */
    #[ORM\OneToMany(targetEntity: S3Bucket::class, mappedBy: 'crawl')]
    private Collection $s3Buckets;

    /**
     * @var Collection<int, IamRole>
     */
    #[ORM\OneToMany(targetEntity: IamRole::class, mappedBy: 'crawl')]
    private Collection $iamRoles;

    /**
     * @var Collection<int, IamUser>
     */
    #[ORM\OneToMany(targetEntity: IamUser::class, mappedBy: 'crawl')]
    private Collection $iamUsers;

    /**
     * @var Collection<int, CloudWatchAlarm>
     */
    #[ORM\OneToMany(targetEntity: CloudWatchAlarm::class, mappedBy: 'crawl')]
    private Collection $cloudWatchAlarms;

    /**
     * @var Collection<int, EcsCluster>
     */
    #[ORM\OneToMany(targetEntity: EcsCluster::class, mappedBy: 'crawl')]
    private Collection $ecsClusters;

    /**
     * @var Collection<int, EksCluster>
     */
    #[ORM\OneToMany(targetEntity: EksCluster::class, mappedBy: 'crawl')]
    private Collection $eksClusters;

    /**
     * @var Collection<int, LoadBalancer>
     */
    #[ORM\OneToMany(targetEntity: LoadBalancer::class, mappedBy: 'crawl')]
    private Collection $loadBalancers;

    /**
     * @var Collection<int, HostedZone>
     */
    #[ORM\OneToMany(targetEntity: HostedZone::class, mappedBy: 'crawl')]
    private Collection $hostedZones;

    public function __construct()
    {
        $this->awsAccounts = new ArrayCollection();
        $this->vpcs = new ArrayCollection();
        $this->lambdaFunctions = new ArrayCollection();
        $this->ec2Instances = new ArrayCollection();
        $this->rdsInstances = new ArrayCollection();
        $this->securityGroups = new ArrayCollection();
        $this->s3Buckets = new ArrayCollection();
        $this->iamRoles = new ArrayCollection();
        $this->iamUsers = new ArrayCollection();
        $this->cloudWatchAlarms = new ArrayCollection();
        $this->ecsClusters = new ArrayCollection();
        $this->eksClusters = new ArrayCollection();
        $this->loadBalancers = new ArrayCollection();
        $this->hostedZones = new ArrayCollection();
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

    /**
     * @return Collection<int, S3Bucket>
     */
    public function getS3Buckets(): Collection
    {
        return $this->s3Buckets;
    }

    public function addS3Bucket(S3Bucket $s3Bucket): static
    {
        if (!$this->s3Buckets->contains($s3Bucket)) {
            $this->s3Buckets->add($s3Bucket);
            $s3Bucket->setCrawl($this);
        }

        return $this;
    }

    public function removeS3Bucket(S3Bucket $s3Bucket): static
    {
        if ($this->s3Buckets->removeElement($s3Bucket)) {
            if ($s3Bucket->getCrawl() === $this) {
                $s3Bucket->setCrawl(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, IamRole>
     */
    public function getIamRoles(): Collection
    {
        return $this->iamRoles;
    }

    public function addIamRole(IamRole $iamRole): static
    {
        if (!$this->iamRoles->contains($iamRole)) {
            $this->iamRoles->add($iamRole);
            $iamRole->setCrawl($this);
        }

        return $this;
    }

    public function removeIamRole(IamRole $iamRole): static
    {
        if ($this->iamRoles->removeElement($iamRole)) {
            if ($iamRole->getCrawl() === $this) {
                $iamRole->setCrawl(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, IamUser>
     */
    public function getIamUsers(): Collection
    {
        return $this->iamUsers;
    }

    public function addIamUser(IamUser $iamUser): static
    {
        if (!$this->iamUsers->contains($iamUser)) {
            $this->iamUsers->add($iamUser);
            $iamUser->setCrawl($this);
        }

        return $this;
    }

    public function removeIamUser(IamUser $iamUser): static
    {
        if ($this->iamUsers->removeElement($iamUser)) {
            if ($iamUser->getCrawl() === $this) {
                $iamUser->setCrawl(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CloudWatchAlarm>
     */
    public function getCloudWatchAlarms(): Collection
    {
        return $this->cloudWatchAlarms;
    }

    public function addCloudWatchAlarm(CloudWatchAlarm $cloudWatchAlarm): static
    {
        if (!$this->cloudWatchAlarms->contains($cloudWatchAlarm)) {
            $this->cloudWatchAlarms->add($cloudWatchAlarm);
            $cloudWatchAlarm->setCrawl($this);
        }

        return $this;
    }

    public function removeCloudWatchAlarm(CloudWatchAlarm $cloudWatchAlarm): static
    {
        if ($this->cloudWatchAlarms->removeElement($cloudWatchAlarm)) {
            if ($cloudWatchAlarm->getCrawl() === $this) {
                $cloudWatchAlarm->setCrawl(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EcsCluster>
     */
    public function getEcsClusters(): Collection
    {
        return $this->ecsClusters;
    }

    public function addEcsCluster(EcsCluster $ecsCluster): static
    {
        if (!$this->ecsClusters->contains($ecsCluster)) {
            $this->ecsClusters->add($ecsCluster);
            $ecsCluster->setCrawl($this);
        }

        return $this;
    }

    public function removeEcsCluster(EcsCluster $ecsCluster): static
    {
        if ($this->ecsClusters->removeElement($ecsCluster)) {
            if ($ecsCluster->getCrawl() === $this) {
                $ecsCluster->setCrawl(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EksCluster>
     */
    public function getEksClusters(): Collection
    {
        return $this->eksClusters;
    }

    public function addEksCluster(EksCluster $eksCluster): static
    {
        if (!$this->eksClusters->contains($eksCluster)) {
            $this->eksClusters->add($eksCluster);
            $eksCluster->setCrawl($this);
        }

        return $this;
    }

    public function removeEksCluster(EksCluster $eksCluster): static
    {
        if ($this->eksClusters->removeElement($eksCluster)) {
            if ($eksCluster->getCrawl() === $this) {
                $eksCluster->setCrawl(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LoadBalancer>
     */
    public function getLoadBalancers(): Collection
    {
        return $this->loadBalancers;
    }

    public function addLoadBalancer(LoadBalancer $loadBalancer): static
    {
        if (!$this->loadBalancers->contains($loadBalancer)) {
            $this->loadBalancers->add($loadBalancer);
            $loadBalancer->setCrawl($this);
        }

        return $this;
    }

    public function removeLoadBalancer(LoadBalancer $loadBalancer): static
    {
        if ($this->loadBalancers->removeElement($loadBalancer)) {
            if ($loadBalancer->getCrawl() === $this) {
                $loadBalancer->setCrawl(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HostedZone>
     */
    public function getHostedZones(): Collection
    {
        return $this->hostedZones;
    }

    public function addHostedZone(HostedZone $hostedZone): static
    {
        if (!$this->hostedZones->contains($hostedZone)) {
            $this->hostedZones->add($hostedZone);
            $hostedZone->setCrawl($this);
        }

        return $this;
    }

    public function removeHostedZone(HostedZone $hostedZone): static
    {
        if ($this->hostedZones->removeElement($hostedZone)) {
            if ($hostedZone->getCrawl() === $this) {
                $hostedZone->setCrawl(null);
            }
        }

        return $this;
    }
}
