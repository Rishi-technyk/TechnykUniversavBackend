@extends('layouts.admin_web')
@section('content')

<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
                Room Charges
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('room.charges.master.add') }}"> <button class="btn btn-sm btn-success">Add</button></a>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped" id="example">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Room</th>
              <th scope="col">Category</th>
              <th scope="col">Cate. Type</th>
              <th scope="col">Occupant</th>
              <th scope="col">Charges/Nite</th>
              <th scope="col">No. Of Rooms</th>
              <th scope="col">Max. Nites</th>
              <th scope="col">Status</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
                
                @foreach($datas as $key => $data)
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $data->room_category?$data->room_category->name:'NA' }}</td>
                        <td>{{ $data->category?$data->category->Catg_Name:'NA' }}</td>
                        <td>{{ $data->categoryType?$data->categoryType->CategoryType:'NA' }}</td>
                        <td>{{ $data->occupant?$data->occupant->name:'NA' }}</td>
                        <td>{{ $data->charges_nite }}</td>
                        <td>{{ $data->no_of_booked_room }}</td>
                        <td>{{ $data->max_no_of_nites }}</td>
                        <td>
                            @if($data->status=='Active')
                                <a href="{{ route('room.charges.master.status', encrypt($data->id)) }}" class="btn-sm btn btn-outline-success">Active</a>
                            @else
                                <a href="{{ route('room.charges.master.status', encrypt($data->id)) }}" class="btn-sm btn btn-outline-danger">Inactive</a>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('room.charges.master.edit', encrypt($data->id)) }}" class="btn-sm btn btn-success">Edit</a>
                            <a href="{{ route('room.charges.master.delete', encrypt($data->id)) }}" onclick="return confirm('Are you sure?')" class="btn-sm btn btn-danger">Delete</a>
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