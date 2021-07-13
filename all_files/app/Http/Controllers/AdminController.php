<?php
namespace App\Http\Controllers;

use App\CanceledOrders;
use App\Category;
use App\Charts\AdminUserChart;
use App\Charts\AdminUserPieChart;
use App\Charts\OrderChart;
use App\Coupan;
use App\DashboardSetting;
use App\Faq;
use App\FullOrderCancelLog;
use App\Genral;
use App\Hotdeal;
use App\Invoice;
use App\Order;
use App\PendingPayout;
use App\Product;
use App\SellerPayout;
use App\SpecialOffer;
use App\Store;
use App\Testimonial;
use App\User;
use App\VisitorChart;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PDF;


class AdminController extends Controller
{

    public function user_read()
    {
        auth()->user()
            ->unreadNotifications
            ->where('n_type', '=', 'user')
            ->markAsRead();
        return redirect()
            ->back();
    }

    public function order_read()
    {
        auth()
            ->user()
            ->unreadNotifications
            ->where('n_type', '=', 'order_v')
            ->markAsRead();
        return redirect()
            ->back();
    }

    public function ticket_read()
    {
        auth()
            ->user()
            ->unreadNotifications
            ->where('n_type', '=', 'ticket')
            ->markAsRead();
        return redirect()
            ->back();
    }

    public function all_read()
    {
        auth()
            ->user()
            ->unreadNotifications
            ->where('n_type', '!=', 'order_v')
            ->markAsRead();
        return redirect()
            ->back();
    }

