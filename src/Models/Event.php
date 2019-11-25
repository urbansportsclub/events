<?php

namespace OneFit\Events\Models;

/**
 * Class Event.
 */
abstract class Event
{
    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_DELETED = 'deleted';
    public const EVENT_GENERIC = 'generic';
}
