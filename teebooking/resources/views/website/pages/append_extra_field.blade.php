
<div class="row extra_venue" id="row">
    <hr class="mt-4 mb-2"> 
    <div class="col-lg-12 mt-4 text-right">
        <button type="button" id="DeleteRow" value="{{$rand}}" class="btn-sm btn btn-outline-danger mb-2 f-right"><i class="fa fa-times" aria-hidden="true"></i></button>
    </div>                                   
    <div class="col-lg-2">
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

    <div class="col-lg-2">
        <div class="form-group">
            <label class="text-muted"> Venue <small> (Capacity <span class="venue_capacity_{{$rand}}"></span>) </small> </label>
            <select class="form-control venue_{{$rand}}" name="vanue_id[]" onchange="selectVanue({{ $rand }})" required>
              <option value="">Select Venue</option>
            </select>
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group">
            <label class="text-muted"> Charge </label>
            <input type="text" name="charges[]" class="form-control charges_{{$rand}}" placeholder="Charge" readonly required>
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group">
            <label class="text-muted"> GST(<span class="gst_per_text_{{$rand}}"></span>%) </label>
            <input type="text" name="gst_amount[]" class="form-control gst_amt_{{$rand}}" placeholder="GST Amount" readonly required>
            <input type="hidden" name="gst_per[]" class="form-control gst_per_{{$rand}}" placeholder="GST" readonly>
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group">
            <label class="text-muted"> Security Deposit </label>
            <input type="text" name="security_deposit[]" class="form-control security_deposit_{{$rand}}" placeholder="Security Deposit" readonly>
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group">
            <label class="text-muted"> Total </label>
            <input type="text" name="total[]" class="form-control total_{{$rand}}" placeholder="Total" readonly required>
        </div>
    </div>    

</div>

