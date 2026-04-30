<div class="product-content-box">
    <!-- !!- ===================================== Inner Content Start ======================== -!! -->

    <div class="mng-heading">
        <h4> </h4>
    </div>
    <div class="mng-group-box">
        <div class="mng-group-left">
            <div class="mng-g-heading">
                <h5> Buddies </h5>
                <div class="mng-gl-toggle">
                    <div class="btn-group">

                        <button type="button" class="dropdown-toggle" data-bs-toggle="modal"
                            data-bs-target="#buddyAddModal">
                            ADD BUDDY
                        </button>

                    </div>
                </div>
            </div>
            <div class="mng-gl-data">
                <ul class="mng-gl-list">
                    @foreach(\App\CPU\Helpers::get_buddy_list() as $buddy)
                    <li class="mng-gl-item">
                        <a href="#" class="mng-data">
                            <div class="mng-gl-box">
                                <div class="mng-gl-img">
                                <div class="mng-img mng-img-4">{{ ucfirst(substr($buddy['name'], 0, 1)) }}</div>
                                   
                                </div>
                                <div class="mng-gl-text">
                                    <h1> {{$buddy['name']}}</h1>
                                    <p> {{$buddy['Email']}}</p>
                                </div>
                                <div class="mng-close-btn">
                                    <a onclick="return confirm('Are you sure you want to delete this Buddy?');"
                                        href="{{route('delete-buddy',[$buddy['id']])}}" class="mng-close-btn"> <i
                                            class="fa-solid fa-xmark" style="color: #000000;"> </i> </a>
                                </div>
                            </div>
                        </a>
                    </li>
                    @endforeach




                </ul>
            </div>
        </div>
        <div class="mng-group-right">
            <div class="mng-g-heading">
                <h5> Groups </h5>
                <div class="mng-gl-toggle">
                    <div class="btn-group">
                        <button type="button" class="dropdown-toggle" data-bs-toggle="modal"
                            data-bs-target="#groupModal">
                            CREATE GROUP
                        </button>

                    </div>
                </div>
            </div>
            <div class="mng-ggl-data">
                <ul class="mng-ggl-list">
                    @foreach(\App\CPU\Helpers::get_my_group_list() as $group)
                    <li class="mng-ggl-item">
                        <div class="mng-ggl-box">
                            <div class="mng-ggl-img">
 

                            @if(isset($group->player1_name))
                                <div class="mng-img mng-img-4">{{ ucfirst(substr($group->player1_name, 0, 1)) }}</div>
                            @endif
                            @if(isset($group->player2_name))
                                <div class="mng-img mng-img-1">{{ ucfirst(substr($group->player2_name, 0, 1)) }}</div>
                            @endif
                            @if(isset($group->player3_name))
                                <div class="mng-img mng-img-2">{{ ucfirst(substr($group->player3_name, 0, 1)) }}</div>
                            @endif
                            @if(isset($group->player4_name))
                                <div class="mng-img mng-img-3">{{ ucfirst(substr($group->player4_name, 0, 1)) }}</div>
                            @endif
                            </div>
                            <div class="mng-ggl-text">
                                <h4>{{$group->group_name}}</h4>
                                <ul class="mng-ggl-text-list">
                                    <li class="mng-ggl-text-item">{{$group->player1_name}} </li>
                                    <li class="mng-ggl-text-item"> {{$group->player2_name}} </li>
                                    <li class="mng-ggl-text-item"> {{$group->player3_name}} </li>
                                    <li class="mng-ggl-text-item"> {{$group->player4_name}} </li>
                                </ul>
                            </div>
                            <div class="mng-ggl-close-btn">
                            <a onclick="return confirm('Are you sure you want to delete this Group?');"
                                        href="{{route('delete-group',[$group['id']])}}" class="mng-close-btn"> <i
                                            class="fa-solid fa-xmark" style="color: #000000;"> </i> </a>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- !!- ===================================== Inner Content End ======================== -!! -->
</div>

