## Manual
Besides using the package to produce events in a generic way, this package also provides a way to manually produce and consume events using package services provided by service provider.

## Producing events
Producing an event is quite simple. If you dive into implementation you will notice that `ProducerService` exposes `produce` and `flush` methods.

**Important:** Without proper shutdown, messages can get lost. It is a client responsibility to call flush periodically. This should typically be done prior to destroying a producer instance to make sure all queued and in-flight produce requests are completed before terminating.

**Note:** `ProducerService::produce` method accept predefined `Message` object

```
<?php

$message = new OneFit\Events\Models\Message();

$message->setType($type)
        ->setSource($source)
        ->setSalt($salt)
        ->setId($id)
        ->setEvent($event)
        ->setConnection($connection)
        ->setPayload($payload);

$producer = app()->make(OneFit\Events\Services\ProducerService::class);

$producer->produce($message, $topic);
$producer->flush();
```

All of the message parameters are optional strings, created for our business need. Feel free to fork and update `Message` specification for your needs.

## Consuming events 
`ConsumerService` exposes `consume` and `subscribe` methods. Before consuming messages from kafka stream we need to subscribe to a certain topic or multiple topics.
 
**Important:** In order to consume events you will need initiate `ConsumerService` with sending group id to the service provider.

**Note:** This example assumes that a reader is familiar with kafka terminology, and knows what group id means/does.

```
<?php

$consumer = app()->make(ConsumerService::class, ['group_id' => $groupId]);
$consumer->subscribe([$topic]);

while (true) {
    $message = $consumer->consume(5000);
    if ($message->hasError()) {
        echo 'Error: '.$message->getError();
    } else {
        echo 'Id: '.$message->getId().PHP_EOL;
        echo 'Type: '.$message->getType().PHP_EOL;
        echo 'Event: '.$message->getEvent().PHP_EOL;
        echo 'Source: '.$message->getSource().PHP_EOL;
        echo 'Connection: '.$message->getConnection().PHP_EOL;
        echo 'Payload: '.$message->getPayload().PHP_EOL;
    }
}
```
