<?php

namespace OneFit\Events\Models;

abstract class Type
{
    public const CHECK_IN = 'check_in';
    public const FRIEND_CONNECTION = 'friend_connection';
    public const WORKOUT_INVITATION = 'workout_invitation';
    public const GENERIC = 'generic';
}
