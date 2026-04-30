  <!-- !!- ===================================== HEADER START HERE ======================== -!! -->
  <header class="header-style-01">
      <nav class="navbar navbar-area navbar-border navbar-padding navbar-expand-lg">
          <div class="container custom-container-one nav-container">
              
              
              <div class="navbar-right-content show-nav-content">
                  <div class="single-right-content">
                      <div class="navbar-right-flex">
                          <div class="navbar-right-btn">
                              <a href="javascript:void(0)"> {{auth()->user()->DisplayName}}</a>
                              <div>Member ID: {{auth()->user()->MemberID}}</div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </nav>
  </header>
  <!-- !!- ===================================== HEADER END HERE ======================== -!! -->