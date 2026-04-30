@foreach($card_items as $slt)
<p class="mt-2"><b>Slot Date : {{ date("d", strtotime($slt->slot_date)) }} {{ date("D", strtotime($slt->slot_date)) }}</b></p>
<p><b>Slot Time : {{ $slt->slot->label ?? 'N/A' }}</b></p>
@endforeach