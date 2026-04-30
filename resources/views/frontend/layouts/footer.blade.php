<footer class="footer-section">
    <div class="container">
        <div class="footer-text">
            <div class="row">
                <div class="col-lg-4">
                    <div class="ft-contact">
                        <h6>Address</h6>
                        <ul>
                            <li>{{ $setting->address }}</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 offset-lg-1">
                    <div class="ft-contact">
                        <h6>Contact Phone</h6>
                        <ul>
                            <li>{{ $setting->phone }}</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 offset-lg-1">
                    <div class="ft-contact">
                        <h6>Contact Email</h6>
                        <ul>
                            <li>{{ $setting->email }}</li>
                        </ul> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="co-text">
                        <p> Copyright &copy;{{ date('Y', strtotime('-1 year')) }}-<script>document.write(new Date().getFullYear());</script> All Rights Reserved By <a href="{{ route('student.dashboard') }}" target="_blank">{{ $setting->project_name }}</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>