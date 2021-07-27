<?php

namespace Rockbuzz\LaraOrders\Schemaless;

use Illuminate\Support\Collection;
use Rockbuzz\LaraUtils\Schemaless\Base;

class Notes extends Base
{
    protected function getBaseItems(): Collection
    {
        return collect();
    }

}