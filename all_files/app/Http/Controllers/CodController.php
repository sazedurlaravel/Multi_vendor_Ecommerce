<?php
namespace App\Http\Controllers;

use App\Address;
use App\AddSubVariant;
use App\Cart;
use App\Cod;
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
use Auth;
use Crypt;
use DB;
use Illuminate\Http\Request;
use Mail;
use Session;
use Twilosms;

class CodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    #for payment with cod
    public function payviacod(Request $request)
    {

        require_once 'price.php';

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

        $total = sprintf("%.2f", $total * $conversion_rate);

        if (round($request->actualtotal, 2) != $total) {

            notify()->error('Payment has been modifed !', 'Please try again !');
            return redirect(route('order.review'));

        }

        $payout = Crypt::decrypt($request->amount);
        $hc = decrypt(Session::get('handlingcharge'));
        $payout = round($payout, 2);
        $order_id = uniqid();
        $user = Auth::user();
        $invno = 0;
        $venderarray = array();
        $qty_total = 0;
        $pro_id = array();
        $mainproid = array();
        $total_tax = 0;
        $total_shipping = 0;
        $cart_table = Cart::where('user_id', $user->id)
            ->get();
        $inv_cus = Invoice::first();

        if (Cart::isCoupanApplied() == '1') {

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
        $hc = round($hc, 2);
        $discount = sprintf("%.2f", $discount * $conversion_rate);

        \DB::beginTransaction();

        $neworder = new Order();
        $neworder->order_id = $order_id;
        $neworder->qty_total = $qty_total;
        $neworder->user_id = Auth::user()->id;
        $neworder->delivery_address = Session::get('address');
        $neworder->billing_address = Session::get('billing');
        $neworder->order_total = $payout - $hc;
        $neworder->tax_amount = round($total_tax, 2);
        $neworder->shipping = round($total_shipping, 2);
        $neworder->status = '1';
        $neworder->coupon = Cart::getCoupanDetail() ? Cart::getCoupanDetail()->code : null;
        $neworder->paid_in = session()->get('currency')['value'];
        $neworder->vender_ids = $venderarray;
        $neworder->paid_in_currency = Session::get('currency')['id'];
        $neworder->transaction_id = $inv_cus->cod_prefix . str_random(10) . $inv_cus->cod_postfix;
        $neworder->payment_receive = 'no';
        $neworder->payment_method = 'COD';
        $neworder->discount = $discount;
        $neworder->distype = Cart::getCoupanDetail() ? Cart::getCoupanDetail()->link_by : null;
        $neworder->pro_id = $pro_id;
        $neworder->main_pro_id = $mainproid;
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

        #Making Invoice
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

                if ($findvariant
                    ->products->tax_r != '') {

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
            $newInvoice->local_pick = $invcart->ship_type;
            $newInvoice->status = 'pending';
            $newInvoice->variant_id = $invcart->variant_id;
            $newInvoice->vender_id = $invcart->vender_id;
            $newInvoice->price = $price;
            $newInvoice->tax_amount = $invcart->tax_amount;
            $newInvoice->igst = session()->has('igst') && isset(session()->get('igst')[$key]) ? session()->get('igst')[$key] : null;
            $newInvoice->sgst = session()->has('indiantax') ? session()->get('indiantax')[$key]['sgst'] : null;
            $newInvoice->cgst = session()->has('indiantax') ? session()->get('indiantax')[$key]['cgst'] : null;
            $newInvoice->shipping = round($invcart->shipping * $conversion_rate, 2);
            $newInvoice->discount = round($invcart->disamount * $conversion_rate, 2);
            $newInvoice->tracking_id = InvoiceDownload::createTrackingID();
            $newInvoice->save();

        }

        #End

        if (Cart::isCoupanApplied() == '1' && Cart::getCoupanDetail()) {
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
            $variant = AddSubVariant::find($id);

            if (isset($variant)) {

                $used = $variant->stock - $carts->qty;
                DB::table('add_sub_variants')
                    ->where('id', $id)->update(['stock' => $used]);

            }

        }

        \DB::commit();


        $orderiddb = $inv_cus->order_prefix . $order_id;

        $user->notify(new UserOrderNotification($order_id, $orderiddb));
        $get_admins = User::where('role_id', '=', 'a')->get();

        /*Sending notifcation to all admin*/
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

            if (isset($e) && $e->email) {
                Mail::to($e->email)->send(new OrderMail($neworder, $inv_cus, $paidcurrency));
            }

        } catch (\Exception $e) {

        }
        /*End*/

        $config = Config::first();

        if ($config->sms_channel == '1') {

            $smsmsg = 'Your order #' . $orderiddb . ' placed successfully ! You can view your order by visiting here:%0a';

            $smsurl = route('user.view.order', $neworder->order_id);

            $smsmsg .= $smsurl . '%0a%0a';

            $smsmsg .= 'Thanks for shopping with us - ' . config('app.name');

            if (env('DEFAULT_SMS_CHANNEL') == 'msg91' && $config->msg91_enable == '1') {

                try {

                    $user->notify(new SMSNotifcations($smsmsg));

                } catch (\Exception $e) {

                    \Log::error('Error: ' . $e->getMessage());

                }

            }

            if (env('DEFAULT_SMS_CHANNEL') == 'twillo') {

                try {
                    Twilosms::sendMessage($smsmsg, '+' . Auth::user()->phonecode . Auth::user()->mobile);
                } catch (\Exception $e) {
                    \Log::error('Twillo Error: ' . $e->getMessage());
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

        Cart::where('user_id', $user->id)
            ->delete();

        $msg = "Order #$inv_cus->order_prefix $neworder->order_id placed successfully !";

        notify()->success($msg);
        // return response()->view('front.thankyou', $neworder->order_id, 200);
        return redirect()->route('order.done', ['orderid' => $neworder->order_id]);

    }
    #end

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cod  $cod
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $cat = Cod::where('order_id', $id)->first();
        if (empty($cat)) {
            $data = Cod::create($input);

            $data->save();
            return redirect('admin/cod')
                ->with("updated", "Cod Setting Has Been Updated");
        } else {
            $cat->update($input);
            return redirect('admin/cod')->with("updated", "Cod Setting Has Been Updated");
        }
    }

    public function editupdateOn(Request $request, $id)
    {
        $input = $request->all();
        $cat = Cod::where('order_id', $id)->first();
        if (empty($cat)) {
            $data = Cod::create($input);

            $data->save();
            return redirect('vender/cod')
                ->with("updated", "Cod Setting Has Been Updated");
        } else {
            $cat->update($input);
            return redirect('vender/cod')->with("updated", "Cod Setting Has Been Updated");
        }
    }
}
