<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class FrontController extends Controller
{
    public function index(){
       $products= Product::where('is_featured','Yes')->orderBy('id','DESC')->where('status','1')->take(8)->get();
       $data['productFeatured']=$products;
       $latestproducts= Product::orderBy('id','DESC')->where('status','1')->take(8)->get();
       $data['latestproducts']=$latestproducts;
        return View('front.home',$data); 
    }
public function addToWhishlist(Request $request){
    if(Auth::check() ==false){
        session(['url.intended'=>url()->previous()]);
        return response()->json([
            'status'=>false,
        ]);
    }
    $product =Product::where('id',$request->id)->first();
    if($product ==null){
        return response()->json([
            'status'=>true,
            'message'=>'<div class="alert alert-danger">Product not found</div>'
        ]);
    }
    Wishlist::updateOrCreate(
        [
          'user_id'=>Auth::user()->id,
          'product_id'=>$request->id
        ],
        [
            'user_id'=>Auth::user()->id,
            'product_id'=>$request->id
        ]
    );


  //  $wishlist =new Wishlist;
   // $wishlist->user_id=Auth::user()->id;
 //   $wishlist->product_id=$request->id;
  //  $wishlist->save();
   // $message='Product added in your wishlist';
   // session()->flash('success',$message);
    return response()->json([
        'status'=>true,
        'message'=>'<div class="alert alert-success"><strong>"'.$product->title.'"</strong> added in your wishlist</div>'

    ]);
}
public function contactus(){
    return View('front.contactus');
}
public function sendContactMail(Request $request){
    $validator=Validator::make($request->all(),[
        'name'=>'required',
        'email'=>'required|email',
        'subject'=>'required'
    ]);
    if($validator->passes()){
        //send email
        $mailData=[
            'name'=>$request->name,
            'email'=>$request->email,
            'subject'=>$request->subject,
            'message'=>$request->message,
            'mail_subject'=>'You have receive a contact email'
        ];
        $admin =User::where('id',3)->first();
        Mail::to($admin->email)->send(new ContactMail($mailData));
        session()->flash('success','Thanks for contacting us.');
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
}
