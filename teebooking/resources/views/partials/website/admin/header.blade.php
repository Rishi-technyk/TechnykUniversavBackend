  <!-- !!- ===================================== HEADER START HERE ======================== -!! -->
  <header class="header-style-01">
      <nav class="navbar navbar-area navbar-border navbar-padding navbar-expand-lg">
          <div class="container custom-container-one nav-container">
              <div class="logo-wrapper">
                  <a href="" class="logo">
                      <img src="{{ asset('public/admin/assets/img/logo.png') }}" style="width:100px;" alt />
                  </a>
              </div>
              <div class="responsive-mobile-menu d-lg-none">
                  <a href="javascript:void(0)" class="click-nav-right-icon">
                      <i class="las la-ellipsis-v"></i>
                  </a>
                  <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                      data-bs-target="#hotel_booking_menu">
                      <span class="navbar-toggler-icon"></span>
                  </button>
              </div>
              <div class="collapse navbar-collapse" id="hotel_booking_menu">
                  
              </div>
              <div class="navbar-right-content show-nav-content">
                  <div class="single-right-content">
                      <div class="navbar-right-flex">
                          <div class="navbar-right-btn">
                              <a href="javascript:void(0)"> {{auth()->user()->DisplayName}}</a>
                              <div>Member ID: {{auth()->user()->MemberID}}</div>
                          </div>
                          <div class="btn-wrapper">
                              <a href="{{route('logout')}}" class="cmn-btn btn-bg-1 radius-10">
                                  Logout
                              </a>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </nav>
  </header>
  <!-- !!- ===================================== HEADER END HERE ======================== -!! -->