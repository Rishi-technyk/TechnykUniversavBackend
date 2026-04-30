@extends('frontend.layouts.app')

@section('title', 'Table Transactions')

@section('content')

<section class="room-details-section">
    <div class="container">
        <!-- Breadcrumb Section Begin -->
        <div class="card-section-title-box">
            <span class="card-section-bar"></span>
            <h4 class="card-section-title">Table Transactions</h4>
        </div>
        <!-- Breadcrumb Section End -->
        
        <div class="card-section-title-box">
            <span class="card-section-bar"></span>

            <table class="table table-border">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Booking Date</th>
                        <th scope="col">Venue</th>
                        <th scope="col">Meal</th>
                        <th scope="col">Time</th>
                        <th scope="col">Table</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datas as $key => $data)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $data->created_at ? date("d-m-Y", strtotime($data->created_at)) : '' }}</td>
                            <td>{{ $data->venue->name ?? '' }}</td>
                            <td>{{ $data->meal->name ?? '' }}</td>
                            <td>{{ $data->time->time ?? '' }}</td>
                            <td>{{ $data->table->name ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div >
                {!! $datas->links() !!}
            </div>
        </div>

    </div>
</section>

@endsection

@section('script')

@endsection