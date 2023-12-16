<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\DiscountCoupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use SebastianBergmann\Type\TrueType;

class CartController extends Controller
{
     public function addToCart( Request $request){
        $product=Product::with('product_images')->find($request->id);
        if ($product ==null) {
         return response()->json([
            'status'=>false,
            'message'=>'Product not found'
         ]);
         }
         if(Cart::count() > 0){
            $cartContent=Cart::content();
            $productAlreadyExists=false;
            foreach ($cartContent as $item) {
              if ($item->id == $product->id) {
               $productAlreadyExists=true;
              }
            }
            if($productAlreadyExists == false){
               Cart::add($product->id,$product->title,1,$product->price,['productImage'=>(!empty($product->product_images)) ? $product->product_images->first() :'']);
               $status=true;
               $message=$product->title.'  Added  in cart';
               session()->flash('success', $message);
            }else{
               $status=false;
               $message=$product->title.' Already added  in cart';
            }

         }else{
           
            Cart::add($product->id,$product->title,1,$product->price,['productImage'=>(!empty($product->product_images)) ? $product->product_images->first() :'']);
            $status=true;
            $message=$product->title.'Product added in cart';
                 
            session()->flash('success', $message);
         }
         return response()->json([
            'status'=>$status,
            'message'=>$message
         ]);
        
     }
     public function cart(){
      $cartContent =Cart::content();
      $data['cartContent']=$cartContent;
      //dd($data);
        return View('front.cart',$data);
     }
     public function updateCart(Request $request){
      $rowId=$request->rowId;
      $qty=$request->qty;
      //check qty stock
      $itemInfo = Cart::get($rowId);
      $product =Product::find(($itemInfo->id));
      if ($product->track_qty  == 'Yes') {
         if ($qty <= $product->qty) {
            Cart::update($rowId,$qty);
            $message='Cart update successfully';
            $status=true;
            session()->flash('success',$message);
         }else{
            $message ='Request qty ('.$qty.') not available in stock.';
            $status=false;
            session()->flash('error',$message);
         }
      }else{
         Cart::update($rowId,$qty);
         $message='Cart update successfully';
         $status=true;
         session()->flash('success',$message);
      }
      
      return response()->json([
         'status'=> $status,
         'message'=>$message
      ]);

     }
     public function deleteItem(Request $request){
      $rowId=$request->rowId;
      $itemInfo=Cart::get($rowId);

      if($itemInfo == null){
         $errorMassage='Item not found';
         session()->flash('error', $errorMassage);
         return response()->json([
            'status'=> false,
            'message'=>  $errorMassage
         ]);
      }
      Cart::remove($request->rowId);
      $massage='Item removed from cart successfully';
      session()->flash('error', $massage);
      return response()->json([
         'status'=> true,
         'message'=>  $massage
      ]);

     }
     public function checkout(){
      $discount=0;
      if(Cart::count()==0){
         return redirect()->route('front.cart');
      }
      if (Auth::check() == false) {
         // Store the intended URL in the session
         if (!session()->has('url.intended')) {
            session(['url.intended' => url()->current()]);
        }    
         return redirect()->route('account.login');
     }  
     $customerAddress =CustomerAddress::where('user_id',Auth::user()->id)->first();
     session()->forget('url.intended');
     $countries=Country::orderBy('name','ASC')->get();

     $subTotal=Cart::subtotal(2,'.','');
      if(session()->has('code')){
         
         $code= session()->get('code');
         if($code->type =='percent'){
            $discount =($code->discount_amount/100)*$subTotal;
         }else{
            $discount=$code->discount_amount;

         }
       }

   // shipping calculate
    if($customerAddress !='')
    {
      $userCountry =$customerAddress->country_id;
      $shippingInfo = ShippingCharge::where('country_id',$userCountry)->first();
      $totalQty=0;
      $totalShippingCharge=0;
      $grandTotal=0;
      foreach (Cart::content() as $item ) {
       $totalQty+=$item->qty;
      }
      $totalShippingCharge=$totalQty*$shippingInfo->amount;
      $grandTotal= ($subTotal-$discount)+$totalShippingCharge;
    }else{
      $totalShippingCharge=0;
      $grandTotal=($subTotal-$discount);
    }
     
      return View('front.checkout',[
         'countries'=>$countries,
         'customerAddress'=>$customerAddress,
         'totalShippingCharge'=>$totalShippingCharge,
         'discount'=>$discount,
         'grandTotal'=>$grandTotal
      ]);
     }
     public function processcheckout(Request $request){
      $validator=Validator::make($request->all(),[
         'first_name'=>'required',
         'last_name'=>'required',
         'country'=>'required',
         'city'=>'required',
         'address'=>'required',
         'mobile'=>'required',

      ]);
      if($validator->fails()){
         return response()->json([
            'message'=>'Please fix the Error',
            'status'=>false,
            'errors'=>$validator->errors()
         ]);
      }
     // $customerAddress=CustmerAddress::
     $user= Auth::user();
     CustomerAddress::updateOrCreate(
     ['user_id'=>$user->id],
     [
      'user_id'=>$user->id,
      'first_name'=>$request->first_name,
      'last_name'=>$request->last_name,
      'email'=>$request->email,
      'mobile'=>$request->mobile,
      'address'=>$request->address,
      'apartment'=>$request->appartment,
      'city'=>$request->first_name,
      'state'=>$request->first_name,
      'zip'=>$request->first_name,
      'country_id'=>$request->country,
     ]);
     if($request->payment_method == 'cod'){
      $shipping =0;
      $discount =0;
      $discountCodeID=NULL;
      $promoCode='';
      $subTotal =Cart::subtotal(2,'.','');
      if(session()->has('code')){
         $code= session()->get('code');
         if($code->type =='percent'){
            $discount =($code->discount_amount/100)*$subTotal;
         }else{
            $discount=$code->discount_amount;
         }
         $discountCodeID=$code->id;
         $promoCode=$code->code;

       }
   
      $grandTotal =$subTotal+$shipping;
      $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();
      $totalQty = 0;
      foreach (Cart::content() as $item) {
          $totalQty += $item->qty;
      }
      if ($shippingInfo != null) {        
         // Issue: The shipping charge calculation seems incorrect
         $shipping = $totalQty * $shippingInfo->amount;
         $grandTotal =( $subTotal-$discount) + $shipping;
     }

      $order = new Order;
      $order->subtotal=$subTotal;
      $order->shipping=$shipping;
      $order->grand_total=$grandTotal;
      $order->discount=$discount;
      $order->coupon_code_id =$discountCodeID;
      $order->coupon_code =$promoCode;
      $order->payment_status ='not paid';
      $order->status ='pending';
      $order->grand_total=$grandTotal;
      $order->user_id=$user->id;
      $order->first_name=$request->first_name;
      $order->last_name=$request->last_name;
      $order->email=$request->email;
      $order->address=$request->address;
      $order->state=$request->state;
      $order->city=$request->city;
      $order->zip=$request->zip;
      $order->country_id=$request->country;
      $order->mobile=$request->mobile;
      $order->apartment=$request->appartment;
      $order->save();
      // store order item in order table
      foreach(Cart::content() as $item){
         $orderItem =new OrderItem;
         $orderItem->product_id=$item->id;
         $orderItem->order_id=$order->id;
         $orderItem->name=$item->name;
         $orderItem->qty=$item->qty;
         $orderItem->price=$item->price;
         $orderItem->product_id=$item->id;
         $orderItem->total=$item->price*$item->qty;
         $orderItem->save();

         // update product stock
         $productData =Product::find($item->id);
         if($productData->track_qty =='Yes'){
            $currentQty = $productData->qty;
            $updateQty =$currentQty-$item->qty;
            $productData->qty =$updateQty;
            $productData->save();
         }
      }
      orderEmail($order->id,'customer');
      session()->flash('success','You have successfully placed your order');
      Cart::destroy();
      session()->forget('code');
      return response()->json([
         'message'=>'Order saved successfully',
         'status'=>True
      ]);
     }else{

     }
     }
     public function thankyou(){
  
      return View('front.layouts.thankyou');

     }

