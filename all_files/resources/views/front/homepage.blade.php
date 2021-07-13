@extends("front.layout.master")
@section('meta_tags')
<link rel="canonical" href="{{ url()->current() }}"/>
<meta name="keywords" content="{{ isset($seoset) ? $seoset->metadata_key : '' }}">
<meta property="og:title" content="{{ isset($seoset) ? $seoset->project_name : config('app.name') }}" />
<meta property="og:description" content="{{ isset($seoset) ? $seoset->metadata_des : '' }}" />
<meta property="og:type" content="WebPage"/>
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:image" content="{{ url('images/genral/'.$front_logo) }}" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:image" content="{{ url('images/genral/'.$front_logo) }}" />
<meta name="twitter:description" content="{{ isset($seoset) ? $seoset->metadata_des : '' }}" />
<meta name="twitter:site" content="{{ url()->current() }}" />
<script type="application/ld+json">{"@context":"https:\/\/schema.org","@type":"WebPage","description":"{{ isset($seoset) ? $seoset->metadata_des : '' }}","image":"{{ url('images/genral/'.$front_logo) }}"}</script>
@endsection
@section("body")
<?php $home_slider = App\Widgetsetting::where('name','slider')->first(); ?>
<div class="body-content outer-top-vs" id="top-banner-and-menu">
   
    <div class="container-fluid">
        <div id="app" class="row no-gutters">
            
            <div class="h-100 col-12 col-sm-12 col-md-12 col-lg-12  col-xl-2 sidebar left-sidebar">
                <div class="side-content">
                    <sidebar-desktop></sidebar-desktop>
                </div>
            </div>
            <!-- Start Main -->
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-10 right-sidebar">
                <div class="main-content homebanner-holder">
                    
                    <homepage></homepage>
                    
    
                </div>
            </div>
        </div>
    </div>
    
</div>
{{-- @php
    $selected_language = App\Language::firstWhere('lang_code','=',session()->get('changed_language'));
@endphp --}}
@endsection