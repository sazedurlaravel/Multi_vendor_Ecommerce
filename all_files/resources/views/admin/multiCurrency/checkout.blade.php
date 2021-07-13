<div class="form-group">
  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">
    Enable On Cart Page
  </label>

  <div class="col-md-9 col-sm-9 col-xs-12">

    <label class="switch">
      <input type="checkbox" name="cart_page" onchange="checkoutSetting()" id="cart_page"
        {{$auto_geo->enable_cart_page=="1"?'checked':''}}>
      <span class="knob"></span>
    </label>

  </div>

  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">
    Check Out Currency
  </label>

  <div class="col-md-9 col-sm-9 col-xs-12">
    <label class="switch">
      <input type="checkbox" name="checkout_currency" onchange="checkoutSettingCheckout()" id="checkout_currency"
        {{$auto_geo->checkout_currency=="1"?'checked':''}}>
      <span class="knob"></span>
    </label>


  </div>

</div>


<table class="table">
  <caption>Currency Option</caption>
  <thead>
    <tr>
      <th scope="col">Currency</th>

      <th scope="col">Checkout Currency</th>
      <th scope="col">Payment Method</th>



    </tr>
  </thead>

  <form>

    <tbody>



      <?php  $check_cur = App\multiCurrency::get(); ?>
      @if($check_cur)


      @foreach($check_cur as $key=> $cury)



      <tr>
        <td>


          {{ $cury->currency->code }}
          <input type="hidden" id="currency_checkout{{$key}}" name="currency_checkout{{$cury->id}}"
            value="{{$cury->currency->code}}">

          <input type="hidden" id="currencyId{{$key}}" value="{{$cury->id}}">
        </td>

        <td>

          <select class="js-example-basic-multiple" id="checkout_currency_status{{$key}}">

            <option value="1" @if(!empty($checkout)) {{$checkout->checkout_currency=='1'?'selected':''}} @endif>Yes
            </option>
            <option value="0" @if(!empty($checkout)) {{$checkout->checkout_currency=='0'?'selected':''}} @endif>No
            </option>

          </select>
        </td>
        <td>

         
          <select class="js-example-basic-multiple form-control pay_m" id="payment_checkout{{$key}}" name="payment[]"
            multiple="multiple">

            @if($cury->checkoutCurrencySettings)

            <?php $payments  = explode(",", $cury->checkoutCurrencySettings->payment_method);?>

            <option value="instamojo" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='instamojo'?'selected':''}}@endforeach @endif>Instamojo</option>

            <option @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='wallet' ? 'selected':''}}@endforeach
              @endif value="wallet">Wallet</option>

            <option value="paypal" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='paypal'?'selected':''}}@endforeach @endif>Paypal</option>

            <option value="payu" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='payu'?'selected':''}}@endforeach @endif>PayUBiz/ PayUMoney</option>

            <option value="stripe" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='stripe'?'selected':''}}@endforeach @endif>Stripe</option>

            <option value="paystack" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='paystack'?'selected':''}}@endforeach @endif>Paystack</option>

            <option @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='braintree'?'selected':''}}@endforeach
              @endif value="braintree">Braintree</option>

            <option value="Razorpay" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='Razorpay'?'selected':''}}@endforeach @endif>RazorPay</option>

            <option value="Paytm" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='Paytm'?'selected':''}}@endforeach @endif>PayTM</option>

            <option value="payhere" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='payhere'?'selected':''}}@endforeach @endif>Payhere</option>

            <option value="bankTransfer" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='bankTransfer'?'selected':''}}@endforeach @endif>Bank Transfer</option>

            <option value="skrill" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='skrill'?'selected':''}}@endforeach @endif>Skrill</option>

            <option value="mollie" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='mollie'?'selected':''}}@endforeach @endif>Mollie</option>

            <option @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='sslcommerze'?'selected':''}}@endforeach @endif value="sslcommerze">SslCommerze</option>

            <option value="amarpay" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
            $pay){{$pay=='amarpay'?'selected':''}}@endforeach @endif>Amar Pay</option>

            <option @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
            $pay){{$pay=='iyzico'?'selected':''}}@endforeach @endif value="iyzico">iyzico</option>

            <option value="omise" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='omise'?'selected':''}}@endforeach @endif>Omise</option>

            <option value="rave" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='rave'?'selected':''}}@endforeach @endif>Rave</option>

            <option value="cashfree" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='cashfree'?'selected':''}}@endforeach @endif>Cashfree</option>

            <option value="cashOnDelivery" @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
              $pay){{$pay=='cashOnDelivery'?'selected':''}}@endforeach @endif>Cash On Delivery</option>
            
            @foreach (App\ManualPaymentMethod::where('status','1')->get(); as $item)
                <option @if(!empty($cury->checkoutCurrencySettings)) @foreach($payments as
                  $pay){{$pay == $item->payment_name ? 'selected' : '' }}@endforeach @endif value="{{ $item->payment_name }}">{{ $item->payment_name }}</option>
            @endforeach

            @else


            <option value="instamojo">Instamojo</option>
            <option value="wallet">Wallet</option>
            <option value="paypal">Paypal</option>
            <option value="stripe">Strip</option>
            <option value="paystack">Paystack</option>
            <option value="braintree">Braintree</option>
            <option value="Razorpay">RazorPay</option>
            <option value="Paytm">PayTM</option>
            <option value="bankTransfer">Bank Transfer</option>
            <option value="payu">PayUBiz/ PayUMoney</option>
            <option value="skrill">Skrill</option>
            <option value="mollie">Mollie</option>
            <option value="sslcommerze">SslCommerze</option>
            <option value="amarpay">Amar Pay</option>
            <option value="iyzico">iyzico</option>
            <option value="omise">Omise</option>
            <option value="rave">Rave</option>
            <option value="cashfree">Cashfree</option>
            <option value="cashOnDelivery">Cash On Delivery</option>
            @foreach (App\ManualPaymentMethod::all() as $item)
             
              <option value="{{ $item->payment_name }}">{{ $item->payment_name }}</option>
            @endforeach

            @endif
          </select>
        </td>

      </tr>


      @endforeach
      @endif



      <tr>
        <td colspan="3">
          <div class="pull-left">
            <a class="btn btn-primary" onclick="CheckoutCurrencySubmitForm()">
              <i class="fa fa-save"></i> Save
            </a>
          </div>
        </td>
      </tr>

    </tbody>

  </form>

</table>

<script>
  var baseUrl = "<?= url('/') ?>";
</script>
<script src="{{ url('js/currency.js') }}"></script>