
<div class="row extra_venue" id="row">
    <hr class="mt-4 mb-2">                                    
    <div class="col-lg-6">
        <div class="form-group">
            <label class="text-muted"> Session </label>
            <select class="form-control session_{{$rand}}" name="session_id[]" onchange="selectSession({{ $rand }})" required>
              <option value="">Select Session</option>
              @foreach($session as $key => $sess)
                <option value="{{ $sess->id }}">{{ $sess->name }}</option>
              @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="form-group">
            <label class="text-muted"> Venue <small> (Capacity <span class="venue_capacity_{{$rand}}"></span>) </small> </label>
            <select class="form-control venue_{{$rand}}" name="vanue_id[]" onchange="selectVanue({{ $rand }})" required>
              <option value="">Select Venue</option>
            </select>
        </div>
    </div>  

    <input type="hidden" name="charges[]" class="form-control charges_{{$rand}}" placeholder="Charge">
    <input type="hidden" name="gst_amount[]" class="form-control gst_amt_{{$rand}}" placeholder="GST Amount">
    <input type="hidden" name="gst_per[]" class="form-control gst_per_{{$rand}}" placeholder="GST">
    <input type="hidden" name="security_deposit[]" class="form-control security_deposit_{{$rand}}">
    <input type="hidden" name="venue_capacity_val[]" class="form-control venue_capacity_val_{{$rand}}">
    <input type="hidden" name="total[]" class="form-control total_{{$rand}}" placeholder="Total">

    <div class="col-lg-1 mt-4 text-right">
        <button type="button" id="DeleteRow" value="{{$rand}}" class="btn-sm btn btn-outline-danger mb-2 mt-3 f-right">Remove</button>
    </div>     

</div>

