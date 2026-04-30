@extends('layouts.admin_web')
@section('content')
<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
                Venue Pax
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('venue.pax.add') }}"> <button class="btn btn-sm btn-success">Add</button></a>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped" id="example">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Venue Count</th>
              <th scope="col">Min Pax</th>
              <th scope="col">Max Pax</th>
              <th scope="col">Total Venue Rate</th>
              <th scope="col">Message</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
                
                @foreach($datas as $key => $data)
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $data->venue_count }}</td>
                        <td>{{ $data->min_pax }}</td>
                        <td>{{ $data->max_pax }}</td>
                        <td>{{ $data->total_venue_rate }}</td>
                        <td>{{ $data->message }}</td>
                        <td>
                            <a href="{{ route('venue.pax.edit', encrypt($data->id)) }}" class="btn-sm btn btn-success">Edit</a>
                            <a href="{{ route('venue.pax.delete', encrypt($data->id)) }}" onclick="return confirm('Are you sure?')" class="btn-sm btn btn-danger">Delete</a>
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