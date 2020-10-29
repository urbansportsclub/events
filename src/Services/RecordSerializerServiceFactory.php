<?php

namespace OneFit\Events\Services;

use FlixTech\SchemaRegistryApi\Registry;
use FlixTech\AvroSerializer\Objects\RecordSerializer;

class RecordSerializerServiceFactory
{
    private static $registry;

    public function __construct(Registry\GuzzlePromiseAsyncRegistry $registry)
    {
        static::$registry = $registry;
    }

    public static function create()
    {
        return function () {
            new RecordSerializer(static::$registry);
        };
    }
}
