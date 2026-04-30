@extends('layouts.admin_web')
@section('content')
<section style="background-color: #eee;">
    <div class="py-5">

        <div class="row">
            
            @include('partials.website.admin.side_menu')            

            <div class="col-lg-9">
                <div class="card mb-1 h-100">
                	<div class="card-header">                       
                        
                        <div class="row">
                            <div class="col-lg-6">
                               Add User
                            </div>
                            <div class="col-lg-6">
                                <div class="text-end">
                                    <a href="{{ route('main.superadmin.dashboard') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <form action="{{ route('staff.store') }}" method="Post">
                            @csrf
                            
                            <div class="row">
                            
                                <div class="col-lg-6">
                                    
                                    <div class="form-group">
                                        
                                        <label>Staff Login ID</label>
                                        <input type="text" name="MemberID" class="form-control" placeholder="Enter Staff Login ID" required>

                                    </div>

                                </div>

                                <div class="col-lg-6">
                                    
                                    <div class="form-group">
                                        
                                        <label>Name</label>
                                        <input type="text" name="DisplayName" class="form-control" placeholder="Enter Name" required>

                                    </div>

                                </div>

                                <div class="col-lg-6 mt-4">
                                    
                                    <div class="form-group">
                                        
                                        <label>Email</label>
                                        <input type="email" name="Email" class="form-control" placeholder="Enter Email" required>

                                    </div>

                                </div>

                                <div class="col-lg-6 mt-4">
                                    
                                    <div class="form-group">
                                        
                                        <label>Role</label>
                                        <select class="form-control" name="role" required>
                                            <option value="">Select Role</option>
                                            <option value="Room Manager">Room Manager</option>
                                            <option value="Banquet Manager">Banquet Manager</option>
                                        </select>

                                    </div>

                                </div>

                                <div class="col-lg-6 mt-4">
                                    
                                    <div class="form-group">
                                        
                                        <label>Password</label>
                                        <input type="password" name="Password" class="form-control" placeholder="Enter Password" required>

                                    </div>

                                </div>

                            </div>

                            <div class="text-center mt-4">
                                <button class="btn btn-sm btn-success" type="submit">Submit</button>
                            </div>

                        </form>

                    </div>

                </div>
                
            </div>
        </div>
    </div>
</section>
<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')

@endpush()
@endsection