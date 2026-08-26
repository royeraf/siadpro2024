<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class internetInstituciones extends Model
{
    use HasFactory;
    protected $table = 'internet_institucionesh';
    public $timestamps = true;

    public function getUser(){
        return $this->hasOne(User::class,'id','idUser');
    }
}
