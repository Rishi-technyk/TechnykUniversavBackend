@extends('layouts.web')
@section('content')
<style>
    .table {
        width: 100% !important;
    }
    .bg-grey {
        background-color: #e6e5e5;
    } 

    input[type=text], input[type=number], input[type=date], textarea {
        width: 100% !important;
        padding: 18px 10px !important;
        box-sizing: border-box !important;
        border: solid 1px !important;
        border-radius: .25rem !important;
    }

    select { 
        border: solid 1px !important;
    }

    .form-group+.form-group {
        margin-top: 0px !important;
    }

    .f-11 {
        font-size: 15px;
    }
</style>
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                   <h5>Banquet Availability</h5>
                </nav>
            </div>
        </div>

        <div class="row">

            <div class="col-lg-12">
                <div class="bg-grey row card d-lg-flex card mb-1 h-100">
                    <div class="card-body">
                        <form action="" method="">
                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label>Function Date</label>
                                    <input type="date" id="date" name="date" value="{{ $today_date }}" class="form-control" required>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label>Session</label>
                                    <select class="form-control" name="session">
                                        <option value="">Select Session</option>
                                        @foreach($session as $key => $ses)
                                        <option value="{{ $ses->id }}" {{ $ses->id==$request->session?'selected':'' }}>{{ $ses->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 mt-4">
                                    <button class="btn btn-success mt-2" type="submit">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
             
            <div class="col-lg-12 mt-4">
                <div class="row d-lg-flex card mb-1 h-100">
                    
                    <div class="card-body">
                        <div>
                            <small><b class="f-11"> <span class="text-success">V = Vacant</span> / <span class="text-danger">B = Booked</span> </b></small>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">Venue</th>                                    
                                    <th scope="col">Session</th>
                                    @foreach($first_date as $dt)
                                    <th scope="col">{{ strtok(date("d-m-Y", strtotime($dt)), '-') }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <?php $session_check = []; $venue_check = []; ?>
                                @foreach($charges as $key => $charge)
                                    @if($charge->venue && $charge->session)
                                        <tr>
                                            <td>{{ $charge->venue->name ?? '' }}</td>
                                            <td>{{ $charge->session->name ?? '' }}</td>
                                            @foreach($first_date as $key => $dft)
                                            <td>
                                                <?php
                                                    $banq_check = App\Models\BanquetBookingCharges::where('session_id', $charge->session_id)->where('vanue_id', $charge->venue_id)->whereDate('funDate', $dft)->where('status', 'Active')->exists();
                                                ?>
                                                @if($banq_check)
                                                    <span class="text-danger">B</span>
                                                @else
                                                    <span class="text-success">V</span>
                                                @endif
                                                
                                            </td>
                                            @endforeach 
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        

                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>
<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')
<script>
    $(document).ready(function() {
        document.getElementById("date").min = new Date().toISOString().split("T")[0];
    });
</script>
@endpush()
@endsection