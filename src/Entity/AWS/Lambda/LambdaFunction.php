<?php

namespace App\Entity\AWS\Lambda;

use App\Entity\AWS\Tag;
use App\Entity\CrawlVersion;
use App\Enum\AWS\Lambda\Architecture;
use App\Enum\AWS\Lambda\LambdaRuntime;
use App\Enum\AWS\Lambda\LambdaState;
use App\Enum\AWS\Lambda\PackageType;
use App\Repository\AWS\Lambda\LambdaFunctionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LambdaFunctionRepository::class)]
#[ORM\Table(name: 'lambda_function')]
class LambdaFunction
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('FunctionName')]
    #[Assert\Length(max: 255)]
    private ?string $functionName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('FunctionArn')]
    #[Assert\Length(max: 1024)]
    private ?string $functionArn = null;

    #[ORM\Column(type: 'string', enumType: LambdaRuntime::class, nullable: true)]
    #[SerializedName('Runtime')]
    private ?LambdaRuntime $runtime = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Role')]
    #[Assert\Length(max: 1024)]
    private ?string $role = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Handler')]
    #[Assert\Length(max: 255)]
    private ?string $handler = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    #[SerializedName('CodeSize')]
    private ?int $codeSize = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[SerializedName('Description')]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('Timeout')]
    private ?int $timeout = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('MemorySize')]
    private ?int $memorySize = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[SerializedName('LastModified')]
    private ?\DateTimeImmutable $lastModified = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('CodeSha256')]
    #[Assert\Length(max: 64)]
    private ?string $codeSha256 = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Version')]
    #[Assert\Length(max: 1024)]
    private ?string $version = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('DeadLetterConfig')]
    private ?array $deadLetterConfig = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('TracingConfig')]
    private ?array $tracingConfig = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('KMSKeyArn')]
    #[Assert\Length(max: 1024)]
    private ?string $kmsKeyArn = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('MasterArn')]
    #[Assert\Length(max: 1024)]
    private ?string $masterArn = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    #[SerializedName('RevisionId')]
    private ?string $revisionId = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('Architectures')]
    private ?array $architectures = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('EphemeralStorage')]
    private ?array $ephemeralStorage = null;

    #[ORM\Column(type: 'string', enumType: LambdaState::class, nullable: true)]
    #[SerializedName('State')]
    private ?LambdaState $state = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('StateReason')]
    #[Assert\Length(max: 1000)]
    private ?string $stateReason = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('StateReasonCode')]
    #[Assert\Length(max: 255)]
    private ?string $stateReasonCode = null;

    #[ORM\Column(type: 'string', enumType: PackageType::class, nullable: true)]
    #[SerializedName('PackageType')]
    private ?PackageType $packageType = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[SerializedName('ImageConfigResponse')]
    private ?array $imageConfigResponse = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('SigningProfileVersionArn')]
    #[Assert\Length(max: 1024)]
    private ?string $signingProfileVersionArn = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('SigningJobArn')]
    #[Assert\Length(max: 1024)]
    private ?string $signingJobArn = null;

    #[ORM\OneToOne(targetEntity: Environment::class, mappedBy: 'lambdaFunction', cascade: ['persist', 'remove'])]
    private ?Environment $environment = null;

    #[ORM\OneToOne(targetEntity: VpcConfig::class, mappedBy: 'lambdaFunction', cascade: ['persist', 'remove'])]
    private ?VpcConfig $vpcConfig = null;

    /**
     * @var Collection<int, Layer>
     */
    #[ORM\OneToMany(targetEntity: Layer::class, mappedBy: 'lambdaFunction', cascade: ['persist', 'remove'])]
    private Collection $layers;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\OneToMany(targetEntity: Tag::class, mappedBy: 'lambdaFunction', cascade: ['persist', 'remove'])]
    private Collection $tags;

    #[ORM\ManyToOne(inversedBy: 'lambdaFunctions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function __construct()
    {
        $this->layers = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFunctionName(): ?string
    {
        return $this->functionName;
    }

    public function setFunctionName(?string $functionName): static
    {
        $this->functionName = $functionName;
        return $this;
    }

    public function getFunctionArn(): ?string
    {
        return $this->functionArn;
    }

    public function setFunctionArn(?string $functionArn): static
    {
        $this->functionArn = $functionArn;
        return $this;
    }

    public function getRuntime(): ?LambdaRuntime
    {
        return $this->runtime;
    }

    public function setRuntime(?LambdaRuntime $runtime): static
    {
        $this->runtime = $runtime;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getHandler(): ?string
    {
        return $this->handler;
    }

    public function setHandler(?string $handler): static
    {
        $this->handler = $handler;
        return $this;
    }

    public function getCodeSize(): ?int
    {
        return $this->codeSize;
    }

    public function setCodeSize(?int $codeSize): static
    {
        $this->codeSize = $codeSize;
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

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout(?int $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function getMemorySize(): ?int
    {
        return $this->memorySize;
    }

    public function setMemorySize(?int $memorySize): static
    {
        $this->memorySize = $memorySize;
        return $this;
    }

    public function getLastModified(): ?\DateTimeImmutable
    {
        return $this->lastModified;
    }

    public function setLastModified(?\DateTimeImmutable $lastModified): static
    {
        $this->lastModified = $lastModified;
        return $this;
    }

    public function getCodeSha256(): ?string
    {
        return $this->codeSha256;
    }

    public function setCodeSha256(?string $codeSha256): static
    {
        $this->codeSha256 = $codeSha256;
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

    public function getDeadLetterConfig(): ?array
    {
        return $this->deadLetterConfig;
    }

    public function setDeadLetterConfig(?array $deadLetterConfig): static
    {
        $this->deadLetterConfig = $deadLetterConfig;
        return $this;
    }

    public function getTracingConfig(): ?array
    {
        return $this->tracingConfig;
    }

    public function setTracingConfig(?array $tracingConfig): static
    {
        $this->tracingConfig = $tracingConfig;
        return $this;
    }

    public function getKmsKeyArn(): ?string
    {
        return $this->kmsKeyArn;
    }

    public function setKmsKeyArn(?string $kmsKeyArn): static
    {
        $this->kmsKeyArn = $kmsKeyArn;
        return $this;
    }

    public function getMasterArn(): ?string
    {
        return $this->masterArn;
    }

    public function setMasterArn(?string $masterArn): static
    {
        $this->masterArn = $masterArn;
        return $this;
    }

    public function getRevisionId(): ?string
    {
        return $this->revisionId;
    }

    public function setRevisionId(?string $revisionId): static
    {
        $this->revisionId = $revisionId;
        return $this;
    }

    public function getArchitectures(): ?array
    {
        return $this->architectures;
    }

    public function setArchitectures(?array $architectures): static
    {
        $this->architectures = $architectures;
        return $this;
    }

    public function getEphemeralStorage(): ?array
    {
        return $this->ephemeralStorage;
    }

    public function setEphemeralStorage(?array $ephemeralStorage): static
    {
        $this->ephemeralStorage = $ephemeralStorage;
        return $this;
    }

    public function getState(): ?LambdaState
    {
        return $this->state;
    }

    public function setState(?LambdaState $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function getStateReason(): ?string
    {
        return $this->stateReason;
    }

    public function setStateReason(?string $stateReason): static
    {
        $this->stateReason = $stateReason;
        return $this;
    }

    public function getStateReasonCode(): ?string
    {
        return $this->stateReasonCode;
    }

    public function setStateReasonCode(?string $stateReasonCode): static
    {
        $this->stateReasonCode = $stateReasonCode;
        return $this;
    }

    public function getPackageType(): ?PackageType
    {
        return $this->packageType;
    }

    public function setPackageType(?PackageType $packageType): static
    {
        $this->packageType = $packageType;
        return $this;
    }

    public function getImageConfigResponse(): ?array
    {
        return $this->imageConfigResponse;
    }

    public function setImageConfigResponse(?array $imageConfigResponse): static
    {
        $this->imageConfigResponse = $imageConfigResponse;
        return $this;
    }

    public function getSigningProfileVersionArn(): ?string
    {
        return $this->signingProfileVersionArn;
    }

    public function setSigningProfileVersionArn(?string $signingProfileVersionArn): static
    {
        $this->signingProfileVersionArn = $signingProfileVersionArn;
        return $this;
    }

    public function getSigningJobArn(): ?string
    {
        return $this->signingJobArn;
    }

    public function setSigningJobArn(?string $signingJobArn): static
    {
        $this->signingJobArn = $signingJobArn;
        return $this;
    }

    public function getEnvironment(): ?Environment
    {
        return $this->environment;
    }

    public function setEnvironment(?Environment $environment): static
    {
        $this->environment = $environment;
        if ($environment !== null) {
            $environment->setLambdaFunction($this);
        }
        return $this;
    }

    public function getVpcConfig(): ?VpcConfig
    {
        return $this->vpcConfig;
    }

    public function setVpcConfig(?VpcConfig $vpcConfig): static
    {
        $this->vpcConfig = $vpcConfig;
        if ($vpcConfig !== null) {
            $vpcConfig->setLambdaFunction($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, Layer>
     */
    public function getLayers(): Collection
    {
        return $this->layers;
    }

    public function addLayer(Layer $layer): static
    {
        if (!$this->layers->contains($layer)) {
            $this->layers->add($layer);
            $layer->setLambdaFunction($this);
        }
        return $this;
    }

    public function removeLayer(Layer $layer): static
    {
        if ($this->layers->removeElement($layer)) {
            if ($layer->getLambdaFunction() === $this) {
                $layer->setLambdaFunction(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->setLambdaFunction($this);
        }
        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            if ($tag->getLambdaFunction() === $this) {
                $tag->setLambdaFunction(null);
            }
        }
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