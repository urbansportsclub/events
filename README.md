# Laravel/Lumen 5.x 6.x Kafka Events

[![Build Status](https://travis-ci.com/onefit/base.svg?token=yyNHsRRTPoEN35wt46sb&branch=master)](https://travis-ci.com/onefit/base)
[![StyleCI](https://styleci.io/repos/221408130/shield?branch=master)](https://styleci.io/repos/221408130)

This package contains services to produce and consume events using kafka stream processing. The library supports both laravel 5.x and 6.x versions.

Package uses [arnaud-lb/php-rdkafka](https://github.com/arnaud-lb/php-rdkafka) library which is a php extension wrapper around [edenhill/librdkafka](https://github.com/edenhill/librdkafka) library.

## Package requirements
* librdkafka >= 0.11.x.
* php >= 7.1
* ext-rdkafka
* ext-pcntl
* ext-json

**Important:** Events package is expecting that you have at least defined env variable *METADATA_BROKER_LIST* which is as its name states, a comma-separated list of the kafka brokers for your application to use, e.g. ```broker-0.localhost:9092,broker-1.localhost:9092```

List of the available configuration parameters with their description can be found in [configuration overview](docs/CONFIGURATION.md).

## Installation
To get started, you just need to pull *onefit/events* package into your application.

For the laravel applications the service is auto-discoverable, so beside pulling the package and specifying application events, there is not much to do.

For lumen applications, both *EventsServiceProvider* and *events* configuration should be manually registered inside of the *bootstrap/app.php* for given lumen application.

In a nutshell, the package is plug-and-play. After you pull the package inside of your app (most likely using ```composer update onefit/events```), the only thing you need to do is to specify which of the events your application should produce. For this purpose we extended the existing laravel functionality of the observers and listeners.

The basic configuration would look something like this (*events.php*).
```
<?php

return [
    'producers' => [
        \MyModels\Member::class => [
            'member' => 'member_domain',
        ],
    ],
    'listeners' => [
        'notifications' => 'member_domain',
    ],
    'source' => 'my-awesome-app',
];
```

What we have in the example above, is what we refer to as generic observers. While *source* is hopefully self-explanatory, *producers* and *listeners* require at least some explanation, hence the sections on their own.

## Producers
The producers configuration is part of configuration where we are mapping our project models with the type of the event which will be produced. This is done so we can have loose coupling between our application domain and the potential consumers, which do not necessarily need to know about our domain models and would instead rather focus on the occurred event.

The structure is as follows ```domain_model => [ common_event_type => topic_name ]```

If you dive into implementation, you will see that when initialising *EventServiceProvider*, for every *domain_model* specified inside of the producers configuration, we are registering generic observers to listen for the following events: *created*, *deleted*, *updated* for the given domain model. We are not going to explain laravel events, since this is something that a reader should already be aware of, but in case you would like to refresh your memory, please follow this [link](https://laravel.com/docs/5.8/eloquent#events).

One of the good things about this kind of behaviour is that these events, or more precisely observers registered to handle them, are non blocking, meaning that if the error occurs while producing an event, the initial process that caused the event will not be stopped, nor the data will be lost. For given configuration, every domain model specified will have a generic observer attached, which will listen the events mentioned above.

## Listeners
Listeners are similar but not the same as producers. Their primary intention was to give us an ability to produce custom events, not related to a specific domain model or its activity. 

The structure is as follows ```event_type_to_listen => topic_name```

What is happening in the background, is that during the initialisation of the *EventsServiceProvider* we are registering custom observer to listen only to events of given type and produce them to kafka stream once they occur.

With the given configuration example, we are able to produce a custom event inside of laravel/lumen app as following:

```event('notifications.user_paid', $myUserInstance);```

**Important:** Since the events package was mainly build for laravel/lumen, the model sent with the event, as all of the other eloquent models, must be an instance of *Illuminate\Contracts\Queue\QueueableEntity* interface which is a part of [illuminate/support](https://github.com/illuminate/support) package.

Hopefully this is enough to get you going, if you would like to find out the magic happening in the background, please checkout the implementation inside of the events package.

**Note:** We did not implemented all of the available configuration settings, since at this point we don’t have a need to navigate from default values. To see a list of all of the available configuration parameters, click [here](https://github.com/edenhill/librdkafka/blob/master/CONFIGURATION.md).

## License
onefit/events is available under the MIT license. See the [LICENSE](LICENSE) file for more information.
