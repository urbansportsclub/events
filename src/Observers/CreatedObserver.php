<?php

namespace OneFit\Events\Observers;

use Illuminate\Contracts\Queue\QueueableEntity;

/**
 * Class CreatedObserver.
 */
class CreatedObserver extends AbstractObserver
{
    /**
     * @param QueueableEntity $entity
     */
    public function __invoke(QueueableEntity $entity): void
    {
        $this->created($entity);
    }
}
