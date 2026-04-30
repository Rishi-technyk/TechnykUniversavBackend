@extends('layouts.admin_web')
@section('content')
<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Add Multi Venue Block
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('venue.block') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('venue.block.multi.store') }}" method="Post">
            @csrf

            <div class="row">

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>From Date</label>
                        <input type="date" name="from_date" class="form-control" placeholder="Enter From Date" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>To Date</label>
                        <input type="date" name="to_date" class="form-control" placeholder="Enter To Date" required>

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