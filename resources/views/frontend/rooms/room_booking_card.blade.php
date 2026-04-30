<h3>Cart Summary</h3>

@if($card_items->count() > 0)
    
    <form action="" method="get">
        <div class="room-summary">
            @foreach($card_items as $item)
            <div class="row mt-4">
                <div class="col-lg-2">
                    <a href="javascript:void(0)" onclick="removeRoomFromCard({{ $item->id }})" class="remove-room text-danger">X</a>
                </div>
                <div class="col-lg-10">
                    <h6>{{ $item->room->name ?? 'No Name' }}</h6>
                </div>
                <div class="col-lg-2"></div>
                <div class="col-lg-5">
                    <?php 
                        $item_room_charge = $item->room_charges+$item->gst_amount;
                    ?>
                    {{ format_price($item_room_charge) }} x {{ $item->no_of_days }}
                </div>
                <div class="col-lg-5 text-right">
                    {{ format_price($item->room_charge_total) }}
                </div>
            </div>
            <?php $card_total += $item->room_charge_total; ?>
            @endforeach 
        </div>
        <div class="row total-price mt-2">
            <div class="col-lg-2"></div>
            <div class="col-lg-5 mt-1"> <h6>Cart Total</h6></div>
            <div class="col-lg-5 mt-1 text-right"> <b>{{ format_price($card_total) }}</b> </div>
        </div>
        <a href="{{ route('room-booking.summary') }}"><button class="mb-4" type="button">Checkout</button></a>
    </form>
@else
    <div class="room-summary text-center">
        <p>No rooms added in card</p>
    </div>
    <div class="row total-price">
        <div class="col-lg-8 mt-1"> <h6>Cart Total</h6></div>
        <div class="col-lg-4 mt-1"> <b>{{ format_price($card_total) }}</b> </div>
    </div>
@endif