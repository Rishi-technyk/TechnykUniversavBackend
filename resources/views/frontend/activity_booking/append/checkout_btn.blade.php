@if($check_guest>'0')
<a href="{{ route('checkout.booking', encrypt($card->id)) }}"><button class="btn btn-success btn-sm` checkout-btn" type="button">Checkout</button></a>
@else
<a href="#guestSection"><button class="btn btn-success btn-sm checkout-btn" type="button">Checkout</button></a>
@endif