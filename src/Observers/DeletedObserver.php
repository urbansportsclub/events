<?php

namespace OneFit\Events\Observers;

use Illuminate\Contracts\Queue\QueueableEntity;

/**
 * Class DeletedObserver.
 */
class DeletedObserver extends AbstractObserver
{
    /**
     * @param QueueableEntity $entity
     */
    public function __invoke(QueueableEntity $entity): void
    {
        $this->deleted($entity);
    }
}
