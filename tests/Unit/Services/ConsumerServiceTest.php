<?php

namespace Tests\Unit\Services;

use OneFit\Events\Services\ConsumerService;
use PHPUnit\Framework\MockObject\MockClass;
use PHPUnit\Framework\TestCase;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

/**
 * Class ConsumerServiceTest
 * @package Tests\Unit\Services
 */
class ConsumerServiceTest extends TestCase
{
    /**
     * @var KafkaConsumer|MockClass
     */
    private $consumerMock;

    /**
     * @var Conf|MockClass
     */
    private $configurationMock;

    /**
     * @var ConsumerService
     */
    private $consumerService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->consumerMock = $this->createMock(KafkaConsumer::class);
        $this->configurationMock = $this->createMock(Conf::class);

        $this->consumerService = new ConsumerService($this->consumerMock, $this->configurationMock);
    }

    /** @test */
    public function configuration_will_be_set()
    {
        $this->configurationMock
            ->expects($this->any())
            ->method('set')
            ->withConsecutive(
                ['metadata.broker.list', 'localhost:9092'],
                ['auto.offset.reset', 'smallest'],
                ['topic.metadata.refresh.sparse', true],
                ['topic.metadata.refresh.interval.ms', 300000],
                ['queue.buffering.max.ms', 0.5],
                ['internal.termination.signal', 29]
            );

        $producer = new ConsumerService($this->consumerMock, $this->configurationMock);

        $this->assertInstanceOf(ConsumerService::class, $producer);
    }

    /** @test */
    public function can_set_group_id()
    {
        $groupId = 'friend_request_silent_push';

        $this->configurationMock
            ->expects($this->once())
            ->method('set')
            ->with('group.id', $groupId);

        $this->consumerService->setGroupId($groupId);
    }

    /** @test */
    public function can_set_consumer_cb()
    {
        $consumeCb = function () {};

        $this->configurationMock
            ->expects($this->once())
            ->method('setConsumeCb')
            ->with($consumeCb);

        $this->consumerService->setConsumeCb($consumeCb);
    }

    /** @test */
    public function can_subscribe_to_topics()
    {
        $topics = ['friend_request_received', 'friend_request_sent'];

        $this->consumerMock
            ->expects($this->once())
            ->method('subscribe')
            ->with($topics);

        $this->consumerService->subscribe($topics);
    }

    /** @test */
    public function can_consume_stored_messages()
    {
        $this->consumerMock
            ->expects($this->once())
            ->method('consume')
            ->with(120000);

        $this->consumerService->consume();
    }
}
