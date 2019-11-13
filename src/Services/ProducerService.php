<?php

namespace OneFit\Events\Services;

use RdKafka\Conf;
use RdKafka\Producer;

/**
 * Class ProducerService
 * @package OneFit\Events\Services
 */
class ProducerService
{
    /**
     * @var Producer
     */
    private $producer;

    /**
     * @var Conf
     */
    private $configuration;

    /**
     * ProducerService constructor.
     * @param Conf $configuration
     * @param Producer $producer
     */
    public function __construct(Producer $producer, Conf $configuration)
    {
        $this->producer = $producer;
        $this->configuration = $configuration;

        $this->setConfiguration();
    }

    /**
     * @param string $topic
     * @param string $payload
     */
    public function produce(string $topic, string $payload): void
    {
        $topic = $this->producer->newTopic($topic);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $payload);
        $this->producer->poll(0);
        $this->flushProducer(env('FLUSH_RETRIES', 10));
    }

    /**
     * @return void
     */
    private function setConfiguration(): void
    {
        // Initial list of Kafka brokers
        $this->configuration->set('metadata.broker.list', env('METADATA_BROKER_LIST', 'localhost:9092'));

        // Default timeout for network requests
        $this->configuration->set('socket.timeout.ms', env('SOCKET_TIMEOUT_MS', 60000));

        // Produce exactly once and keep the original produce order
        $this->configuration->set('enable.idempotence', env('ENABLE_IDEMPOTENCE', false));

        // Fetch only the topics in use, reduce the bandwidth
        $this->configuration->set('topic.metadata.refresh.sparse', env('TOPIC_METADATA_REFRESH_SPARSE', true));
        $this->configuration->set('topic.metadata.refresh.interval.ms', env('TOPIC_METADATA_REFRESH_INTERVAL_MS', 300000));

        // Default time before sending a batch of messages
        $this->configuration->set('queue.buffering.max.ms', env('QUEUE_BUFFERING_MAX_MS', 0.5));

        // Signal that librdkafka will use to quickly terminate on rd_kafka_destroy()
        pcntl_sigprocmask(SIG_BLOCK, [env('INTERNAL_TERMINATION_SIGNAL', 29)]);
        $this->configuration->set('internal.termination.signal', env('INTERNAL_TERMINATION_SIGNAL', 29));
    }

    /**
     * @param int $retries
     * @param int $counter
     */
    private function flushProducer(int $retries, int $counter = 0): void
    {
        if ($counter > $retries) {
            throw new \RuntimeException('Was unable to flush, messages might be lost!');
        }

        $response = $this->producer->flush(env('FLUSH_TIMEOUT_MS', 10000));

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $response) {
            $counter++;
            $this->flushProducer($retries, $counter);
        }
    }
}