     public function getOrderSummery(Request $request){
   
       $subTotal = Cart::subtotal(2, '.', '');
       //discount
       $discount=0;
      
       if(session()->has('code')){
         $code= session()->get('code');
         if($code->type =='percent'){
            $discount =($code->discount_amount/100)*$subTotal;
         }else{
            $discount=$code->discount_amount;
         }
       }


      if ($request->country_id > 0) {
        $shippingInfo = ShippingCharge::where('country_id', $request->country_id)->first();

        $totalQty = 0;
        foreach (Cart::content() as $item) {
            $totalQty += $item->qty;
        }

        if ($shippingInfo != null) {
         
            // Issue: The shipping charge calculation seems incorrect
            $shippingCharge = $totalQty * $shippingInfo->amount;
            $grandTotal = ($subTotal-$discount) + $shippingCharge;

            return response()->json([
                'status' => true,
                'grandTotal' => number_format($grandTotal, 2),
                'discount'=>number_format($discount,2),
                'shippingCharge' => number_format($shippingCharge, 2), 
            ]);
        }else{
         return response()->json([
            'status' => true,
            'grandTotal' => number_format($subTotal, 2),
            'shippingCharge' => number_format(0, 2),
        ]);
        }
    } else {
        // Issue: The shippingCharge key is set to 0 instead of the actual shipping charge
        return response()->json([
            'status' => true,
            'grandTotal' => number_format($subTotal-$discount, 2),
            'discount'=>number_format($discount,2),
            'shippingCharge' => number_format(0, 2),
        ]);
    }
}
public function applyDiscount(Request $request){
  $code=DiscountCoupon::where('code',$request->code)->first();
  if($code ==null){
   return response()->json([
      'status' => false,
      'message' => 'Invalid discount coupon',
  ]);
  }
  $now=Carbon::now();
  
 // echo $now->format('Y-m-d H:i:s');
  if($code->start_at !=""){
   $startDate=Carbon::createFromFormat('Y-m-d H:i:s',$code->start_at);
   if($now->lt($startDate))
   {
      return response()->json([
         'status'=>false,
         'message'=>'Invalid discount coupon'
      ]);
   }
   //exipre check
   if($code->expires_at !=""){
      $expireDate=Carbon::createFromFormat('Y-m-d H:i:s',$code->expires_at);
      if($now->gt($expireDate))
      {
         return response()->json([
            'status'=>false,
            'message'=>'Invalid discount coupon'
         ]);
      }
     }
     if($code->max_uses>0){
      $coupunUsed=Order::where('coupon_code_id',$code->id)->count();
      if($coupunUsed >= $code->max_uses){
         return response()->json([
            'status'=>false,
            'message'=>'Invalid Discount Coupon'
         ]);
        }
     }
     
    if($code->max_uses_user>0){
      $couponUsedByUser=Order::where(['coupon_code_id',$code->id.'user_id'=>Auth::user()->id])->count();
      if($couponUsedByUser >= $code->max_uses_user){
       return response()->json([
          'status'=>false,
          'message'=>'You already used this coupon discount'
       ]);
      } 
    }
   //min amount
   $subTotal = Cart::subtotal(2, '.', '');
   if($code->min_amount>0){
      if($subTotal <$code->min_amount){
         return response()->json([
            'status'=>false,
            'message'=>'Your min amount must be tk' .$code->min_amount
         ]);
      }

   }
     session()->put('code',$code);
     return $this->getOrderSummery($request);
  }
}
public function removediscount(Request $request){
   session()->forget('code');
   return $this->getOrderSummery($request);
}

}
