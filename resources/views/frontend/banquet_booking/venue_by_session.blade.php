<label class="text-muted"> Venue </label>
<select class="contact-select venue_{{ $request->argument }}" name="vanue_id[]" onchange="selectVanue({{$request->argument}}, this.value)" required>
    <option value="">Select Venue</option>  
    @foreach($vanue as $key => $van)
    <option value="{{ $van->id }}">{{ $van->name }}</option>
    @endforeach                                                
</select>