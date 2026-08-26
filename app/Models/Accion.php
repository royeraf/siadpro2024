<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accion extends Model
{
    use HasFactory;
    protected $table = 'pro_accions';
    public $timestamps = false;

    public function getUser(){
        return $this->hasOne(User::class,'id','idUser');
    }
}
