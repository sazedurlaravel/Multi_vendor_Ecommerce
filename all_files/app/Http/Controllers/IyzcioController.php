<?php

namespace App\Http\Controllers;

use App\Address;
use App\AddSubVariant;
use App\Cart;
use App\Config;
use App\Coupan;
use App\Genral;
use App\Invoice;
use App\InvoiceDownload;
use App\Mail\OrderMail;
use App\Notifications\OrderNotification;
use App\Notifications\SellerNotification;
use App\Notifications\SMSNotifcations;
use App\Notifications\UserOrderNotification;
use App\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Twilosms;

class IyzcioController extends Controller
{
    public function pay(Request $request)
    {   

        require_once 'price.php';

        $adrid = Session::get('address');

        $address = Address::find($adrid);

        $c = strlen($address->phone);

        if ($c < 10) {

            notify()->error("Invalid Phone no. ");
            return redirect(route('order.review'));
        }

        $cart_table = Auth::user()->cart;
        $total = 0;

        foreach ($cart_table as $cart) {

            if ($cart->product->tax_r != null && $cart->product->tax == 0) {

                if ($cart->ori_offer_price != 0) {
                    //get per product tax amount
                    $p = 100;
                    $taxrate_db = $cart->product->tax_r;
                    $vp = $p + $taxrate_db;
                    $taxAmnt = $cart->product->offer_price / $vp * $taxrate_db;
                    $taxAmnt = sprintf("%.2f", $taxAmnt);
                    $price = ($cart->ori_offer_price - $taxAmnt) * $cart->qty;

                } else {

                    $p = 100;
                    $taxrate_db = $cart->product->tax_r;
                    $vp = $p + $taxrate_db;
                    $taxAmnt = $cart->product->price / $vp * $taxrate_db;

                    $taxAmnt = sprintf("%.2f", $taxAmnt);

                    $price = ($cart->ori_price - $taxAmnt) * $cart->qty;
                }

            } else {

                if ($cart->semi_total != 0) {

                    $price = $cart->semi_total;

                } else {

                    $price = $cart->price_total;

                }
            }

            $total = $total + $price;

        }
       
       
        $total = sprintf("%.2f",$total * $conversion_rate);

        if (round($request->actualtotal, 2) != $total) {

            notify()->error('Payment has been modifed !','Please try again !');
            return redirect(route('order.review'));

        }
        
        $amount = Crypt::decrypt($request->amount);
        $conversation_id = $request->conversation_id;
        $basket_id = 'B' . substr(str_shuffle("0123456789"), 0, 5);
        $user_id = 'BY' . Auth::user()->id;
        $fname = Auth::user()->name;
        $lname = Auth::user()->name;
        $address = $request->address;
        $city = $request->city;
        $state = $request->state;
        $country = $request->country;
        $item_id = 'BI' . substr(str_shuffle("0123456789"), 0, 3);
        $pincode = $request->pincode;
        $now = Carbon::now()->toDateTimeString();
        $ip = $request->ip();
        $currency = $request->currency;

        $language = strtoupper(App::getLocale());

        $identity = $request->identity_number;
        $email = $request->email;
        $mobile = $request->mobile;

        Cookie::queue('user_selection', Auth::user()->id, 100);

        $options = new \Iyzipay\Options();
        $options->setApiKey(env('IYZIPAY_API_KEY'));
        $options->setSecretKey(env('IYZIPAY_SECRET_KEY'));
        $options->setBaseUrl(env('IYZIPAY_BASE_URL'));

        $request = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
        $request->setLocale($language);
        $request->setConversationId($conversation_id);
        $request->setPrice($amount);
        $request->setPaidPrice($amount);
        $request->setCurrency($currency);
        $request->setBasketId($basket_id);
        $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
        $request->setCallbackUrl(route('iyzcio.callback'));
        $request->setEnabledInstallments(array(1));
        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId($user_id);
        $buyer->setName($fname);
        $buyer->setSurname($lname);
        $buyer->setGsmNumber($mobile);
        $buyer->setEmail($email);
        $buyer->setIdentityNumber($identity);
        $buyer->setLastLoginDate($now);
        $buyer->setRegistrationDate($now);
        $buyer->setRegistrationAddress($address);
        $buyer->setIp($ip);
        $buyer->setCity($city);
        $buyer->setCountry($country);
        $buyer->setZipCode($pincode);
        $request->setBuyer($buyer);
        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName($fname);
        $shippingAddress->setCity($city);
        $shippingAddress->setCountry($country);
        $shippingAddress->setAddress($address);
        $shippingAddress->setZipCode($pincode);
        $request->setShippingAddress($shippingAddress);
        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName($fname);
        $billingAddress->setCity($city);
        $billingAddress->setCountry($country);
        $billingAddress->setAddress($address);
        $billingAddress->setZipCode($pincode);
        $request->setBillingAddress($billingAddress);
        $basketItems = array();
        $firstBasketItem = new \Iyzipay\Model\BasketItem();
        $firstBasketItem->setId($item_id);
        $firstBasketItem->setName(config('app.name'));
        $firstBasketItem->setCategory1(config('app.name'));
        $firstBasketItem->setCategory2(config('app.name'));
        $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
        $firstBasketItem->setPrice($amount);
        $basketItems[0] = $firstBasketItem;

        $request->setBasketItems($basketItems);
        # make request
        $payWithIyzicoInitialize = \Iyzipay\Model\PayWithIyzicoInitialize::create($request, $options);

        // dd($payWithIyzicoInitialize);

        if ($payWithIyzicoInitialize->getstatus() == 'success') {
            $url = $payWithIyzicoInitialize->getpayWithIyzicoPageUrl();

            return redirect($url);
        }

        notify()->error($payWithIyzicoInitialize->getErrorMessage());
        return redirect(route('order.review'));

    }

