@extends('admin.layouts.app')
@section('content')
<!-- Content Header (Page header) -->
    <section class="content-header">					
                        <div class="container-fluid my-2">
                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <h1>Add Shipping Edit </h1>
                                </div>
                                <div class="col-sm-6 text-right">
                                    <a href="{{ route('shipping.create') }}" class="btn btn-primary">Back</a>
                                </div>
                            </div>
                        </div>
                        <!-- /.container-fluid -->
    </section>
				<section class="content">					
					<div class="container-fluid">
                        @include('admin.message')
                        <form action="" method="post" id="shippingForm" name="shippingForm">
                            <div class="card">
                                <div class="card-body">								
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="name">Name</label>
                                               <select name="country" id="country" class="form-control">                                               
                                               <option value="">Select a Country Name</option>
                                               @if($countries->isNotEmpty())
                                               @foreach($countries as $country)
                                               <option  value="{{ $country->name }}">{{ $country->name }}</option>
                                               @endforeach
                                               @endif
                                               </select>
                                                <p></p>	
                                            </div>
                                            <div class="mb-3">
                                            <label for="city">city</label>
                                                    <select name="city" id="city" class="form-control">
                                                        <option value=" "> Select a City</option>
                                                        <option  value="Dhaka">Dhaka</option>
                                                        <option value="Out Side Dhaka">Out Side Dhaka</option>
                                                       
                                                    </select>
                                                <p></p>	
                                            </div>
                                        </div>    
                                        <div class="col-md-4">
                                            <label for="name">Name</label>
                                            <input value="{{ $shippingCharge->amount }}" type="text" name="amount" id="amount"class="form-control" placeholder="amount">
                                            <p></p>	
                                        </div>  
                                                                       	                                                    
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
                                </div>							
                            </div>                          
                        </form>
                        
					</div>
					<!-- /.card -->
				</section>
				<!-- /.content -->
			
			
@endsection
@section('customJs')
<script>
    $("#shippingForm").submit(function (event){
        event.preventDefault();
        var element =$(this);
        $("button[type=submit]").prop('disabled',true);
        $.ajax({
            url: '{{ route("shipping.update",$shippingCharge->id) }}',
            type: 'put',
            data: element.serializeArray(),
            dataType: 'json',
            success: function(response){
                $("button[type=submit]").prop('disabled',false);

                if (response["status"]==true) {
                    window.location.href="{{ route('shipping.create') }}";
                }else{
                    var errors=response['errors'];
                if (errors['country']) {
                    $('#country').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['country']);
                }else{
                    $('#country').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                }
                if (errors['amount']) {
                    $('#amount').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['amount']);
                }else{
                       $('#amount').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                }



                }
                
            }, error: function(jqXHR, exception){
                console.log("something went wrong");
            }

        })
    });
</script>
@endsection 