@extends('layouts.admin_web')
@section('content')
<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Edit Venue Block
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('venue.block') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('venue.block.update') }}" method="Post">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="row">

                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Venue Master</label>
                        <select class="form-control" name="venue_id" required>
                            <option value="">Select Venue Master</option>
                            @foreach($venue as $ven)
                            <option value="{{ $ven->id }}" {{ $data->venue_id==$ven->id?'selected':'' }}>{{ $ven->name }}</option>
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
                            <option value="{{ $ses->id }}" {{ $data->session_id==$ses->id?'selected':'' }}>{{ $ses->name }}</option>
                            @endforeach
                        </select>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>From Date</label>
                        <input type="date" name="from_date" value="{{ $data->from_date }}" class="form-control" placeholder="Enter From Date" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>To Date</label>
                        <input type="date" name="to_date" value="{{ $data->to_date }}" class="form-control" placeholder="Enter To Date" required>

                    </div>

                </div>

                <div class="col-lg-12 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Remark</label>
                        <textarea class="form-control" name="remark" placeholder="Enter Remark">{{ $data->remark }}</textarea>

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