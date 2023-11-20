<?php

namespace App\Http\Controllers\admin;

use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Image;



class CategoryController extends Controller
{
    public function index(Request $request){
        $categories = Category::latest();
        if (!empty($request->get('keyword'))) {
            $categories = $categories->where('name','like','%'.$request->get('keyword').'%');
        }


       $categories =$categories->paginate(10);
       return View('admin.category.list',compact('categories'));

    }
    public function create(){
        return View('admin.category.create');
    }
    public function store(Request $request){
        $validatior =Validator::make($request->all(),[
            'name'=>'required',
            'slug'=>'required|unique:categories',
        ]);
        if($validatior->passes()){
            $category=new Category();
            $category->name=$request->name;
            $category->slug=$request->slug;
            $category->status=$request->status;
            $category->showhome=$request->showhome;
           $category->save();

           //save image
           if (!empty($request->image_id)) {
              $tempImage =TempImage::find($request->image_id);
              $extArray =explode('.',$tempImage->name);
              $ext =last($extArray);
              $newImageName =$category->id.'.'.$ext;
              $sPath =public_path().'/temp/'.$tempImage->name;
              $dPath =public_path().'/uploads/category/'.$newImageName;
              File::copy($sPath,$dPath);            
              $category->image=$newImageName;
              $category->save();
           }


           $request->session()->flash('success', 'Category added successfully');

           return response()->json([
            'status'=>true,
            'message'=>'Category added successfully'
        ]);

        }else {
            return response()->json([
                'status'=>false,
                'errors'=>$validatior->errors()
            ]);
        }
    }
    public function edit($categoryId,Request $request){
        $category =Category::find($categoryId);
        if(empty($category)){
            return redirect()->route('categories.index');
        }
        return View('admin.category.edit',compact('category'));
    }
    public function update($categoryId,Request $request){
        $category =Category::find($categoryId);
        if(empty($category)){
            $request->session()->flash('error','Category not found');
            return response()->json([
                'status'=>false,
                'notFound'=>true,
                'message'=>'Category not found'
            ]);
        }
        $validatior =Validator::make($request->all(),[
            'name'=>'required',
            'slug'=>'required|unique:categories,slug,'.$category->id.',id',
        ]);
        if($validatior->passes()){
            
            $category->name=$request->name;
            $category->slug=$request->slug;
            $category->status=$request->status;
            $category->showhome=$request->showhome;
            $category->save();
            $oldImage =$category->image;

           //save image
           if (!empty($request->image_id)) {
              $tempImage =TempImage::find($request->image_id);
              $extArray =explode('.',$tempImage->name);
              $ext =last($extArray);
              $newImageName =$category->id.'-'.time().'.'.$ext;
              $sPath =public_path().'/temp/'.$tempImage->name;
              $dPath =public_path().'/uploads/category/'.$newImageName;
              File::copy($sPath,$dPath);
             


              $category->image=$newImageName;
              $category->save();
              File::delete(public_path().'/uploads/category/'.$oldImage);
   



           }


           $request->session()->flash('success', 'Category updated successfully');

           return response()->json([
            'status'=>true,
            'message'=>'Category updated successfully'
        ]);

        }else {
            return response()->json([
                'status'=>false,
                'errors'=>$validatior->errors()
            ]);
        }
    }
    public function destroy($categoryId,Request $request){
        $category =Category::find($categoryId);
        if(empty($category)){
            $request->session()->flash('error','Category not found');
            return response()->json([
                'status'=>true,
                'message'=>'Category not found'
            ]);
        }
        File::delete(public_path().'/uploads/category/'.$category->image);
        $category->delete();
        $request->session()->flash('success','Category deleted successfully');

        return response()->json([
            'status'=>true,
            'message'=>'Category Deleted Successfull'
        ]);
    }
}
