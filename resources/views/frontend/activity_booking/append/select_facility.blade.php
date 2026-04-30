<div class="col-lg-4 col-sm-12">
    <div class="card facility-card active-card">
        <div class="card-body">
            <h5 class="card-title facility-title">{{ $facility->name }}</h5>
            <p class="card-text facility-sub-title">{{ \Illuminate\Support\Str::limit($facility->short_description,40) }}</p>
        </div>
        <div class="card-footer p-d-0">
            <div class="row">
                <div class="col-lg-6">
                    <p class="text-dark facility-price">₹{{ number_format($facility->charge, 2) }} </p>
                </div>
                <div class="col-lg-6 text-right">
                    <button class="btn-sm btn btn-success fz-13" type="button" disabled>Selected</button>
                </div>
            </div>
        </div>
    </div>
</div>