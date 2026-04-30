@extends('layouts.admin_web')
@section('content')
<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
               Add Category Type
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('category.type') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('category.type.store') }}" method="Post">
            @csrf

            <div class="row">
            
                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Category Type</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter Category Type" required>

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