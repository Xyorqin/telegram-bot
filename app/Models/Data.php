<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    use HasFactory;
    Protected $table = 'datas';

    protected $fillable = [
        'chat_id',
        'phone',
        'region',
        'city',
        'address',
        
    ];
}
