@extends('front.layouts.app')
@section('content')
<section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="#">My Account</a></li>
                    <li class="breadcrumb-item">Settings</li>
                </ol>
            </div>
        </div>
    </section>

    <section class=" section-11 ">
        <div class="container  mt-5">
            <div class="row">
                <div class="col-md-12">
                    @include('front.layouts.message')
                </div>
                <div class="col-md-3">
                   @include('front.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="h5 mb-0 pt-2 pb-2">Personal Information</h2>
                        </div>
                        <form action=""  name="profileUpdate" id="profileUpdate">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="mb-3">               
                                    <label for="name">Name</label>
                                    <input value="{{ $user->name }}" type="text" name="name" id="name" placeholder="Enter Your Name" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-3">            
                                    <label for="email">Email</label>
                                    <input value="{{ $user->email }}" type="text" name="email" id="email" placeholder="Enter Your Email" class="form-control">
                                    <p></p>
                                </div>
                                <div class="mb-3">                                    
                                    <label for="phone">Phone</label>
                                    <input value="{{ $user->phone }}" type="text" name="phone" id="phone" placeholder="Enter Your Phone" class="form-control">
                                    <p></p>
                                </div>
                                <div class="d-flex">
                                    <button class="btn btn-dark">Update</button>
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
    </section>
@endsection
@section('customJs')
<script>
    $("#profileUpdate").submit(function(event){
        event.preventDefault();
        $.ajax({
            url:'{{ route("account.updateProfile") }}',
            type:'post',
            data:$(this).serializeArray(),
            DataType:'json',
            success:function(response){
                if(response.status==true){
                    

                     $("#profileUpdate #name").removeClass('is-invalid').find('p').html('').removeClass('invalid-feedback');
                     $("#profileUpdate #email").removeClass('is-invalid').find('p').html('').removeClass('invalid-feedback');
                     $("#profileUpdate  #phone").removeClass('is-invalid').find('p').html('').removeClass('invalid-feedback');
                     window.location.href='{{ route("account.profile") }}';

                } else{
                    var errors=response.errors;
                    if(errors.name){
                       $("#profileUpdate #name").addClass('is-invalid').siblings('p').html(errors.name).addClass('invalid-feedback');
                    }else{
                        $("#profileUpdate #name").removeClass('is-invalid').find('p').html('').removeClass('invalid-feedback');

                    }
                    if(errors.email){
                       $("#profileUpdate #email").addClass('is-invalid').siblings('p').html(errors.email).addClass('invalid-feedback');
                    }else{
                        $("#profileUpdate #email").removeClass('is-invalid').siblings('p').html('').removeClass('invalid-feedback');
                    }
                    if(errors.phone){
                       $("#profileUpdate #phone").addClass('is-invalid').siblings('p').html(errors.phone).addClass('invalid-feedback');
                    }else{
                        $("#profileUpdate #phone").removeClass('is-invalid').siblings('p').html('').removeClass('invalid-feedback');
                    }
                }

            }

        });

    });

    
</script>
@endsection