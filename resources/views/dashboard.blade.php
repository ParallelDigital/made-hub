@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- Book a Class Section - Full Width Container -->
<div class="max-w-7xl mx-auto mb-6 px-2 sm:px-4">
    <div id="bookClass" class="dashboard-card bg-gray-800 rounded-lg border border-gray-700 p-4 sm:p-6 w-full overflow-hidden">
        <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
            <h3 class="text-base sm:text-lg font-semibold text-white">Book a Class</h3>
            <div class="flex items-center gap-2">
                <button id="dash-today-btn" class="px-3 py-1.5 rounded border border-gray-600 text-gray-200 hover:bg-gray-700 text-sm">Today</button>
                <input id="dash-class-date" type="date" class="hidden md:block bg-gray-900 border border-gray-700 text-gray-100 text-sm rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-primary" value="{{ now()->format('Y-m-d') }}" />
            </div>
        </div>
        <!-- Enhanced Week Navigation -->
        <div class="bg-gray-900 border border-gray-700 rounded-lg p-3 mb-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-300">Select Date</h4>
                <div class="flex items-center gap-2">
                    <button id="dash-prev-week" class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-800 text-gray-300 hover:text-white transition-colors" type="button" aria-label="Previous week">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button id="dash-next-week" class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-800 text-gray-300 hover:text-white transition-colors" type="button" aria-label="Next week">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            <div class="relative">
                <div id="dash-week-days" class="flex items-center gap-2 overflow-x-auto scrollbar-thin scrollbar-thumb-gray-600 scrollbar-track-gray-800 pb-2 w-full"></div>
                <!-- Scroll indicators -->
                <div class="absolute left-0 top-0 bottom-2 w-4 bg-gradient-to-r from-gray-900 to-transparent pointer-events-none opacity-0 transition-opacity" id="scroll-left-indicator"></div>
                <div class="absolute right-0 top-0 bottom-2 w-4 bg-gradient-to-l from-gray-900 to-transparent pointer-events-none opacity-0 transition-opacity" id="scroll-right-indicator"></div>
            </div>
        </div>

        <!-- Selected Date Header -->
        <div class="flex items-center justify-between mb-2">
            <h4 id="dash-selected-date" class="text-white text-base font-semibold">—</h4>
        </div>

        <div id="dash-classes-loading" class="text-gray-300 text-sm py-6 hidden">Loading classes...</div>
        <div id="dash-classes-empty" class="text-gray-300 text-sm py-6 hidden">No classes scheduled for this date.</div>
        <div id="dash-classes-list" class="divide-y divide-gray-700"></div>

        <template id="dash-class-item-template">
            <div class="py-3 flex flex-col sm:flex-row items-start justify-between gap-3 sm:gap-4">
                <div class="w-full sm:min-w-[72px] sm:w-auto text-gray-300 text-sm">
                    <div class="font-semibold text-white" data-field="time">6:00 AM</div>
                    <div class="text-xs" data-field="duration">60 min.</div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white font-medium truncate" data-field="title">Class Name</div>
                    <div class="text-gray-300 text-xs" data-field="instructor">Instructor</div>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-100" data-field="spots">0 left</span>
                    </div>
                </div>
                <div class="shrink-0 flex items-stretch gap-2 w-full sm:w-auto sm:items-center sm:justify-end mt-2 sm:mt-0" data-field="actions">
                    <!-- Buttons injected here -->
                </div>
            </div>
        </template>
    </div>
</div>

