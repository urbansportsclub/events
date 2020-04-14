<?php

namespace OneFit\Events\Observers;

use JsonSerializable;

/**
 * Class CreatedObserver.
 */
class CreatedObserver extends AbstractObserver
{
    /**
     * @param JsonSerializable $entity
     */
    public function __invoke(JsonSerializable $entity): void
    {
        $this->created($entity->jsonSerialize());
    }
}
