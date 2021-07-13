<?php

use App\Allcountry;
use App\AutoDetectGeo;
use App\CurrencyNew;
use App\Location;
use App\multiCurrency;
use Illuminate\Support\Facades\Session;

$setting = AutoDetectGeo::first();

$CurrencyNew = CurrencyNew::query();

$currencyQuery = multiCurrency::query();

$myip = request()->getClientIp();

$ip = geoip()->getLocation($myip);

$defaultCurrency = $currencyQuery->where('default_currency', 1)->first();

$conversion_rate = 1;

if ($setting->enabel_multicurrency == '1') {

    if ($setting->auto_detect == '1' && $setting->currency_by_country == '0') {

        $detectCurrency = $ip->currency;
        $detectCountry = $ip->country;

        if (empty(Session::get('currency'))) {

            $currency = $CurrencyNew->firstWhere('code', '=', $detectCurrency);

            if ($currency && $detectCurrency == $currency->code) {

                Session::put('currency', [
                    'id' => $currency->code,
                    'mainid' => $currency->id,
                    'value' => $currency->currencyextract->currency_symbol,
                    'position' => $currency->currencyextract->position,
                ]);

                if ($currency->currencyextract->default_currency != 1) {
                    $defcurrate = currency(1.00, $defaultCurrency->currency->code, session()->get('currency')['id'], $format = false);
                    $conversion_rate = $defcurrate + $currency->currencyextract->add_amount;
                } else {
                    $conversion_rate = 1 + $currency->currencyextract->add_amount;
                }

            } else {

                //Set Default Currency if For Location that currency is not found ! //

                Session::put('currency', [
                    'id' => $defaultCurrency->currency->code,
                    'mainid' => $defaultCurrency->currency->id,
                    'value' => $defaultCurrency->currency_symbol,
                    'position' => $defaultCurrency->position,
                ]);

                try {
                    if ($defaultCurrency->default_currency != 1) {
                        $conversion_rate = $defaultCurrency->currency->exchange_rate + $defaultCurrency->add_amount;
                    } else {
                        $conversion_rate = 1+isset($currency['currencyextract']) ? $currency->currencyextract->add_amount : 0;
                    }
                } catch (\Exception $e) {
                    Session::put('currency', [
                        'id' => $defaultCurrency->currency->code,
                        'mainid' => $defaultCurrency->currency->id,
                        'value' => $defaultCurrency->currency_symbol,
                        'position' => $defaultCurrency->position,
                    ]);
                }

            }

        } else {

            
            $currency = $CurrencyNew->firstWhere('code', '=', session()->get('currency')['id']);

            if (Session::has('previous_cur')) {

                $from = $defaultCurrency->currency->code;
                $to = Session::get('current_cur');

                $defcurrate = currency(1.00, $from, $to, $format = false);


                if ($currency) {

                    if ($currency->currencyextract->default_currency != 1) {
                        $conversion_rate = $defcurrate + $currency->currencyextract->add_amount;
                    } else {
                        $conversion_rate = 1 + $currency->currencyextract->add_amount;
                    }

                    Session::put('currency', [
                        'id' => $currency->code,
                        'mainid' => $currency->id,
                        'value' => $currency->currencyextract->currency_symbol,
                        'position' => $currency->currencyextract->position,
                    ]);

                } else {

                    Session::put('currency', [
                        'id' => $defaultCurrency->currency->code,
                        'mainid' => $defaultCurrency->currency->id,
                        'value' => $defaultCurrency->currency_symbol,
                        'position' => $defaultCurrency->position,
                    ]);

                    $conversion_rate = 1 + $defaultCurrency->add_amount;

                }

            } else {

                if ($currency->currencyextract->default_currency != 1) {
                    $rate = currency(1.00, $defaultCurrency->currency->code, session()->get('currency')['id'], $format = false);
                    $conversion_rate = $rate + $currency->currencyextract->add_amount;

                } else {
                    $conversion_rate = 1 + $currency->currencyextract->add_amount;
                }

                Session::put('currency', [
                    'id' => $currency->code,
                    'mainid' => $currency->id,
                    'value' => $currency->currencyextract->currency_symbol,
                    'position' => $currency->currencyextract->position,
                ]);

            }

        }

    } elseif ($setting->auto_detect == '1' && $setting->currency_by_country == '1') {

        /** if Currency by country is on */
        $currency = '';
        $country = Allcountry::firstwhere('nicename', $ip->country);

        if (empty(session()->get('currency'))) {

            foreach (Location::orderBy('id', 'DESC')->get() as $loc) {

                $arr[] = $loc->country_id;

                if (in_array($country->id, $arr)) {

                    try {
                        $currency = $CurrencyNew->firstWhere('code', '=', $loc->currency);

                        if ($currency->currencyextract->default_currency != 1) {
                            $conversion_rate = $currency->exchange_rate + $currency->currencyextract->add_amount;
                        } else {
                            $conversion_rate = 1 + $currency->currencyextract->add_amount;
                        }

                        Session::put('currency', [
                            'id' => $currency->code,
                            'mainid' => $currency->id,
                            'value' => $currency->currencyextract->currency_symbol,
                            'position' => $currency->currencyextract->position,
                        ]);
                    } catch (\Exception $e) {
                        Session::put('currency', [
                            'id' => $defaultCurrency->currency->code,
                            'mainid' => $defaultCurrency->currency->id,
                            'value' => $defaultCurrency->currency_symbol,
                            'position' => $defaultCurrency->position,
                        ]);

                        $conversion_rate = 1 + $defaultCurrency->add_amount;
                    }

                }
            }

        } else {

            if (session()->has('previous_cur')) {

                $from = $defaultCurrency->currency->code;
                $to = Session::get('current_cur');

                $currency = $CurrencyNew->firstWhere('code', '=', session()->get('current_cur'));

                if ($currency && $currency->currencyextract) {

                    $defcurrate = currency(1.00, $from, $to, $format = false);

                    if ($currency->currencyextract->default_currency != 1) {
                        $conversion_rate = $defcurrate + $currency->currencyextract->add_amount;
                    } else {
                        $conversion_rate = 1 + $currency->currencyextract->add_amount;
                    }

                    Session::put('currency', [
                        'id' => $currency->code,
                        'mainid' => $currency->id,
                        'value' => $currency->currencyextract->currency_symbol,
                        'position' => $currency->currencyextract->position,
                    ]);

                } else {

                    Session::put('currency', [
                        'id' => $defaultCurrency->currency->code,
                        'mainid' => $defaultCurrency->currency->id,
                        'value' => $defaultCurrency->currency_symbol,
                        'position' => $defaultCurrency->position,
                    ]);

                    $conversion_rate = 1 + $defaultCurrency->add_amount;

                }

            } else {

                foreach (Location::orderBy('id', 'DESC')->get() as $loc) {

                    $arr[] = $loc->country_id;

                    if (in_array($country->id, $arr)) {

                        $currency = $CurrencyNew->firstWhere('code', '=', $loc->currency);

                        if (!$currency) {

                            Session::put('currency', [
                                'id' => $defaultCurrency->currency->code,
                                'mainid' => $defaultCurrency->currency->id,
                                'value' => $defaultCurrency->currency_symbol,
                                'position' => $defaultCurrency->position,
                            ]);

                            $conversion_rate = 1 + $defaultCurrency->add_amount;

                            break;
                        }

                        if ($currency->currencyextract->default_currency != 1) {
                            $conversion_rate = $currency->exchange_rate + $currency->currencyextract->add_amount;
                        } else {
                            $conversion_rate = 1 + $currency->currencyextract->add_amount;
                        }

                        Session::put('currency', [
                            'id' => $currency->code,
                            'mainid' => $currency->id,
                            'value' => $currency->currencyextract->currency_symbol,
                            'position' => $currency->currencyextract->position,
                        ]);

                        break;

                    }
                }

            }

        }

    } else {

        if (session()->has('previous_cur')) {

            $from = $defaultCurrency->currency->code;

            $to = Session::get('currency')['id'];

            $currency = $CurrencyNew->firstWhere('code', '=', session()->get('current_cur'));

            if ($currency && $currency->currencyextract) {

                $defcurrate = currency(1.00, $from, $to, $format = false);

                if ($currency->currencyextract->default_currency != 1) {
                    $conversion_rate = $defcurrate + $currency->currencyextract->add_amount;
                } else {
                    $conversion_rate = 1 + $currency->currencyextract->add_amount;
                }

                Session::put('currency', [
                    'id' => $currency->code,
                    'mainid' => $currency->id,
                    'value' => $currency->currencyextract->currency_symbol,
                    'position' => $currency->currencyextract->position,
                ]);

            }
        } else {

            Session::put('currency', [
                'id' => $defaultCurrency->currency->code,
                'mainid' => $defaultCurrency->currency->id,
                'value' => $defaultCurrency->currency_symbol,
                'position' => $defaultCurrency->position,
            ]);

            $conversion_rate = 1 + $defaultCurrency->add_amount;
        }

    }
} else {

    Session::put('currency', [
        'id' => $defaultCurrency->currency->code,
        'mainid' => $defaultCurrency->currency->id,
        'value' => $defaultCurrency->currency_symbol,
        'position' => $defaultCurrency->position,
    ]);

    $conversion_rate = 1 + $defaultCurrency->add_amount;

}

$conversion_rate = sprintf('%.2f', $conversion_rate);
