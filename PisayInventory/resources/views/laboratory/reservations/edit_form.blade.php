<form data-id="{{ $reservation->reservation_id }}">
    @csrf
    <div class="mb-3">
        <label for="laboratory_id" class="form-label">Laboratory</label>
        <select name="laboratory_id" id="laboratory_id" class="form-control" required>
            @foreach($laboratories as $lab)
                <option value="{{ $lab->laboratory_id }}" 
                    {{ $lab->laboratory_id == $reservation->laboratory_id ? 'selected' : '' }}>
                    {{ $lab->laboratory_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label for="reservation_date" class="form-label">Reservation Date</label>
        <input type="date" 
               class="form-control" 
               id="reservation_date" 
               name="reservation_date"
               value="{{ date('Y-m-d', strtotime($reservation->reservation_date)) }}"
               required>
    </div>

    <div class="mb-3">
        <label for="start_time" class="form-label">Start Time</label>
        <input type="time" 
               class="form-control" 
               id="start_time" 
               name="start_time"
               value="{{ date('H:i', strtotime($reservation->start_time)) }}"
               required>
    </div>

    <div class="mb-3">
        <label for="end_time" class="form-label">End Time</label>
        <input type="time" 
               class="form-control" 
               id="end_time" 
               name="end_time"
               value="{{ date('H:i', strtotime($reservation->end_time)) }}"
               required>
    </div>

    <div class="mb-3">
        <label for="purpose" class="form-label">Purpose</label>
        <textarea class="form-control" 
                  id="purpose" 
                  name="purpose" 
                  rows="3" 
                  required>{{ $reservation->purpose }}</textarea>
    </div>

    <div class="mb-3">
        <label for="num_students" class="form-label">Number of Students</label>
        <input type="number" 
               class="form-control" 
               id="num_students" 
               name="num_students"
               value="{{ $reservation->num_students }}"
               min="1">
    </div>

    <div class="mb-3">
        <label for="remarks" class="form-label">Remarks</label>
        <textarea class="form-control" 
                  id="remarks" 
                  name="remarks" 
                  rows="3">{{ $reservation->remarks }}</textarea>
    </div>
</form> 