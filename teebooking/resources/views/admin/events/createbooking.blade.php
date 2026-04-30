@extends('layouts.admin')

@section('content')

<main id="main" class="main">

<div class="pagetitle">
    <h1>Create Booking</h1>
</div>

<section class="section">

<div class="card">
<div class="card-body">

<form id="adminBookingForm">

@csrf

<input type="hidden" name="event_id" value="{{ $event->id }}">

<!-- MEMBER -->
<div class="mb-3">
<label class="form-label">Member</label>

<div class="mb-3">
<label class="form-label">Member ID</label>

<input
type="text"
class="form-control"
id="memberSearch"
placeholder="Enter Member ID"
/>

<input type="hidden" name="member" id="member_id">

<div id="memberResults" class="list-group mt-2"></div>

</div>
</div>


<!-- TICKETS -->
<h5 class="mt-4">Tickets</h5>

@foreach($event->ticketTypes as $ticket)

<div class="row mb-3">

<div class="col-md-6">
<label>{{$ticket->type}} (₹{{$ticket->amount}})</label>
</div>

<div class="col-md-3">

<input
type="number"
min="0"
value="0"
class="form-control ticketQty"
data-id="{{$ticket->id}}"
/>

</div>

</div>

@endforeach


<!-- WAITER -->
@if($event->waiter)

<div class="mt-4">

<label>Waiters (₹{{$event->waiter->waiter_cost}} each)</label>

<input
type="number"
name="waiters"
min="0"
class="form-control"
value="0"
/>

</div>

@endif


<!-- PAYMENT TYPE -->
<div class="mt-4">

<label>Payment Type</label>

<select name="payment_type" id="paymentType" class="form-control" required>

<option value="">Select Payment</option>

@foreach($payment_type as $payment)

<option
value="{{$payment['type_code']}}"
data-ref="{{$payment['ref_no']}}"
>
{{$payment['name']}}
</option>

@endforeach

</select>

</div>


<!-- REF NUMBER -->
<div class="mt-3 d-none" id="refBox">

<label>Reference No</label>

<input
type="text"
name="reference_no"
class="form-control"
/>

</div>


<!-- BUTTON -->
<div class="mt-4">

<button class="btn btn-success">
Create Booking
</button>

<a href="{{route('admin.events')}}" class="btn btn-secondary">
Back
</a>

</div>

</form>

</div>
</div>

</section>

</main>

@endsection
<script>
let members = @json($members);

document.getElementById('memberSearch')
.addEventListener('keyup',function(){

let value = this.value.toLowerCase();

let results = members.filter(m =>
m.MemberID.toLowerCase().includes(value)
).slice(0,5);

let html='';

results.forEach(m=>{

html += `
<a href="#" class="list-group-item list-group-item-action member-item"
data-id="${m.id}"
data-name="${m.MemberID} - ${m.DisplayName}">
${m.MemberID} - ${m.DisplayName}
</a>
`;

});

document.getElementById('memberResults').innerHTML = html;

});

document.addEventListener('click',function(e){

if(e.target.classList.contains('member-item')){

e.preventDefault();

let id = e.target.dataset.id;
let name = e.target.dataset.name;

document.getElementById('memberSearch').value = name;
document.getElementById('member_id').value = id;

document.getElementById('memberResults').innerHTML='';

}

});
</script>
<script>

fetch('/admin/payment-types')
.then(res => res.json())
.then(data => {

let select = document.getElementById('paymentType');

data.payment_types.forEach(p => {

let option = document.createElement('option');

option.value = p.type_code;
option.text = p.name;

option.dataset.ref = p.ref_no;

select.appendChild(option);

});

});

</script>
<script>

document.getElementById('paymentType')
.addEventListener('change', function(){

let ref = this.options[this.selectedIndex].dataset.ref;

if(ref == "1"){
document.getElementById('refBox').classList.remove('d-none');
}
else{
document.getElementById('refBox').classList.add('d-none');
}

});

</script>
<script>

document.getElementById('adminBookingForm')
.addEventListener('submit', function(e){

e.preventDefault();

let tickets = [];

document.querySelectorAll('.ticketQty').forEach(input => {

let qty = parseInt(input.value);

if(qty > 0){

tickets.push({
id: input.dataset.id,
quantity: qty
});

}

});

let formData = new FormData(this);

tickets.forEach((t,i)=>{

formData.append(`tickets[${i}][id]`,t.id);
formData.append(`tickets[${i}][quantity]`,t.quantity);

});

fetch('/admin/book-tickets', {

method:'POST',
headers:{
'X-CSRF-TOKEN':document.querySelector('input[name="_token"]').value
},
body:formData

})
.then(res => res.json())
.then(data => {

if(data.status){

alert("Booking Created: "+data.booking_no);
location.reload();

}
else{

alert(data.message);

}

});

});

</script>
<script>
document.getElementById('paymentType')
.addEventListener('change',function(){

let ref = this.options[this.selectedIndex].dataset.ref;

let refBox = document.getElementById('refBox');
let refInput = refBox.querySelector('input');

if(ref == "1"){

refBox.classList.remove('d-none');
refInput.setAttribute('required',true);

}else{

refBox.classList.add('d-none');
refInput.removeAttribute('required');

}

});
</script>