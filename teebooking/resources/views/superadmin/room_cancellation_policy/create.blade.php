@extends('layouts.admin_web')
@section('content')

<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Add Room Cancellation Policy
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('room.cancellation.policy') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('room.cancellation.policy.store') }}" method="Post">
            @csrf

            <div class="row">
            
                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>From Days</label>
                        <input type="number" name="from_days" class="form-control" placeholder="Enter From Days" required>

                    </div>

                </div>

                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>To Days</label>
                        <input type="text" name="to_days" class="form-control" placeholder="Enter To Days" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Deduction (%)</label>
                        <input type="text" name="deduction" class="form-control" placeholder="Enter Deduction" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>GST (%)</label>
                        <input type="text" name="GST" class="form-control" placeholder="Enter GST in Percentage" required>

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