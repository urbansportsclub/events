## Consumer service in Symfony 4.x and 5.x

### Installation

To get started, you just need to pull *onefit/events* package into your application.

~~~
composer require onefit/events
~~~

After the installation you need to load this library in your `config/bundles.php` file:
```
<?php

return [
    //...
    OneFit\Events\Bundle\EventsBundle::class => ['all' => true],
];

```

Also you should change your `.env` adding some parameters:
```
CONSUMER_OFFSET_RESET_DEFAULT='earliest' # References to rdkafka auto.offset.reset
CONSUMER_AUTO_COMMIT_DEFAULT='false' # References to rdkafka enable.auto.commit
CONSUMER_STORE_OFFSET_DEFAULT='false' # References to enable.auto.offset.store
CONSUMER_GROUP_ID_DEFAULT='default' # To identify kafka consumer instance (rdkafka group.id)
KAFKA_BROKER_LIST_DEFAULT='127.0.0.1' # Where is the kafka broker (rdkafka metadata.broker.list)
```
### Usage

You can now inject the `ConsumerService` as it is autowireable (using Symfony DI). <br>Take a look at this command example:

```php

use OneFit\Events\Models\Message;
use OneFit\Events\Services\ConsumerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KafkaConsumerCommand extends Command
{
    private const KAFKA_TIMEOUT = 3000;

    private const CONSUMABLE_TOPICS = [
        'checkins',
    ];

    private const ALLOWED_TYPES = [
        'checkin',
    ];

    private ConsumerService $consumer;

    protected static $defaultName = 'kafka:consume';

    public function __construct(ConsumerService $consumerService)
    {
            $this->consumer = $consumerService;
    }
    
    // configure()...

    protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $this->io = new SymfonyStyle($input, $output);
    
            $this->io->write(
                sprintf(
                    'Initiating consumer for topics [%s] with group id [%s]',
                    implode(',', self::CONSUMABLE_TOPICS),
                    getenv('CONSUMER_GROUP_ID') ?? getenv('CONSUMER_GROUP_ID_DEFAULT')
                )
            );
    
            $this->consumer->subscribe(self::CONSUMABLE_TOPICS);
    
            while (true) {
                try {
                    /** @var Message $message */
                    $message = $this->consumer->consume(self::KAFKA_TIMEOUT);
                } catch (\Exception $exception) {
                    // Log the error
                }
            }
    
            return true;
        }
}          
``` 