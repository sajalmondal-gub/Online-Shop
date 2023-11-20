<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class BrandController extends Controller
{
     public function index(Request $request){
        $brands = Brand::latest();
        if (!empty($request->get('keyword'))) {
            $brands = $brands->where('name','like','%'.$request->get('keyword').'%');
        }


       $brands =$brands->paginate(10);
       return View('admin.brand.list',compact('brands'));
     }
     public function create(){
        return View('admin.brand.create');
     }
     public function store(Request $request){
        $validatior =Validator::make($request->all(),[
            'name'=>'required',
            'slug'=>'required|unique:brands',
        ]);
        if($validatior->passes()){
            $brand=new Brand();
            $brand->name=$request->name;
            $brand->slug=$request->slug;
            $brand->status=$request->status;
            $brand->showhome=$request->showhome;
           $brand->save();
           $request->session()->flash('success', 'Brand added successfully');

           return response()->json([
            'status'=>true,
            'message'=>'Brand added successfully'
        ]);

        }else {
            return response()->json([
                'status'=>false,
                'errors'=>$validatior->errors()
            ]);
        }
     }
     public function edit($id,Request $request){
        $brand =Brand::find($id);
        if(empty($brand)){
            return redirect()->route('brands.index');
        }
        return View('admin.brand.edit',compact('brand'));
     }
     public function update($id,Request $request){
        $brand =Brand::find($id);
        if(empty($brand)){
            $request->session()->flash('error','Brand not found');
            return response()->json([
                'status'=>false,
                'notFound'=>true,
                'message'=>'Brand not found'
            ]);
        }
        $validatior =Validator::make($request->all(),[
            'name'=>'required',
            'slug'=>'required|unique:brands,slug,'.$brand->id.',id',
        ]);
        if($validatior->passes()){
            
            $brand->name=$request->name;
            $brand->slug=$request->slug;
            $brand->status=$request->status;
            $brand->showhome=$request->showhome;
            $brand->save();
           $request->session()->flash('success', 'Brand updated successfully');

           return response()->json([
            'status'=>true,
            'message'=>'Brand updated successfully'
        ]);

        }else {
            return response()->json([
                'status'=>false,
                'errors'=>$validatior->errors()
            ]);
        }
     }
     public function destroy($id,Request $request){
        $brand =Brand::find($id);
        if(empty($brand)){
            $request->session()->flash('error','Brands not found');
            return response()->json([
                'status'=>true,
                'message'=>'Brand not found'
            ]);
        }
      
        $brand->delete();
        $request->session()->flash('success','Category deleted successfully');

        return response()->json([
            'status'=>true,
            'message'=>'Brands Deleted Successfull'
        ]);
    }
     

}
