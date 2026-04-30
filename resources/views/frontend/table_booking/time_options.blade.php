<select class="contact-select" name="time_id" id="timSelect" required>
    <option value="">Select Time</option>  
    @foreach($times as $key => $time)
    <option value="{{ $time->id }}">{{ $time->time }}</option>
    @endforeach                                                
</select>