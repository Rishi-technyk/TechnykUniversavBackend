@extends('layouts.admin_web')
@section('content')

<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Add Block Room
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('room.block') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('room.block.store') }}" method="Post">
            @csrf

            <div class="row">

                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Room Category</label>
                        <select class="form-control" name="room_category_id" required>
                            <option value="">Select Room Category</option>
                            @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </select>

                    </div>

                </div>

                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>No. of Blocked Romm</label>
                        <input type="number" name="blocked_room" class="form-control" placeholder="Enter No of Blocked Romm" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>From Date</label>
                        <input type="date" name="from_date" class="form-control" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>To Date</label>
                        <input type="date" name="to_date" class="form-control" required>

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