<?php

namespace App\Entity\AWS\IAM;

use App\Entity\CrawlVersion;
use App\Repository\AWS\IAM\IamUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IamUserRepository::class)]
class IamUser
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('UserName')]
    #[Assert\Length(max: 255)]
    #[Groups(['iam_user_list_export'])]
    private ?string $userName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('UserId')]
    #[Assert\Length(max: 255)]
    #[Groups(['iam_user_list_export'])]
    private ?string $userId = null;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    #[SerializedName('Arn')]
    #[Assert\Length(max: 2048)]
    #[Groups(['iam_user_list_export'])]
    private ?string $arn = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Path')]
    #[Assert\Length(max: 512)]
    #[Groups(['iam_user_list_export'])]
    private ?string $path = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[SerializedName('CreateDate')]
    #[Groups(['iam_user_list_export'])]
    private ?\DateTime $createDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[SerializedName('PasswordLastUsed')]
    #[Groups(['iam_user_list_export'])]
    private ?\DateTime $passwordLastUsed = null;

    #[ORM\ManyToOne(inversedBy: 'iamUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): static
    {
        $this->userName = $userName;
        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): static
    {
        $this->userId = $userId;
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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function getCreateDate(): ?\DateTime
    {
        return $this->createDate;
    }

    public function setCreateDate(?\DateTime $createDate): static
    {
        $this->createDate = $createDate;
        return $this;
    }

    public function getPasswordLastUsed(): ?\DateTime
    {
        return $this->passwordLastUsed;
    }

    public function setPasswordLastUsed(?\DateTime $passwordLastUsed): static
    {
        $this->passwordLastUsed = $passwordLastUsed;
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
