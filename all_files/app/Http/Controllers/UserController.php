<?php

namespace App\Http\Controllers;

use App\Allcity;
use App\Allcountry;
use App\Allstate;
use App\Country;
use App\Genral;
use App\Store;
use App\User;
use Avatar;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Image;
use Yajra\DataTables\Facades\DataTables as FacadesDataTables;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('is_admin');
        $this->wallet_system = Genral::findOrFail(1)->wallet_enable;
    }

    public function index(Request $request)
    {
        if(!$request->get('filter')){
            notify()->error('Invalid URL');
            return redirect('/myadmin');
        }

        
        if ($request->get('filter') == 'admin') {
            
            if($request->get('q')){
                $users = User::where('role_id', '=', 'a')->where('name', 'LIKE', '%' . $request->get('q') . '%')->orWhere('email', 'LIKE', '%' . $request->get('q') . '%')->orderBy('id','DESC')->paginate(12);
            }else{
                $users = User::where('role_id', '=', 'a')->orderBy('id','DESC')->paginate(12);
            }

        }elseif ($request->get('filter') == 'sellers') {
            if($request->get('q')){
                $users = User::where('role_id', '=', 'v')->where('name', 'LIKE', '%' . $request->get('q') . '%')->orWhere('email', 'LIKE', '%' . $request->get('q') . '%')->orderBy('id','DESC')->paginate(12);
            }else{
                $users = User::where('role_id', '=', 'v')->orderBy('id','DESC')->paginate(12);
            }
        }elseif ($request->get('filter') == 'customer') {
            if($request->get('q')){
                $users = User::where('role_id', '=', 'u')->where('name', 'LIKE', '%' . $request->get('q') . '%')->orWhere('email', 'LIKE', '%' . $request->get('q') . '%')->orderBy('id','DESC')->paginate(12);
            }else{
                $users = User::where('role_id', '=', 'u')->orderBy('id','DESC')->paginate(12);
            }
            
        }else{
            notify()->error('Invalid URL');
            return redirect('/myadmin');
        }
       
        return view("admin.user.show", compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        $country = Country::all();
        return view("admin.user.add_user", compact("country"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'image' => 'mimes:jpeg,jpg,png,bmp,gif',
        ]);

        $input = $request->all();

        $u = new User;

        if ($file = $request->file('image')) {

            $optimizeImage = Image::make($file);
            $optimizePath = public_path() . '/images/user/';
            $image = time() . $file->getClientOriginalName();
            $optimizeImage->resize(200, 200, function ($constraint) {
                $constraint->aspectRatio();
            });
            $optimizeImage->save($optimizePath . $image);

            $input['image'] = $image;

            $input['password'] = Hash::make($request->password);

        }

        $input['password'] = Hash::make($request->password);

        $u->create($input);

        return back()->with("added", "User Has Been Added");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(\App\Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $country = Country::all();
        $states = Allstate::where('country_id', $user->country_id)->get();
        $citys = Allcity::where('state_id', $user->state_id)->get();
        return view("admin.user.edit", compact("country","user", "states", "citys"));

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

        $data = $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'image' => 'mimes:jpeg,jpg,png,bmp,gif',
        ]);

        $user = User::findOrFail($id);

        $input = $request->all();

        if (isset($request->is_pass_change)) {
            $this->validate($request, [
                'password' => 'required|between:6,255|confirmed',
                'password_confirmation' => 'required',
            ]);
            $newpass = Hash::make($request->password);
            $input['password'] = $newpass;

        } else {
            $input['password'] = $user->password;
        }

        if ($file = $request->file('image')) {

            if ($user->image != '' && file_exists(public_path() . '/images/user/' . $user->image)) {
                unlink(public_path() . '/images/user/' . $user->image);
            }

            $optimizeImage = Image::make($request->file('image'));
            $optimizePath = public_path() . '/images/user/';
            $name = time() . $file->getClientOriginalName();
            $optimizeImage->resize(200, 200, function ($constraint) {
                $constraint->aspectRatio();
            });
            $optimizeImage->save($optimizePath . $name, 72);
            $input['image'] = $name;

        }

        try {

            $user->update($input);

            if (isset($request->wallet_status) && isset($user->wallet)) {
                $user->wallet()->update(['status' => '1']);
            } else {
                $user->wallet()->update(['status' => '0']);
            }

        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == '1062') {
                return back()->with("warning", "Email Alerdy Exists");
            }
        }

        notify()->success('details has been updated',"$user->name");

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
        $user = User::find($id);

        if ($user->image != null && file_exists(public_path() . '/images/user/' . $user->image)) {
            unlink(public_path() . '/images/user/' . $user->image);
        }

        if ($this->wallet_system == 1 && isset($user->wallet)) {

            $user->wallet->wallethistory()->delete();
            $user->wallet->delete();

        }

        $value = $user->delete();

        if ($value) {
            notify()->error("User has been deleted !");
            return back();
        }
    }


    public function appliedform(Request $request)
    {
        $stores = \DB::table('stores')->join('allcities', 'allcities.id', '=', 'stores.city_id')->join('allstates', 'stores.state_id', '=', 'allstates.id')->join('allcountry', 'allcountry.id', '=', 'stores.country_id')->join('users', 'users.id', '=', 'stores.user_id')->select('stores.*', 'allcities.pincode as pincode', 'allcities.name as city', 'allstates.name as state', 'allcountry.name as country', 'users.name as username')->where('stores.apply_vender', '=', '0')->get();

        if ($request->ajax()) {
            return FacadesDataTables::of($stores)->addIndexColumn()
                ->addColumn('detail', function ($row) {
                    $html = '';
                    $html .= "<p><b>Store Name:</b> $row->name</p>";
                    $html .= "<p><b>Requested By:</b> $row->username</p>";
                    $html .= "<p><b>Address:</b> $row->address,</p>";
                    $html .= "<p><b>Store Location:</b> $row->city, $row->state, $row->country</p>";
                    if ($row->pincode) {
                        $html .= "<p><b>Pincode:</b> $row->pincode</p>";
                    } else {
                        $html .= "<p><b>Pincode:</b> - </p>";
                    }

                    return $html;
                })
                ->addColumn('requested_at', function ($row) {
                    return '<b>' . date("d-M-Y | h:i A", strtotime($row->created_at)) . '</b>';
                })
                ->addColumn('action', 'admin.user.requestaction')
                ->rawColumns(['detail', 'requested_at', 'action'])
                ->make(true);
        }

        return view("admin.user.appliyed_vender")->withList(count($stores));
    }

    public function choose_country(Request $request)
    {

        $id = $request['catId'];

        $country = Allcountry::findOrFail($id);
        $upload = Allstate::where('country_id', $id)->pluck('name', 'id')->all();

        return response()->json($upload);
    }

    public function choose_city(Request $request)
    {

        $id = $request['catId'];

        $state = Allstate::findOrFail($id);
        $upload = Allcity::where('state_id', $id)->pluck('name', 'id')->all();

        return response()->json($upload);
    }

}
