@extends('layouts.admin_web')
@section('content')

<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Add Venue Pax
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('venue.pax') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('venue.pax.store') }}" method="Post">
            @csrf

            <div class="row">
            
                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Venue Count</label>
                        <input type="number" name="venue_count" class="form-control" placeholder="Enter Venue Count" required>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="form-group">
                        
                        <label>Min Pax</label>
                        <input type="number" name="min_pax" class="form-control" placeholder="Enter Min Pax" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">

                    <div class="form-group">
                        
                        <label>Max Pax</label>
                        <input type="number" name="max_pax" class="form-control" placeholder="Enter Max Pax" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">

                    <div class="form-group">
                        
                        <label>Total Venue Rate</label>
                        <input type="number" name="total_venue_rate" class="form-control" placeholder="Enter Venue Rate">

                    </div>

                </div>

                <div class="col-lg-6 mt-4">

                    <div class="form-group">
                        
                        <label>Message</label>
                        <input type="text" name="message" class="form-control" placeholder="Enter Message" required>

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