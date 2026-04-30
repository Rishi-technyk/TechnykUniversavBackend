@extends('layouts.admin_web')
@section('content')
<section style="background-color: #eee;">
    <div class="py-5">

        <div class="row">
            
            @include('partials.website.admin.side_menu')            

            <div class="col-lg-9">
                <div class="card mb-1 h-100">
                	<div class="card-header">                       
                        
                        <div class="row">
                            <div class="col-lg-6">
                                Users
                            </div>
                            <div class="col-lg-6">
                                <div class="text-end">
                                    <a href="{{ route('staff.add') }}"> <button class="btn btn-sm btn-success">Add</button></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped" id="example">
                          <thead>
                            <tr>
                              <th scope="col">#</th>
                              <th scope="col">Member ID</th>
                              <th scope="col">DisplayName</th>
                              <th scope="col">Role</th>
                              <th scope="col">Email</th>
                              <th scope="col">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                                
                                @foreach($member as $key => $data)
                                    <tr>
                                        <td>{{ ++$key }}</td>
                                        <td>{{ $data->MemberID }}</td>
                                        <td>{{ $data->DisplayName }}</td>
                                        <td>{{ $data->role }}</td>
                                        <td>{{ $data->Email }}</td>
                                        <td>
                                            <a href="{{ route('staff.edit', encrypt($data->id)) }}" class="btn-sm btn btn-success">Edit</a>
                                            <a href="{{ route('staff.delete', encrypt($data->id)) }}" onclick="return confirm('Are you sure?')" class="btn-sm btn btn-danger">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                          </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>
<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')

@endpush()
@endsection