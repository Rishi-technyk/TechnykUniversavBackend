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
    <div class="row">
        <div class="col-lg-10">
            <h2>Payment By Razorpay</h2>                 
        </div>
        <div class="col-lg-2">
           <input id="submit-pay" type="submit" onclick="subcribeNow(this);" value="Pay Now" class="btn btn-primary" />
        </div>
    </div><!-- /.row -->
    <input type="hidden" class="reference_no" value="<?= $reference_no ?>">
    <input type="hidden" class="email" value="<?= $student->Email ?>">
    <input type="hidden" class="rollno" value="<?= $student->MemberID ?>">
    <input type="hidden" class="amount" value="<?= $amount ?>">
    <input type="hidden" class="name" value="<?= $student->DisplayName ?>">
    <input type="hidden" class="mobile_number" value="<?= $student->Phone ?>">
    <input type="hidden" class="order_id" value="<?= $order_id ?>">
    <input type="hidden" class="type" value="<?= $type ?>">
    <input type="hidden" class="route" value="<?= route('e-transaction',encrypt($reference_no)) ?>">

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
   <script>
    $(document).ready(function() {
        subcribeNow();
    });

    function subcribeNow()
    {
        var id          = $('.reference_no').val();
        var roll_number = $('.rollno').val();
        var email       = $('.email').val();
        var amount      = $('.amount').val();
        var order_id    = $('.order_id').val();
        var type        = $('.type').val();
        var route       = $('.route').val();
      
        let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        
      // console.log(userdata)
      var options = {
       "key": "rzp_live_nhs37nCQ3ATb5X",
       "amount": amount, // Example: 2000 paise = INR 20
       "name": "Teebooking",
       "description": "Payment",
       "image": "https://teebooking.aepta.in/public/admin/assets/img/logo.png",// COMPANY LOGO
       "order_id": order_id,
       "handler": function (response) {
           console.log('ResData',response);
           
            $.ajax({
               method:'POST',
               url:'{{ route('payment.response') }}',
               data:{
                  _token: CSRF_TOKEN,
                  ReferenceNo : id,
                  type : type,
                  email : email,
                  amount : amount/100,
                  razorpay_payment_id:response.razorpay_payment_id,
                  razorpay_order_id:response.razorpay_order_id,
               },
               // dataType:'JSON',
               success:function(res){
                  console.log('Response',res);
                  window.location.replace(route);
               }
            })
           // AFTER TRANSACTION IS COMPLETE YOU WILL GET THE RESPONSE HERE.
       },
       "prefill": {
           "name": $('.name').val(), // pass customer name
           "email": email,// customer email
           "contact": $('.mobile_number').val(), //customer phone no.
       },
       "notes": {
           "Registration_no": $('.rollno').val(), //customer address 
       },
       "theme": {
           "color": "#15b8f3" // screen color
       }
      };
      // console.log(options);
      var propay = new Razorpay(options);
      propay.open();
   }
   </script>
  </body>
</html>