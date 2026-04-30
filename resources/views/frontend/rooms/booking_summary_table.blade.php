<div class="container">
    <div class="card-section-title-box">
        <!-- Breadcrumb Section Begin -->
        <div class="card-section-title-box">
            <span class="card-section-bar"></span>
            <h4 class="card-section-title">Room Booking Summary</h4>
        </div>
        <!-- Breadcrumb Section End -->
        <div class="row mb-4">

            <div class="col-lg-3 mb-4 mt-4">
                <h6>Booking ID : {{ $datas?$datas->booking_number:'' }}</h6>
            </div>
                            
            <div class="col-lg-3 mb-4 mt-4">

                <span class="text-success">Check In : </span> {{ $datas?date("F d, Y", strtotime($datas->checkin)):'' }}
                
            </div>

            <div class="col-lg-3 mb-4 mt-4">

                <span class="text-danger">Check Out : </span> {{ $datas?date("F d, Y", strtotime($datas->checkout)):'' }}
                
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
                                    <small> <b>Name : </b> {{ $item->guest_name ?? 'N/A' }}</small><br>
                                    @endif
                                    @if($item->guest_email)
                                    <small> <b>Email : </b> {{ $item->guest_email ?? 'N/A' }}</small><br>
                                    @endif
                                    @if($item->guest_mobile)
                                    <small> <b>Mobile : </b> {{ $item->guest_mobile ?? 'N/A' }}</small><br>
                                    @endif
                                </td>
                                <td>{{ $item->adult ?? '0' }}/{{ $item->child ?? '0' }}</td>
                                <td>{{ $item->no_of_rooms }} / {{ $item->no_of_days }}</td>
                                <td>{{ $item->gst_per }}</td>
                                <td>{{ format_price($item->room_charges, 2) }}</td>
                                <td>{{ format_price($item->room_charge_total, 2) }}</td>
                            </tr>
                            <?php $g_total += $item->room_charge_total; ?>
                        @endforeach

                    @else

                        <tr>
                            <td colspan="8" class="text-center text-secondary">Card Empty!</td>
                        </tr>

                    @endif                                    
                    
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="7"></td>
                        <td><b>Total</b></td>
                        <td>{{ format_price($g_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="7"></td>
                        <td><b>Payment Mode</b></td>
                        <td>Online</td>
                    </tr>
                    </tfoot>
                </table>

            </div>

            @if($datas)
                <div class="col-lg-8"></div>
                <div class="col-lg-4">
                    <div class="mt-4 text-right">
                        <a href="{{ route('empty.card', $datas->booking_number) }}"><button type="button">Empty Cart</button></a>

                        <a href="{{ route('checkout.card', $datas->booking_number) }}"><button type="button">Pay Now</button></a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>