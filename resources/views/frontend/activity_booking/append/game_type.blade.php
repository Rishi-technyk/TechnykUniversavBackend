@if(isset($card_items))
<div class="row mt-4">
    <div class="col-lg-2"></div>
    <div class="col-lg-3"> <b>Name</b> </div>
    <div class="col-lg-3"> <b>Email</b> </div>
    <div class="col-lg-3"> <b>Mobile</b> </div>
    <div class="col-lg-1"></div>
    <hr>
        <?php $key = '1'; ?>

        @foreach($card_items as $item => $info)

            <?php $guest_by_slots = App\Models\ActivityCardGuestInfo::where('card_id', $info->card_id)->where('card_item_id', $info->id)->get(); ?>
            
            <?php $key = '1'; ?>
            
            @if(count($guest_by_slots))

                <div class="col-lg-11 mb-4"> <b>Slot : </b> {{ date("d", strtotime($info->slot_date)) }} {{ date("D", strtotime($info->slot_date)) }} {{ $info->slot->label }}</div>

                @foreach($guest_by_slots as $guest)                    

                    <div class="col-lg-2 mb-4">
                        Player {{ $key }} 
                    </div>
                    <div class="col-lg-3 f-r mb-4">
                        <input type="text" class="form-control player_name_{{ $key }}" placeholder="Enter Player Name" value="{{ $guest->player_name }}" name="player_name[]" required readonly>
                        <input type="hidden" class="form-control occupant_id_{{ $key }}" value="{{ $guest->occupant_id }}" name="occupant_id[]">
                        <input type="hidden" class="form-control slot_id_{{ $key }}" name="slot_id[]" value="{{ $guest->slot_id }}">
                            <input type="hidden" class="form-control slot_date_{{ $key }}" name="slot_date[]" value="{{ $guest->slot_date }}">
                    </div>
                    <div class="col-lg-3 f-r mb-4">
                        <input type="text" class="form-control player_email_{{ $key }}" placeholder="Enter Player Email" value="{{ $guest->player_email }}" name="player_email[]" required readonly>
                    </div>
                    <div class="col-lg-2 f-r mb-4">
                        <input type="number" class="form-control player_mobile_{{ $key }}" placeholder="Enter Player Mobile" value="{{ $guest->player_mobile }}" name="player_mobile[]" required readonly>
                    </div> 
                    <div class="col-lg-1 f-r mb-4">
                        <button class="btn btn-success btn-sm" type="button" onclick="modifyPlayer({{ $guest->id }})">Modify</button>
                    </div> 

                    <?php $key++ ?>

                @endforeach

            @else

                @if($game_type)
                    <div class="col-lg-8 mb-4"> <b>Slot : </b> {{ date("d", strtotime($info->slot_date)) }} {{ date("D", strtotime($info->slot_date)) }} {{ $info->slot->label }}</div>

                    <div class="col-lg-3 mb-4">
                        <a href="javascript:" class="btn btn-success checkout-btn btn-sm" onclick="openModal({{ $game_type->no_of_players }}, {{$item}})">Pick Players</a>
                    </div>
                    <?php $key = '1'; ?>
                    
                    <?php for ($i = 0; $i < $game_type->no_of_players; $i++){ ?>
                   
                        <div class="col-lg-2 mb-4">
                            Player {{ $key }} 
                        </div> 
                        <div class="col-lg-3 f-r mb-4">
                            <input type="text" class="form-control player_name_{{ $key }}_{{$item}}" placeholder="Enter Player Name" name="player_name[]" required readonly>
                            <input type="hidden" class="form-control occupant_id_{{ $key }}_{{$item}}" name="occupant_id[]">
                            <input type="hidden" class="form-control slot_id_{{ $key }}_{{$item}}" name="slot_id[]" value="{{ $info->slot_id }}">
                            <input type="hidden" class="form-control slot_date_{{ $key }}_{{$item}}" name="slot_date[]" value="{{ $info->slot_date }}">
                        </div>
                        <div class="col-lg-3 f-r mb-4">
                            <input type="text" class="form-control player_email_{{ $key }}_{{$item}}" placeholder="Enter Player Email" name="player_email[]" required readonly>
                        </div>
                        <div class="col-lg-2 f-r mb-4">
                            <input type="number" class="form-control player_mobile_{{ $key }}_{{$item}}" placeholder="Enter Player Mobile" name="player_mobile[]" required readonly>
                        </div> 
                        <div class="col-lg-1 f-r mb-4">
                            <!-- <button class="btn btn-primary btn-sm" type="button" onclick="modifyPlayer({{ $key }})">Modify</button> -->
                        </div> 
                        
                    <?php $key++ ?>
                    <?php } ?> 
                @endif

            @endif

        @endforeach

</div>

@endif
