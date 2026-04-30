@extends('layouts.admin_web')
@section('content')
<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Add Occupant Master
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('occupant.master') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('occupant.master.store') }}" method="Post">
            @csrf

            <div class="row">
            
                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter Name" required>

                    </div>

                </div>

                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Additional Info</label>
                        <select class="form-control" name="additional_info" required>
                            <option value="">Select Additional Info</option>
                            <option>Yes</option>
                            <option>No</option>
                        </select>

                    </div>

                </div>

            </div>

            <div class="text-center mt-4">
                <button class="btn btn-sm btn-success" type="submit">Submit</button>
            </div>

        </form>

    </div>

</div>
@push('js')

@endpush()
@endsection