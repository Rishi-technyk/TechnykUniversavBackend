@extends('layouts.admin')

@section('content')


    <main id="main" class="main">
        
     
        <!-- End Page Title -->

        <section class="section dashboard">

<div class="card">
            <div class="card-body">
              <h5 class="card-title">Session Manage</h5>

              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered" id="borderedTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#bordered-home" type="button" role="tab" aria-controls="home" aria-selected="true">Rental Clubs</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#bordered-profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Caddies</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#bordered-contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Transportation</button>
                </li>
              </ul>
              <div class="tab-content pt-2" id="borderedTabContent">
                <div class="tab-pane fade show active" id="bordered-home" role="tabpanel" aria-labelledby="home-tab">
                 @include('admin.tee.rental_clubs.index')
                </div>
                <div class="tab-pane fade" id="bordered-profile" role="tabpanel" aria-labelledby="profile-tab">
                @include('admin.tee.caddies.index')
                </div>
                <div class="tab-pane fade" id="bordered-contact" role="tabpanel" aria-labelledby="contact-tab">
                @include('admin.tee.transportation.index')
                </div>
              </div><!-- End Bordered Tabs -->

            </div>
          </div>

          </section>
    </main>
    @push('js')
        
    
    <script>
        $(document).on('change', '.status', function () {
            var id = $(this).attr("id");
            if ($(this).prop("checked") == true) {
                var status = 1;
            } else if ($(this).prop("checked") == false) {
                var status = 0;
            }
            //alert(status);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('tee-session-times.status')}}",
                method: 'POST',
                data: {
                    id: id,
                    status: status
                },
                success: function (data) {
                    console.log(data)
                    if (data.success == true) {
                        toastr.success('Status updated successfully');
                    } else {
                        toastr.error('Status updated failed. Product must be approved');
                        location.reload();
                    }
                }
            });
        });
        </script>
        @endpush
@endsection