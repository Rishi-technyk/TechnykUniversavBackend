<table class="table table-bordered" style="margin-top: 7%;">
    <thead>
        <tr>
            <th scope="col">Status</th>
            <th scope="col">Room</th>
            <th scope="col">Occupant Type</th>
            <th scope="col">Adult/Child</th>
            <th scope="col">Room Count / Days</th>
            <th scope="col">GST(%)</th>
            <th scope="col">Rent/Nite</th>
            <th scope="col">Total</th>
            <th scope="col">Cancellation GST</th>
            <th scope="col">Cancellation Amt</th>
            <th scope="col">Net Deducation</th>
        </tr>
    </thead>
    <tbody>
        <?php $g_total = '0'; $deducation_amt = '0'; ?>
        @foreach($data_items as $key => $item)
        <tr>
            <td>
                @if($item->status=='Active' && $datas->checkout >= date('Y-m-d'))
                    <button class="btn btn-sm btn-outline-danger" onclick="cancelRoom({{ $item->id }})">Cancel</button>
                @elseif($item->status=='Cancelled')
                    <b class="text-danger">Cancelled</b>
                @elseif($item->status=='Active')
                    <b class="text-success">Active</b>
                @endif
            </td>
            <td>{{ $item->room->name ?? '' }}</td>
            <td>{{ $item->occupant->name ?? '' }}</td>
            <td>{{ $item->adult ?? '0' }}/{{ $item->child ?? '0' }}</td>
            <td>{{ $item->no_of_rooms }} / {{ $item->no_of_days }}</td>
            <td>{{ $item->gst_per }}</td>
            <td>{{ format_price($item->room_charges, 2) }}</td>
            <?php 
                $g_total += $item->room_charge_total;
                $deducation_amt += $item->cancellation_deducation;
            ?>
            <td>{{ format_price($item->room_charge_total, 2) }}</td>
            <td>{{ $item->cancellation_GST }}</td>
            <td>{{ format_price($item->cancellation_amt, 2) }}</td>
            <td>{{ format_price($item->cancellation_deducation, 2) }}</td>
        </tr>

        @endforeach
        
    </tbody>

    <tfoot>
        <tr>
            <td colspan="6"></td>
            <td> <b>Total</b> </td>
            <td class="flow-right"> <b>{{ format_price($g_total, 2) }}</b> </td>
        </tr>

        <tr>
            <td colspan="9"></td>
            <td> <b>Total Advance Paid</b> </td>
            <td class="flow-right"> <b>{{ format_price($g_total, 2) }}</b> </td>
        </tr>

        <tr>
            <td colspan="9"></td>
            <td> <b>Previous Cancelaltion</b> </td>
            <?php $prev_de = $prev_bookings - ($latest_bookings->cancellation_deducation??'0'); ?>
        <td class="flow-right"> <b> <span class="text-danger">(-)</span> {{ format_price($prev_de, 2) }}</b> </td>
        </tr>

        <tr>
            <td colspan="9"></td>
            <td> <b>Deduction</b> </td>
            <td class="flow-right"> <b><span class="text-danger">(-)</span> {{ format_price($latest_bookings->cancellation_deducation ?? '0', 2) }}</b> </td>
        </tr>
        <?php $refund = $g_total-$deducation_amt; ?>
        <tr>
            <td colspan="9"></td>
            <td> <b>Refund</b> </td>
            <td class="flow-right"> <b>{{ format_price($refund, 2) }}</b> </td>
        </tr>
    </tfoot>
</table>