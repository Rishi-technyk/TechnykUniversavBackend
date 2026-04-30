<table class="table table-striped" style="margin-top: 7%;">
    <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">Venue</th>
          <th scope="col">Session</th>
          <th scope="col">GST Per.</th>
          <th scope="col">GST Amount</th>
          <th scope="col">Charges</th>
          <th scope="col">Total</th>
          <th scope="col">Cancellation (%)</th>
          <th scope="col">Cancellation Amt</th>
          <th scope="col">GST</th>
          <th scope="col">Net Deducation</th>
        </tr>
    </thead>
    <tbody>
        <?php $total = '0'; $deducation_amt = '0'; ?> 
        @foreach($bookings as $key => $booking)
        <tr>
            <?php
                $session = DB::table('sessions')->where('id', $booking->session_id)->first();
                $total += $booking->total;
                $deducation_amt += $booking->cancellation_deducation;
            ?>
          <td scope="row">
                @if($booking->status=='Active')
                <button class="btn btn-sm btn-outline-danger" onclick="cancelVenue({{ $booking->id }})">Cancel</button>
                @else
                <b class="text-danger">Cancelled</b>
                @endif
          </td>
          <td>{{ $booking->venue->name ?? '' }}</td>
          <td>{{ $session->name }}</td>
          <td>{{ $booking->gst_per }}%</td>
          <td>{{ format_price($booking->gst_amount, 2) }}</td>
          <td>{{ format_price($booking->charges, 2) }}</td>
          <td>{{ format_price($booking->total, 2) }}</td>
          <td>{{ $booking->cancellation_per }}{{ $booking->cancellation_per ? '%' : '' }}</td>
          <td>{{ format_price($booking->cancellation_amt, 2) }}</td>
          <td>{{ format_price($booking->cancellation_GST_amt, 2) }}</td>
          <td>{{ format_price($booking->cancellation_deducation, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5"></td>
            <td> <b>Total</b> </td>
            <td> <b>{{ format_price($total, 2) }}</b> </td>
        </tr>

        <tr>
            <td colspan="9"></td>
            <td> <b>Total Advance Paid</b> </td>
            <td> <b>{{ format_price($total, 2) }}</b> </td>
        </tr>

        <tr>
            <td colspan="9"></td>
            <td> <b>Previous Cancelaltion</b> </td>
            <?php $prev_de = $prev_bookings-$latest_bookings->cancellation_deducation; ?>
            <td> <b> <span class="text-danger">(-)</span> {{ format_price($prev_de, 2) }}</b> </td>
        </tr>

        <tr>
            <td colspan="9"></td>
            <td> <b>Deduction</b> </td>
            <td> <b><span class="text-danger">(-)</span> {{ format_price($latest_bookings->cancellation_deducation, 2) }}</b> </td>
        </tr>
        <?php $refund = $total-$deducation_amt; ?>
        <tr>
            <td colspan="9"></td>
            <td> <b>Refund</b> </td>
            <td> <b>{{ format_price($refund, 2) }}</b> </td>
        </tr>
        
    </tbody>
</table>