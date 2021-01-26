<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    const ACTIVE = 1;
    use HasFactory;
    protected $table = 'website';
}
