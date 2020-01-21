<?php

namespace OneFit\Events\Models;

/**
 * Interface Type
 * @package OneFit\Events\Models
 */
interface Type
{
    public const CHECK_IN = 'check_in';
    public const FRIEND_CONNECTION = 'friend_connection';
    public const WORKOUT_INVITATION = 'workout_invitation';
    public const NOTIFICATION = 'notification';
    public const MEMBER = 'member';
    public const AUTHENTICATABLE = 'authenticatable';
    public const TOKENS = 'tokens';
    public const PERIOD = 'period';
    public const ORDER = 'order';
    public const RECURRING = 'recurring';
    public const FINE = 'fine';
    public const SURPLUS_MANDATE = 'surplus_mandate';
}
