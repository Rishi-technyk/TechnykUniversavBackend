@extends('backend.layouts.app')

@section('title', 'Admin | Role Create')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Role Create</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.roles') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="container-fluid">
            
            <div class="card card-outline card-info">

                <div class="card-body">
                    <form action="{{ route('admin.role.store') }}" method="post">
                        @csrf
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter role name" required>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-12">
                                
                                <table class="permissionTable table">

                                    <th>Section</th>
                                    <th>
                                        <label>
                                        <!-- <input id="selectAll" onclick="selectAllPermission()" type="checkbox"> -->
                                            Select All
                                        </label>
                                    </th>
                                    <th>Available permissions</th>

                                    <tbody class="role-permission">
                                        @foreach($custom_permission as $key => $group)
                                        <tr>
                                            <td><b>{{ ucfirst($key) }}</b></td>
                                            <td width="30%">
                                                <input class="selectall" onclick="selectAll(this.value)" value="{{$key}}" type="checkbox">
                                                Select All
                                            </td>

                                            <td>
                                                @forelse($group as $permission)
                                                    
                                                    @if(in_array($permission->id, $permissionIds))

                                                        <input name="permissions[]" class="permissioncheckbox {{$key}}" type="checkbox" value="{{$permission->name}}" checked>

                                                        &nbsp; {{ucfirst($permission->name)}} &nbsp;&nbsp;

                                                    @else

                                                        <input name="permissions[]" class="permissioncheckbox {{$key}}" type="checkbox" value="{{$permission->name}}">

                                                        &nbsp; {{ucfirst($permission->name)}} &nbsp;&nbsp;

                                                    @endif

                                                @empty

                                                    No permission in this group !

                                                @endforelse

                                            </td>

                                        </tr>

                                        @endforeach

                                    </tbody>

                                </table>
                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-info">Create Role</button>
                        </div>
                    </form>
                </div>
                <!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>

@endsection

@section('script')
<script>

    function selectAll(argument) {

        var cls = "."+argument;

        if($(cls).prop('checked')==true){

            $(cls).prop('checked', false);

        } else {

            $(cls).prop('checked', true); 

        }

    }

</script>
@endsection