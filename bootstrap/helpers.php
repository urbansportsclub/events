<?php

// Check the interface exists before trying to use it
if (! interface_exists('Illuminate\Contracts\Support\DeferrableProvider')) {
    require_once __DIR__ . '/DeferrableProvider.php';
}
