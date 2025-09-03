@extends('admin.layout')

@section('title', 'Create New Class')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.classes.index') }}" class="text-gray-400 hover:text-white mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Create New Class</h1>
    </div>

    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <form id="class-form" action="{{ route('admin.classes.store') }}" method="POST" class="px-6 py-6 space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-300">Class Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                       class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                       placeholder="e.g., Morning HIIT" required>
                @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-300">Description</label>
                <textarea name="description" id="description" rows="3" 
                          class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                          placeholder="Brief description of the class...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="instructor_id" class="block text-sm font-medium text-gray-300">Instructor</label>
                    <select name="instructor_id" id="instructor_id" 
                            class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                        <option value="">Select Instructor</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}" {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('instructor_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="max_spots" class="block text-sm font-medium text-gray-300">Max Spots</label>
                    <input type="number" name="max_spots" id="max_spots" value="{{ old('max_spots') }}" min="1" max="50"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('max_spots')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-300">Price (£)</label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" min="0" step="0.01"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('price')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="class_date" class="block text-sm font-medium text-gray-300">Class Date</label>
                    <input type="date" name="class_date" id="class_date" value="{{ old('class_date') }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('class_date')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300">Start Time</label>
                    <input type="hidden" name="start_time" id="start_time_hidden" value="{{ old('start_time') }}">
                    <div class="mt-1 grid grid-cols-3 gap-2">
                        <input type="number" id="start_hour" min="1" max="12" placeholder="HH" class="block w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                        <input type="number" id="start_minute" min="0" max="59" placeholder="MM" class="block w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                        <select id="start_ampm" class="block w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <div id="start_preview" class="font-mono text-lg text-gray-200">--:-- --</div>
                        <div class="space-x-2">
                            <button type="button" id="start_pick" class="px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded">Pick</button>
                            <button type="button" id="start_minus_15" class="px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded">-15m</button>
                            <button type="button" id="start_plus_15" class="px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded">+15m</button>
                            <button type="button" id="start_now" class="px-2 py-1 bg-primary hover:bg-purple-400 text-white rounded">Now</button>
                        </div>
                    </div>
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300">End Time</label>
                    <input type="hidden" name="end_time" id="end_time_hidden" value="{{ old('end_time') }}">
                    <div class="mt-1 grid grid-cols-3 gap-2">
                        <input type="number" id="end_hour" min="1" max="12" placeholder="HH" class="block w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                        <input type="number" id="end_minute" min="0" max="59" placeholder="MM" class="block w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                        <select id="end_ampm" class="block w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <div id="end_preview" class="font-mono text-lg text-gray-200">--:-- --</div>
                        <div class="space-x-2">
                            <button type="button" id="end_pick" class="px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded">Pick</button>
                            <button type="button" id="end_minus_15" class="px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded">-15m</button>
                            <button type="button" id="end_plus_15" class="px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded">+15m</button>
                            <button type="button" id="end_now" class="px-2 py-1 bg-primary hover:bg-purple-400 text-white rounded">Now</button>
                        </div>
                    </div>
                    @error('end_time')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-center">
                    <input type="checkbox" name="active" id="active" value="1" {{ old('active', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-primary focus:ring-primary border-gray-600 bg-gray-700 rounded">
                    <label for="active" class="ml-2 block text-sm text-gray-300">
                        Active Class
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="recurring_weekly" id="recurring_weekly" value="1" {{ old('recurring_weekly') ? 'checked' : '' }}
                           class="h-4 w-4 text-primary focus:ring-primary border-gray-600 bg-gray-700 rounded">
                    <label for="recurring_weekly" class="ml-2 block text-sm text-gray-300">
                        Recurring Weekly
                    </label>
                </div>
            </div>

            <div id="recurring_days_section" class="hidden">
                <label class="block text-sm font-medium text-gray-300 mb-2">Select Days for Weekly Recurrence</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @php
                        $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
                    @endphp
                    @foreach($days as $value => $label)
                        <div class="flex items-center">
                            <input type="checkbox" name="recurring_days[]" id="day_{{ $value }}" value="{{ $value }}"
                                   {{ in_array($value, old('recurring_days', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-600 bg-gray-700 rounded">
                            <label for="day_{{ $value }}" class="ml-2 block text-sm text-gray-300">
                                {{ $label }}
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('recurring_days')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <script>
                document.getElementById('recurring_weekly').addEventListener('change', function() {
                    const recurringDaysSection = document.getElementById('recurring_days_section');
                    if (this.checked) {
                        recurringDaysSection.classList.remove('hidden');
                    } else {
                        recurringDaysSection.classList.add('hidden');
                        // Uncheck all day checkboxes
                        const dayCheckboxes = document.querySelectorAll('input[name="recurring_days[]"]');
                        dayCheckboxes.forEach(checkbox => checkbox.checked = false);
                    }
                });

                // Show/hide on page load if already checked
                if (document.getElementById('recurring_weekly').checked) {
                    document.getElementById('recurring_days_section').classList.remove('hidden');
                }

                // -------- Time Inputs (Manual HH:MM with AM/PM) --------
                function prefillTime(hiddenId, hId, mId, apId) {
                    const val = (document.getElementById(hiddenId)?.value || '').trim();
                    if (!val) return;
                    const [hh, mm] = val.split(':');
                    if (hh === undefined || mm === undefined) return;
                    let hour = parseInt(hh, 10);
                    const minute = parseInt(mm, 10);
                    let ampm = 'AM';
                    if (hour === 0) { hour = 12; ampm = 'AM'; }
                    else if (hour === 12) { ampm = 'PM'; }
                    else if (hour > 12) { hour -= 12; ampm = 'PM'; }
                    document.getElementById(hId).value = isNaN(hour) ? '' : hour;
                    document.getElementById(mId).value = isNaN(minute) ? '' : String(minute).padStart(2, '0');
                    document.getElementById(apId).value = ampm;
                }

                function compose24h(h, m, ap) {
                    let hour = parseInt(h || '0', 10);
                    let minute = parseInt(m || '0', 10);
                    if (isNaN(hour) || isNaN(minute) || hour < 1 || hour > 12 || minute < 0 || minute > 59) return '';
                    if (ap === 'AM') {
                        if (hour === 12) hour = 0;
                    } else {
                        if (hour !== 12) hour += 12;
                    }
                    return `${String(hour).padStart(2,'0')}:${String(minute).padStart(2,'0')}`;
                }

                // Prefill from old hidden values
                prefillTime('start_time_hidden', 'start_hour', 'start_minute', 'start_ampm');
                prefillTime('end_time_hidden', 'end_hour', 'end_minute', 'end_ampm');

                // Compose on submit
                document.getElementById('class-form').addEventListener('submit', function(e) {
                    const start = compose24h(
                        document.getElementById('start_hour').value,
                        document.getElementById('start_minute').value,
                        document.getElementById('start_ampm').value
                    );
                    const end = compose24h(
                        document.getElementById('end_hour').value,
                        document.getElementById('end_minute').value,
                        document.getElementById('end_ampm').value
                    );
                    if (!start || !end) {
                        // Let backend validation handle errors, but ensure fields exist
                        // Alternatively, prevent and show a message.
                    }
                    document.getElementById('start_time_hidden').value = start;
                    document.getElementById('end_time_hidden').value = end;
                });

                // -------- Digital Clock Preview & Controls --------
                function format12(h, m, ap) {
                    const hh = String(parseInt(h || '0', 10)).padStart(2, '0');
                    const mm = String(parseInt(m || '0', 10)).padStart(2, '0');
                    return `${hh}:${mm} ${ap || 'AM'}`;
                }

                function updatePreview(prefix) {
                    const ph = document.getElementById(`${prefix}_hour`).value;
                    const pm = document.getElementById(`${prefix}_minute`).value;
                    const ap = document.getElementById(`${prefix}_ampm`).value;
                    document.getElementById(`${prefix}_preview`).textContent = format12(ph, pm, ap);
                }

                ['start','end'].forEach(prefix => {
                    ['hour','minute','ampm'].forEach(s => {
                        document.getElementById(`${prefix}_${s}`).addEventListener('input', () => updatePreview(prefix));
                        document.getElementById(`${prefix}_${s}`).addEventListener('change', () => updatePreview(prefix));
                    });
                });

                function adjustMinutes(prefix, delta) {
                    let h = parseInt(document.getElementById(`${prefix}_hour`).value || '12', 10);
                    let m = parseInt(document.getElementById(`${prefix}_minute`).value || '0', 10);
                    let ap = document.getElementById(`${prefix}_ampm`).value;
                    // convert to minutes since midnight using 12h
                    let hh = h % 12;
                    if (ap === 'PM') hh += 12;
                    let total = hh * 60 + m + delta;
                    if (total < 0) total = (24*60) + total; // wrap
                    total = total % (24*60);
                    let nh = Math.floor(total / 60);
                    let nm = total % 60;
                    ap = nh >= 12 ? 'PM' : 'AM';
                    let displayH = nh % 12;
                    if (displayH === 0) displayH = 12;
                    document.getElementById(`${prefix}_hour`).value = displayH;
                    document.getElementById(`${prefix}_minute`).value = String(nm).padStart(2, '0');
                    document.getElementById(`${prefix}_ampm`).value = ap;
                    updatePreview(prefix);
                }

                document.getElementById('start_minus_15').addEventListener('click', () => adjustMinutes('start', -15));
                document.getElementById('start_plus_15').addEventListener('click', () => adjustMinutes('start', 15));
                document.getElementById('end_minus_15').addEventListener('click', () => adjustMinutes('end', -15));
                document.getElementById('end_plus_15').addEventListener('click', () => adjustMinutes('end', 15));

                function setNow(prefix) {
                    const now = new Date();
                    let nh = now.getHours();
                    let nm = now.getMinutes();
                    // round to nearest 5 minutes
                    nm = Math.round(nm / 5) * 5;
                    if (nm === 60) { nh = (nh + 1) % 24; nm = 0; }
                    const ap = nh >= 12 ? 'PM' : 'AM';
                    let displayH = nh % 12; if (displayH === 0) displayH = 12;
                    document.getElementById(`${prefix}_hour`).value = displayH;
                    document.getElementById(`${prefix}_minute`).value = String(nm).padStart(2, '0');
                    document.getElementById(`${prefix}_ampm`).value = ap;
                    updatePreview(prefix);
                }

                document.getElementById('start_now').addEventListener('click', () => setNow('start'));
                document.getElementById('end_now').addEventListener('click', () => setNow('end'));

                // Initialize previews on load
                updatePreview('start');
                updatePreview('end');

                // -------- Modal Time Picker --------
                let pickerPrefix = null;
                function qs(id){ return document.getElementById(id); }
                function showPicker(prefix){
                    pickerPrefix = prefix;
                    // seed with current fields
                    qs('picker_hour').value = qs(`${prefix}_hour`).value || '';
                    qs('picker_minute').value = qs(`${prefix}_minute`).value || '';
                    const ap = qs(`${prefix}_ampm`).value || 'AM';
                    setPickerAP(ap);
                    qs('time_picker_modal').classList.remove('hidden');
                    qs('picker_hour').focus();
                }
                function hidePicker(){ qs('time_picker_modal').classList.add('hidden'); }
                function setPickerAP(val){
                    qs('picker_ap_am').classList.toggle('bg-purple-200', val==='AM');
                    qs('picker_ap_am').classList.toggle('text-purple-900', val==='AM');
                    qs('picker_ap_pm').classList.toggle('bg-purple-200', val==='PM');
                    qs('picker_ap_pm').classList.toggle('text-purple-900', val==='PM');
                    qs('picker_ampm').value = val;
                }
                function applyPicker(){
                    if(!pickerPrefix) return;
                    let h = parseInt(qs('picker_hour').value||'');
                    let m = parseInt(qs('picker_minute').value||'');
                    if(isNaN(h) || h<1 || h>12) h = 12;
                    if(isNaN(m) || m<0 || m>59) m = 0;
                    qs(`${pickerPrefix}_hour`).value = h;
                    qs(`${pickerPrefix}_minute`).value = String(m).padStart(2,'0');
                    qs(`${pickerPrefix}_ampm`).value = qs('picker_ampm').value;
                    updatePreview(pickerPrefix);
                    hidePicker();
                }
                qs('start_pick').addEventListener('click', ()=>showPicker('start'));
                qs('end_pick').addEventListener('click', ()=>showPicker('end'));
                // Modal events
                qs('picker_cancel').addEventListener('click', hidePicker);
                qs('picker_ok').addEventListener('click', applyPicker);
                qs('picker_ap_am').addEventListener('click', ()=>setPickerAP('AM'));
                qs('picker_ap_pm').addEventListener('click', ()=>setPickerAP('PM'));
                qs('time_picker_backdrop').addEventListener('click', hidePicker);
            </script>

            <!-- Time Picker Modal -->
            <div id="time_picker_modal" class="fixed inset-0 z-50 hidden">
                <div id="time_picker_backdrop" class="absolute inset-0 bg-black/50"></div>
                <div class="relative mx-auto mt-24 w-96 bg-white rounded-lg shadow-lg">
                    <div class="p-5">
                        <div class="text-xs tracking-widest text-gray-500 mb-3">ENTER TIME</div>
                        <div class="flex items-center space-x-3">
                            <input id="picker_hour" type="number" min="1" max="12" placeholder="7" class="w-24 text-4xl font-semibold border-2 border-purple-500 rounded-md px-3 py-2 focus:outline-none" />
                            <div class="text-3xl font-bold">:</div>
                            <input id="picker_minute" type="number" min="0" max="59" placeholder="15" class="w-24 text-4xl font-semibold bg-gray-100 rounded-md px-3 py-2 focus:outline-none" />
                            <div class="flex flex-col ml-2">
                                <input type="hidden" id="picker_ampm" value="AM" />
                                <button type="button" id="picker_ap_am" class="px-3 py-2 rounded-t-md border border-gray-300">AM</button>
                                <button type="button" id="picker_ap_pm" class="px-3 py-2 rounded-b-md border border-gray-300 border-t-0">PM</button>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-6 text-purple-600 font-medium">
                            <button type="button" id="picker_cancel" class="hover:text-purple-800">CANCEL</button>
                            <button type="button" id="picker_ok" class="hover:text-purple-800">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-700">
                <a href="{{ route('admin.classes.index') }}" 
                   class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Create Class
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
