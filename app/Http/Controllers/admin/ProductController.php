<?php

namespace App\Http\Controllers\admin;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductRating;
use App\Models\SubCategory;
use App\Models\TempImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request){
        $products=Product::latest('id')->with('product_images');
        if ($request->get('keyword') !="") {
           $products=$products->where('title','like','%'.$request->keyword.'%');
        }
        $products=$products->paginate();
        
        $data['products']=$products;
        return View('admin.products.list',$data);
        
    }
    public function create(){

        $data=[];
        $categories =Category::orderBy('name','ASC')->get();
        $brands=Brand::orderBy('name','ASC')->get();
        $data['categories']=$categories;
        $data['brands']=$brands;
        return View('admin.products.create',$data);
    }
    public function store(Request $request){
        
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
        $product->shipping_returns =$request->shipping_returns;
        $product->short_description =$request->short_description;

        $product->save();
      //image
      if (!empty($request->image_array)) {
        foreach ($request->image_array as $temp_image_id) {
            $tempImageinfo = TempImage::find($temp_image_id);
            $extArray=explode('.',$tempImageinfo->name);
            $ext=last($extArray);
            $productImage =new ProductImage();
            $productImage->product_id=$product->id;
            $productImage->image='NULL';
            $productImage->save();
            $imageName=$product->id.'-'.$productImage->id.'-'.time().'.'.$ext;
            $productImage->image=$imageName;
            $sPath =public_path().'/temp/'.$tempImageinfo->name;
             $dPath =public_path().'/uploads/product/'.$imageName;
             File::copy($sPath,$dPath); 
             $productImage->image=$imageName;
             $productImage->save();
        }
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
    
    public function edit(Request $request,$id){
       $product= Product::find($id);

       if (empty($product)) {
        //$request->session()->flash('error','Product not found');
        return redirect()->route('products.index')->with('error','Product not found');
       }

      $productImages = ProductImage::where('product_id',$product->id)->get();


       $subCategories =SubCategory::where('category_id',$product->category_id)->get();
       
        $data=[];
        
        $categories =Category::orderBy('name','ASC')->get();
        $brands=Brand::orderBy('name','ASC')->get();
        $data['categories']=$categories;
        $data['brands']=$brands;
        $data['product']=$product;
        $data['subCategories']=$subCategories;
        $data['productImages']=$productImages;
        return View('admin.products.edit',$data);


    }
    public function update(Request $request,$id){

        $product= Product::find($id);
         
        $rules =[
            'title'=>'required',
            'slug'=>'required|unique:products,slug,'.$product->id.',id',
            'price'=>'required|numeric',
            'sku'=>'required|unique:products,sku,'.$product->id.',id',
            'track_qty'=>'required|in:Yes,No',
            'category'=>'required|numeric',
            'is_featured'=>'required|in:Yes,No',

        ];
        if(!empty($request->track_qty) && $request->track_qty=='Yes'){
            $rules['qty']='required|numeric';

        }
       $validator= Validator::make($request->all(),$rules);
       if($validator->passes()){
        
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
        $product->shipping_returns =$request->shipping_returns;
        $product->short_description =$request->short_description;
        //$product->is_featured =$request->is_featured;

        $product->save();
      //image
 
     
        $request->session()->flash('success','Product updated successfully');
        return response()->json([
            'status'=>true,
            'message'=>'Product updated successfully'

        ]);
        

       }else{
        return response()->json([
            'status'=>false,
            'errors'=>$validator->errors()

        ]);
       }

    }
    public function destroy($id,Request $request){
        
        $product =Product::find($id);
        if(empty($product)){
            $request->session()->flash('error','Category not found');
            return response()->json([
                'status'=>true,
                'message'=>'Records not found'
            ]);
        }
        $productImages = ProductImage::where('product_id',$id)->get();
        if(!empty($productImages)){
            foreach ($productImages as $productImage) {
                File::delete(public_path('uploads/product/'.$productImage->image));
            }
          ProductImage::where('product_id',$id)->delete();

        }

        $product->delete();
        $request->session()->flash('success','product deleted successfully');
        return response()->json([
            'status'=>true,
            'message'=>'product Deleted Successfull'
        ]);

    }
    public function product_ratings(Request $request){
        $ratings =ProductRating::select('product_ratings.*','products.title as productTitle')->orderBy('product_ratings.created_at','DESC');
        $ratings =$ratings->leftJoin('products','products.id','product_ratings.product_id');
        if ($request->get('keyword') !="") {
            $ratings=$ratings->orWhere('products.title','like','%'.$request->keyword.'%');
            $ratings=$ratings->orWhere('product_ratings.username','like','%'.$request->keyword.'%');
         }
        $ratings=$ratings->paginate(10);
        return View('admin.products.ratings',[
            'ratings'=>$ratings
        ]);
    }

    public function changeRatingStatus(Request $request){
        $productRating =ProductRating::find($request->id);
        $productRating->status=$request->status;
        $productRating->save();
        session()->flash('success','Status changed successfully');
        return response()->json([
            'status'=>true,
        ]);

    }
}
