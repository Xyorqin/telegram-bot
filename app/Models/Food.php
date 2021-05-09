<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    Protected $table = 'foods';

    protected $fillable = [
        'foods_name'
        
    ];
}