<!-- Main Dashboard Grid for Other Cards -->
<div class="dashboard-grid max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 px-2 sm:px-4">
    <!-- QR Code Card -->
    <div class="dashboard-card bg-gray-800 rounded-lg border border-gray-700 p-4 sm:p-5 order-2 lg:order-2">
        <h3 class="text-base sm:text-lg font-semibold text-white mb-3">Your Check-in QR</h3>
        <p class="text-sm text-gray-300 mb-4">Show this QR at the studio to check in quickly.</p>
        <div class="qr-container bg-gray-900 rounded-lg p-3 sm:p-4 flex items-center justify-center">
            <div class="qr-code">{!! $qrSvg !!}</div>
        </div>
        <div class="mt-4 text-xs break-all text-gray-300">
            <span class="block mb-1 text-gray-400">Backup link:</span>
            @if(isset($userQrUrl) && $userQrUrl)
                <a href="{{ $userQrUrl }}" class="underline text-primary hover:opacity-90 break-all" target="_blank" rel="noopener">{{ $userQrUrl }}</a>
                <p class="text-xs text-gray-500 mt-1">Click to open your check-in page</p>
            @else
                <span class="text-red-400">QR URL not available - please contact support</span>
            @endif
        </div>
    </div>

    <!-- Profile Summary -->
    <div class="dashboard-card bg-gray-800 rounded-lg border border-gray-700 p-4 sm:p-5 lg:col-span-1">
        <h3 class="text-base sm:text-lg font-semibold text-white mb-3">Profile</h3>
        <div class="space-y-2 text-sm sm:text-base text-gray-200">
            <p class="break-words"><span class="text-gray-400">Name:</span> {{ Auth::user()->name }}</p>
            <p class="break-words"><span class="text-gray-400">Email:</span> {{ Auth::user()->email }}</p>
            <p class="break-words"><span class="text-gray-400">QR Code ID:</span> {{ Auth::user()->qr_code }}</p>
            @php 
                $role = Auth::user()->role; 
                $hasMembership = Auth::user()->hasActiveMembership();
                $hasUnlimited = method_exists(Auth::user(), 'hasActiveUnlimitedPass') && Auth::user()->hasActiveUnlimitedPass();
                $currentCredits = $hasMembership ? Auth::user()->getAvailableCredits() : Auth::user()->getNonMemberAvailableCredits();
                $hasAnyCredits = ($currentCredits ?? 0) > 0 || $hasUnlimited;
                $creditsExpiry = (!$hasMembership && !$hasUnlimited) ? Auth::user()->credits_expires_at : null;
                $unlimitedExpiry = $hasUnlimited ? Auth::user()->unlimited_pass_expires_at : null;
            @endphp
            @if($role === 'admin' || $role === 'administrator' || $role === 'instructor')
                <p class="break-words"><span class="text-gray-400">Booking Code (PIN):</span> <span class="font-mono tracking-widest text-white text-sm sm:text-base">{{ Auth::user()->pin_code ?? '— — — —' }}</span></p>
            @endif
            <p>
                <span class="text-gray-400">Membership:</span>
                @if(Auth::user()->hasActiveMembership())
                    Active ({{ Auth::user()->membership?->name ?? 'Member' }})
                @else
                    None
                @endif
            </p>
            @if($hasUnlimited)
                <p class="break-words"><span class="text-gray-400">Unlimited Pass:</span> Active @if($unlimitedExpiry) until {{ \Carbon\Carbon::parse($unlimitedExpiry)->format('D, M j, Y') }} @endif</p>
            @else
                <p class="break-words"><span class="text-gray-400">Credits:</span> {{ $currentCredits }}</p>
                @if($creditsExpiry)
                    <p class="break-words"><span class="text-gray-400">Credits Expire:</span> {{ \Carbon\Carbon::parse($creditsExpiry)->format('D, M j, Y') }}</p>
                @endif
            @endif
            <p class="break-words">
                <span class="text-gray-400">Cancellations Q{{$quarter}} {{$year}}:</span>
                <span class="{{ $remainingCancellations > 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ 2 - $remainingCancellations }}/2 used ({{ $remainingCancellations }} remaining)
                </span>
            </p>
        </div>
        <div class="profile-actions flex flex-col sm:flex-row gap-3 mt-4">
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-700 text-white hover:bg-gray-600 transition text-sm sm:text-base min-h-[44px]">Edit Profile</a>
            <a href="{{ route('purchase.index') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-primary text-black hover:opacity-90 transition text-sm sm:text-base min-h-[44px]">Buy Credits / Membership</a>
        </div>
    </div>

    <!-- Upcoming Classes -->
    <div class="dashboard-card bg-gray-800 rounded-lg border border-gray-700 p-4 sm:p-5 lg:col-span-1">
        <h3 class="text-base sm:text-lg font-semibold text-white mb-3">Your Upcoming Classes</h3>
        @if($upcomingBookings->isEmpty())
            <p class="text-gray-300 text-sm sm:text-base">You have no upcoming classes booked.</p>
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center mt-4 px-4 py-2 rounded-md bg-primary text-black hover:opacity-90 transition text-sm sm:text-base min-h-[44px] w-full sm:w-auto">Book a Class</a>
        @else
            <ul class="divide-y divide-gray-700">
                @foreach($upcomingBookings as $booking)
                    <li class="upcoming-class py-3 flex flex-col sm:flex-row items-start justify-between gap-3 sm:gap-4">
                        <div class="upcoming-class-details flex-1">
                            <p class="text-white font-medium text-sm sm:text-base break-words">{{ $booking->fitnessClass->name }}</p>
                            <p class="text-gray-300 text-xs sm:text-sm break-words">
                                @php
                                    // Use booking_date if available (for recurring classes), otherwise use class_date
                                    $displayDate = $booking->booking_date ?? $booking->fitnessClass->class_date;
                                @endphp
                                {{ \Carbon\Carbon::parse($displayDate)->format('D, M j, Y') }} ·
                                {{ \Carbon\Carbon::parse($booking->fitnessClass->start_time)->format('g:i A') }}
                            </p>
                            <p class="text-gray-400 text-xs sm:text-sm break-words">Instructor: {{ $booking->fitnessClass->instructor->name ?? 'N/A' }}</p>
                        </div>
                        <div class="upcoming-class-actions shrink-0 w-full sm:w-auto">
                            <a href="{{ route('booking.confirmation', ['classId' => $booking->fitness_class_id]) }}" class="text-primary hover:underline text-sm inline-flex items-center justify-center min-h-[44px] w-full sm:w-auto text-center mr-2">Details</a>
                            @php
                                // Use booking_date for validation if available
                                $bookingDateStr = $booking->booking_date ? $booking->booking_date->toDateString() : $booking->fitnessClass->class_date->toDateString();
                                $classStart = \Carbon\Carbon::parse($bookingDateStr . ' ' . $booking->fitnessClass->start_time);
                                $canCancel = $booking->status === 'confirmed' && !$classStart->isPast();
                            @endphp
                            @if($canCancel)
                                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                                    @if($remainingCancellations > 0)
                                        <button onclick="cancelBooking({{ $booking->id }}, '{{ $booking->fitnessClass->name }}')" class="text-red-500 hover:text-red-700 hover:underline text-sm inline-flex items-center justify-center min-h-[44px] w-full sm:w-auto text-center">Cancel</button>
                                    @else
                                        <span class="text-gray-500 text-sm inline-flex items-center justify-center min-h-[44px] w-full sm:w-auto text-center" title="No cancellations remaining this quarter">Cancel Limit Reached</span>
                                    @endif
                                    <button onclick="deleteBooking({{ $booking->id }}, '{{ $booking->fitnessClass->name }}')" class="text-red-800 hover:text-red-900 hover:underline text-sm inline-flex items-center justify-center min-h-[44px] w-full sm:w-auto text-center">Delete</button>
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    
    <!-- Use Credits Confirmation Modal -->
    <div id="use-credits-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative max-w-md mx-auto mt-24 bg-gray-900 border border-gray-700 rounded-lg shadow-xl text-gray-100">
            <div class="p-5 border-b border-gray-700">
                <h3 class="text-lg font-semibold">Confirm Use of Credits</h3>
            </div>
            <div class="p-5 space-y-3">
                <p>Use 1 credit to book:</p>
                <div class="bg-gray-800 rounded p-3">
                    <div id="uc-class-name" class="font-medium">Class Name</div>
                    <div id="uc-class-datetime" class="text-sm text-gray-300">Date and time</div>
                </div>
                <div id="uc-error" class="hidden text-sm text-red-400"></div>
            </div>
            <div class="p-4 border-t border-gray-700 flex items-center justify-end gap-3">
                <button id="uc-cancel" class="px-4 py-2 rounded border border-gray-600 hover:bg-gray-800">Cancel</button>
                <button id="uc-confirm" class="px-4 py-2 rounded bg-primary text-black hover:opacity-90">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function(){
        const dateInput = document.getElementById('dash-class-date');
        const listEl = document.getElementById('dash-classes-list');
        const emptyEl = document.getElementById('dash-classes-empty');
        const loadingEl = document.getElementById('dash-classes-loading');
        const tmpl = document.getElementById('dash-class-item-template');
        const CLASSES_API = '{{ url('/api/classes') }}';
        const USER_CREDITS = Number({{ Auth::user()->hasActiveMembership() ? (int) Auth::user()->getAvailableCredits() : (int) Auth::user()->getNonMemberAvailableCredits() }});
        const USER_UNLIMITED = {{ (method_exists(Auth::user(),'hasActiveUnlimitedPass') && Auth::user()->hasActiveUnlimitedPass()) ? 'true' : 'false' }};
        const weekDaysContainer = document.getElementById('dash-week-days');
        const todayBtn = document.getElementById('dash-today-btn');
        const prevWeekBtn = document.getElementById('dash-prev-week');
        const nextWeekBtn = document.getElementById('dash-next-week');
        const selectedDateHeader = document.getElementById('dash-selected-date');
        let dashCurrentDate = '{{ now()->format('Y-m-d') }}';
        let dashIsLoading = false;

        // Use Credits Modal elements
        const ucModal = document.getElementById('use-credits-modal');
        const ucName = document.getElementById('uc-class-name');
        const ucDT = document.getElementById('uc-class-datetime');
        const ucError = document.getElementById('uc-error');
        const ucConfirm = document.getElementById('uc-confirm');
        const ucCancel = document.getElementById('uc-cancel');
        let ucClassId = null;
        let ucClassDate = null;

        function parseTimeToMinutes(t){
            if(!t) return null;
            const [h,m] = t.split(':').map(n=>parseInt(n,10));
            return h*60+m;
        }
        function formatTime12(t){
            const mins = parseTimeToMinutes(t);
            if(mins===null) return '';
            let h = Math.floor(mins/60);
            const m = mins%60;
            const ampm = h>=12? 'PM':'AM';
            h = h%12; if(h===0) h=12;
            return `${h}:${String(m).padStart(2,'0')} ${ampm}`;
        }

        function btn(html){
            const span = document.createElement('span');
            span.innerHTML = html.trim();
            return span.firstChild;
        }

        function renderClasses(classes){
            listEl.innerHTML='';
            if(!classes || classes.length===0){
                emptyEl.classList.remove('hidden');
                return;
            }
            emptyEl.classList.add('hidden');
            classes.forEach(c=>{
                const node = tmpl.content.cloneNode(true);
                node.querySelector('[data-field="time"]').textContent = formatTime12(c.start_time);
                node.querySelector('[data-field="duration"]').textContent = `${c.duration || 60} min.`;
                node.querySelector('[data-field="title"]').textContent = `${c.name}`;
                node.querySelector('[data-field="instructor"]').textContent = c.instructor?.name || 'No Instructor';
                const spotsTxt = (c.available_spots ?? 0) > 0 ? `${c.available_spots} left` : 'Full';
                node.querySelector('[data-field="spots"]').textContent = spotsTxt;
                const actions = node.querySelector('[data-field="actions"]');

                if (c.is_past){
                    actions.appendChild(btn(`<button class="px-3 py-2 rounded border border-gray-600 text-gray-400 text-xs cursor-not-allowed w-full sm:w-auto" disabled>Past</button>`));
                } else if ((c.available_spots ?? 0) <= 0){
                    actions.appendChild(btn(`<button class="px-3 py-2 rounded border border-gray-600 text-gray-400 text-xs cursor-not-allowed w-full sm:w-auto" disabled>Full</button>`));
                } else {
                    actions.appendChild(btn(`<a href="{{ url('/checkout') }}/${c.id}" class="px-3 py-2 rounded border border-primary text-black bg-primary hover:opacity-90 text-xs w-full sm:w-auto">Reserve</a>`));
                    const canUse = USER_UNLIMITED || USER_CREDITS > 0;
                    const disabledAttrs = canUse ? '' : 'disabled title="No credits" class="cursor-not-allowed opacity-60"';
                    const safeName = (c.name || '').replace(/&/g, '&amp;').replace(/</g, '&lt;');
                    const classDate = c.class_date || dashCurrentDate;
                    actions.appendChild(btn(`<button data-class-id="${c.id}" data-class-name="${safeName}" data-class-date="${classDate}" data-class-time="${c.start_time}" class="px-3 py-2 rounded border border-gray-300 text-white hover:bg-gray-700 text-xs dash-use-credits w-full sm:w-auto" ${disabledAttrs}>Use Credits</button>`));
                }
                listEl.appendChild(node);
            });
        }

        function fetchClasses(date){
            loadingEl.classList.remove('hidden');
            listEl.innerHTML='';
            emptyEl.classList.add('hidden');
            const url = new URL(CLASSES_API, window.location.origin);
            url.searchParams.set('date', date);
            return fetch(url.toString())
                .then(r=>r.json())
                .then(data=>{
                    selectedDateHeader.textContent = data.selectedDate || date;
                    dashUpdateWeekNavigation(data.weekDays, data.prevWeek, data.nextWeek);
                    renderClasses(data.classes || []);
                })
                .catch(()=>{
                    emptyEl.textContent = 'Unable to load classes.';
                    emptyEl.classList.remove('hidden');
                })
                .finally(()=>{
                    loadingEl.classList.add('hidden');
                });
        }

        function dashUpdateWeekNavigation(weekDays, prevWeek, nextWeek){
            weekDaysContainer.innerHTML = '';
            const isMobile = window.innerWidth <= 640;
            const isTablet = window.innerWidth <= 768;
            
            weekDays.forEach(day => {
                const btn = document.createElement('button');
                // Responsive button sizing - ultra-compact for mobile
                const buttonClasses = isMobile 
                    ? 'flex-shrink-0 text-center px-0.5 py-1 w-[32px] min-w-[32px] max-w-[32px]'
                    : isTablet 
                    ? 'flex-shrink-0 text-center px-1 py-1.5 w-[36px] min-w-[36px] max-w-[36px]'
                    : 'flex-shrink-0 text-center px-3 py-2 min-w-[60px]';
                    
                btn.className = `${buttonClasses} rounded-lg transition-colors duration-200 ${
                    day.is_selected 
                        ? 'bg-primary text-black font-semibold shadow-md' 
                        : 'bg-gray-800 text-gray-300 hover:bg-gray-700 hover:text-white'
                }`;
                btn.setAttribute('data-date', day.full_date);
                
                // Responsive text sizing - ultra-small for mobile
                const dayTextSize = isMobile ? 'text-[0.35rem]' : isTablet ? 'text-[0.4rem]' : 'text-xs';
                const dateTextSize = isMobile ? 'text-[0.6rem]' : isTablet ? 'text-xs' : 'text-lg';
                
                btn.innerHTML = `
                    <div class="${dayTextSize} font-medium leading-none ${day.is_selected ? 'text-black' : 'text-gray-400'}">${day.day}</div>
                    <div class="${dateTextSize} font-bold leading-none mt-0.5 ${day.is_selected ? 'text-black' : 'text-gray-200'}">${day.date}</div>
                `;
                btn.addEventListener('click', () => dashLoadDate(day.full_date));
                weekDaysContainer.appendChild(btn);
            });
            
            prevWeekBtn.onclick = () => dashLoadDate(prevWeek);
            nextWeekBtn.onclick = () => dashLoadDate(nextWeek);
            
            // Scroll selected day into view
            const selected = weekDaysContainer.querySelector(`[data-date="${dashCurrentDate}"]`);
            if (selected) {
                setTimeout(() => {
                    selected.scrollIntoView({behavior:'smooth', inline:'center', block:'nearest'});
                }, 100);
            }
            
            // Update scroll indicators
            updateScrollIndicators();
        }

        function updateScrollIndicators() {
            const leftIndicator = document.getElementById('scroll-left-indicator');
            const rightIndicator = document.getElementById('scroll-right-indicator');
            
            if (leftIndicator && rightIndicator) {
                const canScrollLeft = weekDaysContainer.scrollLeft > 0;
                const canScrollRight = weekDaysContainer.scrollLeft < (weekDaysContainer.scrollWidth - weekDaysContainer.clientWidth);
                
                leftIndicator.style.opacity = canScrollLeft ? '1' : '0';
                rightIndicator.style.opacity = canScrollRight ? '1' : '0';
            }
        }

        function dashLoadDate(date){
            if (dashIsLoading) return;
            dashIsLoading = true;
            dashCurrentDate = date;
            fetchClasses(date).finally(()=>{ dashIsLoading = false; });
        }

        function openUseCreditsModal(info){
            ucClassId = info.id;
            ucClassDate = info.date || null;
            ucName.textContent = info.name || 'Class';
            ucDT.textContent = `${info.date} • ${formatTime12(info.time)}`;
            ucError.classList.add('hidden');
            ucError.textContent = '';
            ucModal.classList.remove('hidden');
        }

        function closeUseCreditsModal(){
            ucModal.classList.add('hidden');
            ucClassId = null;
        }

        document.addEventListener('click', (e)=>{
            const btnEl = e.target.closest('.dash-use-credits');
            if(!btnEl) return;
            if (btnEl.hasAttribute('disabled')) return;
            const info = {
                id: btnEl.getAttribute('data-class-id'),
                name: btnEl.getAttribute('data-class-name'),
                date: btnEl.getAttribute('data-class-date') || dashCurrentDate,
                time: btnEl.getAttribute('data-class-time')
            };
            openUseCreditsModal(info);
        });

        if (ucCancel) ucCancel.addEventListener('click', closeUseCreditsModal);
        if (ucConfirm) ucConfirm.addEventListener('click', function(){
            if(!ucClassId) return;
            ucConfirm.disabled = true;
            fetch(`{{ url('/book-with-credits') }}/${ucClassId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ selected_date: ucClassDate })
            })
            .then(async r => {
                const data = await r.json().catch(()=>({success:false,message:'Unexpected server response'}));
                if (data.success) {
                    alert(data.message || 'Booked with credits!', 'success', 'Success');
                    closeUseCreditsModal();
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        fetchClasses(dateInput.value);
                    }
                } else {
                    ucError.textContent = data.message || 'Booking failed.';
                    ucError.classList.remove('hidden');
                }
            })
            .catch(() => {
                ucError.textContent = 'Network error. Please try again.';
                ucError.classList.remove('hidden');
            })
            .finally(()=>{ ucConfirm.disabled = false; });
        });

        // Cancel booking function with enhanced modal
        window.cancelBooking = function(bookingId, className) {
            showConfirmModal(
                `Are you sure you want to cancel your booking for "${className}"?\n\nYou can only cancel 2 classes per quarter. This action cannot be undone.`,
                function() {
                    // User confirmed - proceed with cancellation
                    fetch(`{{ url('/cancel-booking') }}/${bookingId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showResultModal(data.message || 'Booking cancelled successfully!', 'success', 'Success');
                            // Reload the page to refresh the upcoming bookings list
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showResultModal(data.message || 'Cancellation failed.', 'error', 'Error');
                        }
                    })
                    .catch(() => showResultModal('Network error. Please try again.', 'error', 'Error'));
                },
                function() {
                    // User cancelled - do nothing
                },
                {
                    title: 'Cancel Booking',
                    yesText: 'Yes, Cancel',
                    noText: 'Keep Booking',
                    danger: true,
                    icon: 'warning'
                }
            );
        };

        // Delete booking function with enhanced modal
        window.deleteBooking = function(bookingId, className) {
            showConfirmModal(
                `Are you sure you want to permanently delete your booking for "${className}"?\n\nThis will completely remove the booking from the system and cannot be undone.`,
                function() {
                    // User confirmed - proceed with deletion
                    fetch(`{{ url('/delete-booking') }}/${bookingId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showResultModal(data.message || 'Booking deleted successfully!', 'success', 'Success');
                            // Reload the page to refresh the upcoming bookings list
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showResultModal(data.message || 'Deletion failed.', 'error', 'Error');
                        }
                    })
                    .catch(() => showResultModal('Network error. Please try again.', 'error', 'Error'));
                },
                function() {
                    // User cancelled - do nothing
                },
                {
                    title: 'Delete Booking',
                    yesText: 'Yes, Delete Permanently',
                    noText: 'Keep Booking',
                    danger: true,
                    icon: 'warning'
                }
            );
        };

        // Result modal function using the existing alert modal system
        window.showResultModal = function(message, type = 'info', title = null) {
            showAlertModal(message, type, title);
        };

        // Add scroll event listener for indicators
        if (weekDaysContainer) {
            weekDaysContainer.addEventListener('scroll', updateScrollIndicators);
        }

        // Add resize listener to update calendar on screen size change
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                // Re-render the current week with new responsive sizing
                fetchClasses(dashCurrentDate);
            }, 150);
        });

        // Init
        fetchClasses(dashCurrentDate);
        if (dateInput) {
            dateInput.addEventListener('change', ()=> dashLoadDate(dateInput.value));
        }
        if (todayBtn) {
            todayBtn.addEventListener('click', ()=> dashLoadDate('{{ now()->format('Y-m-d') }}'));
        }
    })();
</script>
@endpush
