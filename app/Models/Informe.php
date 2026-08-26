<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informe extends Model
{
    use HasFactory;
    protected $table = 'pro_informes';
    public $timestamps = false;

    public function getUser(){
        return $this->hasOne(User::class,'id','idUser');
    }
}
