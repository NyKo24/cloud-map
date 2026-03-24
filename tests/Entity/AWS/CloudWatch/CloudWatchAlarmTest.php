<?php

namespace App\Tests\Entity\AWS\CloudWatch;

use App\Entity\AWS\CloudWatch\CloudWatchAlarm;
use App\Entity\CrawlVersion;
use PHPUnit\Framework\TestCase;

class CloudWatchAlarmTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $alarm = new CloudWatchAlarm();

        $alarm->setAlarmName('my-alarm');
        $this->assertSame('my-alarm', $alarm->getAlarmName());

        $alarm->setAlarmArn('arn:aws:cloudwatch:us-east-1:123456789012:alarm:my-alarm');
        $this->assertSame('arn:aws:cloudwatch:us-east-1:123456789012:alarm:my-alarm', $alarm->getAlarmArn());

        $alarm->setAlarmDescription('Test alarm description');
        $this->assertSame('Test alarm description', $alarm->getAlarmDescription());

        $alarm->setStateValue('OK');
        $this->assertSame('OK', $alarm->getStateValue());

        $alarm->setStateReason('Threshold not crossed');
        $this->assertSame('Threshold not crossed', $alarm->getStateReason());

        $alarm->setMetricName('CPUUtilization');
        $this->assertSame('CPUUtilization', $alarm->getMetricName());

        $alarm->setNamespace('AWS/EC2');
        $this->assertSame('AWS/EC2', $alarm->getNamespace());

        $alarm->setStatistic('Average');
        $this->assertSame('Average', $alarm->getStatistic());

        $alarm->setPeriod(300);
        $this->assertSame(300, $alarm->getPeriod());

        $alarm->setEvaluationPeriods(3);
        $this->assertSame(3, $alarm->getEvaluationPeriods());

        $alarm->setThreshold(80.0);
        $this->assertSame(80.0, $alarm->getThreshold());

        $alarm->setComparisonOperator('GreaterThanThreshold');
        $this->assertSame('GreaterThanThreshold', $alarm->getComparisonOperator());

        $alarm->setActionsEnabled(true);
        $this->assertTrue($alarm->isActionsEnabled());
    }

    public function testCrawlVersionRelationship(): void
    {
        $alarm = new CloudWatchAlarm();
        $crawlVersion = new CrawlVersion();

        $alarm->setCrawl($crawlVersion);
        $this->assertSame($crawlVersion, $alarm->getCrawl());
    }

    public function testCrawlVersionAddCloudWatchAlarm(): void
    {
        $crawlVersion = new CrawlVersion();
        $alarm = new CloudWatchAlarm();

        $crawlVersion->addCloudWatchAlarm($alarm);

        $this->assertCount(1, $crawlVersion->getCloudWatchAlarms());
        $this->assertSame($crawlVersion, $alarm->getCrawl());
    }

    public function testCrawlVersionAddCloudWatchAlarmDoesNotDuplicate(): void
    {
        $crawlVersion = new CrawlVersion();
        $alarm = new CloudWatchAlarm();

        $crawlVersion->addCloudWatchAlarm($alarm);
        $crawlVersion->addCloudWatchAlarm($alarm);

        $this->assertCount(1, $crawlVersion->getCloudWatchAlarms());
    }

    public function testCrawlVersionRemoveCloudWatchAlarm(): void
    {
        $crawlVersion = new CrawlVersion();
        $alarm = new CloudWatchAlarm();

        $crawlVersion->addCloudWatchAlarm($alarm);
        $this->assertCount(1, $crawlVersion->getCloudWatchAlarms());

        $crawlVersion->removeCloudWatchAlarm($alarm);
        $this->assertCount(0, $crawlVersion->getCloudWatchAlarms());
        $this->assertNull($alarm->getCrawl());
    }

    public function testDefaultValues(): void
    {
        $alarm = new CloudWatchAlarm();

        $this->assertNull($alarm->getId());
        $this->assertNull($alarm->getAlarmName());
        $this->assertNull($alarm->getAlarmArn());
        $this->assertNull($alarm->getAlarmDescription());
        $this->assertNull($alarm->getStateValue());
        $this->assertNull($alarm->getStateReason());
        $this->assertNull($alarm->getMetricName());
        $this->assertNull($alarm->getNamespace());
        $this->assertNull($alarm->getStatistic());
        $this->assertNull($alarm->getPeriod());
        $this->assertNull($alarm->getEvaluationPeriods());
        $this->assertNull($alarm->getThreshold());
        $this->assertNull($alarm->getComparisonOperator());
        $this->assertNull($alarm->isActionsEnabled());
        $this->assertNull($alarm->getCrawl());
    }

    public function testSettersReturnSelf(): void
    {
        $alarm = new CloudWatchAlarm();

        $this->assertSame($alarm, $alarm->setAlarmName('test'));
        $this->assertSame($alarm, $alarm->setAlarmArn('arn'));
        $this->assertSame($alarm, $alarm->setAlarmDescription('desc'));
        $this->assertSame($alarm, $alarm->setStateValue('OK'));
        $this->assertSame($alarm, $alarm->setStateReason('reason'));
        $this->assertSame($alarm, $alarm->setMetricName('metric'));
        $this->assertSame($alarm, $alarm->setNamespace('ns'));
        $this->assertSame($alarm, $alarm->setStatistic('Average'));
        $this->assertSame($alarm, $alarm->setPeriod(300));
        $this->assertSame($alarm, $alarm->setEvaluationPeriods(1));
        $this->assertSame($alarm, $alarm->setThreshold(50.0));
        $this->assertSame($alarm, $alarm->setComparisonOperator('GreaterThanThreshold'));
        $this->assertSame($alarm, $alarm->setActionsEnabled(true));
        $this->assertSame($alarm, $alarm->setCrawl(new CrawlVersion()));
    }
}
