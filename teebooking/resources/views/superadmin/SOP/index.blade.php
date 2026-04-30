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
                SOP
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped" id="example">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Type</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
                
                @foreach($datas as $key => $data)
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $data->type }}</td>
                        <td>
                            <a href="{{ route('SOP.edit', encrypt($data->id)) }}" class="btn-sm btn btn-success">Edit</a>
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