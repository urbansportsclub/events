<?php

namespace OneFit\Events\Services;

use Closure;
use FlixTech\SchemaRegistryApi\Registry;
use FlixTech\AvroSerializer\Objects\RecordSerializer;

class RecordSerializerServiceFactory
{
    /**
     * @var Registry\PromisingRegistry
     */
    private static $registry;

    /**
     * RecordSerializerServiceFactory constructor.
     *
     * @param Registry\PromisingRegistry $registry
     */
    public function __construct(Registry\PromisingRegistry $registry)
    {
        static::$registry = $registry;
    }

    /**
     * @return Closure
     */
    public static function create(): Closure
    {
        return function () {
            new RecordSerializer(static::$registry);
        };
    }
}
