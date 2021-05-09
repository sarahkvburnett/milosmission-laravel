<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Owner extends Model
{
    public $additional_attributes = ['fullname'];

    public function getFullNameAttribute(){
        return "{$this->firstname} {$this->lastname}";
    }

}
