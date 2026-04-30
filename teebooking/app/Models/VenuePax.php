<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenuePax extends Model
{
   use HasFactory;

   protected $guarded = [];

   protected $table = "venue_paxs";
}