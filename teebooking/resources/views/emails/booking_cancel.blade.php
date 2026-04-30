@component('mail::message')
Dear Member,<br>

Your Tee Booking has been {{$data->booking_type}} for {{ \Carbon\Carbon::parse($data->booking_date)->format('jS F, Y')}} against booking number {{$data->bookingId}} <br>
Tee Time: {{$data->tee_time}}
<br>
Tee Hole: {{$data->hole_number}}
<br>
@if($data->player1_name)
Player1: {{$data->player1_name}}/{{$data->player1_member_id}}
@else
Player1: NA
@endif
<br>
@if($data->player2_name)
Player2: {{$data->player2_name}}/{{$data->player2_member_id}}
@else
Player2: NA
@endif
<br>
@if($data->player3_name)
Player3: {{$data->player3_name}}/{{$data->player3_member_id}}
@else
Player3: NA
@endif
<br>
@if($data->player4_name)
Player4: {{$data->player4_name}}/{{$data->player4_member_id}}
@else
Player4: NA
@endif
<br>

<!-- @component('mail::button', ['url' => 'https://yourblogurl.com/printable-version'])
Print
@endcomponent -->

Regards,<br>
{{ config('app.name') }}
@endcomponent
