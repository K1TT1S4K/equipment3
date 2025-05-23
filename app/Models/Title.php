<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Title extends Model
{
    //
    protected $fillable = [
        'name',
        'group'
    ];
    // public function equipments() : HasMany {
    //     return $this->hasMany(Equipment::class);
    // }

    public function equipment_types(): HasMany
    {
        return $this->hasMany(Equipment_type::class);
    }

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}
