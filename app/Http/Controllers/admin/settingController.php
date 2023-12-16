<?php

namespace App\Http\Controllers\admin;

use id;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;

class settingController extends Controller
{
    public function showpasswordform(){
        return View('admin.changepassword');
    }
    public function changepassword(Request $request){
        $validator=Validator::make($request->all(),[
            'old_password'=>'required',
            'new_password'=>'required|min:5',
            'confirm_password'=>'required|same:new_password'
        ]);
        $admin =User::where('id',Auth::guard('admin')->user()->id)->first();
        if($validator->passes()){

            if(!Hash::check($request->old_password,$admin->password)){
             session()->flash('error','Your old password is incorrect, please try again');
             return response()->json([
                 'status'=>true
             ]);
            }
            User::where('id',Auth::guard('admin')->user()->id)->update([
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
}