    public function index()
    {
        $lang = Session::get('changed_language');

        $products = Product::whereHas('category')->whereHas('subvariants')->whereHas('subcategory')->whereHas('brand')->whereHas('store')->whereHas('vender')->get();

        $order = Order::where('status', '=', '1')->count();
        $usersquery = User::query();
        $user = $usersquery->count();
        $store = Store::count();
        $coupan = Coupan::count();
        $faqs = Faq::count();
        $category = Category::count();
        $cancelorder = CanceledOrders::count();
        $fcanorder = FullOrderCancelLog::count();
        $totalcancelorder = $fcanorder + $cancelorder;
        $inv_cus = Invoice::first();
        $setting = Genral::first();
        $totalsellers = $usersquery->where('role_id', '=', 'v')->where('status', '=', '1')->count();
        $dashsetting = DashboardSetting::first();

        $fillColors = [
            "rgba(255, 99, 132, 0.2)",
            "rgba(22,160,133, 0.2)",
            "rgba(255, 205, 86, 0.2)",
            "rgba(51,105,232, 0.2)",
            "rgba(244,67,54, 0.2)",
            "rgba(34,198,246, 0.2)",
            "rgba(153, 102, 255, 0.2)",
            "rgba(255, 159, 64, 0.2)",
            "rgba(233,30,99, 0.2)",
            "rgba(205,220,57, 0.2)",
        ];

        /*Creating Userbarchart*/

        $users = array(

            User::whereMonth('created_at', '01')
                ->whereYear('created_at', date('Y'))
                ->count(), //January

            User::whereMonth('created_at', '02')
                ->whereYear('created_at', date('Y'))
                ->count(), //Feb

            User::whereMonth('created_at', '03')
                ->whereYear('created_at', date('Y'))
                ->count(), //March

            User::whereMonth('created_at', '04')
                ->whereYear('created_at', date('Y'))
                ->count(), //April

            User::whereMonth('created_at', '05')
                ->whereYear('created_at', date('Y'))
                ->count(), //May

            User::whereMonth('created_at', '06')
                ->whereYear('created_at', date('Y'))
                ->count(), //June

            User::whereMonth('created_at', '07')
                ->whereYear('created_at', date('Y'))
                ->count(), //July

            User::whereMonth('created_at', '08')
                ->whereYear('created_at', date('Y'))
                ->count(), //August

            User::whereMonth('created_at', '09')
                ->whereYear('created_at', date('Y'))
                ->count(), //September

            User::whereMonth('created_at', '10')
                ->whereYear('created_at', date('Y'))
                ->count(), //October

            User::whereMonth('created_at', '11')
                ->whereYear('created_at', date('Y'))
                ->count(), //November

            User::whereMonth('created_at', '12')
                ->whereYear('created_at', date('Y'))
                ->count(), //December

        );

        $userchart = new AdminUserChart;

        $userchart->labels(['January', 'Febuary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']);

        $userchart->title('Monthly Registered Users in ' . date('Y'))->dataset('Monthly Registered Users', 'bar', $users)->options([
            'fill' => 'true',
            'shadow' => 'true',
            'borderWidth' => '1',
        ])->backgroundColor($fillColors)->color($fillColors);

        /*END*/

        /*Creating order chart*/

        $totalorder = array(

            Order::where('status', '1')->whereMonth('created_at', '01')
                ->whereYear('created_at', date('Y'))
                ->count(), //January

            Order::where('status', '1')->whereMonth('created_at', '02')
                ->whereYear('created_at', date('Y'))
                ->count(), //Feb

            Order::where('status', '1')->whereMonth('created_at', '03')
                ->whereYear('created_at', date('Y'))
                ->count(), //March

            Order::where('status', '1')->whereMonth('created_at', '04')
                ->whereYear('created_at', date('Y'))
                ->count(), //April

            Order::where('status', '1')->whereMonth('created_at', '05')
                ->whereYear('created_at', date('Y'))
                ->count(), //May

            Order::where('status', '1')->whereMonth('created_at', '06')
                ->whereYear('created_at', date('Y'))
                ->count(), //June

            Order::where('status', '1')->whereMonth('created_at', '07')
                ->whereYear('created_at', date('Y'))
                ->count(), //July

            Order::where('status', '1')->whereMonth('created_at', '08')
                ->whereYear('created_at', date('Y'))
                ->count(), //August

            Order::where('status', '1')->whereMonth('created_at', '09')
                ->whereYear('created_at', date('Y'))
                ->count(), //September

            Order::where('status', '1')->whereMonth('created_at', '10')
                ->whereYear('created_at', date('Y'))
                ->count(), //October

            Order::where('status', '1')->whereMonth('created_at', '11')
                ->whereYear('created_at', date('Y'))
                ->count(), //November

            Order::where('status', '1')->whereMonth('created_at', '12')
                ->whereYear('created_at', date('Y'))
                ->count(), //December

        );

        $orderchart = new OrderChart;

        $orderchart->labels(['January', 'Febuary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']);

        $orderchart->title('Total Orders in ' . date('Y'))->label('Sales')->dataset('Total Sale', 'area', $totalorder)->options([
            'fill' => 'true',
            'fillColor' => 'rgba(77, 150, 218, 0.8)',
            'color' => '#4d96da',
            'shadow' => true,
        ]);

        /*END*/

        /*Creating Piechart of user */

        $fillColors2 = ['#ff3300', '#7158e2', '#3ae374'];

        $admins = User::where('role_id', '=', 'a')->count();
        $sellers = User::where('role_id', '=', 'v')->count();
        $customers = User::where('role_id', '=', 'u')->count();

        $piechart = new AdminUserPieChart;

        $piechart->labels(['Admin', 'Seller', 'Customers']);

        $piechart->minimalist(true);

        $data = [$admins, $sellers, $customers];

        $piechart->title('User Distribution')->dataset('User Distribution', 'pie', $data)->options([
            'fill' => 'true',
            'shadow' => true,
        ])->color($fillColors2);

        /*End Piechart for user*/

        if ($setting->vendor_enable == 1) {

            $filterpayout = collect();

            $pendingPayout = SellerPayout::join('invoice_downloads','sellerpayouts.orderid','=','invoice_downloads.id')->join('orders','orders.id','=','invoice_downloads.order_id')->join('users','users.id','=','invoice_downloads.vender_id')->join('stores','stores.user_id','=','users.id')->select('users.name as sellername','stores.name as storename','sellerpayouts.*','orders.order_id as orderid','invoice_downloads.inv_no as invid')->get();

            foreach ($pendingPayout as $key => $outp) {

                if ($outp->singleorder->variant->products->return_avbl == 1) {

                    $days = $outp->singleorder->variant->products->returnPolicy->days;
                    $endOn = date("Y-m-d", strtotime("$outp->updated_at +$days days"));
                    $today = date('Y-m-d');

                    if ($today <= $endOn) {

                    } else {

                        $filterpayout->push($outp);

                    }

                } else {
                    $filterpayout->push($outp);
                }

            }

        } else {
            $filterpayout = null;
        }

        $total_testinonials = Testimonial::where('status', '=', '1')->count();

        $total_hotdeals = Hotdeal::where('status', '=', '1')->count();

        $total_specialoffer = SpecialOffer::where('status', '=', '1')->count();

        $latestorders = Order::join('users', 'users.id', '=', 'orders.user_id')->select('users.name as customername', 'orders.order_id as orderid', 'orders.qty_total as qty', 'orders.paid_in as paid_in', 'orders.order_total as ordertotal', 'orders.created_at as created_at')->orderBy('orders.id', 'DESC')->take($dashsetting->max_item_ord)->get();

        $storerequest = Store::join('users', 'users.id', '=', 'stores.user_id')->where('stores.apply_vender', '=', '0')->where('users.status', '=', '1')->select('users.name as owner', 'stores.email as email', 'stores.name as name')->take($dashsetting->max_item_str)->get();

        return view("admin.dashbord.index", compact('total_hotdeals', 'total_specialoffer', 'total_testinonials', 'totalsellers', 'latestorders', 'filterpayout', 'products', 'order', 'user', 'store', 'coupan', 'category', 'totalcancelorder', 'faqs', 'inv_cus', 'userchart', 'piechart', 'orderchart', 'storerequest'));
    }

    public function user()
    {
        $users = User::all();

        return view("admin.user.show", compact("users"));
    }

    public function order_print($id)
    {
        $invpre = Invoice::first();
        $order = order::where('id', $id)->first();

        $pdf = PDF::loadView('admin.print.pdfView', compact('order', 'invpre'));

        return $pdf->setPaper('a4', 'landscape')
            ->download('invoice.pdf');
    }

    public function single(Request $request)
    {
        $a = isset($request['id1']) ? $request['id1'] : 'not yet';

        $userUnreadNotification = auth()->user()
            ->unreadNotifications
            ->where('id', $a)->first();

        if ($userUnreadNotification) {
            $userUnreadNotification->markAsRead();
            return response()->json(['status' => 'success']);
        }

    }

    public function visitorData(Request $request)
    {

        if($request->ajax()){
            $data = VisitorChart::select(\DB::raw('SUM(visit_count) as count'), 'country_code')
            ->groupBy('country_code')
            ->get();

            $result = array();

            foreach ($data as $key => $value) {
                $result[$value->country_code] = $value->count;
            }

            return response()->json($result);
        }
       

    }

}
