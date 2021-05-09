<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Rehoming extends Model
{

    public $additional_attributes = ['summary'];

    public function getSummaryAttribute(){
        return "{$this->date}: {$this->owner()->first()->firstname} {$this->owner()->first()->lastname}";
    }

    public function owner() {
        return $this->hasMany(Owner::class, 'id', 'owner_id');
    }

}
