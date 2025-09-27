// Welcome Page JavaScript - Extracted for Performance

// Global variables
window.IS_AUTH = false;
window.IS_MEMBER = false;
window.IS_UNLIMITED = false;
window.SHOW_PAST = false;
window.__classCardClickBound = false;

// Initialize auth status from Laravel
function initializeAuthStatus() {
    // These will be set by Laravel in the blade template
    if (typeof window.laravelAuth !== 'undefined') {
        window.IS_AUTH = window.laravelAuth.isAuth || false;
        window.IS_MEMBER = window.laravelAuth.isMember || false;
        window.IS_UNLIMITED = window.laravelAuth.isUnlimited || false;
    }
}

// Facilities carousel functionality
function initializeFacilitiesCarousel() {
    const leftArrow = document.querySelector('.carousel-arrow.left');
    const rightArrow = document.querySelector('.carousel-arrow.right');
    const track = document.querySelector('#facilitiesTrack');
    
    if (!leftArrow || !rightArrow || !track) {
        return;
    }
    
    leftArrow.addEventListener('click', () => {
        track.scrollBy({ left: -320, behavior: 'smooth' });
    });
    
    rightArrow.addEventListener('click', () => {
        track.scrollBy({ left: 320, behavior: 'smooth' });
    });
}

// Week navigation functionality
let currentDate = new Date().toISOString().split('T')[0];

function loadDate(date) {
    if (!date) return;
    
    currentDate = date;
    const container = document.getElementById('classes-list');
    if (!container) return;
    
    // Show loading state
    container.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto"></div></div>';
    
    // Build URL with show_past parameter
    const url = new URL('/api/classes', window.location.origin);
    url.searchParams.set('date', date);
    if (window.SHOW_PAST) {
        url.searchParams.set('show_past', '1');
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                container.innerHTML = data.html;
                updateDateHeader(date);
                updateWeekNavigation(date);
                updateURL(date);
                bindClassCardClicks();
            }
        })
        .catch(error => {
            console.error('Error loading classes:', error);
            container.innerHTML = '<div class="text-center py-8 text-red-600">Error loading classes. Please try again.</div>';
        });
}

function updateDateHeader(date) {
    const header = document.querySelector('.date-header h2');
    if (!header) return;
    
    const dateObj = new Date(date + 'T00:00:00');
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    header.textContent = dateObj.toLocaleDateString('en-GB', options);
}

function updateWeekNavigation(selectedDate) {
    const buttons = document.querySelectorAll('.week-day-btn');
    buttons.forEach(btn => {
        btn.classList.remove('selected');
        if (btn.dataset.date === selectedDate) {
            btn.classList.add('selected');
        }
    });
}

function updateURL(date) {
    const url = new URL(window.location);
    url.searchParams.set('date', date);
    if (window.SHOW_PAST) {
        url.searchParams.set('show_past', '1');
    } else {
        url.searchParams.delete('show_past');
    }
    window.history.pushState({}, '', url);
}

function scrollWeekNavigation(direction) {
    const container = document.getElementById('week-days');
    if (!container) return;
    
    const scrollAmount = 200;
    const currentScroll = container.scrollLeft;
    const targetScroll = direction === 'left' 
        ? currentScroll - scrollAmount 
        : currentScroll + scrollAmount;
    
    container.scrollTo({
        left: targetScroll,
        behavior: 'smooth'
    });
}

function goToToday() {
    const today = new Date().toISOString().split('T')[0];
    loadDate(today);
}

function bindClassCardClicks() {
    if (!window.__classCardClickBound) {
        document.addEventListener('click', function(event) {
            const card = event.target.closest('.class-card');
            if (!card) return;
            
            // Skip if clicking on reserve button
            if (event.target.closest('.reserve-button')) return;
            
            const classId = card.dataset.classId;
            const price = card.dataset.price || 0;
            
            if (!classId) return;
            
            // Store description for mobile modal
            if (card.dataset.description) {
                window.selectedClassDescription = card.dataset.description;
            }
            
            // Check if members-only class
            if (card.dataset.membersOnly === '1') {
                if (window.IS_AUTH) {
                    openBookingModal(classId, 0); 
                } else {
                    openBookingModal(classId, 0); 
                }
                return; 
            }
            openBookingModal(classId, price);
        });
        window.__classCardClickBound = true;
    }
}

// Handle browser back/forward buttons
function handlePopState() {
    window.addEventListener('popstate', function(event) {
        const urlParams = new URLSearchParams(window.location.search);
        const date = urlParams.get('date') || new Date().toISOString().split('T')[0];
        const sp = urlParams.get('show_past');
        window.SHOW_PAST = (sp === '1' || sp === 'true');
        if (date !== currentDate) {
            loadDate(date);
        }
    });
}

