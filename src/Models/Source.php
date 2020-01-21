<?php

namespace OneFit\Events\Models;

/**
 * Interface Source
 * @package OneFit\Events\Models
 */
interface Source
{
    public const API = 'api';
    public const AUTH = 'auth';
    public const BACK_OFFICE = 'back_office';
    public const QUEUE_WORKER = 'queue_worker';
    public const UNDEFINED = 'undefined';
    public const PHP_KAFKA_CONSUMER = 'php-kafka-consumer';
}
