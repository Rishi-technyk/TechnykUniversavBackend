@extends('layouts.admin_web')
@section('content')
<style>
    .text-left {
        text-align: right;
        margin-right: 4%;
    }
</style>
<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Add Venue Charge
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('venue.charge') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('venue.charge.store') }}" method="Post">
            @csrf

            <div class="row">

                <div class="col-lg-4">
                    
                    <div class="form-group">
                        
                        <label>Occupant Master</label>
                        <select class="form-control" name="occupant_id" required>
                            <option value="">Select Occupant Master</option>
                            @foreach($occupant as $occ)
                            <option value="{{ $occ->id }}">{{ $occ->name }}</option>
                            @endforeach
                        </select>

                    </div>

                </div>

                <div class="col-lg-4">
                    
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

                <div class="col-lg-4">
                    
                    <div class="form-group">
                        
                        <label>Session</label>
                        <select class="form-control" name="session_id" required>
                            <option value="">Select Session</option>
                            @foreach($session as $sess)
                            <option value="{{ $sess->id }}">{{ $sess->name }}</option>
                            @endforeach
                        </select>

                    </div>

                </div>

                <div class="col-lg-4 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Min Pax</label>
                        <input type="number" name="min_pax[]" class="form-control" placeholder="Enter Min Pax" required>

                    </div>

                </div>

                <div class="col-lg-4 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Max Pax</label>
                        <input type="number" name="max_pax[]" class="form-control" placeholder="Enter Max Pax" required>

                    </div>

                </div>

                <div class="col-lg-3 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Rate</label>
                        <input type="number" name="rate[]" class="form-control" placeholder="Enter Rate" required>

                    </div>

                </div>

                <div class="col-lg-1 mt-4"></div>

            </div>

            <div id="newinput"></div>
            <div class="text-left mt-4">
                <button id="rowAdder" type="button" class="btn btn-sm btn-success mt-2">
                    <span class="bi bi-plus-square-dotted">
                    </span> Add Row
                </button>
            </div>

            <div class="text-center mt-4">
                <button class="btn btn-sm btn-success" type="submit">Submit</button>
            </div>

        </form>

    </div>

</div>

@push('js')
<script type="text/javascript">
    $("#rowAdder").click(function () {
        newRowAdd =
            '<div id="row" class="row"> <div class="col-lg-4 mt-4">'+
                    
                    '<div class="form-group">'+
                        
                        '<label>Min Pax</label>'+
                        '<input type="number" name="min_pax[]" class="form-control" placeholder="Enter Min Pax" required>'+

                    '</div>'+

                '</div>'+

                '<div class="col-lg-4 mt-4">'+
                    
                    '<div class="form-group">'+
                        
                        '<label>Max Pax</label>'+
                        '<input type="number" name="max_pax[]" class="form-control" placeholder="Enter Max Pax" required>'+

                    '</div>'+

                '</div>'+

                '<div class="col-lg-3 mt-4">'+
                    
                    '<div class="form-group">'+
                        
                        '<label>Rate</label>'+
                        '<input type="number" name="rate" class="form-control" placeholder="Enter Rate" required>'+

                    '</div>'+

                '</div>' +
                '<div class="col-lg-1 mt-4 form-group"><br>' +
            '<button class="btn btn-danger btn-sm mt-2" id="DeleteRow" type="button"><i class="bi bi-trash"></i> Remove</button>' +
            '</div>'+
            '</div></div>';

        $('#newinput').append(newRowAdd);
    });
    $("body").on("click", "#DeleteRow", function () {
        $(this).parents("#row").remove();
    })
</script>
@endpush()
@endsection