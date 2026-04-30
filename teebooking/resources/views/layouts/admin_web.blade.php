<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('constants.APP_SHORT_NAME') }}</title>
    <meta content="" name="description" />
    <meta content="" name="keywords" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('public/web/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('public/web/css/line-awesome.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('public/web/css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('public/web/css/newstyle.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Old Datatable -->
    <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css"> -->

    <!-- New Datatable -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css"> 
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css">   
     <link rel="icon" href="https://aepta.in/wp-content/uploads/2023/08/cropped-aptalogo-32x32.png" sizes="32x32" /> 

</head>




<body>
    <style>
        .btn-button {
            background-color: #2E4374;
            border: none;
            color: white;
            padding: 8px 8px;
            text-align: left;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            width: 100%;
        }

        .active-btn {
            background-color: #04AA6D !important;
        }
        table {
                width: 100% !important;
        }

        table th{
            text-align: center !important;
        }

        /* Datatable CSS */

        .dt-layout-start, .dt-column-order {
            display: none !important;
        }

        .paging_simple_numbers, .dataTables_info {
            font-size: 10px !important;
        }

        input[type=text], input[type=email], input[type=file], input[type=search], input[type=number], input[type=date], textarea {
            width: 100% !important;
            padding: 13px 10px !important;
            box-sizing: border-box !important;
            border: solid 1px !important;
            border-radius: .25rem !important;
        }

        .buttons-excel {
            display: none !important;
        }

        @media (min-width: 992px) {
            .col-lg-3 {
                flex: 0 0 auto;
                width: 20% !important;
            }
        }

        .sideButton {
            float: left;
            margin-right: 1%;
        }

        .categoriesSideBar {
          display: flex;
          flex-direction: row-reverse;
          align-items: center;
          padding-top: 10px;
          padding-bottom: 10px;
          width: 0;
        }

        .categoriesSideBar--is-open {
          width: 290px;
        }

        .middlePart {
          display: flex;
          flex-direction: row;
        }

        .gigsSpace {
            width: 100%;
            padding: 1%;
        }
    </style>
    <!-- ======= Header ======= -->
    @include('partials.website.admin.header')
    <!-- End Header -->

    <!-- !!- ===================================== Main Start ======================== -!! -->
    <section style="background-color: #eee;">

        <div class="row">
                
                
            <div class="middlePart">
              
                <div class="categoriesSideBar categoriesSideBar--is-open" id="categoriesSideBar">
                    @include('partials.website.admin.side_menu') 
                </div>
              
                <div class="gigsSpace">          

                    @yield('content')
                    
                </div>

            </div>

        </div>

    </section>
   
        

    <!-- !!- =====================================  Main End ======================== -!! -->

    <!-- ======= Footer ======= -->
    @include('partials.website.admin.footer')
    <div class="modal loading-btn-form" id="otpModal" tabindex="-1" role="dialog" aria-labelledby="otpModalLabel"
    aria-hidden="true">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="otpModalLabel">Your OTP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form for player details -->
              
                    <div class="row">
                        <div class="col-md-12 p-3 text-center">
                            <h4 id="otpLabel"></h4>
                           
                        </div>
                       
                       
                    </div>
            </div>
        </div>
    </div>
</div>
    <!-- End Footer -->

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('public/web/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{ asset('public/web/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{ asset('public/web/js/main.js')}}"></script>
    <script src="https://unpkg.com/dayjs@1.8.21/dayjs.min.js"></script>

    <script>
    $('.head').click(function() {
        $(this).toggleClass('active');
        $(this).parent().find('.arrow').toggleClass('arrow-animate');
        $(this).parent().find('.content').slideToggle(280);
    });
    $('.otpModalClass').click(function() {
        $.ajax({
             url: "{{route('get-otp')}}",
             method: "get",
             success: function(response) {
                $("#otpLabel").text(response);
                $('#otpModal').modal('show');
                //  toastr.success(response
                //      .message);
                // window.location.reload();
                // $('#bookNowModal').modal('hide');
             }
        });
    
    });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <!-- <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script> -->

    <script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#example').DataTable( {
                dom: 'Bfrtip',
                buttons: [
                    'excel'
                ],
                scrollX: true,
                pageLength: 100
            } );
        } );

        // buttons: [
        //             'copy', 'csv', 'excel', 'pdf', 'print'
        //         ]
    </script>

    <script>
        const sideBarToggle = document.querySelector('#sideButton')
       
        sideBarToggle.addEventListener('click', function() {
          const categoriesSideBar = document.querySelector('#categoriesSideBar')

          categoriesSideBar.classList.toggle('categoriesSideBar--is-open')
        })
    </script>


    @if ($message = Session::get('error'))
    <script>
    toastr.error('{{ $message }}');
    </script>
    @endif
    @if ($message = Session::get('success'))
    <script>
    toastr.success('{{ $message }}');
    </script>
    @endif

    @if ($messages = Session::get('errors'))
    @foreach($messages->all() as $message)
        <script>
            toastr.error('{{ $message }}');
        </script>
    @endforeach
    @endif


@stack('js')
@stack('sub-js')
</body>

</html>