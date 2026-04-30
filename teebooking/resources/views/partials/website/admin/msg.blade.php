@if(Session::has('message'))
    <div class="alert alert-success">
        {{session('message')}}
    </div>
@endif

@if(Session::has('error'))
    <div class="alert alert-danger">
        {{session('message')}}
    </div>
@endif