<!-- The modal structure -->
<div class="modal loading-btn-form" id="buddyAddModal" tabindex="-1" role="dialog" aria-labelledby="buddyAddModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buddyAddModalLabel">Add Buddy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form for player details -->
                <form id="addBuddyForm" method="post" action="{{route('store-buddy')}}">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="player5Name">Enter Name</label>
                            <input type="hidden" id="player5NameSelected" name="buddy_member_id">
                            <input type="text" id="player5Name" class="form-control buddy-list" list="datalistOptions5"
                                autocomplete="off">
                            <datalist id="datalistOptions5">
                            </datalist>
                        </div>
                    </div>
                    <button type="submit" id="buddySubmit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- The modal structure -->
<div class="modal loading-btn-form" id="groupModal" tabindex="-1" role="dialog" aria-labelledby="groupModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupModalLabel">Add Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form for player details -->
                <form id="addGroupForm" method="post" action="{{route('store_group')}}">
                    @csrf
                  
                    <div class="row">
                    <div class="col-md-12    mb-3">
                            <input type="text" name="group_name" class="form-control" value="" required placeholder="Enter Group Name">
                            
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="player6Name">Enter Person 1 Name</label>
                            <input type="hidden" id="player6NameSelected" name="player6_id" value="{{auth()->user()->id}}">
                            <input type="text" id="player6Name" class="form-control buddy-list" list="datalistOptions6"
                                autocomplete="off" value="{{auth()->user()->DisplayName}}/{{auth()->user()->MemberID}}">
                            <datalist id="datalistOptions6">
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="player7Name">Enter Person 2 Name</label>
                            <input type="hidden" id="player7NameSelected" name="player7_id">
                            <input type="text" id="player7Name" class="form-control buddy-list" list="datalistOptions7"
                                autocomplete="off">
                            <datalist id="datalistOptions7">
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="player8Name">Enter Person 3 Name</label>
                            <input type="hidden" id="player8NameSelected" name="player8_id">
                            <input type="text" id="player8Name" class="form-control buddy-list" list="datalistOptions8"
                                autocomplete="off">
                            <datalist id="datalistOptions8">
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="player9Name">Enter Person 4 Name</label>
                            <input type="hidden" id="player9NameSelected" name="player9_id">
                            <input type="text" id="player9Name" class="form-control buddy-list" list="datalistOptions9"
                                autocomplete="off">
                            <datalist id="datalistOptions9">
                            </datalist>
                        </div>
                    </div>
                    <button type="submit" id="groupSubmit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('sub-js')
<script>
$('#addBuddyForm').submit(function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    // Submit form via AJAX
    $.ajax({
        url: $(this).attr('action'),
        method: $(this).attr('method'),
        data: formData,
        success: function(response) {
            toastr.success(response.message);
            window.location.reload();

        },
        error: function(xhr) {
            $html = "Submit";
            var $submitBtn = $('#buddySubmit');
            $submitBtn.prop('disabled', false);
            $submitBtn.html('');
            $submitBtn.html($html);

            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;

                $.each(errors, function(key, value) {
                    toastr.error(value);
                });
            } else if (xhr.status === 400) {

                toastr.error(xhr.responseJSON.error);

            } else {
                toastr.error('An error occurred. Please try again.');
            }
        }
    });

});

$('#addGroupForm').submit(function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    // Submit form via AJAX
    $.ajax({
        url: $(this).attr('action'),
        method: $(this).attr('method'),
        data: formData,
        success: function(response) {
            toastr.success(response.message);
            window.location.reload();

        },
        error: function(xhr) {
            $html = "Submit";
            var $submitBtn = $('#groupSubmit');
            $submitBtn.prop('disabled', false);
            $submitBtn.html('');
            $submitBtn.html($html);

            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;

                $.each(errors, function(key, value) {
                    toastr.error(value);
                });
            } else if (xhr.status === 400) {

                toastr.error(xhr.responseJSON.error);

            } else {
                toastr.error('An error occurred. Please try again.');
            }
        }
    });

});
</script>
@endpush