// Initialize calendar
function initializeCalendar() {
    const scheduleContainer = document.querySelector('.schedule-container');
    if (scheduleContainer) {
        // Force a layout reflow
        scheduleContainer.offsetHeight;
        
        setTimeout(() => {
            scheduleContainer.style.opacity = '1';
            // Center currently selected week day
            const container = document.getElementById('week-days');
            const selected = container?.querySelector('.week-day-btn.selected') || container?.querySelector('.week-day-btn.today');
            if (selected && typeof selected.scrollIntoView === 'function') {
                selected.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }
        }, 100);
    }
}

function toggleShowPast() {
    window.SHOW_PAST = !window.SHOW_PAST;
    loadDate(currentDate);
}

// Booking modal functionality
window.selectedClassId = null;
window.selectedClassPrice = 0;
window.selectedClassDescription = '';

function openBookingModal(classId, price) {
    window.selectedClassId = classId;
    window.selectedClassPrice = price || 0;

    const priceNum = parseInt(price) || 0;
    const priceElement = document.getElementById('modalClassPrice');
    if (priceElement) {
        priceElement.textContent = `£${priceNum.toLocaleString()}`;
    }

    // Get class description
    let classDescription = '';
    const card = document.querySelector(`.class-card[data-class-id="${classId}"]`);
    
    if (card && card.dataset && card.dataset.description) {
        classDescription = card.dataset.description;
    } else if (window.selectedClassDescription) {
        classDescription = window.selectedClassDescription;
        window.selectedClassDescription = '';
    }
    
    // Show/hide description
    const descElement = document.getElementById('classDescription');
    if (descElement) {
        if (classDescription && classDescription.trim()) {
            descElement.textContent = classDescription;
            descElement.style.display = 'block';
        } else {
            descElement.style.display = 'none';
        }
    }

    // Adjust modal for members-only classes
    const isMembersOnly = card && card.dataset && card.dataset.membersOnly === '1';
    const useCreditsLabel = document.getElementById('useCreditsLabel');
    const useCreditsRight = document.getElementById('useCreditsRight');
    const payBtn = document.getElementById('payButton');
    const membersOnlyOptions = document.getElementById('membersOnlyOptions');
    const bookingModalMessage = document.getElementById('bookingModalMessage');
    const useCreditsBtn = document.querySelector('#bookingModal button[onclick*="bookWithCredits"]');

    if (isMembersOnly) {
        if (bookingModalMessage) {
            bookingModalMessage.textContent = 'This class is for members only:';
        }
        
        if (window.IS_MEMBER) {
            if (useCreditsLabel) useCreditsLabel.textContent = 'Book (Members)';
            if (useCreditsRight) useCreditsRight.textContent = 'Free';
            if (payBtn) payBtn.classList.add('hidden');
            if (membersOnlyOptions) membersOnlyOptions.classList.add('hidden');
            if (useCreditsBtn) useCreditsBtn.classList.remove('hidden');
        } else {
            if (payBtn) payBtn.classList.add('hidden');
            if (membersOnlyOptions) membersOnlyOptions.classList.remove('hidden');
            if (useCreditsBtn) useCreditsBtn.classList.add('hidden');
        }
    } else {
        if (bookingModalMessage) {
            bookingModalMessage.textContent = 'Choose how you\'d like to book this class:';
        }
        if (useCreditsLabel) useCreditsLabel.textContent = 'Use Credits';
        if (useCreditsRight) useCreditsRight.textContent = '1 Credit';
        if (payBtn) payBtn.classList.remove('hidden');
        if (membersOnlyOptions) membersOnlyOptions.classList.add('hidden');
        if (useCreditsBtn) useCreditsBtn.classList.remove('hidden');
    }

    const modal = document.getElementById('bookingModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeBookingModal() {
    const modal = document.getElementById('bookingModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

function bookWithCredits(classId) {
    const cid = classId || window.selectedClassId;
    
    if (!window.IS_AUTH) {
        openLoginModal();
        return;
    }
    
    const card = document.querySelector(`.class-card[data-class-id="${cid}"]`);
    const isMembersOnly = card && card.dataset && card.dataset.membersOnly === '1';
    
    if (isMembersOnly && window.IS_MEMBER) {
        closeBookingModal();
        openConfirmModal('Book this members-only class for free?', function() {
            performCreditBooking(cid);
        });
    } else if (isMembersOnly && !window.IS_MEMBER) {
        closeBookingModal();
        openMembersOnlyModal();
    } else {
        if (window.IS_UNLIMITED) {
            closeBookingModal();
            openConfirmModal('Book with your unlimited pass?', function() {
                performCreditBooking(cid);
            });
            return;
        }
        
        const span = document.getElementById('availableCreditsData');
        const available = span ? (parseInt(span.getAttribute('data-credits')) || 0) : 0;
        if (available > 0) {
            closeBookingModal();
            openConfirmModal('Use 1 credit to book this class?', function() {
                performCreditBooking(cid);
            });
        } else {
            closeBookingModal();
            openNoCreditsModal();
        }
    }
}

function buySpot(classId) {
    closeBookingModal();
    const card = document.querySelector(`.class-card[data-class-id="${classId}"]`);
    const isMembersOnly = card && card.dataset && card.dataset.membersOnly === '1';
    if (isMembersOnly && !window.IS_MEMBER) {
        openMembersOnlyModal();
        return;
    }
    window.location.href = `/checkout/${classId}`;
}

function redirectToLogin(classId, price) {
    closeBookingModal();
    const cid = (typeof classId !== 'undefined' && classId !== null) ? classId : window.selectedClassId;
    const prRaw = (typeof price !== 'undefined' && price !== null) ? price : window.selectedClassPrice;
    const priceNum = parseInt(prRaw || 0) || 0;
    const redirectPath = `/?openBooking=1&classId=${cid||''}&price=${priceNum}`;
    window.location.href = `/login?redirect=${redirectPath}`;
}

// Login modal functionality
function submitModalLogin() {
    const email = (document.getElementById('loginEmail')?.value || '').trim();
    const password = (document.getElementById('loginPassword')?.value || '').trim();
    const errorEl = document.getElementById('loginError');
    
    if (!email || !password) {
        if (errorEl) {
            errorEl.classList.remove('hidden');
            errorEl.textContent = 'Please enter your email and password.';
        }
        return;
    }
    
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/ajax/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email, password })
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) throw new Error(data.message || 'Invalid credentials.');
        return data;
    })
    .then(() => {
        const classId = window.selectedClassId;
        const price = window.selectedClassPrice || 0;
        window.location.href = `/?openBooking=1&classId=${classId||''}&price=${price||0}`;
    })
    .catch((err) => {
        if (errorEl) {
            errorEl.classList.remove('hidden');
            errorEl.textContent = err.message || 'Invalid email or password.';
        }
    });
}

