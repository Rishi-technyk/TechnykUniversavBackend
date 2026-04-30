@extends('layouts.admin_web')
@section('content')
<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Add Venue Block
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('venue.block') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('venue.block.store') }}" method="Post">
            @csrf

            <div class="row">

                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Venue Master</label>
                        <select class="form-control" name="venue_id" required>
                            <option value="">Select Venue Master</option>
                            @foreach($venue as $ven)
                            <option value="{{ $ven->id }}">{{ $ven->name }}</option>
                            @endforeach
                        </select>

                    </div>

                </div>

                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Session</label>
                        <select class="form-control" name="session_id" required>
                            <option value="">Select Session</option>
                            @foreach($session as $ses)
                            <option value="{{ $ses->id }}">{{ $ses->name }}</option>
                            @endforeach
                        </select>

                    </div>

                </div>

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

                <div class="col-lg-12 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Remark</label>
                        <textarea class="form-control" name="remark" placeholder="Enter Remark"></textarea>

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