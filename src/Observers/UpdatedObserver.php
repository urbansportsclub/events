<?php

namespace OneFit\Events\Observers;

use Illuminate\Contracts\Queue\QueueableEntity;

/**
 * Class UpdatedObserver
 * @package OneFit\Events\Observers
 */
class UpdatedObserver extends AbstractObserver
{
    /**
     * @param QueueableEntity $entity
     */
    public function __invoke(QueueableEntity $entity): void
    {
        $this->updated($entity);
    }
}
