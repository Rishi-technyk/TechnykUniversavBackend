<option>Select Venue</option>
@foreach($vanue as $key => $van)
    @if(checkVenueBlock($van->id, $request->session, $request->function_date))
    <option value="{{ $van->id }}">{{ $van->name }}</option>
    @endif
@endforeach