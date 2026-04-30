@extends('layouts.admin_web')
@section('content')

<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Edit Venue Pax
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('venue.pax') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('venue.pax.update') }}" method="Post">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="row">
            
                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Venue Count</label>
                        <input type="text" name="venue_count" class="form-control" placeholder="Enter Venue Count" value="{{ $data->venue_count }}" required>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="form-group">
                        
                        <label>Min Pax</label>
                        <input type="number" name="min_pax" class="form-control" placeholder="Enter Min Pax" value="{{ $data->min_pax }}" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">

                    <div class="form-group">
                        
                        <label>Max Pax</label>
                        <input type="number" name="max_pax" class="form-control" placeholder="Enter Max Pax" value="{{ $data->max_pax }}" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">

                    <div class="form-group">
                        
                        <label>Total Venue Rate</label>
                        <input type="number" name="total_venue_rate" class="form-control" value="{{ $data->total_venue_rate }}" placeholder="Enter Venue Rate">

                    </div>

                </div>

                <div class="col-lg-6 mt-4">

                    <div class="form-group">
                        
                        <label>Message</label>
                        <input type="text" name="message" class="form-control" placeholder="Enter Message" value="{{ $data->message }}" required>

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