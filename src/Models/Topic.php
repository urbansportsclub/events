<?php

namespace OneFit\Events\Models;

/**
 * Class Topic
 * @package OneFit\Events\Models
 */
abstract class Topic
{
    public const GLOBAL_EVENT = 'global_event';
    public const RESOURCE_CREATED = 'resource_created';
    public const RESOURCE_UPDATED = 'resource_updated';
    public const RESOURCE_DELETED = 'resource_deleted';
}
