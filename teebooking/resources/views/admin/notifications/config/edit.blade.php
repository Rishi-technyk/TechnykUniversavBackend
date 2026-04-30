@extends('layouts.admin')

@section('content')
    <style>
        .btn-success {
            background-color: #012970 !important;
        }
    </style>
        <main id="main" class="main">
            <section class="section dashboard">

                <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0"></h4>

                </div>
                <div class="row gx-2 gx-lg-3">
                    <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                        <div class="card">
                            <div class="card-header">
                                <a href="{{route('notifications')}}" class="float-end btn btn-success">Back</a>
                                <h1 class="page-header-title" style="font-size: 1.25rem;">Edit Notification </h1>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('update', ['id' => $notification->id]) }}" method="post"
                                      style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mt-3">
                                        <label class="input-label"
                                               for="exampleFormControlInput1">Title </label>
                                        <input type="text" name="title" value="{{$notification->title}}" class="form-control" placeholder="New notification"
                                               required>
                                    </div>
                                    <div class="row">
                                        <div class="form-group mt-3 col-md-6">
                                            <label class="input-label" for="exampleFormControlInput1">Date</label>
                                            <input type="date" name="date" value="{{$notification->date}}" class="form-control" required>
                                        </div>
                                        <div class="form-group mt-3 col-md-6">
                                            <label class="input-label" for="exampleFormControlInput1">Time</label>
                                            <input type="time" name="time" value="{{ date('H:i', strtotime($notification->time)) }}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label class="input-label" for="exampleFormControlInput1">Short Intro</label>
                                        <textarea name="short_descriptions" class="form-control" maxlength="100" required>{{$notification->short_descriptions}}</textarea>
                                    </div>

                                    <div class="form-group mt-3" >
                                        <label class="input-label"for="exampleFormControlInput1">Description </label>
                                            <textarea id="description" name="description" class="form-control" required>{{$notification->description}}</textarea>
                                    </div>
                                    <div class="mb-3 mt-3">
                                        <label for="formFileSm" class="form-label">Image</label>
                                        <input class="form-control form-control-sm" id="formFileSm" name="banner" type="file" onchange="previewImage(event)">
                                    </div>
                                     <img  id="imagePreview" src="" alt="Preview" style="display:none; width: 20%; border: 1px solid; border-radius: 10px;">
                                    <hr>
                                    <div class="mb-3 mt-3">
                                        <img   src="{{ url('get-notification-image/' . $notification->image) }}" style=" height: 120px; border: 1px solid; border-radius: 10px;">
                                    </div>
                                    <button type="submit" class="btn btn-success">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
        </section>
    </main>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@5/tinymce.min.js"></script>

    <script>
        ClassicEditor
            .create(document.querySelector('#description'))
            .catch(error => {
                console.error(error);
            });
    </script>

<script>

        function previewImage(event) {
        var input = event.target;
        var reader = new FileReader();

        reader.onload = function () {
            var imagePreview = document.getElementById('imagePreview');
            imagePreview.src = reader.result;
            imagePreview.style.display = 'block';
        };

        reader.readAsDataURL(input.files[0]);
    }
</script>
@endsection
