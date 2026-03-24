<?php

namespace App\Entity\AWS\IAM;

use App\Entity\CrawlVersion;
use App\Repository\AWS\IAM\IamRoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IamRoleRepository::class)]
class IamRole
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('RoleName')]
    #[Assert\Length(max: 255)]
    #[Groups(['iam_role_list_export'])]
    private ?string $roleName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('RoleId')]
    #[Assert\Length(max: 255)]
    #[Groups(['iam_role_list_export'])]
    private ?string $roleId = null;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    #[SerializedName('Arn')]
    #[Assert\Length(max: 2048)]
    #[Groups(['iam_role_list_export'])]
    private ?string $arn = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Path')]
    #[Assert\Length(max: 512)]
    #[Groups(['iam_role_list_export'])]
    private ?string $path = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[SerializedName('CreateDate')]
    #[Groups(['iam_role_list_export'])]
    private ?\DateTime $createDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[SerializedName('Description')]
    #[Groups(['iam_role_list_export'])]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('MaxSessionDuration')]
    #[Groups(['iam_role_list_export'])]
    private ?int $maxSessionDuration = null;

    #[ORM\ManyToOne(inversedBy: 'iamRoles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    public function setRoleName(?string $roleName): static
    {
        $this->roleName = $roleName;
        return $this;
    }

    public function getRoleId(): ?string
    {
        return $this->roleId;
    }

    public function setRoleId(?string $roleId): static
    {
        $this->roleId = $roleId;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getMaxSessionDuration(): ?int
    {
        return $this->maxSessionDuration;
    }

    public function setMaxSessionDuration(?int $maxSessionDuration): static
    {
        $this->maxSessionDuration = $maxSessionDuration;
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
