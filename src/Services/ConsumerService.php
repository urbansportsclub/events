<?php

namespace OneFit\Events\Services;

use RdKafka\Conf;
use RdKafka\KafkaConsumer;

/**
 * Class ConsumerService.
 */
class ConsumerService
{
    /**
     * @var KafkaConsumer
     */
    private $consumer;

    /**
     * @var Conf
     */
    private $configuration;

    /**
     * ConsumerService constructor.
     * @param KafkaConsumer $consumer
     * @param Conf          $configuration
     */
    public function __construct(KafkaConsumer $consumer, Conf $configuration)
    {
        $this->consumer = $consumer;
        $this->configuration = $configuration;

        $this->setConfiguration();
    }

    /**
     * @param  string             $groupId
     * @param  callable           $consumeCb
     * @param  string             $topic
     * @throws \RdKafka\Exception
     */
    public function consume(string $groupId, callable $consumeCb, string $topic): void
    {
        // Set the group id. This is required when storing offsets on the broker
        $this->configuration->set('group.id', $groupId);

        // Set consume callback to use with poll
        $this->configuration->setConsumeCb($consumeCb);

        $this->consumer->subscribe([$topic]);
        $timeout = env('CONSUME_TIMEOUT_MS', 120000);

        while (true) {
            $message = $this->consumer->consume($timeout);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    echo "Got message: {$message->payload} from {$message->topic_name}".PHP_EOL;
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo 'No more messages; will wait for more'.PHP_EOL;
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo 'Timed out'.PHP_EOL;
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }

    /**
     * @return void
     */
    private function setConfiguration(): void
    {
        // Initial list of Kafka brokers
        $this->configuration->set('metadata.broker.list', env('METADATA_BROKER_LIST', 'localhost:9092'));

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $this->configuration->set('auto.offset.reset', env('AUTO_OFFSET_RESET', 'smallest'));

        // Fetch only the topics in use, reduce the bandwidth
        $this->configuration->set('topic.metadata.refresh.sparse', env('TOPIC_METADATA_REFRESH_SPARSE', true));
        $this->configuration->set('topic.metadata.refresh.interval.ms', env('TOPIC_METADATA_REFRESH_INTERVAL_MS', 300000));

        // Default time before sending a batch of messages
        $this->configuration->set('queue.buffering.max.ms', env('QUEUE_BUFFERING_MAX_MS', 0.5));

        // Signal that librdkafka will use to quickly terminate on rd_kafka_destroy()
        pcntl_sigprocmask(SIG_BLOCK, [env('INTERNAL_TERMINATION_SIGNAL', 29)]);
        $this->configuration->set('internal.termination.signal', env('INTERNAL_TERMINATION_SIGNAL', 29));
    }
}
