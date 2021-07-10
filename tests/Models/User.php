<?php

namespace Tests\Models;

use Rockbuzz\LaraOrders\Traits\HasOrder;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasOrder;

    protected $guarded = [];
}
