<?php

namespace App\Http\Controllers\admin;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class ProductImageController extends Controller
{
    public function update(Request $request){
        $image =$request->image;
        $ext =$image->getClientOriginalExtension();
        $sPath =$image->getPathName();
        $productImage =new ProductImage();
        $productImage->product_id=$request->product_id;
        $productImage->image='NULL';
        $productImage->save();
        $imageName=$request->product_id.'-'.$productImage->id.'-'.time().'.'.$ext;
        $productImage->image=$imageName;

        $dPath =public_path().'/uploads/product/'.$imageName;
        File::copy($sPath,$dPath); 
        $productImage->image=$imageName;
        $productImage->save();
        return response()->json([
            'status'=>true,
            'image_id'=>$productImage->id,
            'ImagePath'=> asset('uploads/product/'.$productImage->image),
            'message'=>'Image saved successfully'

        ]);
    }
    public function destroy(Request $request){
        $productImage = ProductImage::find($request->id);
        if (empty($productImage)) {
            return response()->json([
                'status'=>false,
                'message'=>'Image not found'
    
            ]);
        }
        File::delete(public_path('uploads/product/'.$productImage->image));
        $productImage->delete();
        return response()->json([
            'status'=>true,
            'message'=>'Image deleted successfully'

        ]);

    }
}
