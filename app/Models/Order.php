<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'food_id',
        'user_id',
        'phone',
        'region',
        'city',
        'address',
        
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function food()
    {
        return $this->belongsTo(Food::class);

    }
}
