<?php

return [
    'producers' => [
//        \OneFit\Base\Models\CheckIn::class => [
//            \OneFit\Events\Models\Type::CHECK_IN => \OneFit\Events\Models\Topic::MEMBER_DOMAIN,
//        ],
//        \OneFit\Base\Models\FriendConnection::class => [
//            \OneFit\Events\Models\Type::FRIEND_CONNECTION => \OneFit\Events\Models\Topic::MEMBER_DOMAIN,
//        ],
//        \OneFit\Base\Models\WorkoutInvitation::class => [
//            \OneFit\Events\Models\Type::WORKOUT_INVITATION => \OneFit\Events\Models\Topic::MEMBER_DOMAIN,
//        ],
    ],
    'listeners' => [
//        \OneFit\Events\Models\Type::NOTIFICATION => \OneFit\Events\Models\Topic::NOTIFICATION_STREAM,
    ],
    'source' => \OneFit\Events\Models\Source::UNDEFINED,
];
