<?php

namespace App\Search;

class CloudWatchAlarmListSearch
{
    public ?string $alarmName = null;
    public ?string $stateValue = null;
    public ?string $metricName = null;
    public ?string $namespace = null;
    public ?bool $actionsEnabled = null;
    public ?int $userId = null;
}
