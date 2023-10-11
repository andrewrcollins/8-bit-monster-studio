<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monster extends Model
{
    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'max_hit_points' => 0,
        'defense' => 0,
        'attack_damage' => 0,
        'attack_is_ranged' => false,
        'max_damage' => 0,
        'special_attack_chance' => 0,
        'special_attack' => 0,
        'special_attack_is_ranged' => false,
    ];
}
