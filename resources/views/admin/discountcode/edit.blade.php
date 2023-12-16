@extends('admin.layouts.app')
@section('content')
<!-- Content Header (Page header) -->
    <section class="content-header">					
                        <div class="container-fluid my-2">
                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <h1>Edit Coupon Code</h1>
                                </div>
                                <div class="col-sm-6 text-right">
                                    <a href="{{ route('coupon.index') }}" class="btn btn-primary">Back</a>
                                </div>
                            </div>
                        </div>
                        <!-- /.container-fluid -->
    </section>
				<!-- Main content -->
				<section class="content">
					<!-- Default box -->
					<div class="container-fluid">
                        <form action="" method="post" id="categoryForm" name="categoryForm">
                            <div class="card">
                                <div class="card-body">								
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="code">Coupon Code</label>
                                                <input value="{{ $coupon->code }}" type="text" name="code" id="code" class="form-control" placeholder="coupon code">
                                                <p></p>	
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name">Name</label>
                                                <input value="{{ $coupon->name }}"  type="text"  name="name" id="name" class="form-control" placeholder="coupon code name">
                                                <p></p>	
                                            </div>
                                        </div>	

                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_uses">Max  Uses</label>
                                                <input value="{{ $coupon->max_uses }}"  type="number"  name="max_uses" id="max_uses" class="form-control" placeholder="Max uses">
                                                <p></p>	
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_uses_user">Max Uses User </label>
                                                <input value="{{ $coupon->max_uses_user }}"  type="number"  name="max_uses_user" id="max_uses_user" class="form-control" placeholder="max uses user">
                                                <p></p>	
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="type">Type</label>
                                                    <select name="type" id="type" class="form-control">
                                                        <option {{ ($coupon->type =='percent') ?'selected':'' }} value="percent">Percent(%)</option>
                                                        <option  {{ ($coupon->type =='fixed') ?'selected':'' }} value="fixed">Fixed</option>
                                                    </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_uses_user">Discount Amount</label>
                                                <input value="{{ $coupon->discount_amount }}" type="number"  name="discount_amount" id="discount_amount" class="form-control" placeholder=" Discount amount">
                                                <p></p>	
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_uses_user">Min Amount</label>
                                                <input value="{{ $coupon->min_amount }}" type="number"  name="min_amount" id="min_amount" class="form-control" placeholder="Min Amount">
                                                <p></p>	
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="type">Status</label>
                                                    <select name="status" id="status" class="form-control">
                                                        <option {{ ($coupon->status == 1)?'selected':'' }} value="1">Active</option>
                                                        <option {{ ($coupon->status == 0) ?'selected':'' }} value="0">Block</option>
                                                    </select>
                                            </div>
                                        </div>  
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_uses_user">Starts At</label>
                                                <input value="{{ $coupon->start_at }}" autocomplete="off" type="text"  name="start_at" id="start_at" class="form-control" placeholder="start at">
                                                <p></p>	
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_uses_user">Expires AT</label>
                                                <input value="{{ $coupon->expires_at }}" autocomplete="off" type="text"  name="expires_at" id="expires_at" class="form-control" placeholder="expires_at">
                                                <p></p>	
                                            </div>
                                        </div> 
                                        <div class="col-md-6">
                                            <div class="col-mb-3">
                                            <label for="name">Description</label>
                                                <textarea class="form-control" name="description" id="description" cols="20" rows="5">{{ $coupon->description }}</textarea>
                                                <p></p>	
                                            </div>
                                        </div>                                                          
                                    </div>
                                </div>							
                            </div>
                            <div class="pb-5 pt-3">
                                <button type="submit" class="btn btn-primary">Add</button>
                                <a href="{{ route('coupon.create') }}" class="btn btn-outline-dark ml-3">Cancel</a>
                            </div>
                        </form>
					</div>
					<!-- /.card -->
				</section>
				<!-- /.content -->
			
			
@endsection
@section('customJs')
<script>
     $(document).ready(function(){
            $('#start_at').datetimepicker({
                // options here
                format:'Y-m-d H:i:s',
            });
            $('#expires_at').datetimepicker({
                // options here
                format:'Y-m-d H:i:s',
            });
        });
    $("#categoryForm").submit(function (event){
        event.preventDefault();
        var element =$(this);
        $("button[type=submit]").prop('disabled',true);
        $.ajax({
            url: '{{ route("coupon.update",$coupon->id) }}',
            type: 'put',
            data: element.serializeArray(),
            dataType: 'json',
            success: function(response){
                $("button[type=submit]").prop('disabled',false);

                if (response["status"]==true) {
                    window.location.href="{{ route('coupon.index') }}";
                    $('#code').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    $('#discount_amount').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    $('#start_at').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    $('#expires_at').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    
                }else{
                    var errors=response['errors'];
                if (errors['code']) {
                    $('#code').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['code']);
                }else{
                    $('#code').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                }

                if (errors['discount_amount']) {
                    $('#discount_amount').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['discount_amount']);
                }else{
                       $('#discount_amount').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                }
                if (errors['start_at']) {
                    $('#start_at').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['start_at']);
                }else{
                       $('#start_at').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                }
                if (errors['expires_at']) {
                    $('#expires_at').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['expires_at']);
                }else{
                       $('#expires_at').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                }




                }
                
            }, error: function(jqXHR, exception){
                console.log("something went wrong");
            }

        })
    });

    $("#name").change(function(){
        element =$(this);
        $("button[type=submit]").prop('disabled',true);
        $.ajax({
            url: '{{ route("getSlug") }}',
            type: 'get',
            data: {title: element.val()},
            dataType: 'json',
            success: function(response){
                $("button[type=submit]").prop('disabled',false);
               if (response["status"] == true) {
                $("#slug").val(response["slug"]);
               }
            }
    });

    });
</script>
@endsection 