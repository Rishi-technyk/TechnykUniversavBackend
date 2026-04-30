@extends('layouts.admin')

@section('content')


<style>
    .session-container {
        background: #e9f3ff;
        padding: 15px;
        border-radius: 8px;
    }

    .move-right {
    float: right;
    margin-left: 10px; /* Adjust this margin as needed */
}

</style>
    <main id="main" class="main">
        <section class="section dashboard">
            <div class="container mt-4">
                <h2>Create Tee Booking </h2>
                <form action="{{ route('tee_bookings.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="golf_start_time" class="form-label">Golf Start Time:</label>
                                    <input type="time" id="golf_start_time" name="golf_start_time" class="form-control" required>
                                </div>
                            
                                <div class="col-6 mb-3">
                                    <label for="golf_end_time" class="form-label">Golf End Time:</label>
                                    <input type="time" id="golf_end_time" name="golf_end_time" class="form-control" required>
                                </div>
                                
                                <div class="col-6 mb-3">
                                    <label for="from_date" class="form-label">Start Date:</label>
                                    <input type="date" name="from_date" class="form-control" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="to_date" class="form-label">End Date:</label>
                                    <input type="date" name="to_date" class="form-control" required>
                                </div>
                            </div>
                            
                
                            <!-- TeeSheet Fields Section -->
                            <div id="sessionsSection">
                                <!-- Session fields will be added dynamically here using JavaScript -->
                            </div>
                            
                            <!-- Add Session Button -->
                            <div>
                                <button type="button" class="btn btn-primary mb-3" id="addSession">Add Session</button>
                            </div>
                
                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-success">Create Tee Booking</button>
                        </form>
            </div>
        
            <!-- Include JavaScript to handle dynamic addition of session fields -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            
            <script>
                $(document).ready(function() {
                    const addSessionButton = $('#addSession');
                    const sessionsSection = $('#sessionsSection');
            
                    let sessionIndex = 0;
                    
                    $(document).on('change', '.session-dropdown', function() {
                        const selectedIndex = this.selectedIndex;
                        const selectedOption = this.options[selectedIndex];
            
                        // Get start and end time from the data attributes
                        const startTime = selectedOption.getAttribute('data-start').split(':').slice(0, 2).join(':'); // Extract hours and minutes
                        const endTime = selectedOption.getAttribute('data-end').split(':').slice(0, 2).join(':'); // Extract hours and minutes

                        // Update hidden input values
                        $(this).siblings('.session-start-time').val(startTime);
                        $(this).siblings('.session-end-time').val(endTime);
                        var id = $(this).attr('id');
                        // Update the label for session categories
                        getCategoriesLabel(selectedOption,id);
                    });
                    
                    function getCategoriesLabel(selectedOption,id) {
                        // Extract session categories from the selected option
                        const sessionCategories = $(selectedOption).data('categories');

                        // Format the categories label
                        const formattedCategories = sessionCategories.map(category => category.category_type_Code).join(', ');
                        $.ajax({
                            url: `/api/category-types?codes=${formattedCategories}`,
                            type: 'GET',
                            success: function (response) {
                                // Handle the API response
                                $('#session_category'+id).text(response.categoryTypeValues);
                                //currentChild.siblings('label').text(response.categoryTypeValues);

                            },
                            error: function (error) {
                                // Handle errors
                                console.error(error);
                            }
                        });
                    }

            
                    addSessionButton.click(function() {
                        // Add Session fields dynamically using jQuery
                        const sessionFields = `
                            <div class="row session-container mb-3 ">
                                
                            <div class="" style="text-align-last:end;">
                            <i class="fa fa-times text-danger" aria-hidden="true"></i>
                                       
                                </div>
                               
                            
                                <div class="mb-3">
                               
                                    <label for="sessions[${sessionIndex}][session_id]" class="form-label">Session:</label>
                                    <select name="sessions[${sessionIndex}][session_id]" id="${sessionIndex}" class="form-select session-dropdown" required>
                                        <option value="" disabled selected>Select Session</option>
                                        @foreach($sessions as $session)
                                            <option value="{{ $session->id }}" data-start="{{ $session->start_time }}" data-end="{{ $session->end_time }}" data-categories="{{ json_encode($session->sessionCategories) }}">{{ $session->session_name }} ({{ $session->start_time }} - {{ $session->end_time }})</option>
                                        @endforeach
                                    </select>
                                    <label class="form-label small">Session Categories: </label> 
                                    <i><label id="session_category${sessionIndex}" class="form-label small session_category${sessionIndex}"></label></i>
                
                                    <!-- Hidden input fields for session start and end time -->
                                    <input type="hidden" name="sessions[${sessionIndex}][start_time]" id="sessionStartTime${sessionIndex}" class="session-start-time" value="">
                                    <input type="hidden" name="sessions[${sessionIndex}][end_time]" id="sessionEndTime${sessionIndex}" class="session-end-time" value="">
                                </div>
                
                                <div class="col-6 mb-3">
                                    <label for="sessions[${sessionIndex}][slot_interval]" class="form-label">Slot Interval (In Minutes):</label>
                                    <input type="number" name="sessions[${sessionIndex}][slot_interval]" class="form-control" required>
                                </div>
                
                                <div class="col-6 mb-3">
                                    <label for="sessions[${sessionIndex}][tee_off_hole_id]" class="form-label">Tee Off Hole:</label>
                                    <select name="sessions[${sessionIndex}][tee_off_hole_id]" class="form-select" required>
                                        @foreach($teeHoles as $teeHole)
                                            <option value="{{ $teeHole->id }}">{{ $teeHole->hole_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        `;
            
                        sessionsSection.append(sessionFields);
                        sessionIndex++;
                    });
            
                    // Form submission
                    $('form').submit(function(event) {
                        // Check if at least one session is added
                        if (sessionIndex === 0) {
                            alert('Please add at least one session.');
                            return false; // Prevent form submission
                        }
            
                        // Your additional validation logic for start and end times
                        const startTime = $('#golf_start_time').val();
                        const endTime = $('#golf_end_time').val();
            
                        if (startTime >= endTime) {
                            alert('End time must be greater than start time.');
                            return false; // Prevent form submission
                        }
                        
                        // Custom client-side validation
                        $('.session-dropdown').each(function(index, element) {
                            const sessionStartTime = $(element).siblings('.session-start-time').val();
                            const sessionEndTime = $(element).siblings('.session-end-time').val();
                            const golfStartTime = $('#golf_start_time').val();
                            const golfEndTime = $('#golf_end_time').val();
            
                            if (sessionStartTime < golfStartTime || sessionEndTime > golfEndTime) {
                                alert('Session time does not match golf start and end time.');
                                event.preventDefault(); // Prevent form submission
                                return false;
                            }
                        });
                    });

                    $(document).on('click', '.fa-times', function() {
                       
                        $(this).closest('.session-container').remove();
                    });
                });
            </script>

        </section>
    </main>
@endsection
