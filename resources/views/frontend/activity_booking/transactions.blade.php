@extends('frontend.layouts.app')

@section('title', 'Activity Transactions')
<style>
    .amount {
        font-size: 14px;
        margin-top: -5%;
    }
    .facility-card:hover {
        transform: translateY(-5px);
        border: 2px solid #2e4374 !important;
    }
    .card-hight {
        height: 566px;
        overflow-y: scroll;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .f-e {
        float: inline-end;
    }

    .summary-box {
        border: 1px solid;
        padding: 2%;
        border-radius: 4px;
    }

    .fz-14 {
        font-size: 13px;
    }

    .fz-11 {
        font-size: 11px;
    }

    .btn-success {
        background-color: #2e4374 !important;
        border-color: #2e4374 !important;
    }
</style>
@section('content')

<section class="room-details-section">

    <div class="container">

        <!-- Breadcrumb Section Begin -->

        <div class="card-section-title-box">

            <span class="card-section-bar"></span>

            <h4 class="card-section-title">Activity Transactions</h4>

        </div>

        <!-- Breadcrumb Section End -->

        
        <div class="card-section-title-box">
            
            <span class="card-section-bar"></span>

            <div class="row">
                
                <div class="col-lg-8">
                    
                    <div class="card">

                        <div class="card-body">

                            @if(count($datas))
                                    
                                <div class="row pd-2">

                                    @foreach($datas as $data)
                                        
                                    <div class="col-lg-6">

                                        <a href="#summary-card" onclick="getTraDetails({{ $data->id }})">

                                            <div class="card mt-2 mr-4 facility-card">
                                        
                                                <div class="card-body box-{{ $data->id }}">

                                                    <div class="row">

                                                        <div class="col-lg-12"> 

                                                            <div class="row">
                                                                <div class="col-lg-8">
                                                                    <h6 class="text-muted mt-2" title="Booking ID">#{{ $data->booking_number }}</h6>
                                                                </div>
                                                                <div class="col-lg-4 text-right">
                                                                    <p class="text-right" title="Booking Status">
                                                                        @if($data->status == 'Active')
                                                                            <span class="f-e text-success">Active</span>
                                                                        @elseif($data->status == 'Cancelled')
                                                                            <span class="f-e text-danger">Cancelled</span>
                                                                        @else
                                                                            <span class="f-e text-warning">Pending</span>
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <h5 class="facility-title mt-2">{{ $data->facility->name ?? '' }}</h5>

                                                            <p class="facility-sub-title mt-2">{{ \Illuminate\Support\Str::limit($data->facility->short_description,30) }}</p>
                                                            
                                                            <div class="row">

                                                                <div class="col-lg-8">
                                                                    <p class="text-muted amount" title="Booking Amount"> <span>B. Amount : {{ format_price($data->facility_total, 2) }}</span> </p>
                                                                </div>
                                                                <div class="col-lg-4 text-right" title="Payment Status">
                                                                    <small>
                                                                        @if($data->payment_status == 'Paid')
                                                                            <span class="f-e text-success">Paid</span>
                                                                        @else
                                                                            <span class="f-e text-danger">Not Paid</span>
                                                                        @endif
                                                                    </small>
                                                                </div>
                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </a>

                                    </div>

                                    @endforeach

                                </div>

                                <div>
                                    {!! $datas->links() !!}
                                </div>

                            @else

                                <div class="text-center text-danger facility-title">No Booking Available</div>

                            @endif
                            
                        </div>
                        
                    </div>
                </div>

                <div class="col-lg-4 tran-details" id="summary-card">
                
                </div>

            </div>

        </div>
        

    </div>

</section>



@endsection

@section('script')
<script>
    let LastClass = '';

    function getTraDetails(tid) {

        var active_box = '.box-'+tid;
        $(LastClass).removeClass('active-box');
        
        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'POST',

            url:"{{ route('booking.transaction') }}",

            data:{id:tid},

            success:function(data){
                console.log(data);
                $('.tran-details').html(data);
                
                $(active_box).addClass('active-box');
            }

        });

        LastClass = active_box;

    }
</script>

<script>
    function cancelSlot(sid, fid) {
        if(confirm('Are you sure cancel this slot?')){
            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });

            $.ajax({

                type:'POST',

                url:"{{ route('cancel.slot') }}",

                data:{id:sid, fid:fid},

                success:function(data){
                    console.log(data);
                    if(data.status){
                        getTraDetails(data.booking_id);
                    } else {
                        alert(data.msg);
                    }
                }

            });
        }
    }
</script>
@endsection