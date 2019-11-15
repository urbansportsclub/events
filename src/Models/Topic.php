<?php

namespace OneFit\Events\Models;

/**
 * Class Topic.
 */
abstract class Topic
{
    public const EVENT_GLOBAL = 'event_global';
    public const EVENT_RESOURCE_CREATED = 'event_resource_created';
    public const EVENT_RESOURCE_UPDATED = 'event_resource_updated';
    public const EVENT_RESOURCE_DELETED = 'event_resource_deleted';
}
