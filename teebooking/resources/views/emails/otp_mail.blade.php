@component('mail::message')
Dear Member,<br>

Your OTP is been {{$data}} <br>

<br>

<!-- @component('mail::button', ['url' => 'https://yourblogurl.com/printable-version'])
Print
@endcomponent -->

Regards,<br>
{{ config('app.name') }}
@endcomponent
