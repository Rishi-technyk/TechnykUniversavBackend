<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Payment By Razorpay</title>
  </head>
  <body>
    <form method="POST" action="https://api.razorpay.com/v1/checkout/embedded" class="form">
      <!--<input type="hidden" name="key_id" value="rzp_test_TfccAiRNRpb2hA"/>-->
       <input type="hidden" name="key_id" value="rzp_live_bTbzKWioHJFVoP"/> 
      <input type="hidden" name="amount" value="{{ $amount }}"/>
      <input type="hidden" name="currency" value="INR"/>
      <input type="hidden" name="order_id" value="{{ $order_id }}"/>
      <input type="hidden" name="name" value="{{ $student->DisplayName }}"/>
      <input type="hidden" name="description" value="{{ $student->DisplayName }}"/>
      <input type="hidden" name="image" value="https://teebooking.aepta.in/public/admin/assets/img/logo.png"/>
      <input type="hidden" name="prefill[name]" value="{{ $student->DisplayName }}"/>
      <input type="hidden" name="prefill[contact]" value="{{ $mobile }}"/>
      <input type="hidden" name="prefill[email]" value="{{ $student->Email }}"/>
      <input type="hidden" name="notes[shipping address]" value="{{ $student->DisplayName }}"/>
      <input type="hidden" name="callback_url" value="{{URL::to('/')}}/razorpay/callback"/>
      <input type="hidden" name="cancel_url" value="{{URL::to('/')}}/razorpay/cancel"/>
      <input type="hidden" name="notes[MemberID]" value="{{ $student->MemberID }}"/>
      <input type="hidden" name="notes[SC_ID]" value="{{ $student->SC_ID }}"/>
      <input type="hidden" name="notes[PaymentType]" value="{{ $type }}"/>
      <!-- <button>Submit</button> -->
    </form>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
   
   <script>
    $(document).ready(function() {
        $('.form').submit();
    });
   </script>
  </body>
</html>