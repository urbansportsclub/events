<?php

namespace Illuminate\Contracts\Support;

interface deferrable_provider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides();
}
