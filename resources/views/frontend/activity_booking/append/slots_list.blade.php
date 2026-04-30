@if(count($facility_slots))
<div class="f-r mt-4">
    <div class="btn-group" role="group" aria-label="Basic example">
        <button type="button" class="btn btn-white btm-sm today-btn" onclick="getTodaySlot()">Today</button>
        <button type="button" class="btn btn-white previus_btn btm-sm" disabled onclick="getPrevSlot()"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-white btm-sm next_btn" onclick="getNextSlot()"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
    </div>
</div>

<table class="table table-hover mt-2">
    <thead>
        <tr>
            <th scope="col"></th>
            @foreach($first_date as $dt)
            <th scope="col">{{ date("d", strtotime($dt)) }}<br>{{ date("D", strtotime($dt)) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($facility_slots as $slot)
            <tr>
                <th scope="row">{{ $slot->slot->label }}</th>
                @foreach($first_date as $key => $dft)

                    <?php
                        $disabled = '';

                        $parts = explode('-', $slot->slot->name);

                        $end_time = $parts[1];

                        $slotDate = date("Y-m-d", strtotime($dft));

                        if($slotDate == date('Y-m-d')){

                            if($end_time>date("Hi")){
                                $disabled = '';
                            } else {
                                $disabled = 'disabled';
                            }
                        }
                        
                    ?>
                    
                    <td>
                       
                        @if(checkPerticularSlot($slot->slot_id, $dft, $facility_id))

                            @if(checkSlot($slot->slot_id, $dft, $facility_id)>='0')
                                
                                <input type="hidden" class="slot_date_{{$slot->slot_id}}_{{ $key }}" value="{{ $dft }}">
                                <input type="hidden" value="{{ $slot->slot_id }}" class="ss">
                                <input type="hidden" value="{{ $card_id }}" class="cc">
                                
                                @if(checkSlot($slot->slot_id, $dft, $facility_id)<='5' && checkSlot($slot->slot_id, $dft, $facility_id)>'0')

                                    @if(checkSlotBool($slot->slot_id, $dft, $card_id))
                                    <button class="slo_btn btn btn-sm btn-warning btn_slot_{{$slot->slot_id}}_{{ $key }} selected_slot_btn" onclick="bookSlot({{$slot->slot_id}}, {{ $key }}, {{$facility_id}})" type="button" {{ $disabled }}>{{ checkSlot($slot->slot_id, $dft, $facility_id) }} Left</button>
                                    @else
                                    <button class="slo_btn btn btn-sm btn-warning btn_slot_{{$slot->slot_id}}_{{ $key }}" onclick="bookSlot({{$slot->slot_id}}, {{ $key }}, {{$facility_id}})" type="button" {{ $disabled }}>{{ checkSlot($slot->slot_id, $dft, $facility_id) }} Left</button>
                                    @endif

                                @elseif(checkSlot($slot->slot_id, $dft, $facility_id)=='0')
                                    <button class="slo_btn btn btn-sm btn-danger text-white" disabled type="button">0 Left</button>
                                @else

                                    @if(checkSlotBool($slot->slot_id, $dft, $card_id))
                                    <button class="slo_btn btn btn-sm btn-success text-white btn_slot_{{$slot->slot_id}}_{{ $key }} selected_slot_btn" onclick="bookSlot({{$slot->slot_id}}, {{ $key }}, {{$facility_id}})" type="button" {{ $disabled }}>{{ checkSlot($slot->slot_id, $dft, $facility_id) }} Left</button>
                                    @else
                                    <button class="slo_btn btn btn-sm btn-success text-white btn_slot_{{$slot->slot_id}}_{{ $key }}" onclick="bookSlot({{$slot->slot_id}}, {{ $key }}, {{$facility_id}})" type="button" {{ $disabled }}>{{ checkSlot($slot->slot_id, $dft, $facility_id) }} Left</button>
                                    @endif
                                    
                                @endif
                                    
                            @endif

                        @else

                            <button class="slo_btn btn btn-sm btn-secondary text-white" disabled type="button">0 Left</button>
                            
                        @endif
                    </td>
                @endforeach 
            </tr>
        @endforeach
    </tbody>
</table>
<input type="hidden" value="{{ $last_date }}" class="last_date">
<input type="hidden" value="{{ $current_date }}" class="current_date">
<input type="hidden" value="{{ $prev_date }}" class="prev_date">
@else
<hr class="mt-4">
<div class="text-center">
    <p>Sorry! No Slot Available.</p>
</div>

@endif