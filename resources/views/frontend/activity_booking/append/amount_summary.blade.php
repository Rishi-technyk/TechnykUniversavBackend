<div class="col-lg-7"><b>Basic Amount</b> <small>{{ $slot_count }}X{{ $card->facility_amount }}</small> </div>
<div class="col-lg-5 f-r mt-4"><b>₹{{ number_format($card->facility_amount*$slot_count, 2) }}</b></div>

<div class="col-lg-7"><b>Guest Charge</b> </div>
<div class="col-lg-5 f-r"><b>₹{{ number_format($card->guest_total_amount, 2) }}</b></div>

<div class="col-lg-7"><b>GST Amt ({{ $card->facility_gst_per }}%)</b></div>
<div class="col-lg-5 f-r"><b>₹{{ number_format($card->facility_gst_amt, 2) }}</b></div>
<hr>
<div class="col-lg-7 total-section"><b>Net Amount</b></div>
<div class="col-lg-5 f-r total-section"><b>₹{{ number_format($card->facility_total, 2) }}</b></div>

<input type="hidden" class="card_id" value="{{ $card->id }}">