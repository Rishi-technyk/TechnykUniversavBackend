@extends('layouts.admin_web')
@section('content')
<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Edit Cancellation Policy
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('cancellation.policy') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('cancellation.policy.update') }}" method="Post">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="row">
            
                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>From Days</label>
                        <input type="number" name="from_days" class="form-control" placeholder="Enter From Days" value="{{ $data->from_days }}" required>

                    </div>

                </div>

                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>To Days</label>
                        <input type="text" name="to_days" class="form-control" placeholder="Enter To Days" value="{{ $data->to_days }}" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Deduction</label>
                        <input type="text" name="deduction" class="form-control" placeholder="Enter Deduction" value="{{ $data->deduction }}" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>GST (%)</label>
                        <input type="text" name="GST" class="form-control" value="{{ $data->GST }}" placeholder="Enter GST in Percentage" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Venue</label>
                        <select class="form-control" name="venue_id" required>
                            <option value="">Select Venue</option>
                            @foreach($venue as $ven)
                            <option value="{{ $ven->id }}" {{ $data->venue_id==$ven->id?'selected':'' }}>{{ $ven->name }}</option>
                            @endforeach
                        </select>

                    </div>

                </div>

            </div>

            <div class="text-center mt-4">
                <button class="btn btn-sm btn-success" type="submit">Update</button>
            </div>

        </form>

    </div>

</div>
@push('js')

@endpush()
@endsection