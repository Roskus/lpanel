<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Website extends Model
{
    const ACTIVE = 1;
    use HasFactory;
    use SoftDeletes;
    protected $table = 'website';
}
