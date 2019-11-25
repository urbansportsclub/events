<?php

namespace OneFit\Events\Models;

/**
 * Class Event
 * @package OneFit\Events\Models
 */
abstract class Event
{
    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_DELETED = 'deleted';
    public const EVENT_GENERIC = 'generic';
}
