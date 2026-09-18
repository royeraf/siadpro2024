<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $table = 'pro_plans';
    public $timestamps = true;

    public function getUser(){
        return $this->hasOne(User::class,'id','idUser');
    }
}
