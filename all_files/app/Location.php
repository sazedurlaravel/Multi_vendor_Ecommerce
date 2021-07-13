<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{

    protected $guarded = [];
    public function linkedtomulticurrency(){
    	return $this->belongsTo('App\multiCurrency','multi_currency','id');
    }
}
