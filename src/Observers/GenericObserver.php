<?php

namespace OneFit\Events\Observers;

use Illuminate\Contracts\Queue\QueueableEntity;

class GenericObserver extends AbstractObserver
{
    /**
     * @param string $event
     * @param QueueableEntity $entity
     */
    public function __invoke(string $event, QueueableEntity $entity): void
    {
        $this->custom($event, $entity);
    }
}
