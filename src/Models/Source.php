<?php

namespace OneFit\Events\Models;

abstract class Source
{
    public const API = 'api';
    public const BACK_OFFICE = 'back_office';
    public const QUEUE_WORKER = 'queue_worker';
    public const UNDEFINED = 'undefined';
}
