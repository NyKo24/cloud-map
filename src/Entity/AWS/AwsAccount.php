<?php

namespace App\Entity\AWS;

use App\Entity\CrawlVersion;
use App\Entity\Customer;
use App\Repository\AWS\AwsAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AwsAccountRepository::class)]
class AwsAccount
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[SerializedName('_id')]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(pattern: '/^arn:aws:organizations::\d{12}:account\/o-[a-z0-9]{10,32}\/\d{12}/')]
    #[SerializedName('Arn')]
    #[Groups(['aws_account_list_export'])]
    private ?string $arn = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[SerializedName('Email')]
    #[Groups(['aws_account_list_export'])]
    private ?string $email = null;

    #[ORM\Column(length: 12, nullable: true)]
    #[SerializedName('Id')]
    #[Groups(['aws_account_list_export'])]
    private ?string $awsId = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Assert\Choice(choices: ['INVITED', 'CREATED'])]
    #[SerializedName('JoinedMethod')]
    #[Groups(['aws_account_list_export'])]
    private ?string $joinedMethod = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[SerializedName('JoinedTimestamp')]
    #[Groups(['aws_account_list_export'])]
    private ?\DateTimeInterface $joinedTimestamp = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[SerializedName('Name')]
    #[Groups(['aws_account_list_export'])]
    private ?string $name = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Choice(choices: ['ACTIVE', 'SUSPENDED', 'PENDING_CLOSURE'])]
    #[SerializedName('Status')]
    #[Groups(['aws_account_list_export'])]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'awsAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(inversedBy: 'awsAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAwsId(): ?string
    {
        return $this->awsId;
    }

    public function setAwsId(?string $awsId): static
    {
        $this->awsId = $awsId;

        return $this;
    }

    public function getJoinedMethod(): ?string
    {
        return $this->joinedMethod;
    }

    public function setJoinedMethod(string $joinedMethod): static
    {
        $this->joinedMethod = $joinedMethod;

        return $this;
    }

    public function getJoinedTimestamp(): ?\DateTimeInterface
    {
        return $this->joinedTimestamp;
    }

    public function setJoinedTimestamp(?\DateTimeInterface $joinedTimestamp): static
    {
        $this->joinedTimestamp = $joinedTimestamp;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

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
