<?php

namespace Tests\Unit;

use Illuminate\Contracts\Foundation\Application;
use OneFit\Events\EventsServiceProvider;
use OneFit\Events\Services\ConsumerService;
use OneFit\Events\Services\ProducerService;
use PHPUnit\Framework\MockObject\MockClass;
use PHPUnit\Framework\TestCase;

/**
 * Class EventsServiceProviderTest
 * @package Tests\Unit
 */
class EventsServiceProviderTest extends TestCase
{
    /**
     * @var Application|MockClass
     */
    private $applicationMock;

    /**
     * @var EventsServiceProvider
     */
    private $eventServiceProvider;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->applicationMock = $this->createMock(Application::class);
        $this->eventServiceProvider = new EventsServiceProvider($this->applicationMock);

        parent::setUp();
    }

    /** @test */
    public function can_register_bindings()
    {
        $this->applicationMock
            ->expects($this->exactly(2))
            ->method('singleton')
            ->withConsecutive(
                [ProducerService::class, $this->isType('callable')],
                [ConsumerService::class, $this->isType('callable')]
            );

        $this->eventServiceProvider->register();
    }

    /** @test */
    public function can_get_provided_services()
    {
        $services = $this->eventServiceProvider->provides();

        $this->assertContains(ProducerService::class, $services);
        $this->assertContains(ConsumerService::class, $services);
    }
}
