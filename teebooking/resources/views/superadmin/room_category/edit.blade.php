@extends('layouts.admin_web')
@section('content')

<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Edit Room Category
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('room.category') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('room.category.update') }}" method="Post" enctype="multipart/form-data">
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
                        
                        <label>Number of Room</label>
                        <input type="number" name="no_of_room" class="form-control" value="{{ $data->no_of_rooms }}" placeholder="Enter Number of Room" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-2">
                    
                    <div class="form-group">
                        
                        <label>GST (%)</label>
                        <input type="number" name="gst" class="form-control" value="{{ $data->GST }}" placeholder="Enter GST (%)" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-2">
                    
                    <div class="form-group">
                        
                        <label>Room Image</label>
                        <input type="file" name="room_image" class="form-control mb-2" style="padding: 4px 10px !important;">
                        @if($data->room_image)
                        <a href="{{ asset($data->room_image) }}" class="mt-2" target="_blank"><img src="{{ asset($data->room_image) }}" height="100" width="100"></a>
                        @endif
                    </div>

                </div>

                <div class="col-lg-12 mt-2">

                    <div class="form-group">
                        
                        <label>Description</label>
                        <textarea class="form-control" name="description" placeholder="Enter Description">{{ $data->description }}</textarea>

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