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
    public const EVENT_FRIEND_INVITE_ACCEPTED = 'friend_invite_accepted';
    public const EVENT_FRIEND_INVITE_DISMISSED = 'friend_invite_dismissed';
    public const EVENT_WORKOUT_INVITE_ACCEPTED = 'workout_invite_accepted';
    public const EVENT_WORKOUT_INVITE_DISMISSED = 'workout_invite_dismissed';
    public const EVENT_CHARGEABLE_STATUS_UPDATED = 'chargeable_status_updated';
}
