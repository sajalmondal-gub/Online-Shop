<?php

namespace App\Http\Controllers\admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(){
     $totalorders= Order::where('status','!=','cancelled')->count();
     $totalcustomers= User::where('role',1)->count();
     $totalProducts= Product::count();
     $totalrevenue= Order::where('status','!=','cancelled')->sum('grand_total');
     //this month revenue
      $startMonth =Carbon::now()->startOfMonth()->format('Y-m-d');
      $currenDate =Carbon::now()->format('Y-m-d');
      
      $revenueThisMonth=Order::where('status','!=','cancelled')->whereDate('created_at','>=',$startMonth)->whereDate('created_at','<=',$currenDate)->sum('grand_total');
     //last month total sale 
     $lastMonthStartDate= Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
     $lastMonthendingDate=  Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
     $totalsaleoflastmonth=Order::where('status','!=','cancelled')->whereDate('created_at','>=',$lastMonthStartDate)->whereDate('created_at','<=',$lastMonthendingDate)->sum('grand_total');
     //last 30days sale 
     $lastThirtyStartDays=Carbon::now()->subDays(30)->format('y-m-d');
     $lastThirtyDayssale =Order::where('status','!=','cancelled')->whereDate('created_at','>=',$lastThirtyStartDays)->whereDate('created_at','<=',$currenDate)->sum('grand_total');
     $lastMonthName=Carbon::now()->subMonth()->startOfMonth()->format('M');
     // total sale today
     $totalsaleToday =Order::where('status','!=','cancelled')->whereDate('created_at','>=',$currenDate)->sum('grand_total');

     return View('admin.dashboard',[
        'totalorders'=>$totalorders,
        'totalProducts'=>$totalProducts,
        'totalcustomers'=>$totalcustomers,
        'totalrevenue'=>$totalrevenue,
        'revenueThisMonth'=>$revenueThisMonth,
        'totalsaleoflastmonth'=>$totalsaleoflastmonth,
        'lastThirtyDayssale'=>$lastThirtyDayssale,
        'lastMonthName'=>$lastMonthName,
        'totalsaleToday'=>$totalsaleToday
      ]);
    }
    public function logout(){
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