// Auto-open modal after login redirect
function handleAutoOpenBooking() {
    const url = new URL(window.location.href);
    const sp = url.searchParams;
    if (sp.get('openBooking') === '1') {
        const classId = parseInt(sp.get('classId')) || null;
        const price = parseInt(sp.get('price')) || 0;
        if (classId) {
            window.selectedClassId = classId;
            window.selectedClassPrice = price;
            openBookingModal(classId, price);
            // Clean the URL
            sp.delete('openBooking');
            sp.delete('classId');
            sp.delete('price');
            const newUrl = url.pathname + (sp.toString() ? ('?' + sp.toString()) : '');
            window.history.replaceState({}, '', newUrl);
        }
    }
}

// Modal utilities
function openLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        const email = document.getElementById('loginEmail');
        setTimeout(() => { email && email.focus(); }, 0);
    }
}

function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

function openConfirmModal(message, onConfirm) {
    const modal = document.getElementById('confirmModal');
    const msg = document.getElementById('confirmMessage');
    if (msg) msg.textContent = message || 'Are you sure?';
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    window.__confirmCb = function(){ 
        try { 
            onConfirm && onConfirm(); 
        } finally { 
            closeConfirmModal(); 
        } 
    };
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    window.__confirmCb = null;
}

function confirmModalYes() { 
    if (window.__confirmCb) window.__confirmCb(); 
}

function confirmModalNo() { 
    closeConfirmModal(); 
}

function openFeedbackModal(title, message) {
    const modal = document.getElementById('feedbackModal');
    const titleEl = document.getElementById('feedbackTitle');
    const messageEl = document.getElementById('feedbackMessage');
    
    if (titleEl) titleEl.textContent = title || 'Notice';
    if (messageEl) messageEl.textContent = message || '';
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeFeedbackModal() {
    const modal = document.getElementById('feedbackModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

function performCreditBooking(classId) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(`/book-with-credits/${classId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || `Request failed (${res.status})`);
        return data;
    })
    .then((data) => {
        window.location.href = `/booking/confirmation/${classId}`;
    })
    .catch((err) => {
        openFeedbackModal('Booking failed', err.message || 'Unable to book with credits.');
    });
}

function openNoCreditsModal() {
    const modal = document.getElementById('noCreditsModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeNoCreditsModal() {
    const modal = document.getElementById('noCreditsModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

function openMembersOnlyModal() {
    const modal = document.getElementById('membersOnlyModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeMembersOnlyModal() {
    const modal = document.getElementById('membersOnlyModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Event listeners
function initializeEventListeners() {
    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('bookingModal');
        if (event.target === modal) {
            closeBookingModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeBookingModal();
        }
    });
}

// Initialize everything when DOM is ready
function initializeWelcomePage() {
    initializeAuthStatus();
    initializeFacilitiesCarousel();
    handlePopState();
    bindClassCardClicks();
    initializeEventListeners();
    handleAutoOpenBooking();
    
    // Initialize calendar
    if (document.readyState === 'complete') {
        initializeCalendar();
    } else {
        window.addEventListener('load', initializeCalendar);
    }
    
    // Fallback for calendar
    setTimeout(() => {
        const scheduleContainer = document.querySelector('.schedule-container');
        if (scheduleContainer && scheduleContainer.style.opacity === '0') {
            scheduleContainer.style.opacity = '1';
        }
    }, 1000);
}

// Auto-initialize when script loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeWelcomePage);
} else {
    initializeWelcomePage();
}
