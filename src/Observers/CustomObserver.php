<?php

namespace OneFit\Events\Observers;

use JsonSerializable;

class CustomObserver extends AbstractObserver
{
    /**
     * @param string $event
     * @param array  $data
     */
    public function __invoke(string $event, array $data): void
    {
        $entity = reset($data);
        if ($entity instanceof JsonSerializable) {
            $split = explode('.', $event);
            $this->custom($entity->jsonSerialize(), end($split));
        }
    }
}
