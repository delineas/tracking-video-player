<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerProgress extends Model
{
  protected $fillable = ['uid', 'vid', 'percent'];

  protected $table = 'player_progress';
}
