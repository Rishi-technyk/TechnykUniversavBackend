@extends('layouts.admin_web')
@section('content')

<div class="card mb-1 h-100">

    <div class="card-header">                       
                            
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
                Category Master
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('category.master.add') }}"> <button class="btn btn-sm btn-success">Add</button></a>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped" id="example">
            <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Name</th>
                  <th scope="col">Status</th>
                  <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                    
                @foreach($datas as $key => $data)
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $data->Catg_Name }}</td>
                        <td>
                            @if($data->status=='Active')
                                <a href="{{ route('category.master.status', encrypt($data->Code)) }}" class="btn-sm btn btn-outline-success">Active</a>
                            @else
                                <a href="{{ route('category.master.status', encrypt($data->Code)) }}" class="btn-sm btn btn-outline-danger">Inactive</a>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('category.master.edit', encrypt($data->Code)) }}" class="btn-sm btn btn-success">Edit</a>
                            <a href="{{ route('category.master.delete', encrypt($data->Code)) }}" onclick="return confirm('Are you sure?')" class="btn-sm btn btn-danger">Delete</a>
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>

</div>

@push('js')

@endpush()
@endsection