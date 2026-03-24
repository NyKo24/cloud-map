<?php

namespace App\Entity\AWS\CloudWatch;

use App\Entity\CrawlVersion;
use App\Repository\AWS\CloudWatch\CloudWatchAlarmRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CloudWatchAlarmRepository::class)]
class CloudWatchAlarm
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('AlarmName')]
    #[Assert\Length(max: 255)]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?string $alarmName = null;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    #[SerializedName('AlarmArn')]
    #[Assert\Length(max: 2048)]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?string $alarmArn = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[SerializedName('AlarmDescription')]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?string $alarmDescription = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('StateValue')]
    #[Assert\Length(max: 255)]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?string $stateValue = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[SerializedName('StateReason')]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?string $stateReason = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('MetricName')]
    #[Assert\Length(max: 255)]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?string $metricName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Namespace')]
    #[Assert\Length(max: 255)]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?string $namespace = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('Statistic')]
    #[Assert\Length(max: 255)]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?string $statistic = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('Period')]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?int $period = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[SerializedName('EvaluationPeriods')]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?int $evaluationPeriods = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[SerializedName('Threshold')]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?float $threshold = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[SerializedName('ComparisonOperator')]
    #[Assert\Length(max: 255)]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?string $comparisonOperator = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[SerializedName('ActionsEnabled')]
    #[Groups(['cloudwatch_alarm_list_export'])]
    private ?bool $actionsEnabled = null;

    #[ORM\ManyToOne(inversedBy: 'cloudWatchAlarms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CrawlVersion $crawl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlarmName(): ?string
    {
        return $this->alarmName;
    }

    public function setAlarmName(?string $alarmName): static
    {
        $this->alarmName = $alarmName;
        return $this;
    }

    public function getAlarmArn(): ?string
    {
        return $this->alarmArn;
    }

    public function setAlarmArn(?string $alarmArn): static
    {
        $this->alarmArn = $alarmArn;
        return $this;
    }

    public function getAlarmDescription(): ?string
    {
        return $this->alarmDescription;
    }

    public function setAlarmDescription(?string $alarmDescription): static
    {
        $this->alarmDescription = $alarmDescription;
        return $this;
    }

    public function getStateValue(): ?string
    {
        return $this->stateValue;
    }

    public function setStateValue(?string $stateValue): static
    {
        $this->stateValue = $stateValue;
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

    public function getMetricName(): ?string
    {
        return $this->metricName;
    }

    public function setMetricName(?string $metricName): static
    {
        $this->metricName = $metricName;
        return $this;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function setNamespace(?string $namespace): static
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getStatistic(): ?string
    {
        return $this->statistic;
    }

    public function setStatistic(?string $statistic): static
    {
        $this->statistic = $statistic;
        return $this;
    }

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod(?int $period): static
    {
        $this->period = $period;
        return $this;
    }

    public function getEvaluationPeriods(): ?int
    {
        return $this->evaluationPeriods;
    }

    public function setEvaluationPeriods(?int $evaluationPeriods): static
    {
        $this->evaluationPeriods = $evaluationPeriods;
        return $this;
    }

    public function getThreshold(): ?float
    {
        return $this->threshold;
    }

    public function setThreshold(?float $threshold): static
    {
        $this->threshold = $threshold;
        return $this;
    }

    public function getComparisonOperator(): ?string
    {
        return $this->comparisonOperator;
    }

    public function setComparisonOperator(?string $comparisonOperator): static
    {
        $this->comparisonOperator = $comparisonOperator;
        return $this;
    }

    public function isActionsEnabled(): ?bool
    {
        return $this->actionsEnabled;
    }

    public function setActionsEnabled(?bool $actionsEnabled): static
    {
        $this->actionsEnabled = $actionsEnabled;
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
