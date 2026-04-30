@extends('layouts.admin_web')
@section('content')
<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
                Edit Category
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('category.master') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('category.master.update') }}" method="Post">
            @csrf
            <input type="hidden" name="id" value="{{ $data->Code }}">
            <div class="row">
            
                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ $data->Catg_Name }}" required>

                    </div>

                </div>

            </div>

            <div class="text-center mt-4">
                <button class="btn btn-sm btn-success" type="submit">Update</button>
            </div>

        </form>

    </div>

</div>
<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')

@endpush()
@endsection