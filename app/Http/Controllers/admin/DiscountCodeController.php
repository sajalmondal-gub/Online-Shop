<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class DiscountCodeController extends Controller
{
    public function index(Request $request){
        $discountCoupon = DiscountCoupon::latest();
        if (!empty($request->get('keyword'))) {
            $discountCoupon = $discountCoupon->where('name','like','%'.$request->get('keyword').'%');
            $discountCoupon = $discountCoupon->orWhere('code','like','%'.$request->get('keyword').'%');
        }


        $discountCoupon =$discountCoupon->paginate(10);
        return View('admin.discountcode.index',compact('discountCoupon'));

    }
    public function create(){
        return View('admin.discountcode.create');

    }
    public function store(Request $request){
        $validator= Validator::make($request->all(),[
            'code'=>'required',
            'type'=>'required',
            'discount_amount'=>'required|numeric',
            'status'=>'required'
        ]);
        if($validator->passes()){
            if(!empty($request->start_at)){
                $now=Carbon::now();
                $startAt=  Carbon::createFromFormat('Y-m-d H:i:s',$request->start_at);
                if($startAt->lte($now)==true){
                    return response()->json([
                        'status'=>false,
                        'errors'=>['start_at'=>'Start date can not be less than current date and time']
        
                    ]);

                }
            }
            if (!empty($request->start_at) && !empty($request->expires_at)) {
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->expires_at);
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_at);
            
                if ($expiresAt->gt($startAt) ==false) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expire date must be greater than start date'],
                    ]);
                }
            }
            
                        
         $discountCode= new DiscountCoupon();
         $discountCode->code=$request->code;
         $discountCode->name=$request->name;
         $discountCode->description=$request->description;
         $discountCode->max_uses=$request->max_uses;
         $discountCode->max_uses_user=$request->max_uses_user;
         $discountCode->type=$request->type;
         $discountCode->discount_amount=$request->discount_amount;
         $discountCode->min_amount=$request->min_amount;
         $discountCode->status=$request->status;
         $discountCode->start_at=$request->start_at;
         $discountCode->expires_at=$request->expires_at;
         $discountCode->save();
         
         $message='Discount Coupon Added successfully';
         session()->flash('success',$message);
         return response()->json([
            'status'=>true,
            'errors'=>$message

        ]);

        }else{
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()

            ]);
        }

    }
    public function edit($id,Request $request){
        $coupon =DiscountCoupon::find($id);
        if($coupon ==null){
            session()->flash('error','Record not found');
            return redirect()->route('coupon.index');
        }
        $data['coupon']=$coupon;
    return View('admin.discountcode.edit',$data);
    }
    public function update(Request $request,$id){
        $discountCode=  DiscountCoupon::find($id);
        if($discountCode == null){
            session()->flash('error','Record not Found');
            return response()->json([
                'status'=>true,
              
            ]);
        }
        $validator= Validator::make($request->all(),[
            'code'=>'required',
            'type'=>'required',
            'discount_amount'=>'required|numeric',
            'status'=>'required'
        ]);
        if($validator->passes()){
            if(!empty($request->start_at)){
                $now=Carbon::now();
                $startAt=  Carbon::createFromFormat('Y-m-d H:i:s',$request->start_at);
                if($startAt->lte($now)==true){
                    return response()->json([
                        'status'=>false,
                        'errors'=>['start_at'=>'Start date can not be less than current date and time']
        
                    ]);

                }
            }
            if (!empty($request->start_at) && !empty($request->expires_at)) {
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->expires_at);
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_at);
            
                if ($expiresAt->gt($startAt) ==false) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expire date must be greater than start date'],
                    ]);
                }
            }
            
                        
     
         $discountCode->code=$request->code;
         $discountCode->name=$request->name;
         $discountCode->description=$request->description;
         $discountCode->max_uses=$request->max_uses;
         $discountCode->max_uses_user=$request->max_uses_user;
         $discountCode->type=$request->type;
         $discountCode->discount_amount=$request->discount_amount;
         $discountCode->min_amount=$request->min_amount;
         $discountCode->status=$request->status;
         $discountCode->start_at=$request->start_at;
         $discountCode->expires_at=$request->expires_at;
         $discountCode->save();
         
         $message='Discount Coupon update successfully';
         session()->flash('success',$message);
         return response()->json([
            'status'=>true,
            'errors'=>$message

        ]);

        }else{
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()

            ]);
        }

    }
    public function destroy(Request $request,$id){
        $discountCode=  DiscountCoupon::find($id);
        if($discountCode == null){
            session()->flash('error','Record not Found');
            return response()->json([
                'status'=>true,
            ]);
        }
        $discountCode->delete();
        session()->flash('success','Discount delete Successfully');
        return response()->json([
            'status'=>true,

        ]);

    }
}
