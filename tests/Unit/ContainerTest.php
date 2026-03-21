<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\Support\TestCase;
use Core\Container\Container;

class ContainerTest extends TestCase
{
    public function test_can_bind_and_resolve(): void
    {
        $container = new Container();

        $container->bind('greeting', fn() => 'Hello Kirpi!');

        $this->assertEquals('Hello Kirpi!', $container->make('greeting'));
    }

    public function test_singleton_returns_same_instance(): void
    {
        $container = new Container();

        $container->singleton('counter', fn() => new class {
            public int $count = 0;
        });

        $a = $container->make('counter');
        $b = $container->make('counter');

        $a->count++;

        $this->assertSame($a, $b);
        $this->assertEquals(1, $b->count);
    }

    public function test_can_bind_instance(): void
    {
        $container = new Container();
        $object    = new \stdClass();
        $object->name = 'Kirpi';

        $container->instance('obj', $object);

        $this->assertSame($object, $container->make('obj'));
    }

    public function test_auto_wiring(): void
    {
        $container = new Container();

        $result = $container->make(\Core\Config\Repository::class, [
            'configPath' => BASE_PATH . '/config',
        ]);

        $this->assertInstanceOf(\Core\Config\Repository::class, $result);
    }

    public function test_bound_returns_true_for_registered(): void
    {
        $container = new Container();
        $container->bind('test', fn() => 'value');

        $this->assertTrue($container->bound('test'));
        $this->assertFalse($container->bound('unknown'));
    }
}