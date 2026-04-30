@extends('layouts.web')
@section('content')

<style>

    table {
        width: 100% !important;
    }

    .f-right {
        text-align: end;
        margin-right: 3% !important;
    }

    .f-right button {
        width: 11% !important;
    }

</style>

<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                   <h5>Booking Summary</h5>
                </nav>
            </div>
        </div>

        <div class="row mt-4 room_table">
             
            <div class="col-lg-12">
                <div class="card mb-1 h-100 mb-4">

                    <div class="card-header">
                        Booking ID : <b>{{ $datas?$datas->booking_number:'' }}</b>
                    </div>

                    <div class="card-body">
                        
                        <div class="row">
                            
                            <div class="col-lg-3">

                                <span class="text-success">Check In : </span> {{ $datas?date("F d, Y", strtotime($datas->checkin)):'' }}
                                
                            </div>

                            <div class="col-lg-3">

                                <span class="text-success">Check Out : </span> {{ $datas?date("F d, Y", strtotime($datas->checkout)):'' }}
                                
                            </div>

                            <div class="col-lg-3">

                                <!-- <span class="text-success">Nites : </span> 0 -->
                                
                            </div>

                        </div>

                        <div class="row mt-4">
                            
                            <div class="col-lg-12">
                                
                                <table class="table">
                                  <thead>
                                    <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Room</th>
                                      <th scope="col">Occupant Type</th>
                                      <th scope="col">Additional Info</th>
                                      <th scope="col">Adult/Child</th>
                                      <th scope="col">Room Count / Days</th>
                                      <th scope="col">GST(%)</th>
                                      <th scope="col">Rent/Nite</th>
                                      <th scope="col">Total Amt</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php $g_total = '0'; ?>
                                    @if(count($data_items))
                                        
                                        @foreach($data_items as $key => $item)

                                            <tr>
                                                <th scope="row">
                                                    <button class="btn btn-sm btn-outline-danger" onclick="cancelRoom({{ $item->id }})" type="button"><i class="fa fa-times" aria-hidden="true"></i></button>
                                                </th>
                                                <td>{{ $item->room->name ?? '' }}</td>
                                                <td>{{ $item->occupant->name ?? '' }}</td>
                                                <td style="text-align: left;">
                                                    @if($item->guest_name)
                                                    <small> <b>Name : </b> {{ $item->guest_name }}</small><br>
                                                    @endif
                                                    @if($item->guest_email)
                                                    <small> <b>Email : </b> {{ $item->guest_email }}</small><br>
                                                    @endif
                                                    @if($item->guest_mobile)
                                                    <small> <b>Mobile : </b> {{ $item->guest_mobile }}</small><br>
                                                    @endif
                                                </td>
                                                <td>{{ $item->adult ?? '0' }}/{{ $item->child ?? '0' }}</td>
                                                <td>{{ $item->no_of_rooms }} / {{ $item->no_of_days }}</td>
                                                <td>{{ $item->gst_per }}</td>
                                                <td>{{ number_format($item->room_charges, 2) }}</td>
                                                <td>{{ number_format($item->room_charge_total, 2) }}</td>
                                            </tr>
                                            <?php $g_total += $item->room_charge_total; ?>
                                        @endforeach

                                    @else

                                        <tr>
                                            <td colspan="8" class="text-secondry">Card Empty!</td>
                                        </tr>

                                    @endif                                    
                                    
                                  </tbody>
                                  <tfoot>
                                    <tr>
                                        <td colspan="7"></td>
                                        <td><b>Total</b></td>
                                        <td>{{ number_format($g_total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7"></td>
                                        <td><b>Payment Mode</b></td>
                                        <td>Online</td>
                                    </tr>
                                  </tfoot>
                                </table>

                            </div>

                        </div>
                        @if($datas)
                        
                        <div class="f-right mt-4 mr-4">
                            <a href="{{ route('empty.card', $datas->booking_number) }}"><button class="btn btn-sm btn-danger" type="button">Empty Cart</button></a>

                            <a href="{{ route('checkout.card', $datas->booking_number) }}"><button class="btn btn-sm btn-success" type="button">Pay Now</button></a>
                        </div>
                        @endif
                        
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

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

    });
</script>

<script>
    function cancelRoom(bookingID) {
        if(confirm("Are you sure?")){

            $.ajax({

                type:'POST',

                url:"{{ route('cancel.room.item') }}",

                data:{bookingID:bookingID},

                success:function(data){
                    console.log(data);
                    getRoomDetails();
                }

            });

        }
    }
</script>

<script>
    function getRoomDetails() {

        var booking_id = '<?php echo $datas?$datas->id:''; ?>';
        
        $.ajax({

            type:'POST',

            url:"{{ route('get.room.item.front') }}",

            data:{booking_id:booking_id},

            success:function(data){
                console.log(data);
                $('.room_table').html(data);
            }

        });
    }
</script>
@endpush()
@endsection