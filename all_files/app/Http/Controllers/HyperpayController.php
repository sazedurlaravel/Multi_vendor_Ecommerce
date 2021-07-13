<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HyperpayController extends Controller
{
    public function pay(){
        $url = "https://test.oppwa.com/v1/checkouts";
        $data = "entityId=8a8294174b7ecb28014b9699220015ca" .
                    "&amount=92.00" .
                    "&currency=EUR" .
                    "&paymentType=DB";

        $entityid = '8a82941865340dc8016537ce08db0845';
        $userid = '8a82941865340dc8016537cdac1e0841';
        $password = 'sXrYy8pnsf';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization:Bearer OGE4Mjk0MTc0YjdlY2IyODAxNGI5Njk5MjIwMDE1Y2N8c3k2S0pzVDg='));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);

        $result = json_decode($responseData);

        return view('hyperpay',compact('result'));
    }
}