    public function callback(Request $request)
    {
        $token = $request->token;

    	$options = new \Iyzipay\Options();
		$options->setApiKey(env('IYZIPAY_API_KEY'));
		$options->setSecretKey(env('IYZIPAY_SECRET_KEY'));
		$options->setBaseUrl(env('IYZIPAY_BASE_URL'));
    	

        $request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
        
        $request->setLocale(\Iyzipay\Model\Locale::EN);
		$request->setConversationId(uniqid());
		$request->setToken($token);

		$checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($request, $options);

		// dd($checkoutForm);

		$status = $checkoutForm->getstatus();

		$txn_id = $checkoutForm->getpaymentId();

        $iyz_currency = $checkoutForm->getcurrency();
        
        if($status == 'success') {

                require_once 'price.php';

                $userid = Cookie::get('user_selection');
                $user = User::find($userid);
                $cart = Session::get('item');
                $invno = 0;
                $venderarray = array();
                $qty_total = 0;
                $pro_id = array();
                $mainproid = array();
                $total_tax = 0;
                $total_shipping = 0;
                $cart_table = Cart::where('user_id', $user->id)
                    ->get();

                if(Cart::isCoupanApplied() == '1'){

                    $discount = Cart::getDiscount();

                } else {

                    $discount = 0;
                }

                foreach ($cart_table as $key => $cart) {

                    array_push($venderarray, $cart->vender_id);

                    $qty_total = $qty_total + $cart->qty;

                    array_push($pro_id, $cart->variant_id);

                    array_push($mainproid, $cart->pro_id);

                    $total_tax = $total_tax + $cart->tax_amount;

                    $total_shipping = $total_shipping + $cart->shipping;

                }

                $total_shipping = sprintf("%.2f", $total_shipping * $conversion_rate);

                $venderarray = array_unique($venderarray);
                $hc = decrypt(Session::get('handlingcharge'));
                $hc = sprintf("%.2f",$hc);

                $neworder = new Order();
                $neworder->order_id = Session::get('order_id');
                $neworder->qty_total = $qty_total;
                $neworder->user_id = Auth::user()->id;
                $neworder->delivery_address = Session::get('address');
                $neworder->billing_address = Session::get('billing');
                $neworder->order_total = Session::get('payamount') - $hc;
                $neworder->tax_amount = round($total_tax, 2);
                $neworder->shipping = round($total_shipping, 2);
                $neworder->status = '1';
                $neworder->coupon = Cart::getCoupanDetail() ?  Cart::getCoupanDetail()->code : NULL;
                $neworder->paid_in = session()->get('currency')['value'];
                $neworder->vender_ids = $venderarray;
                $neworder->transaction_id = $txn_id;
                $neworder->payment_receive = 'yes';
                $neworder->payment_method = 'Iyzico';
                $neworder->paid_in_currency = Session::get('currency')['id'];
                $neworder->pro_id = $pro_id;
                $neworder->discount = sprintf("%.2f",$discount * $conversion_rate);
                $neworder->distype = Cart::getCoupanDetail() ?  Cart::getCoupanDetail()->link_by : NULL;
                $neworder->main_pro_id = $mainproid;
                $neworder->handlingcharge = $hc;
                $neworder->created_at = date('Y-m-d H:i:s');

                $neworder->save();

                #Getting Invoice Prefix
                $invStart = Invoice::first()->inv_start;
                #end
                #Count order
                $ifanyorder = count(Order::all());
                $cart_table2 = Cart::where('user_id', Auth::user()->id)
                    ->orderBy('vender_id', 'ASC')
                    ->get();

                #Creating Invoice
                foreach ($cart_table2 as $key => $invcart) {

                    $lastinvoices = InvoiceDownload::where('order_id', $neworder->id)
                        ->get();
                    $lastinvoicerow = InvoiceDownload::orderBy('id', 'desc')->first();

                    if (count($lastinvoices) > 0) {

                        foreach ($lastinvoices as $last) {
                            if ($invcart->vender_id == $last->vender_id) {
                                $invno = $last->inv_no;

                            } else {
                                $invno = $last->inv_no;
                                $invno = $invno + 1;
                            }
                        }

                    } else {

                        if ($lastinvoicerow) {
                            $invno = $lastinvoicerow->inv_no + 1;
                        } else {
                            $invno = $invStart;
                        }

                    }

                    $findvariant = AddSubVariant::find($invcart->variant_id);
                    $price = 0;

                    /*Handling charge per item count*/
                    $hcsetting = Genral::first();

                    if ($hcsetting->chargeterm == 'pi') {

                        $perhc = $hc / count($cart_table2);

                    } else {

                        $perhc = $hc / count($cart_table2);

                    }
                    /*END*/

                    if ($invcart->semi_total != 0) {

                        if ($findvariant->products->tax_r != '') {

                            $p = 100;
                            $taxrate_db = $findvariant->products->tax_r;
                            $vp = $p + $taxrate_db;
                            $tam = $findvariant->products->offer_price / $vp * $taxrate_db;
                            $tam = sprintf("%.2f", $tam);

                            $price = sprintf("%.2f", ($invcart->ori_offer_price - $tam) * $conversion_rate);
                        } else {
                            $price = $invcart->ori_offer_price * $conversion_rate;
                            $price = sprintf("%.2f", $price);
                        }

                    } else {

                        if ($findvariant->products->tax_r != '') {

                            $p = 100;

                            $taxrate_db = $findvariant->products->tax_r;

                            $vp = $p + $taxrate_db;

                            $tam = $findvariant->products->vender_price / $vp * $taxrate_db;

                            $tam = sprintf("%.2f", $tam);

                            $price = sprintf("%.2f", ($invcart->ori_price - $tam) * $conversion_rate);

                        } else {
                            $price = $invcart->ori_price * $conversion_rate;
                            $price = sprintf("%.2f", $price);

                        }
                    }

                    $newInvoice = new InvoiceDownload();
                    $newInvoice->order_id = $neworder->id;
                    $newInvoice->inv_no = $invno;
                    $newInvoice->qty = $invcart->qty;
                    $newInvoice->status = 'pending';
                    $newInvoice->local_pick = $invcart->ship_type;
                    $newInvoice->variant_id = $invcart->variant_id;
                    $newInvoice->vender_id = $invcart->vender_id;
                    $newInvoice->price = $price;
                    $newInvoice->tax_amount = $invcart->tax_amount;
                    $newInvoice->igst = session()->has('igst') ? session()->get('igst')[$key] : null;
                    $newInvoice->sgst = session()->has('indiantax') ? session()->get('indiantax')[$key]['sgst'] : null;
                    $newInvoice->cgst = session()->has('indiantax') ? session()->get('indiantax')[$key]['cgst'] : null;
                    $newInvoice->shipping = round($invcart->shipping * $conversion_rate, 2);
                    $newInvoice->discount = round($invcart->disamount * $conversion_rate, 2);
                    $newInvoice->handlingcharge = $perhc;
                    $newInvoice->tracking_id = InvoiceDownload::createTrackingID();
                    
                    if ($invcart->product->vender->role_id == 'v') {
                        $newInvoice->paid_to_seller = 'NO';
                    }

                    $newInvoice->save();

                }
                #end
                
                if(Cart::isCoupanApplied() == '1' && Cart::getCoupanDetail()){
                    // Coupon applied //
                    $c = Coupan::find(Cart::getCoupanDetail()->id);

                    if (isset($c)) {
                        $c->maxusage = $c->maxusage - 1;
                        $c->save();
                    }
                }

                //end //

                foreach ($cart_table as $carts) {

                    $id = $carts->variant_id;
                    $variant = AddSubVariant::findorfail($id);

                    if (isset($variant)) {

                        $used = $variant->stock - $carts->qty;
                        DB::table('add_sub_variants')
                            ->where('id', $id)->update(['stock' => $used]);

                    }

                }

                $inv_cus = Invoice::first();
                $order_id = Session::get('order_id');
                $orderiddb = $inv_cus->order_prefix . $order_id;

                $user->notify(new UserOrderNotification($order_id, $orderiddb));
                $get_admins = User::where('role_id', '=', 'a')->get();

                /*Sending notification to all admin*/
                \Notification::send($get_admins, new OrderNotification($order_id, $orderiddb));

                /*Send notifcation to vender*/
                $vender_system = Genral::first()->vendor_enable;

                /*if vender system enable and user role is not admin*/
                if ($vender_system == 1) {

                    $msg = "New Order $orderiddb Received !";
                    $url = route('seller.view.order', $order_id);

                    foreach ($venderarray as $key => $vender) {
                        $v = User::find($vender);
                        if ($v->role_id == 'v') {
                            $v->notify(new SellerNotification($url, $msg));
                        }
                    }

                }
                /*end*/
                Session::forget('page-reloaded');
                /*Send Mail to User*/
                try {
                    
                    $e = Address::find($neworder->delivery_address);
                
                    $paidcurrency = Session::get('currency')['id'];
                    
                    if(isset($e) && $e->email){
                        Mail::to($e->email)->send(new OrderMail($neworder, $inv_cus, $paidcurrency));
                    }
                    

                } catch (\Swift_TransportException $e) {

                    
                }

            $config = Config::first();

            if($config->sms_channel == '1'){

                $smsmsg = 'Your order #'.$orderiddb.' placed successfully ! You can view your order by visiting here:%0a';

                $smsurl = route('user.view.order',$neworder->order_id);
        
                $smsmsg .= $smsurl.'%0a%0a';
        
                $smsmsg .= 'Thanks for shopping with us - '.config('app.name');

                if(env('DEFAULT_SMS_CHANNEL') == 'msg91' && $config->msg91_enable == '1'){

                    try{
                        
                        $user->notify(new SMSNotifcations($smsmsg));

                    }catch(\Exception $e){

                        \Log::error('Error: '.$e->getMessage());

                    }

                }

                if(env('DEFAULT_SMS_CHANNEL') == 'twillo'){

                    try{
                        Twilosms::sendMessage($smsmsg, '+'.Auth::user()->phonecode.Auth::user()->mobile);
                    }catch(\Exception $e){
                        \Log::error('Twillo Error: '.$e->getMessage());
                    }

                }
            }

            Session::forget('cart');
            Session::forget('coupan');
            Session::forget('billing');
            Session::forget('lastid');
            Session::forget('address');
            Session::forget('payout');
            Session::forget('handlingcharge');
            Session::forget('coupanapplied');

            Cart::where('user_id', $user->id)->delete();

            $status = "Order #$inv_cus->order_prefix $neworder->order_id placed successfully !";
            notify()->success("$status");
            return redirect()->route('order.done',['orderid' => $neworder->order_id]);

        }else{
            notify()->warning('Payment failed !','Failed');
            return redirect(route('order.review'));
        }
    }
}