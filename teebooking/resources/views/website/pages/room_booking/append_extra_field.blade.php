
<div class="row" id="row">
    <hr class="mt-4 mb-2">                                    
    <div class="col-lg-12">
                                            
        <table class="table">
            <thead class="thead-light">
                <tr>
                    <th scope="col">Room Category</th>
                    <th scope="col">Available</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">
                        <div class="form-group">
                            <select class="form-control" name="category_id[]">
                                <option value="">Select Room</option>
                                @foreach($category as $cate)
                                <option value="{{ $cate->id }}">{{ $cate->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </th>
                    <td>
                        <b class="room_available_0">0</b>
                        <input type="hidden" class="room_available_input_0">
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

    <div class="col-lg-3">
        <div class="form-group">
            <label class="text-muted"> Occupant Type <b class="text-danger">*</b></label>
            <select class="form-control occupant_{{$rand}}" onchange="occupantType({{$rand}})" name="occupant_type[]" required>
                <option value="">Select Occupant Type</option>
                @foreach($occupan as $key => $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="form-group">
            <label class="text-muted">Guest Name</label>
            <input type="text" class="form-control guest_name_{{$rand}}" name="guest_name[]" placeholder="Guest Name">
        </div>
    </div>

    <div class="col-lg-3">
        <div class="form-group">
            <label class="text-muted">Mobile No.</label>
            <input type="number" class="form-control mobileNo_{{$rand}}" name="mobileNo[]" placeholder="Mobile No.">
        </div>
    </div>

    <div class="col-lg-3">
        <div class="form-group">
            <label class="text-muted"> Email</label>
            <input type="text" class="form-control other_field email_{{$rand}}" value="{{ $member->Email }}" name="email[]" placeholder="Email" readonly>
        </div>
    </div>

    <div class="col-lg-3 mt-4">
        <div class="form-group">
            <label class="text-muted">Adult</label>
            <input type="text" class="form-control adult_{{$rand}}" name="adult[]" placeholder="Adult">
        </div>
    </div>

    <div class="col-lg-3 mt-4">
        <div class="form-group">
            <label class="text-muted">Child</label>
            <input type="text" class="form-control child_{{$rand}}" name="child" placeholder="Child">
        </div>
    </div>
    
    <div class="col-lg-3 mt-4">
        <div class="form-group">
            <label class="text-muted">Rooms to be Booked</label>
            <input type="text" class="form-control room_to_be_booked_{{$rand}}" name="room_to_be_booked" placeholder="Rooms to be Booked">
        </div>
    </div>

    <div class="col-lg-3 mt-4">
        <div class="form-group">
            <label class="text-muted">Tariff/Nite</label>
            <input type="text" class="form-control tariff_nite_{{$rand}}" name="tariff_nite" placeholder="Tariff/Nite">
        </div>
    </div>

    <div class="col-lg-3 mt-4">
        <div class="form-group">
            <label class="text-muted">Total Tariff</label>
            <input type="text" class="form-control total_tariff_{{$rand}}" name="total_tariff" placeholder="Total Tariff">
        </div>
    </div>

    <div class="col-lg-3 mt-4">
        <div class="form-group">
            <label class="text-muted">GST Amount</label>
            <input type="text" class="form-control GST_amt_{{$rand}}" name="GST_amt" placeholder="GST Amount">
        </div>
    </div>

    <div class="col-lg-3 mt-4">
        <div class="form-group">
            <label class="text-muted">Gross Tariff</label>
            <input type="text" class="form-control gross_tariff_{{$rand}}" name="gross_tariff" placeholder="Gross Tariff">
        </div>
    </div>

    <div class="col-lg-3 mt-4">
        <br>
        <button type="button" id="DeleteRow" value="{{$rand}}" class="btn-sm btn btn-danger mt-2">Remove</button>
    </div>

</div>

