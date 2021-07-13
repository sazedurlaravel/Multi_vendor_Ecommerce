<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrencyNew extends Model
{
    protected $table = 'currencies';
    protected $guarded = [];
    
    public function currencyextract(){
        return $this->hasOne('App\multiCurrency','currency_id','id');
    }
}
