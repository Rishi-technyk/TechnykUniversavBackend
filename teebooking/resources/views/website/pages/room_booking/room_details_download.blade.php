<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking Voucher</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 5px;
        }
        .header {
            text-align: center;
        }
        .header img {
            width: 100px;
            margin-bottom: 10px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .sub-header {
            text-align: center;
            font-size: 18px;
            margin-bottom: 3px;
        }

        .sub-sub-header {
            text-align: center;
            font-size: 15px;
            margin-bottom: 20px;
        }

        .details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-gap: 10px;
            margin-bottom: 20px;
        }
        .details div {
            margin-bottom: 10px;
        }
        table {
            
        }
        .second-table th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }

        .grid-container {
                 
        }

        .f-12 {
            font-size: 12px !important;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
             <img src="{{'data:image/png;base64,'.base64_encode(file_get_contents(public_path('admin/assets/img/logo.png')))}}" style="float: left; width: 70px" alt="Logo">
            <!-- <img src="https://teebooking.aepta.in/public/admin/assets/img/logo.png" style="float: left; width: 70px" alt="Logo"> -->
            <div class="title">{{ $setting?$setting->heading:'' }}</div>
            <div class="sub-header">{{ $setting?$setting->sub_heading:'' }}</div>
            <div class="sub-sub-header">Phone : {{ $setting?$setting->phone:'' }} <br> E-mail : {{ $setting?$setting->email:'' }} </div>
        </div>

        <div class="title" style="text-align: center; margin-bottom: 20px; font-size: 18px; border-top: solid; border-bottom: solid 1px; padding: 2px;">ROOM BOOKING VOUCHER</div>

        <table class="f-12" style="width: 100%;">
            <tbody>

                @if(isset($transaction))
                    <tr>
                        <td style="border: none; text-align: left; width: 20%;"><strong>Booking ID</strong></td>
                        <td style="border: none; text-align: left; width: 35%;">{{ $transaction->transID }}</td>

                        <td style="border: none; text-align: left; width: 17%;"><strong>Booking Date</strong></td>
                        <td style="border: none; text-align: left;">{{ date("d-m-Y", strtotime($transaction->created_at)); }}</td>
                    </tr>
                @endif

                <tr>
                    <td style="border: none; text-align: left; width: 20%;"><strong>Member ID</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ $datas->memberID }}</td>

                    <td style="border: none; text-align: left; width: 17%;"><strong>Card ID</strong></td>
                    <td style="border: none; text-align: left;">{{ $datas->chartID }}</td>
                </tr>

                <tr>
                    <td style="border: none; text-align: left; width: 20%;"><strong>Name</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ $member->DisplayName }}</td>

                    <td style="border: none; text-align: left; width: 17%;"><strong>Mobile</strong></td>
                    <td style="border: none; text-align: left;">{{ $member->Mobile }}</td>
                </tr>

                <tr>
                    <td style="border: none; text-align: left; width: 20%;"><strong>Email</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ $member->Email }}</td>                    

                    <td style="border: none; text-align: left; width: 17%;"><strong>Payment Status</strong></td>
                    <td style="border: none; text-align: left;">
                        @if(isset($transaction))

                            @if(isset($transaction) && $transaction->payment_status=='Paid')

                            <b style="color: green;">{{ $transaction->payment_status }}</b>

                            @elseif(isset($transaction) && $transaction->payment_status=='Failed' || $transaction->payment_status=='Not Paid')

                            <span style="color: darkred;">{{ $transaction->payment_status }}</span>

                            @endif

                        @else

                            <span style="color: darkred;">Not Paid</span>

                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="border: none; text-align: left; width: 20%;"><strong>Check IN</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ date("M d, Y", strtotime($datas->checkin)); }}</td>

                    <td style="border: none; text-align: left; width: 17%;"><strong>Check OUT</strong></td>
                    <td style="border: none; text-align: left;">{{ date("M d, Y", strtotime($datas->checkout)); }}</td>
                    
                </tr>

                <tr>
                    <td style="border: none; text-align: left; width: 20%;"><strong>Paid Payment</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ number_format(getBookingTotal($datas->id), 2) }}</td>

                    <td style="border: none; text-align: left; width: 17%;"><strong>Booking Status</strong></td>
                    <td style="border: none; text-align: left;">
                        @if($datas->status == 'Active')
                            <span class="text-success" style="color: green;">Active</span>
                        @elseif($datas->status == 'Cancelled')
                            <span class="text-danger" style="color: darkred;">Cancelled</span>
                        @else
                            <span class="text-warning" style="color: yellow;">Pending</span>
                        @endif
                    </td>
                </tr>
                

            </tbody>
        </table>

        <table class="f-12 second-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
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
                @foreach($data_items as $key => $item)
                <tr>
                    <th scope="row">{{ ++$key }}</th>
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
                    <td>18%</td>
                    <td>{{ number_format($item->room_charges, 2) }}</td>
                    <?php 
                        $GST_a = $item->gst_amount; 
                        
                        $g_total += $item->room_charge_total;
                    ?>
                    <td>{{ number_format($item->room_charge_total, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="7"></td>
                    <td> <b>Total</b> </td>
                    <td> <b>{{ number_format($g_total, 2) }}</b> </td>
                </tr>
            </tbody>
        </table>

    </div>

</body>
</html>
