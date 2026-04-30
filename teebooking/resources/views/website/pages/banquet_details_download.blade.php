<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Party Booking Voucher</title>
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

        <div class="title" style="text-align: center; margin-bottom: 20px; font-size: 18px; border-top: solid; border-bottom: solid 1px; padding: 2px;">PARTY BOOKING VOUCHER</div>

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
                    <td style="border: none; text-align: left; width: 20%;"><strong>Occupant Type</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ $datas->occupant->name ?? '' }}</td>

                    <td style="border: none; text-align: left; width: 17%;"><strong>Card ID</strong></td>
                    <td style="border: none; text-align: left;">{{ $datas->cardID }}</td>
                </tr>

                <tr>
                    <td style="border: none; text-align: left; width: 20%;"><strong>Member ID</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ $datas->memberID }}</td>

                    <td style="border: none; text-align: left; width: 17%;"><strong>Function Date</strong></td>
                    <td style="border: none; text-align: left;">{{ $datas->funDate ? date("d-m-Y", strtotime($datas->funDate)) : '' }}</td>
                </tr>

                <tr>
                    <td style="border: none; text-align: left; width: 20%;"><strong>Name</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ $datas->memberName }}</td>

                    <td style="border: none; text-align: left; width: 17%;"><strong>Function Type</strong></td>
                    <td style="border: none; text-align: left;">{{ $datas->function->name ?? '' }}</td>
                </tr>

                <tr>
                    <td style="border: none; text-align: left; width: 20%;"><strong>Email</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ $datas->memberEmail }}</td>

                    <td style="border: none; text-align: left; width: 17%;"><strong>No. Of Person</strong></td>
                    <td style="border: none; text-align: left;">{{ $datas->noofPerson }}</td>
                </tr>

                <tr>
                    <td style="border: none; text-align: left; width: 20%;"><strong>Mobile</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ $datas->memberMobile }}</td>

                    <td style="border: none; text-align: left; width: 17%;"><strong>Remark</strong></td>
                    <td style="border: none; text-align: left;">{{ $datas->remark }}</td>
                </tr>

                <tr>
                    <td style="border: none; text-align: left; width: 20%;"><strong>Address</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ $datas->address ?? 'NA' }}</td>

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
                    <td style="border: none; text-align: left; width: 20%;"><strong>Paid Payment</strong></td>
                    <td style="border: none; text-align: left; width: 35%;">{{ number_format(getVenueTotal($datas->id), 2) }}</td>

                    <td style="border: none; text-align: left; width: 17%;"><strong></strong></td>
                    <td style="border: none; text-align: left;"></td>
                </tr>
                

            </tbody>
        </table>

        <table class="f-12 second-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Venue</th>
                    <th scope="col">Status</th>
                    <th scope="col">Session</th>
                    <th scope="col">GST Per.</th>
                    <th scope="col">GST Amount</th>
                    <th scope="col">Security Deposit</th>
                    <th scope="col">Charges</th>
                    <th scope="col">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = '0'; ?>
                @foreach($bookings as $key => $booking)
                <tr>
                    <?php
                        $session = DB::table('sessions')->where('id', $booking->session_id)->first();
                        $total += $booking->total;
                    ?>
                  <th scope="row">{{ ++$key }}</th>
                  <td>{{ $booking->venue->name ?? '' }}</td>
                  <td>
                        @if($booking->status=='Active')
                            <span style="color: green;">Active</span>
                        @elseif($booking->status=='Cancelled')
                            <span style="color: darkred;">Cancelled</span>
                        @else
                            <span style="color: yellow;">Pending</span>
                        @endif
                  </td>
                  <td>{{ $session->name }}</td>
                  <td>{{ $booking->gst_per }}%</td>
                  <td>{{ number_format($booking->gst_amount, 2) }}</td>
                  <td>{{ number_format($booking->security_deposit, 2) }}</td>
                  <td>{{ number_format($booking->charges, 2) }}</td>
                  <td>{{ number_format($booking->total, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="7"></td>
                    <td> <b>Total</b> </td>
                    <td> <b>{{ number_format($total, 2) }}</b> </td>
                </tr>
            </tbody>
        </table>

    </div>

</body>
</html>
