# OneFit Events

[![Build Status](https://travis-ci.com/onefit/base.svg?token=yyNHsRRTPoEN35wt46sb&branch=master)](https://travis-ci.com/onefit/base)
[![StyleCI](https://styleci.io/repos/221408130/shield?branch=master)](https://styleci.io/repos/221408130)

This package contains services to produce and consume events using kafka stream processing

## Configuration
| Param | Description | Type | Default |
| --- | --- | --- | --- |
| `METADATA_BROKER_LIST` | Initial list of brokers as a CSV list of broker host or host:port. | string | localhost:9092 |
| `FLUSH_RETRIES` | Specifies the maximum amount of flush retries. | integer | 3 |
| `TOPIC_METADATA_REFRESH_INTERVAL_MS` | Period of time in milliseconds at which topic and broker metadata is refreshed in order to proactively discover any new brokers, topics, partitions or partition leader changes. Use -1 to disable the intervalled refresh (not recommended). If there are no locally referenced topics (no topic objects created, no messages produced, no subscription or no assignment) then only the broker list will be refreshed every interval but no more often than every 10s. | integer | 300000 |
| `TOPIC_METADATA_REFRESH_SPARSE` | Sparse metadata requests (consumes less network bandwidth) | boolean | true |
| `AUTO_OFFSET_RESET` | Action to take when there is no initial offset in offset store or the desired offset is out of range: 'smallest','earliest' - automatically reset the offset to the smallest offset, 'largest','latest' - automatically reset the offset to the largest offset, 'error' - trigger an error which is retrieved by consuming messages and checking 'message->err'. | enum (smallest, earliest, beginning, largest, latest, end, error) | smallest
| `INTERNAL_TERMINATION_SIGNAL` | Signal that librdkafka will use to quickly terminate on rd_kafka_destroy(). If this signal is not set then there will be a delay before rd_kafka_wait_destroyed() returns true as internal threads are timing out their system calls. If this signal is set however the delay will be minimal. The application should mask this signal as an internal signal handler is installed. | integer | 29 |
| `SOCKET_TIMEOUT_MS` | Default timeout for network requests. Producer: ProduceRequests will use the lesser value of socket.timeout.ms and remaining message.timeout.ms for the first message in the batch. Consumer: FetchRequests will use fetch.wait.max.ms + socket.timeout.ms. Admin: Admin requests will use socket.timeout.ms or explicitly set rd_kafka_AdminOptions_set_operation_timeout() value. | integer | 3000 |
| `ENABLE_AUTO_COMMIT` | Automatically and periodically commit offsets in the background. Note: setting this to false does not prevent the consumer from fetching previously committed start offsets. To circumvent this behaviour set specific start offsets per partition in the call to assign(). | boolean | true |
| `SOCKET_BLOCKING_MAX_MS` | Default timeout for network requests. Producer: ProduceRequests will use the lesser value of socket.timeout.ms and remaining message.timeout.ms for the first message in the batch. Consumer: FetchRequests will use fetch.wait.max.ms + socket.timeout.ms. Admin: Admin requests will use socket.timeout.ms or explicitly set rd_kafka_AdminOptions_set_operation_timeout() value. | integer | 50 |
| `MESSAGE_SIGNATURE_SALT` | Local message salt. Signing messages with provided salt will allow consumer to validate the signature if the salt is shared among producer and consumer outside of stream | string | (empty) |
| `MESSAGE_TIMEOUT_MS` | Local message timeout. This value is only enforced locally and limits the time a produced message waits for successful delivery. A time of 0 is infinite. This is the maximum time librdkafka may use to deliver a message (including retries). Delivery error occurs when either the retry count or the message timeout are exceeded. | integer | 3000 |
| `QUEUE_BUFFERING_MAX_MS` | Delay in milliseconds to wait for messages in the producer queue to accumulate before constructing message batches (MessageSets) to transmit to brokers. A higher value allows larger and more effective (less overhead, improved compression) batches of messages to accumulate at the expense of increased message delivery latency. | float | 50 |
