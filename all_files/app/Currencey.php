<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currencey extends Model
{
     protected $fillable = [
          'name','code','symbol','format','exchange_rate','active'
     ];

     
}
