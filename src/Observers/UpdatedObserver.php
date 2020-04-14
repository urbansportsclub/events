<?php

namespace OneFit\Events\Observers;

use JsonSerializable;

/**
 * Class UpdatedObserver.
 */
class UpdatedObserver extends AbstractObserver
{
    /**
     * @param JsonSerializable $entity
     */
    public function __invoke(JsonSerializable $entity): void
    {
        $this->updated($entity->jsonSerialize());
    }
}
