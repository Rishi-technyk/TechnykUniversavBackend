@extends('layouts.admin_web')
@section('content')

<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Edit Venue Master
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('venue.master') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('venue.master.update') }}" method="Post">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="row">
            
                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ $data->name }}" required>

                    </div>

                </div>

                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Capacity</label>
                        <input type="text" name="capacity" class="form-control" placeholder="Enter Capacity" value="{{ $data->capacity }}" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>GSTper</label>
                        <input type="text" name="GSTper" class="form-control" placeholder="Enter GSTper" value="{{ $data->GSTper }}" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Security Deposit</label>
                        <input type="number" name="security_deposit" class="form-control" placeholder="Enter Security Deposit" value="{{ $data->security_deposit }}" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Venue Group</label>
                        <select class="form-control" name="grouping" required>
                            <option value="">Select Venue Grouping</option>
                            @foreach($grouping as $group)
                            <option value="{{ $group->id }}" {{ $group->id==$data->group_id?'selected':'' }}>{{ $group->name }}</option>
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