{{-- <header class="header-style-01">

    <nav class="navbar navbar-area navbar-border navbar-expand-lg navbar-dark">

        <div class="container-fluid custom-container-one nav-container">

            <div class="dashboard-icon">

                <div class="sidebar-icon">

                    <i class="las la-bars"></i>

                </div>

            </div>

            <div class="logo-wrapper">

                <a href="index.html" class="logo">

                    <img src="{{ asset('public/member/assets/img/dsoi-logo-name.png') }}" style="width:100px;" alt />

                </a>

            </div>

            <div class="navbar-right-content">

                <div class="single-right-content">

                    <div class="navbar-author">

                        <div class="navbar-author-flex">

                            <div class="navbar-author-thumb">

                                <img src="{{ asset('public/member/assets/img/single-page/author.jpg') }}" alt="img" />

                            </div>

                            <div class="navbar-author-name">

                                <h6 class="navbar-author-name-title d-none d-sm-block">

                                    {{Auth()->user()->DisplayName}}

                                </h6>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </nav>

</header> --}}


<header class="header-style-01">
      <nav
        class="navbar navbar-area navbar-border navbar-expand-lg navbar-dark"
      >
        <div class="container-fluid custom-container-one nav-container">
          <div class="dashboard-icon">
            <div class="sidebar-icon">
              <i class="las la-bars"></i>
            </div>
          </div>
          <div class="logo-wrapper">
            <a href="index.html" class="logo">
                <img src="{{ asset('public/member/assets/img/dsoi-logo-name.png') }}" style="width:100px;" alt />
            </a>
          </div>
          <div class="navbar-right-content">
            <div class="single-right-content">
              <!-- <div class="navbar-author">
                <div class="navbar-author-flex">
                  <div class="navbar-author-thumb">
                    <img src="assets/img/single-page/author.jpg" alt="img" />
                  </div>
                  <div class="navbar-author-name">
                    <h6 class="navbar-author-name-title d-none d-sm-block">
                      Lucifer
                    </h6>
                  </div>
                </div>
              </div> -->
              <div class="navbar-author">

                    <div class="navbar-author-flex">

                        <div class="navbar-author-thumb">

                            <img src="{{ asset('public/member/assets/img/single-page/author.jpg') }}" alt="img" />

                        </div>

                        <div class="navbar-author-name">

                            <h6 class="navbar-author-name-title"> {{Auth()->user()->DisplayName}} </h6>

                        </div>

                    </div>

                    <div class="navbar-author-wrapper">

                        <div class="navbar-author-wrapper-list">

                            <a href="{{ route('dashboard') }}" class="navbar-author-wrapper-list-item"> Dashboard

                            </a>

                            <!-- <a href="{{ route('profile') }}" class="navbar-author-wrapper-list-item"> Profile </a> -->

                            <a href="{{ route('logout') }}" class="navbar-author-wrapper-list-item"> Log Out </a>

                        </div>

                    </div>

                </div>
            </div>
          </div>
        </div>
      </nav>
    </header>



<!-- 
<header class="header-style-01">

    <nav class="navbar navbar-area navbar-border navbar-padding navbar-expand-lg">

        <div class="container custom-container-one nav-container">

            {{-- for mobile sidebar menu--}}

            {{-- <div class="dashboard-icon">

                <div class="sidebar-icon">

                    <i class="las la-bars"></i>

                </div>

            </div> --}}

            <div class="logo-wrapper">

                <a href="index.html" class="logo">

                    <img src="{{ asset('public/member/assets/img/dsoi-logo-name.png') }}" alt />

                </a>

            </div>

            <div class="responsive-mobile-menu d-lg-none">

                <a href="javascript:void(0)" class="click-nav-right-icon">

                    <i class="las la-bars"></i>

                </a>

                {{-- for mobile header menu --}}

                {{-- <button class="navbar-toggler" type="button" data-bs-toggle="collapse"

                    data-bs-target="#hotel_booking_menu">

                    <span class="navbar-toggler-icon"></span>

                </button> --}}

            </div>

            <div class="navbar-right-content show-nav-content">

                <div class="single-right-content">

                    <div class="navbar-author">

                        <div class="navbar-author-flex">

                            <div class="navbar-author-thumb">

                                <img src="{{ asset('public/member/assets/img/single-page/author.jpg') }}" alt="img" />

                            </div>

                            <div class="navbar-author-name">

                                <h6 class="navbar-author-name-title"> {{Auth()->user()->DisplayName}} </h6>

                            </div>

                        </div>

                        <div class="navbar-author-wrapper">

                            <div class="navbar-author-wrapper-list">

                                <a href="{{ route('dashboard') }}" class="navbar-author-wrapper-list-item"> Dashboard

                                </a>

                                <a href="{{ route('profile') }}" class="navbar-author-wrapper-list-item"> Profile </a>

                                <a href="{{ route('logout') }}" class="navbar-author-wrapper-list-item"> Log Out </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </nav>

</header> -->