<?php

namespace App\Http\Controllers\front;

use App\Mail\ResetPasswordEmail;
use DB;
use App\Models\User;
use App\Models\Order;
use App\Models\Country;
use App\Models\Wishlist;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CustomerAddress;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(){
        return View('front.account.login');

    }
    
    public function register(){
        return View('front.account.register');
    }
    public function preoccessregister(Request $request){
        $validator =Validator::make($request->all(),[
            'name'=>'required|min:3',
            'email'=>'required|unique:users',
            'phone'=>'required',
            'password'=>'required|min:5|confirmed'
        ]);
        if($validator->passes()){
            $user = new User;
            $user->name=$request->name;
            $user->email=$request->email;
            $user->password=Hash::make($request->password);
            $user->phone=$request->phone;
            $user->save();
            session()->flash('success','You have been registerd succcesfully.Thank you');
            return response()->json([
                'status'=>true,
                
            ]);

        } else{
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ]);
        }
    }
    public function authenticate(Request $request){
        $validator =Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required'
        ]);
        if ($validator->passes()) {
            if(Auth::attempt(['email'=>$request->email,'password'=>$request->password],$request->get('remember'))){
                if (session()->has('url.intended')) {
                    // Redirect to the intended URL
                    //dd(session()->get('url.intended'));

                    return redirect(session()->get('url.intended'));
                }
                return redirect()->route('account.profile');
            }else{
              //  session()->flash('error','Password or Email is incorrect');
                return redirect()->route('account.login')->withErrors($validator)->withInput($request->only('email'))->with('error','Password or Email is incorrect');
            }
        }else{
            return redirect()->route('account.login')->withErrors($validator)->withInput($request->only('email'));
        }
    }
    public function profile(){
        $userId=Auth::user()->id;
        $user =User::where('id',$userId)->first();
        $countries=Country::orderBy('name','ASC')->get();
        
        $customerAddress =CustomerAddress::where('user_id',$userId)->first();

       
        return View('front.account.profile',[
            'user'=>$user,
            'countries' =>$countries,
            'customerAddress'=>$customerAddress
        ]);
    }
    public function logout(){
        Auth::logout();
        return redirect()->route('account.login')->with('success','You successfully logged out.');
    }
    public function order(){
        $user =Auth::user();
      $order=  Order::where('user_id',$user->id)->orderBy('created_at','DESC')->get();
      $data['order']=$order;

        return View('front.account.order',$data);

    }
    public function orderDetails($id){
        $data=[];
        $user =Auth::user();
        $order=  Order::where('user_id',$user->id)->where('id',$id)->first();
       $data['order']=$order;
       $orderItems=OrderItem::where('order_id',$id)->get();
       $data['orderItems']=$orderItems;
       $orderItemsCount=OrderItem::where('order_id',$id)->count();
       $data['orderItemsCount']=$orderItemsCount;
       return View('front.account.order-detail',$data);
    }
    public function wishlist(){
        $wishlist =Wishlist::where('user_id',Auth::user()->id)->with('product')->get();
        
        $data['wishlist']=$wishlist;
        return View('front.account.wishlist',$data);
    }
    public function removeProductFromWishlist(Request $request){
        $wishlist=Wishlist::where('user_id',Auth::user()->id)->where('product_id',$request->id)->first();
        if($wishlist ==null){
            session()->flash('error','Product already removed');
            return response()->json([
                'status'=>true,
            ]);
        } else{
            Wishlist::where('user_id',Auth::user()->id)->where('product_id',$request->id)->delete();
            session()->flash('success','Product  removed successfully');
            return response()->json([
                'status'=>true,
            ]);
        }

    }
    public function updateProfile(Request $request){
        $userId=Auth::user()->id;
       $validator =Validator::make($request->all(),[
        'name'=>'required',
        'email'=>'required|email|unique:users,email,'.$userId.',id',
        'phone'=>'required'
       ]);
       if($validator->passes()){
        $user=User::find($userId);
        $user->name=$request->name;
        $user->email=$request->email;
        $user->phone=$request->phone;
        $user->save();
        session()->flash('success','profile Update successfully');
        return response()->json([
            'status'=>true,
            'message'=>'Profile update successfully'
        ]);
       }else{
        return response()->json([
            'status'=>false,
            'errors'=>$validator->errors()
        ]);
       }

    }
    public function showChangePassword(){
        return View('front.account.changepassword');
    }
    public function changePassword(Request $request){
        $validator =Validator::make($request->all(),[
            'old_password'=>'required',
            'new_password'=>'required|min:5',
            'confirm_password'=>'required|same:new_password'
        ]);
        if($validator->passes()){
            $user=User::select('id','password')->where('id',Auth::user()->id)->first();
           if(!Hash::check($request->old_password,$user->password)){
            session()->flash('error','Your old password is incorrect, please try again');
            return response()->json([
                'status'=>true
            ]);

           }
           User::where('id',$user->id)->update([
            'password'=>Hash::make($request->new_password)
           ]);
           session()->flash('success','Your password change successfully');
           return response()->json([
               'status'=>true
           ]);
        }else{
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ]);
        }

    }
    public function forgotpassword(){
        return View('front.account.forgotpassword');
    }
    public function processforgotPassword(Request $request){
        $validator = Validator::make($request->all(),[
            'email'=>'required|exists:users,email'
        ]);
        if($validator->fails()){
            return redirect()->route('front.forgotpassword')->withInput()->withErrors($validator);
        }
        $token =Str::random(70);
        \DB::table('password_reset_tokens')->where('email',$request->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email'=>$request->email,
            'token'=>$token,
            'created_at'=>now()
        ]);
        //send email
       $user= User::where('email',$request->email)->first();
       $formData=[
        'token'=>$token,
        'user'=>$user,
        'mailsubject'=>'You have requested to reset your password'
       ];
       Mail::to($request->email)->send(new ResetPasswordEmail($formData));
       return redirect()->route('front.forgotpassword')->with('success','Please check your email to reset your password');
    }
    public function resetPassword($token){
        $tokenExist=\DB::table('password_reset_tokens')->where('token',$token)->first();
        if($tokenExist==null){
            return redirect()->route('front.forgotpassword')->with('error','Invalid request');
        }
        return View('front.account.reset-forgotpassword',[
            'token'=>$token
        ]);

    }
    public function processresetPassword(Request $request){
        $token=$request->token;
        $tokenObj=\DB::table('password_reset_tokens')->where('token',$token)->first();
        if($tokenObj==null){
            return redirect()->route('front.forgotpassword')->with('error','Invalid request');
        }
        $user =User::where('email',$tokenObj->email)->first();
        $validator = Validator::make($request->all(),[
            'new_password'=>'required|min:5',
            'confirm_password'=>'required|same:new_password'
        ]);
        if($validator->fails()){
            return redirect()->route('front.resetPassword',$token)->withErrors($validator);
        }
        User::where('id',$user->id)->update([
            'password'=>Hash::make($request->new_password)
        ]);
        \DB::table('password_reset_tokens')->where('email',$user->email)->delete();
        return redirect()->route('account.login')->with('success','You have successfully changed your password');
    }
 
}
