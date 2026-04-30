@extends('layouts.admin_web')
@section('content')
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 28px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 23px;
  width: 26px;
  left: 4px;
  bottom: 3px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Setting
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('admin.setting.store') }}" method="Post">
            @csrf

            <div class="row">

                <div class="col-lg-2">
                    
                    <div class="form-group">
                        
                        <label>Heading</label>

                    </div>

                </div>
            
                <div class="col-lg-10">
                    
                    <div class="form-group">
                        
                        <input type="text" name="heading" class="form-control" value="{{ $setting?$setting->heading:'' }}" placeholder="Enter Heading" required>

                    </div>

                </div>

            </div>

            <div class="row mt-4">

                <div class="col-lg-2">
                    
                    <div class="form-group">
                        
                        <label>Sub Heading</label>

                    </div>

                </div>
            
                <div class="col-lg-10">
                    
                    <div class="form-group">
                        
                        <input type="text" name="sub_heading" value="{{ $setting?$setting->sub_heading:'' }}" class="form-control" placeholder="Enter Sub Heading" required>

                    </div>

                </div>

            </div>

            <div class="row mt-4">

                <div class="col-lg-2">
                    
                    <div class="form-group">
                        
                        <label>Phone No.</label>

                    </div>

                </div>
            
                <div class="col-lg-10">
                    
                    <div class="form-group">
                        
                        <input type="text" name="phone" value="{{ $setting?$setting->phone:'' }}" class="form-control" placeholder="Enter Phone No." required>

                    </div>

                </div>

            </div>

            <div class="row mt-4">

                <div class="col-lg-2">
                    
                    <div class="form-group">
                        
                        <label>Email</label>

                    </div>

                </div>
            
                <div class="col-lg-10">
                    
                    <div class="form-group">
                        
                        <input type="email" name="email" value="{{ $setting?$setting->email:'' }}" class="form-control" placeholder="Enter Email" required>

                    </div>

                </div>

            </div>

            <div class="row mt-4">

                <div class="col-lg-2">
                    
                    <div class="form-group">
                        
                        <label>Minimum Days</label>

                    </div>

                </div>
            
                <div class="col-lg-10">
                    
                    <div class="form-group">
                        
                        <input type="number" name="min_days" value="{{ $setting?$setting->min_days:'' }}" class="form-control" placeholder="Enter Min. days ahead of today's date" required>

                    </div>

                </div>

            </div>

            <div class="row mt-4">

                <div class="col-lg-2">
                    
                    <div class="form-group">
                        
                        <label>Maximum Days</label>

                    </div>

                </div>
            
                <div class="col-lg-10">
                    
                    <div class="form-group">
                        
                        <input type="number" name="max_days" value="{{ $setting?$setting->max_days:'' }}" class="form-control" placeholder="Enter Max. days ahead of today's date" required>

                    </div>

                </div>

            </div>

            @if(Auth::check() && Auth::user()->role=='Banquet Manager')
            <div class="row mt-4">

                <div class="col-lg-2">
                    
                    <div class="form-group">
                        
                        <label>Banquet Booking Module</label>

                    </div>

                </div>
            
                <div class="col-lg-10">
                    @if($setting && $setting->banquest_booking_module=='Active')
                    <label class="switch">
                      <input type="checkbox" name="banquest_booking_module" value="1" checked>
                      <span class="slider round"></span>
                    </label>
                    @else
                    <label class="switch">
                      <input type="checkbox" name="banquest_booking_module" value="1">
                      <span class="slider round"></span>
                    </label>
                    @endif

                </div>

            </div>
            @endif

            @if(Auth::check() && Auth::user()->role=='Room Manager')
            <div class="row mt-4">

                <div class="col-lg-2">
                    
                    <div class="form-group">
                        
                        <label>Room Booking Module</label>

                    </div>

                </div>
            
                <div class="col-lg-10">
                    @if($setting && $setting->room_booking_module=='Active')
                    <label class="switch">
                      <input type="checkbox" name="room_booking_module" value="1" checked>
                      <span class="slider round"></span>
                    </label>
                    @else
                    <label class="switch">
                      <input type="checkbox" name="room_booking_module" value="1">
                      <span class="slider round"></span>
                    </label>
                    @endif

                </div>

            </div>
            @endif

            <div class="text-center mt-4">
                <button class="btn btn-sm btn-success" type="submit">Update</button>
            </div>

        </form>

    </div>

</div>
@push('js')

@endpush()
@endsection