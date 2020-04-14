<?php

namespace OneFit\Events\Observers;

use JsonSerializable;

/**
 * Class DeletedObserver.
 */
class DeletedObserver extends AbstractObserver
{
    /**
     * @param JsonSerializable $entity
     */
    public function __invoke(JsonSerializable $entity): void
    {
        $this->deleted($entity->jsonSerialize());
    }
}
