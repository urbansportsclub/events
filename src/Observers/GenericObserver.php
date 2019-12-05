<?php

namespace OneFit\Events\Observers;

use Illuminate\Contracts\Queue\QueueableEntity;

class GenericObserver extends AbstractObserver
{
    /**
     * @param string $event
     * @param array  $data
     */
    public function __invoke(string $event, array $data): void
    {
        $entity = reset($data);
        if ($entity instanceof QueueableEntity) {
            $split = explode('.', $event);
            $this->custom(end($split), $entity);
        }
    }
}
