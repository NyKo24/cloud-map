<?php

namespace App\Tests\Controller\App;

use App\Controller\App\AWSCloudWatchController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Attribute\Route;

class AWSCloudWatchControllerTest extends TestCase
{
    public function testIndexRouteIsDefinedCorrectly(): void
    {
        $method = new \ReflectionMethod(AWSCloudWatchController::class, 'index');
        $attributes = $method->getAttributes(Route::class);

        $this->assertCount(1, $attributes);

        $route = $attributes[0]->newInstance();
        $this->assertSame('/app/aws/cloudwatch/alarms', $route->getPath());
        $this->assertSame('app_aws_cloudwatch_alarms_list', $route->getName());
    }

    public function testIndexFrameRouteIsDefinedCorrectly(): void
    {
        $method = new \ReflectionMethod(AWSCloudWatchController::class, 'indexFrame');
        $attributes = $method->getAttributes(Route::class);

        $this->assertCount(1, $attributes);

        $route = $attributes[0]->newInstance();
        $this->assertSame('/app/aws/cloudwatch/alarms/_frame', $route->getPath());
        $this->assertSame('app_aws_cloudwatch_alarms_list_frame', $route->getName());
    }

    public function testIndexExportRouteIsDefinedCorrectly(): void
    {
        $method = new \ReflectionMethod(AWSCloudWatchController::class, 'indexExport');
        $attributes = $method->getAttributes(Route::class);

        $this->assertCount(1, $attributes);

        $route = $attributes[0]->newInstance();
        $this->assertSame('/app/aws/cloudwatch/alarms/export', $route->getPath());
        $this->assertSame('app_aws_cloudwatch_alarms_list_export', $route->getName());
    }

    public function testControllerHasThreeRoutes(): void
    {
        $class = new \ReflectionClass(AWSCloudWatchController::class);
        $routeCount = 0;

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== AWSCloudWatchController::class) {
                continue;
            }
            if (count($method->getAttributes(Route::class)) > 0) {
                $routeCount++;
            }
        }

        $this->assertSame(3, $routeCount);
    }

    public function testIndexFrameAcceptsRequestAndDependencies(): void
    {
        $method = new \ReflectionMethod(AWSCloudWatchController::class, 'indexFrame');
        $params = $method->getParameters();
        $paramNames = array_map(fn(\ReflectionParameter $p) => $p->getName(), $params);

        $this->assertContains('request', $paramNames);
        $this->assertContains('cloudWatchAlarmRepository', $paramNames);
        $this->assertContains('paginator', $paramNames);
    }

    public function testIndexExportAcceptsRequestAndDependencies(): void
    {
        $method = new \ReflectionMethod(AWSCloudWatchController::class, 'indexExport');
        $params = $method->getParameters();
        $paramNames = array_map(fn(\ReflectionParameter $p) => $p->getName(), $params);

        $this->assertContains('request', $paramNames);
        $this->assertContains('cloudWatchAlarmRepository', $paramNames);
        $this->assertContains('serializer', $paramNames);
    }

    public function testAllRoutesAreUnderAppPrefix(): void
    {
        $class = new \ReflectionClass(AWSCloudWatchController::class);

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== AWSCloudWatchController::class) {
                continue;
            }
            $attributes = $method->getAttributes(Route::class);
            foreach ($attributes as $attr) {
                $route = $attr->newInstance();
                $this->assertStringStartsWith('/app/', $route->getPath(),
                    sprintf('Route %s should be under /app/ prefix', $route->getName()));
            }
        }
    }
}
