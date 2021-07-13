<?php

namespace App\Http\Controllers;

use App\Seo;
use Illuminate\Http\Request;
use DotenvEditor;

/*==========================================
=             Author: Media City            =
=    Author URI: https://mediacity.co.in    =
=            Copyright (c) 2020-21          =
==========================================*/

class SeoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $seo = Seo::first();

        return view("admin.Seo.edit", compact("seo"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $cat = Seo::first();
        $input = $request->all();

        $request->validate([
            "metadata_des" => "required",
            "metadata_key" => "required",
        ],[
            "metadata_des.required" => "Metadata description is required",
            "metadata_key.required" => "Metadata key is required",
        ]);

        $env_keys_save = DotenvEditor::setKeys([
            'FACEBOOK_PIXEL_ID' => $request->FACEBOOK_PIXEL_ID,
        ]);

        $env_keys_save->save();

        if(isset($cat)){
            Seo::create($input);
        }else{
            $cat->update($input);
        }




        
        notify()->success('Seo settings has been updated !');
        return back();

        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $seo = Seo::find($id);

        if(!$seo){
            notify()->error('Seo settings not found !');
            return redirect('/');
        }

        return view("admin.Seo.edit", compact("seo"));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $cat = Seo::find($id);
        $input = $request->all();

        $request->validate([
            "metadata_des" => "required",
            "metadata_key" => "required",
        ],[
            "metadata_des.required" => "Metadata description is required",
            "metadata_key.required" => "Metadata key is required",
        ]);

        if(isset($cat)){
            $cat->update($input);
        }else{
            $cat = new Seo;
            $cat->create($input);
        }

        notify()->success('Seo settings has been updated !');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       
    }

}
