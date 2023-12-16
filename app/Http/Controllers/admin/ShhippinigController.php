<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Country;

use App\Models\ShippingCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class ShhippinigController extends Controller
{
    public function create(){
        $countries =Country::get();
        $data['countries']=$countries;
        $shippingCharges = ShippingCharge::select('shipping_charges.*','countries.name')->leftJoin('countries', 'countries.id', '=', 'shipping_charges.country_id')->get();

      // dd($shippingCharges);
        $data['shippingCharges']=$shippingCharges;
        return View('admin.shipping.create',$data);
    }
    public function store(Request $request){
        $validator=Validator::make($request->all(),[
            'country'=>'required',
            'amount'=>'required',
            
        ]);

        
        if($validator->passes()){
            $shipping =new ShippingCharge;
            $shipping->country_id=$request->country;
            $shipping->amount=$request->amount;
            $shipping->city=$request->city;
            $shipping->save();
            session()->flash('success','Shipping charge add successfully');
            return response()->json([
                'status'=>true,
                
            ]);

        }else{
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ]);
        }
    }
    public function edit($id){
        $shippingCharge=ShippingCharge::find($id);
        $countries =Country::get();
        $data['countries']=$countries;
        $data['shippingCharge']=$shippingCharge;
        return View('admin.shipping.edit',$data);
    }
    public function update($id,Request $request){
        $validator=Validator::make($request->all(),[
            'country'=>'required',
            'amount'=>'required',
            
        ]);
        if($validator->passes()){
            $shipping = ShippingCharge::find($id);
            $shipping->country_id=$request->country;
            $shipping->amount=$request->amount;
            $shipping->city=$request->city;
            $shipping->save();
            session()->flash('success','Shipping charge update successfully');
            return response()->json([
                'status'=>true,
                
            ]);

        }else{
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ]);
        }
    }
    public function distroy($id){

    $shippingCharge =ShippingCharge::find($id);
    $shippingCharge->delete();
    session()->flash('success','Shipping charge delete successfully');
            return response()->json([
                'status'=>true,
                
            ]);

    }
}
