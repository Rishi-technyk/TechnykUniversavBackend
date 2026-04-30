@extends('layouts.admin_web')
@section('content')
<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Edit {{ $data->type }}
            </div>
            <div class="col-lg-6">
                <div class="text-end">
                    <a href="{{ route('SOP') }}"><button class="btn btn-sm btn-success">Back</button></a> 
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <form action="{{ route('SOP.update') }}" method="Post">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="row">

                <div class="col-lg-12 mt-4">
                    
                    <div class="form-group">
                        
                        <label>Content</label>

                        <textarea name="content" id="editor" class="form-control" placeholder="Enter Content">{{ $data->content }}</textarea>

                    </div>

                </div>

            </div>

            <div class="text-center mt-4">
                <button class="btn btn-sm btn-success" type="submit">Update</button>
            </div>

        </form>

    </div>

</div>
@push('js')

<script src="https://cdn.ckeditor.com/4.20.2/standard/ckeditor.js"></script>

<script>
    // Initialize CKEditor
    CKEDITOR.replace('editor');
</script>

@endpush()
@endsection