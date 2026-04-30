@extends('layouts.admin')

@section('content')

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Event Banners</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Events</a></li>
                <li class="breadcrumb-item active">Banners</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">

        <div class="card">
            <div class="card-body">

                {{-- Search --}}
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            class="form-control"
                            placeholder="Search event name..."
                        >
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary">
                            Search
                        </button>
                    </div>

                    @if(request('search'))
                    <div class="col-md-2">
                        <a href="{{ url()->current() }}" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                    @endif
                </form>


                {{-- Table --}}
                <div class="table-responsive">

                    <table class="table table-bordered table-striped align-middle">

                        <thead>
                            <tr>
                                <th width="60">#</th>
                                <th width="180">Banner</th>
                                <th>Event Name</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                        @forelse($events as $event)

                            <tr>

                                <td>
                                    {{ $loop->iteration + ($events->currentPage()-1)*$events->perPage() }}
                                </td>

                                <td>
                                    @if($event->banner)
                                       <img src="{{ asset('public/banners/'.$event->banner) }}" width="140">
                                    @else
                                        <span class="text-muted">No Banner</span>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $event->name }}</strong>
                                </td>

                                <td>

                                  <button 
    class="btn btn-sm btn-primary editBannerBtn"
    data-id="{{ $event->id }}"
    data-name="{{ $event->name }}"
>
    <i class="bi bi-pencil"></i> Edit
</button>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center">
                                    No Events Found
                                </td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $events->withQueryString()->links() }}
                </div>

            </div>
        </div>

    </section>

</main>
<div class="modal fade" id="bannerModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="bannerForm" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Update Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="event_id">

                    <div class="mb-3">
                        <label>Event Name</label>
                        <input type="text" id="event_name" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Upload Banner</label>
                        <input type="file" name="banner" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Update Banner</button>
                </div>

            </div>
        </form>
    </div>
</div>
<script>

document.querySelectorAll('.editBannerBtn').forEach(btn => {

    btn.addEventListener('click', function(){

        let id = this.dataset.id;
        let name = this.dataset.name;

        document.getElementById('event_id').value = id;
        document.getElementById('event_name').value = name;

        let form = document.getElementById('bannerForm');
       form.action = "{{ url('admin/events/banner/update') }}/" + id;

        new bootstrap.Modal(document.getElementById('bannerModal')).show();

    });

});

</script>
@endsection