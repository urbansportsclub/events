<?php

namespace OneFit\Events\Models;

/**
 * Interface Event
 * @package OneFit\Events\Models
 */
interface Event
{
    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_DELETED = 'deleted';
    public const EVENT_FRIEND_INVITE_ACCEPTED = 'friend_invite_accepted';
    public const EVENT_FRIEND_INVITE_DISMISSED = 'friend_invite_dismissed';
    public const EVENT_WORKOUT_INVITE_ACCEPTED = 'workout_invite_accepted';
    public const EVENT_WORKOUT_INVITE_DISMISSED = 'workout_invite_dismissed';
    public const EVENT_FINE_OPENED = 'fine_opened';
    public const EVENT_FINE_CLOSED = 'fine_closed';
    public const EVENT_ORDER_OPENED = 'order_opened';
    public const EVENT_ORDER_CLOSED = 'order_closed';
    public const EVENT_RECURRING_OPENED = 'recurring_opened';
    public const EVENT_RECURRING_CLOSED = 'recurring_closed';
    public const EVENT_SURPLUS_MANDATE_OPENED = 'surplus_mandate_opened';
    public const EVENT_SURPLUS_MANDATE_CLOSED = 'surplus_mandate_closed';
}
