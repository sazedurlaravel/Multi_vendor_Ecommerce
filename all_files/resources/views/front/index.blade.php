@extends("front.layout.master")
@section('meta_tags')
<link rel="canonical" href="{{ url()->current() }}"/>
<meta name="keywords" content="{{ isset($seoset) ? $seoset->metadata_key : '' }}">
<meta property="og:title" content="{{ isset($seoset) ? $seoset->project_name : config('app.name') }}" />
<meta property="og:description" content="{{ isset($seoset) ? $seoset->metadata_des : '' }}" />
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url()->current() }}" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:description" content="{{ isset($seoset) ? $seoset->metadata_des : '' }}" />
<meta name="twitter:site" content="{{ url()->current() }}" />
@endsection
@section('stylesheet')

    @if(isset($popup))
        <style>
            .my-popup-container {
            
                background: url('{{ url('images/offerpopup/'.$popup->image) }}');
                background-position: left;
                background-size: 50%;
                background-repeat: no-repeat;
                background-color: #ffffff;
            }
        </style>
    @endif

@endsection
@section("body")

<div class="body-content outer-top-vs " id="top-banner-and-menu">

  <div class="container-fluid">
    <div class="row no-gutters">
      @include('front.layout.navbar')
      <!-- Start Main -->
      <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-10">
          <div class="main-content homebanner-holder">

              @php
                $getads = App\Adv::where('position','=','beforeslider')->where('status','=',1)->get();
              @endphp

               <!-- Advertisement -->
               @if(isset($getads))

                @foreach($getads as $adv)
                    @include('front.advlayouts.beforeslider')
                @endforeach

               @endif

               <?php $home_slider = App\Widgetsetting::where('name','slider')->first(); ?>
                   
                @if(!empty($home_slider) &&$home_slider->home=='1')

                   <div id="app">
                        <slider></slider>
                   </div>
                @endif

                @php
                  $getads = App\Adv::where('position','=','abovenewproduct')->where('status','=',1)->get();
                @endphp

               <!-- Advertisement -->
               @if(isset($getads))

                @foreach($getads as $adv)
                    @include('front.advlayouts.abovenewproduct')
                @endforeach

               @endif

               <!-- New Product Desktop Widgets -->
               <div id="product-tabs-slider" class="mt-2 scroll-tabs">

                 @include('front.partials.newproduct')

               </div>
               <!-- END -->

              <!-- Small Screen Products Three Tab -->
                <section id="home-product-tab" class="mb-0 mt-3 home-product-tab-main-block">
                  <div class="container">
                      <div class="row home-product-tab">
                          <ul class="nav nav-tabs">
                            <li class="nav-item tab-width"><a data-toggle="tab" class="nav-link active"
                                    href="#newproductsM">{{ __('staticwords.newprods') }}</a></li>
                            <li class="nav-item tab-width"><a class="nav-link" data-toggle="tab"
                                    href="#topcatsM">{{ __('staticwords.tpc') }}</a></li>
                            <li class="nav-item tab-width"><a class="nav-link" data-toggle="tab"
                                    href="#featuredM">{{ __('staticwords.Featured') }}</a></li>
                          </ul>

                          <div class="tab-content">
                              <div id="newproductsM" class="tab-pane fade in show active">
                                  <div class="new-product-block">
                                      <div class="container">
                                          <div class="row">
                                              <div style="width:100%" class="small-screen-scroll-tabs scroll-tabs outer-top-vs">
                                                <div class="tab-content outer-top-xs">
                                                    <div class="product-slider">
                                                        <div class="product-slider-main-block">
                                                            <div class="row no-pad"> 
                                                                @include('front.mobile.layout')
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              
                              <div id="topcatsM" class="tab-pane fade in show">
                                <div class="new-product-block">
                                    <div class="container">
                                        <div class="row">
                                            <div style="width:100%" class="small-screen-scroll-tabs scroll-tabs outer-top-vs">
                                              <div class="tab-content outer-top-xs">
                                                  <div class="product-slider">
                                                      <div class="product-slider-main-block">
                                                          <div class="row no-pad"> 
                                                              @include('front.mobile.topcatsproduct')
                                                          </div>
                                                      </div>
                                                  </div>
                                              </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                              </div>

                              <div id="featuredM" class="tab-pane fade in show">
                                <div class="new-product-block">
                                    <div class="container">
                                        <div class="row">
                                            <div style="width:100%" class="small-screen-scroll-tabs scroll-tabs outer-top-vs">
                                              <div class="tab-content outer-top-xs">
                                                  <div class="product-slider">
                                                      <div class="product-slider-main-block">
                                                          <div class="row no-pad"> 
                                                              @include('front.mobile.featuredproducts')
                                                          </div>
                                                      </div>
                                                  </div>
                                              </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                              </div>

                          </div>

                      </div>
                  </div>
                </section>
              <!-- END -->

              @php
                $getads = App\Adv::where('position','=','abovetopcategory')->where('status','=',1)->get();
              @endphp

              <!-- Advertisement before top categories-->
              @if(isset($getads))

                @foreach($getads as $adv)
                    @include('front.advlayouts.abovetopcategory')
                @endforeach

              @endif
              <!-- END -->

               <!-- Wide screen top category products -->
              <div class="mt-lg-2 mt-md-1 mt-sm-1 mt-xs-1 feature-product-block">
                @include('front.partials.topcatsproduct')
              </div>

              <!-- Advertisements -->
              @php
                $getads = App\Adv::where('position','=','abovelatestblog')->where('status','=',1)->get();
              @endphp

              @if(isset($getads))

              @foreach($getads as $adv)
                  @include('front.advlayouts.abovelatestblog')
              @endforeach

              @endif

              <!-- Blogs -->
              @if(count($blogs)>0)
                <section class="mt-1 section latest-blog">
                    <a title="View all posts" href="{{ route('front.blog.index') }}"
                        class="pull-right btn btn-md btn-info">{{ __('staticwords.vall') }}</a>
                    <h3 class="section-title">{{ __('staticwords.lfromblog') }}</h3>

                    <div class="blog-slider-container outer-top-xs">
                        <div class="owl-responsive owl-carousel blog-slider custom-carousel">
                            @foreach($blogs as $blog)
                            <div class="item">
                                <div class="blog-post">
                                    <div class="blog-post-image">
                                        <div class="image"><a title="{{$blog->heading}}"
                                                href="{{ route('front.blog.show',$blog->slug) }}"><img class="lazy"
                                                    data-src="{{url('images/blog/'.$blog->image)}}" alt="{{ $blog->image }}"></a> </div>
                                    </div>
                                    <!-- /.blog-post-image -->

                                    <div class="blog-post-info text-left">
                                        <h3 class="name"><a href="{{ route('front.blog.show',$blog->slug) }}">{{$blog->heading}}</a>
                                        </h3>

                                        <span class="info">{{ __('staticwords.by') }} {{$blog->user}} &nbsp;|&nbsp;
                                            {{$blog->created_at->format('d/m/Y')}} | {{ read_time($blog->des ) }}</span>
                                        <p class="text">
                                            {{substr(strip_tags($blog->des),0,50)}}{{strlen(strip_tags($blog->des))>50 ? "..." : ""}}
                                        </p>
                                    </div>
                                    <!-- /.blog-post-info -->

                                </div>
                                <!-- /.blog-post -->
                            </div>
                            <!-- /.item -->
                            @endforeach
                        </div>
                        <!-- /.owl-carousel -->
                    </div>
                    <!-- /.blog-slider-container -->
                </section>
              @endif

              <!-- Advertisements -->

              @php
                $getads = App\Adv::where('position','=','abovefeaturedproduct')->where('status','=',1)->get();
              @endphp

              @if(isset($getads))

                @foreach($getads as $adv)
                    @include('front.advlayouts.abovefeaturedproduct')
                @endforeach

              @endif

              <!-- Featured Products -->

              @if(count($featuredProducts)>0)
                <section class="mt-2 section new-arriavls feature-product-block">
                    <h3 class="section-title">{{ __('staticwords.fpro') }}</h3>
                    <div class="owl-carousel home-owl-carousel custom-carousel owl-theme outer-top-xs">

                        @foreach($featuredProducts as $featured)
                        @foreach($featured->subvariants as $key=> $orivar)
                        @if($orivar->def ==1)

                        @php
                        $var_name_count = count($orivar['main_attr_id']);

                        $name=array();
                        $var_name;
                        $newarr = array();
                        for($i = 0; $i<$var_name_count; $i++){ $var_id=$orivar['main_attr_id'][$i];
                            $var_name[$i]=$orivar['main_attr_value'][$var_id]; // echo($orivar['main_attr_id'][$i]);
                            $name[$i]=App\ProductAttributes::where('id',$var_id)->first();

                            }


                            try{
                            $url =
                            url('details').'/'.$featured->id.'?'.$name[0]['attr_name'].'='.$var_name[0].'&'.$name[1]['attr_name'].'='.$var_name[1];
                            }catch(Exception $e)
                            {
                            $url = url('details').'/'.$featured->id.'?'.$name[0]['attr_name'].'='.$var_name[0];
                            }

                            @endphp
                            <div class="item item-carousel">
                                <div class="products">
                                    <div class="product">
                                        <div class="product-image">
                                            <div class="image {{ $orivar->stock ==0 ? "pro-img-box" : ""}}">

                                                <a href="{{$url}}" title="{{$featured->name}}">

                                                    @if(count($featured->subvariants)>0)

                                                    @if(isset($orivar->variantimages['main_image']))
                                                    <img class="owl-lazy {{ $orivar->stock ==0 ? "filterdimage" : ""}}"
                                                        data-src="{{url('variantimages/thumbnails/'.$orivar->variantimages['main_image'])}}"
                                                        alt="{{$featured->name}}">
                                                    <img class="owl-lazy {{ $orivar->stock ==0 ? "filterdimage" : ""}}  hover-image"
                                                        data-src="{{url('variantimages/hoverthumbnail/'.$orivar->variantimages['image2'])}}"
                                                        alt="" />
                                                    @endif

                                                    @else
                                                    <img class="owl-lazy {{ $orivar->stock ==0 ? "filterdimage" : ""}}"
                                                        title="{{ $featured->name }}" data-src="{{url('images/no-image.png')}}"
                                                        alt="No Image" />

                                                    @endif



                                                </a>
                                            </div>

                                            @if($orivar->stock == 0)
                                            <h6 align="center" class="oottext"><span>{{ __('staticwords.Outofstock') }}</span></h6>
                                            @endif

                                            @if($orivar->stock != 0 && $orivar->products->selling_start_at != null &&
                                            $orivar->products->selling_start_at >= $current_date)
                                            <h6 align="center" class="oottext2"><span>{{ __('staticwords.ComingSoon') }}</span></h6>
                                            @endif
                                            <!-- /.image -->



                                            @if($featured->featured=="1")
                                            <div class="tag hot"><span>{{ __('staticwords.Hot') }}</span></div>
                                            @elseif($featured->offer_price!="0")
                                            <div class="tag sale"><span>{{ __('staticwords.Sale') }}</span></div>
                                            @else
                                            <div class="tag new"><span>{{ __('staticwords.New') }}</span></div>
                                            @endif
                                        </div>


                                        <!-- /.product-image -->

                                        <div class="product-info text-left">
                                            <h3 class="name"><a
                                                    href="{{$url}}">{{substr($featured->name, 0, 20)}}{{strlen($featured->name)>20 ? '...' : ""}}</a>
                                            </h3>
                                            @php
                                            $reviews = ProductRating::getReview($featured);
                                            @endphp

                                            @if($reviews != 0)


                                            <div class="pull-left">
                                                <div class="star-ratings-sprite"><span style="width:<?php echo $reviews; ?>%"
                                                        class="star-ratings-sprite-rating"></span></div>
                                            </div>


                                            @else
                                            <div class="no-rating">{{'No Rating'}}</div>
                                            @endif
                                            <div class="description"></div>


                                            <!-- Product-price -->

                                            <div class="product-price"> <span class="price">

                                                    @if($price_login == '0' || Auth::check())

                                                    @php

                                                    $result = ProductPrice::getprice($featured, $orivar)->getData();

                                                    @endphp


                                                    @if($result->offerprice == 0)

                                                    <span class="price"><i class="{{session()->get('currency')['value']}}"></i>
                                                        {{ sprintf("%.2f",$result->mainprice*$conversion_rate) }}</span>

                                                    @else

                                                    <span class="price"><i
                                                            class="{{session()->get('currency')['value']}}"></i>{{ sprintf("%.2f",$result->offerprice*$conversion_rate) }}</span>

                                                    <span class="price-before-discount"><i
                                                            class="{{session()->get('currency')['value']}}"></i>{{  sprintf("%.2f",$result->mainprice*$conversion_rate)  }}</span>

                                                    @endif

                                                    @endif
                                            </div>

                                            <!-- /.product-price -->

                                        </div>
                                        @if($orivar->stock != 0 && $orivar->products->selling_start_at != null &&
                                        $orivar->products->selling_start_at >= $current_date)
                                        @elseif($orivar->stock < 1) @else <!-- /.product-info -->
                                            <div class="cart clearfix animate-effect">
                                                <div class="action">
                                                    <ul class="list-unstyled">
                                                        @if(!empty($auth))
                                                        <?php $cart_table = App\Cart::where('user_id',$auth->id)->where('pro_id',$featured->id)->first(); ?>@endif
                                                        @if(empty($cart_table))

                                                        @if($price_login != 1 || Auth::check())
                                                        <li id="addCart" class="lnk wishlist">


                                                            <form method="POST"
                                                                action="{{route('add.cart',['id' => $featured->id ,'variantid' =>$orivar->id, 'varprice' => $result->mainprice, 'varofferprice' => $result->offerprice ,'qty' =>$orivar->min_order_qty])}}">
                                                                {{ csrf_field() }}
                                                                <button title="{{ __('staticwords.AddtoCart') }}" type="submit"
                                                                    class="addtocartcus btn">
                                                                    <i class="fa fa-shopping-cart"></i>
                                                                </button>
                                                            </form>


                                                        </li>

                                                        @endif
                                                        @else
                                                        <li id="addCart" class="lnk wishlist"> <a class="add-to-cart"
                                                                href="{{url('remove_table_cart/'.$orivar->id)}}"
                                                                title="{{ __('Remove Cart') }}"> <i class="icon fa fa-times"></i> </a>
                                                        </li>
                                                        @endif

                                                        @auth
                                                        @if(Auth::user()->wishlist->count()<1) <li class="lnk wishlist">

                                                            <a mainid="{{ $orivar->id }}" class="cursor-pointer add-to-cart addtowish"
                                                                data-add="{{url('AddToWishList/'.$orivar->id)}}"
                                                                title="{{ __('staticwords.AddToWishList') }}">
                                                                <i class="icon fa fa-heart"></i>
                                                            </a>

                                                            </li>
                                                            @else

                                                            @php
                                                            $ifinwishlist =
                                                            App\Wishlist::where('user_id',Auth::user()->id)->where('pro_id',$orivar->id)->first();
                                                            @endphp

                                                            @if(!empty($ifinwishlist))
                                                            <li class="lnk wishlist active">
                                                                <a mainid="{{ $orivar->id }}"
                                                                    title="{{ __('staticwords.RemoveFromWishlist') }}"
                                                                    class="add-to-cart removeFrmWish active color000 cursor-pointer"
                                                                    data-remove="{{url('removeWishList/'.$orivar->id)}}"> <i
                                                                        class="icon fa fa-heart"></i> </a>
                                                            </li>
                                                            @else
                                                            <li class="lnk wishlist"> <a title="{{ __('staticwords.AddToWishList') }}"
                                                                    mainid="{{ $orivar->id }}"
                                                                    class="add-to-cart addtowish cursor-pointer text-white"
                                                                    data-add="{{url('AddToWishList/'.$orivar->id)}}"> <i
                                                                        class="activeOne icon fa fa-heart"></i>
                                                                </a></li>
                                                            @endif

                                                            @endif
                                                            @endauth
                                                            <li class="lnk"> <a class="add-to-cart"
                                                                    href="{{route('compare.product',$orivar->products->id)}}"
                                                                    title="{{ __('staticwords.Compare') }}"> <i class="fa fa-signal"
                                                                        aria-hidden="true"></i> </a>
                                                            </li>
                                                    </ul>
                                                </div>
                                                <!-- /.action -->
                                            </div>
                                            @endif
                                            <!-- /.cart -->
                                    </div>
                                    <!-- /.product -->

                                </div>
                                <!-- /.products -->
                            </div>
                            <!-- /.item -->
                            @endif
                            @endforeach
                            @endforeach
                    </div>
                    <!-- /.home-owl-carousel -->
                </section>
              @endif

              <!-- END-->
              
              <!-- Advertisements -->
              
              @php
                $getads = App\Adv::where('position','=','afterfeaturedproduct')->where('status','=',1)->get();
              @endphp

              @if(isset($getads))

                @foreach($getads as $adv)
                    @include('front.advlayouts.afterfeaturedproduct')
                @endforeach

              @endif

              <br>

          </div>
        
         

      </div>


          
      </div>

     
      <!-- End Main -->
    </div>
  </div>

  @if(isset($popup) && $popup['enable_popup'] == '1')
  @if(Cookie::get('popup') == '')
    <div class="mlctr-underlayer">
    
        <!-- master popup div container -->
        <div class="my-popup-container mlctr-popup">
    
        <a class="mlctr-close"></a>
            <!-- following div will be overwritten with success message -->
        <div class="mt-3 mlctr-message-success" style="display:block">
            <h1 style="color:{{ $popup['heading_color'] }}">
                {{  $popup['heading']  }}
            </h1>
            <h2 style="color:{{ $popup['subheading_color'] }}" class="mt-3">
            {{ $popup['subheading'] }}</h2>
            @if($popup['description'] != '')
                <h2 style="color:{{ $popup['description_text_color'] }}" class="mt-2">
                    {{ $popup['description'] }}
                </h2>
            @endif

            @if($popup['enable_button'] == '1')
                    
                <a target="__blank" href="{{ $popup['button_link'] }}">
                    <button type="submit" class="redeembutton" style="background:{{ $popup['button_color'] }};color: {{ $popup['button_text_color'] }}">
                    {{ $popup['button_text'] }}
                    </button>
                </a>
            @endif
            <div class="mlctr-privacy text-center">
                <label><input class="offerpop_not_show" type="checkbox" name="do_not_show_me"> {{ __('staticwords.dontshowpopuptext') }}</label>
            </div>
                </div>
            
    
        </div><!--/end of my-popup-container -->
    
    </div><!--/end of my-popup-background-layer -->
  @endif
  @endif
</div>

@endsection
@section('script')


<script src="{{ url('js/wishlist.min.js') }}"></script>
<script>

  $('.offerpop_not_show').on('change',function(){

          if($(this).is(":checked")){
            var opt = 1;
          }else{
            var opt = 0;
          }
      
          $.ajax({
          type : 'GET',
          url  : '{{ route("offer.pop.not.show") }}',
          data : {opt : opt},
          dataType : 'json',
          success : function(response){
            console.log(response);
          }
        });
      
  });

  $('.mlctr-close').on('click',function(){
      $('.mlctr-underlayer').remove();
  });
</script>

<script>
    var baseUrl = @json(url('/'));
</script>
<script src="{{ url('js/app.js') }}"></script>
@endsection