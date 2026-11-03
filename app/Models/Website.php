<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Website extends Model
{
    const ACTIVE = 1;

    const INACTIVE = 0;

    const DOMAIN_PATTERN = '/^[a-z0-9]([a-z0-9._-]*[a-z0-9])?$/iD';

    use HasFactory;
    use SoftDeletes;

    protected $table = 'website';
}
