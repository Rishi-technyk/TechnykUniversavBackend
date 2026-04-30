@extends('layouts.admin_web')
@section('content')

<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Add Room Charges
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('room.charges.master') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('room.charges.master.store') }}" method="Post">
            @csrf

            <div class="row">

                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Category</label>
                        <select class="form-control" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($catgeory as $cate)
                            <option value="{{ $cate->Code }}">{{ $cate->Catg_Name }}</option>
                            @endforeach
                        </select>

                    </div>

                </div>

                <div class="col-lg-6">
                    
                    <div class="form-group">
                        
                        <label>Category Type</label>
                        <select class="form-control" name="category_type_id" required>
                            <option value="">Select Category Type</option>
                            @foreach($catgeory_type as $cate_type)
                            <option value="{{ $cate_type->Code }}">{{ $cate_type->CategoryType }}</option>
                            @endforeach
                        </select>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Occupant Type</label>
                        <select class="form-control" name="occupant_type_id" required>
                            <option value="">Select Occupant Type</option>
                            @foreach($occupants as $occu)
                            <option value="{{ $occu->id }}">{{ $occu->name }}</option>
                            @endforeach
                        </select>

                    </div>

                </div>
            
                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Room Category</label>
                        <select class="form-control" name="room_category_id" required>
                            <option value="">Select Room Category</option>
                            @foreach($room_cates as $room_c)
                            <option value="{{ $room_c->id }}">{{ $room_c->name }}</option>
                            @endforeach
                        </select>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Charges / Nite</label>
                        <input type="number" name="charges" class="form-control" min="1" placeholder="Enter Charges / Nite" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>No. Of Rooms</label>
                        <input type="number" name="no_of_booked_room" class="form-control" placeholder="Enter No. Of Rooms" required>

                    </div>

                </div>

                <div class="col-lg-6 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Max No. Of Nites</label>
                        <input type="number" name="max_no_of_nites" class="form-control" placeholder="Enter Max No. Of Nites" required>

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
<script>
    jQuery('.noofPerson').keyup(function () {     
  this.value = this.value.replace(/[^0-9\.]/g,'');
});
</script>
@endpush()
@endsection