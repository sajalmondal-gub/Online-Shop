<?php

namespace App\Http\Controllers\admin;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\TempImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function create(){

        $data=[];
        $categories =Category::orderBy('name','ASC')->get();
        $brands=Brand::orderBy('name','ASC')->get();
        $data['categories']=$categories;
        $data['brands']=$brands;
        return View('admin.products.create',$data);
    }
    public function store(Request $request){
        dd($request->all());
               $rules =[
            'title'=>'required',
            'slug'=>'required|unique:products',
            'price'=>'required|numeric',
            'sku'=>'required|unique:products',
            'track_qty'=>'required|in:Yes,No',
            'category'=>'required|numeric',
            'is_featured'=>'required|in:Yes,No',

        ];
        if(!empty($request->track_qty) && $request->track_qty=='Yes'){
            $rules['qty']='required|numeric';

        }
       $validator= Validator::make($request->all(),$rules);
       if($validator->passes()){
        $product = new Product;
        $product->title =$request->title;
        $product->slug =$request->slug;
        $product->description =$request->description;
        $product->price =$request->price;
        $product->compare_price =$request->compare_price;
        $product->sku =$request->sku;
        $product->barcode =$request->barcode;
        $product->track_qty =$request->track_qty;
        $product->qty =$request->qty;
        $product->status =$request->status;
        $product->category_id =$request->category;
        $product->sub_category_id =$request->sub_category;
        $product->brand_id =$request->brand;
        $product->is_featured =$request->is_featured;

        $product->save();
      //image
      if (!empty($request->image_id)) {   
        $tempImage =TempImage::find($request->image_id);
        $extArray =explode('.',$tempImage->name);
        $ext =last($extArray);
        $newImageName =$product->id.'.'.$ext;
        $sPath =public_path().'/temp/'.$tempImage->name;
        $dPath =public_path().'/uploads/product/'.$newImageName;
        File::copy($sPath,$dPath);            
        $product->image=$newImageName;
        $product->save();
     }
     
        $request->session()->flash('success','Product added successfully');
        return response()->json([
            'status'=>true,
            'message'=>'Product added successfully'

        ]);
        

       }else{
        return response()->json([
            'status'=>false,
            'errors'=>$validator->errors()

        ]);
       }

    }
}
