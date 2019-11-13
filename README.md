# OneFit Events

[![Build Status](https://travis-ci.com/onefit/base.svg?token=yyNHsRRTPoEN35wt46sb&branch=master)](https://travis-ci.com/onefit/base)
[![StyleCI](https://styleci.io/repos/221408130/shield?branch=master)](https://styleci.io/repos/221408130)

This package contains services to produce and consume events using kafka stream processing

## Configuration
| Param | Description | Type | Default |
| --- | --- | --- | --- |
| `METADATA_BROKER_LIST` | Initial list of brokers as a CSV list of broker host or host:port. | string | localhost:9092 |
| `FLUSH_TIMEOUT_MS` | Specifies the maximum amount of time (in milliseconds) that the call will block. To wait indefinitely provide -1. | integer | 10000 |
| `FLUSH_RETRIES` | Specifies the maximum amount of flush retries. | integer | 10 |
| `QUEUED_MAX_MESSAGES_KBYTES` | 	Maximum number of kilobytes per topic+partition in the local consumer queue. This value may be overshot by fetch.message.max.bytes. This property has higher priority than queued.min.messages. | integer | 1048576 |
| `TOPIC_METADATA_REFRESH_INTERVAL_MS` | Period of time in milliseconds at which topic and broker metadata is refreshed in order to proactively discover any new brokers, topics, partitions or partition leader changes. Use -1 to disable the intervalled refresh (not recommended). If there are no locally referenced topics (no topic objects created, no messages produced, no subscription or no assignment) then only the broker list will be refreshed every interval but no more often than every 10s. | integer | 300000 |
| `TOPIC_METADATA_REFRESH_SPARSE` | Sparse metadata requests (consumes less network bandwidth) | boolean | true |
| `QUEUE_BUFFERING_MAX_MS` | Delay in milliseconds to wait for messages in the producer queue to accumulate before constructing message batches (MessageSets) to transmit to brokers. A higher value allows larger and more effective (less overhead, improved compression) batches of messages to accumulate at the expense of increased message delivery latency. | float | 0.5 |
| `AUTO_OFFSET_RESET` | Action to take when there is no initial offset in offset store or the desired offset is out of range: 'smallest','earliest' - automatically reset the offset to the smallest offset, 'largest','latest' - automatically reset the offset to the largest offset, 'error' - trigger an error which is retrieved by consuming messages and checking 'message->err'. | enum (smallest, earliest, beginning, largest, latest, end, error) | smallest
| `INTERNAL_TERMINATION_SIGNAL` | Signal that librdkafka will use to quickly terminate on rd_kafka_destroy(). If this signal is not set then there will be a delay before rd_kafka_wait_destroyed() returns true as internal threads are timing out their system calls. If this signal is set however the delay will be minimal. The application should mask this signal as an internal signal handler is installed. | integer | 29 |
| `SOCKET_TIMEOUT_MS` | Default timeout for network requests. Producer: ProduceRequests will use the lesser value of socket.timeout.ms and remaining message.timeout.ms for the first message in the batch. Consumer: FetchRequests will use fetch.wait.max.ms + socket.timeout.ms. Admin: Admin requests will use socket.timeout.ms or explicitly set rd_kafka_AdminOptions_set_operation_timeout() value. | integer | 60000 |
| `CONSUME_TIMEOUT_MS` | Specifies the maximum amount of time (in milliseconds) that the consume call will block | integer | 120000 |
| `ENABLE_IDEMPOTENCE` | When set to true, the producer will ensure that messages are successfully produced exactly once and in the original produce order. | boolean | false |
