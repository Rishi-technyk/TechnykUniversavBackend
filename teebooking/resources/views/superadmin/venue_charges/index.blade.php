@extends('layouts.admin_web')
@section('content')
<style>
    table td {
        text-align: center !important;
    }
</style>

<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
                Venue Charge
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('venue.charge.add') }}"> <button class="btn btn-sm btn-success">Add</button></a>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped" id="example">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Venue</th>
              <th scope="col">Session</th>
              <th scope="col">Occupant</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
                
                @foreach($datas as $key => $data)
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $data->venue->name ?? '' }}</td>
                        <td>{{ $data->session?$data->session->name:'' }}</td>
                        <td>{{ $data->occupant->name ?? '' }}</td>
                        <td>
                            <a href="{{ route('venue.charge.edit', encrypt($data->id)) }}" class="btn-sm btn btn-success">Edit</a>
                            <a href="{{ route('venue.charge.delete', encrypt($data->id)) }}" onclick="return confirm('Are you sure?')" class="btn-sm btn btn-danger">Delete</a>
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