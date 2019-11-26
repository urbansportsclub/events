<?php

return [
    'producers' => [
        \OneFit\Events\Models\Topic::MEMBER_DOMAIN => [
//            \OneFit\Events\Models\Type::CHECK_IN => \OneFit\Base\Models\CheckIn::class,
//            \OneFit\Events\Models\Type::FRIEND_CONNECTION => \OneFit\Base\Models\FriendConnection::class,
//            \OneFit\Events\Models\Type::WORKOUT_INVITATION => \OneFit\Base\Models\WorkoutInvitation::class,
        ],
    ],
    'listeners' => [
//        \OneFit\Events\Models\Type::NOTIFICATION => \OneFit\Events\Models\Topic::NOTIFICATION_STREAM,
    ],
    'source' => \OneFit\Events\Models\Source::UNDEFINED,
];
