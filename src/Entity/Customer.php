<?php

namespace App\Entity;

use App\Entity\AWS\AwsAccount;
use App\Enum\Customer\CustomerIntegrationModeEnum;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $awsRootRoleArn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $awsRootOrganisationId = null;

    /**
     * @var Collection<int, AwsAccount>
     */
    #[ORM\OneToMany(targetEntity: AwsAccount::class, mappedBy: 'customer', orphanRemoval: true)]
    private Collection $awsAccounts;

    /**
     * @var Collection<int, CrawlVersion>
     */
    #[ORM\OneToMany(targetEntity: CrawlVersion::class, mappedBy: 'customer')]
    private Collection $crawls;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $awsAccountRoleName = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'customers')]
    private Collection $users;

    #[ORM\Column(nullable: true, enumType: CustomerIntegrationModeEnum::class)]
    private ?CustomerIntegrationModeEnum $integrationMode = null;

    public function __construct()
    {
        $this->awsAccounts = new ArrayCollection();
        $this->crawls = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = strtoupper($name);

        return $this;
    }

    public function getAwsRootRoleArn(): ?string
    {
        return $this->awsRootRoleArn;
    }

    public function setAwsRootRoleArn(string $awsRootRoleArn): static
    {
        $this->awsRootRoleArn = $awsRootRoleArn;

        return $this;
    }

    public function getAwsRootOrganisationId(): ?string
    {
        return $this->awsRootOrganisationId;
    }

    public function setAwsRootOrganisationId(string $awsRootOrganisationId): static
    {
        $this->awsRootOrganisationId = $awsRootOrganisationId;

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
            $awsAccount->setCustomer($this);
        }

        return $this;
    }

    public function removeAwsAccount(AwsAccount $awsAccount): static
    {
        if ($this->awsAccounts->removeElement($awsAccount)) {
            // set the owning side to null (unless already changed)
            if ($awsAccount->getCustomer() === $this) {
                $awsAccount->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CrawlVersion>
     */
    public function getCrawls(): Collection
    {
        return $this->crawls;
    }

    public function addCrawl(CrawlVersion $crawl): static
    {
        if (!$this->crawls->contains($crawl)) {
            $this->crawls->add($crawl);
            $crawl->setCustomer($this);
        }

        return $this;
    }

    public function removeCrawl(CrawlVersion $crawl): static
    {
        if ($this->crawls->removeElement($crawl)) {
            // set the owning side to null (unless already changed)
            if ($crawl->getCustomer() === $this) {
                $crawl->setCustomer(null);
            }
        }

        return $this;
    }

    public function getAwsAccountRoleName(): ?string
    {
        return $this->awsAccountRoleName;
    }

    public function setAwsAccountRoleName(string $awsAccountRoleName): static
    {
        $this->awsAccountRoleName = $awsAccountRoleName;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addCustomer($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeCustomer($this);
        }

        return $this;
    }

    public function getIntegrationMode(): ?CustomerIntegrationModeEnum
    {
        return $this->integrationMode;
    }

    public function setIntegrationMode(?CustomerIntegrationModeEnum $integrationMode): static
    {
        $this->integrationMode = $integrationMode;

        return $this;
    }
}
