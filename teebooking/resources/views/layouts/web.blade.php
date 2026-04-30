<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LGC</title>
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
    <!-- <link rel="icon" href="https://aepta.in/wp-content/uploads/2023/08/cropped-aptalogo-32x32.png" sizes="32x32" /> -->
        

</head>




<body onLoad="noBack();" onpageshow="if (event.persisted) noBack();" onUnload="">
    <!-- ======= Header ======= -->
    @include('partials.website.header')
    <!-- End Header -->

    <!-- !!- ===================================== Main Start ======================== -!! -->
    <main id="main">
        @yield('content')
    </main>
    <!-- !!- =====================================  Main End ======================== -!! -->

    <!-- ======= Footer ======= -->
    @include('partials.website.footer')
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
        
        // Show the modal
       
    
    });

    // Cleander Start Here 
    // let currentDate = dayjs();
    // let daysInMonth = dayjs().daysInMonth();
    // let firstDayPosition = dayjs().startOf("month").day();
    // let monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October",
    //     "November", "December"
    // ];
    // let weekNames = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    // let dateElement = document.querySelector("#calendar .calendar-dates");
    // let calendarTitle = document.querySelector(".calendar-title-text");
    // let nextMonthButton = document.querySelector("#nextMonth");
    // let prevMonthButton = document.querySelector("#prevMonth");
    // let dayNamesElement = document.querySelector(".calendar-day-name");
    // let todayButton = document.querySelector("#today");
    // let dateItems = null;
    // let newMonth = null;
    // weekNames.forEach(function(item) {
    //     dayNamesElement.innerHTML += `<div>${item}</div>`;
    // });

   /* function plotDays() {
        let count = 1;
        dateElement.innerHTML = "";

        let prevMonthLastDate = currentDate.subtract(1, "month").endOf("month").$D;
        let prevMonthDateArray = [];
        for (let p = 1; p < firstDayPosition; p++) {
            prevMonthDateArray.push(prevMonthLastDate--);
        }
        prevMonthDateArray.reverse().forEach(function(day) {
            dateElement.innerHTML += `<button class="calendar-dates-day-empty">${day}</button>`;
        });

        for (let i = 0; i < daysInMonth; i++) {
            dateElement.innerHTML += `<button class="calendar-dates-day">${count++}</button>`;
        }

        let diff =
            42 - Number(document.querySelector(".calendar-dates").children.length);
        let nextMonthDates = 1;
        for (let d = 0; d < diff; d++) {
            document.querySelector(
                ".calendar-dates"
            ).innerHTML += `<button class="calendar-dates-day-empty">${nextMonthDates++}</button>`;
        }
        calendarTitle.innerHTML = `${monthNames[currentDate.month()]
        } - ${currentDate.year()}`;
    }

    function highlightCurrentDate() {
        dateItems = document.querySelectorAll(".calendar-dates-day");
        if (dateElement && dateItems[currentDate.$D - 1]) {
            dateItems[currentDate.$D - 1].classList.add("today-date");
        }
    }
    nextMonthButton.addEventListener("click", function() {
        newMonth = currentDate.add(1, "month").startOf("month");
        setSelectedMonth();
    });
    prevMonthButton.addEventListener("click", function() {
        newMonth = currentDate.subtract(1, "month").startOf("month");
        setSelectedMonth();
    });
    todayButton.addEventListener("click", function() {
        newMonth = dayjs();
        setSelectedMonth();
        setTimeout(function() {
            highlightCurrentDate();
        }, 50);
    });

    function setSelectedMonth() {
        daysInMonth = newMonth.daysInMonth();
        firstDayPosition = newMonth.startOf("month").day();
        currentDate = newMonth;
        plotDays();
    }
    plotDays();
    setTimeout(function() {
        highlightCurrentDate();
    }, 50);
    // Cleander End Here 


    $(document).ready(function() {
        var move = "255px";
        var activeLeftPosition =
            $("div.active").val() !== undefined ?
            (activeLeftPosition = $("div.active").position().left) :
            (activeLeftPosition = 0);
        var activeWidth = $("div.active").width();
        var listWidth = $(".timeline-list").width();
        var center = activeLeftPosition + activeWidth / 2 - listWidth / 2;
        $(".timeline-list").animate({
            scrollLeft: "+=" + center
        }, "slow");

        $("div.active")
            .next("div.timeline-item")
            .css("border-left-width", "0");

        $(".prev-btn").click(function() {
            $(".timeline-list").animate({
                scrollLeft: "-=" + move
            });
        });

        $(".next-btn").click(function() {
            $(".timeline-list").animate({
                scrollLeft: "+=" + move
            });
        });
    });*/
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>


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

    <script type="text/javascript">
        // window.history.forward();
        function noBack()
        {
            window.history.forward();
        }
    </script>


@stack('js')
@stack('sub-js')
</body>

</html>