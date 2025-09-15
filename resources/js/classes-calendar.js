import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';

// Simple tooltip implementation without Bootstrap dependency

// Store calendar instance
let calendar;

// Function to initialize the calendar
function initCalendar() {
    const calendarEl = document.getElementById('classes-calendar');
    if (!calendarEl) return;

    // AGGRESSIVE font enforcement - force ProFontWindows on ALL elements
    const forceProFontWindows = (element) => {
        if (!element) return;
        
        // Force font on the element itself
        element.style.setProperty('font-family', "'ProFontWindows', monospace", 'important');
        element.style.setProperty('font-weight', '400', 'important');
        element.style.setProperty('font-style', 'normal', 'important');
        
        // Force font on all descendants
        const allElements = element.querySelectorAll('*');
        allElements.forEach(el => {
            el.style.setProperty('font-family', "'ProFontWindows', monospace", 'important');
            el.style.setProperty('font-weight', '400', 'important');
            el.style.setProperty('font-style', 'normal', 'important');
        });
        
        // Set CSS variables
        element.style.setProperty('--fc-font-family', "'ProFontWindows', monospace", 'important');
        element.style.setProperty('--fc-page-bg-color', '#111827', 'important');
        element.style.setProperty('--fc-neutral-bg-color', '#1f2937', 'important');
        element.style.setProperty('--fc-neutral-text-color', '#e5e7eb', 'important');
        element.style.setProperty('--fc-border-color', '#374151', 'important');
        element.style.setProperty('--fc-button-text-color', '#ffffff', 'important');
        element.style.setProperty('--fc-button-bg-color', '#3b82f6', 'important');
        element.style.setProperty('--fc-button-border-color', '#3b82f6', 'important');
        element.style.setProperty('--fc-button-hover-bg-color', '#2563eb', 'important');
        element.style.setProperty('--fc-button-hover-border-color', '#2563eb', 'important');
        element.style.setProperty('--fc-button-active-bg-color', '#1d4ed8', 'important');
        element.style.setProperty('--fc-button-active-border-color', '#1d4ed8', 'important');
        element.style.setProperty('--fc-today-bg-color', 'rgba(59, 130, 246, 0.2)', 'important');
    };

    // Watch for ANY DOM changes and force font
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) { // Element node
                    forceProFontWindows(node);
                }
            });
        });
        forceProFontWindows(calendarEl);
    });
    
    observer.observe(calendarEl, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['style', 'class']
    });

    forceProFontWindows(calendarEl);

    // Destroy existing calendar if it exists
    if (calendar) {
        calendar.destroy();
    }

    // Initialize the calendar
    calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        datesSet: function() { 
            setTimeout(() => forceProFontWindows(calendarEl), 0);
        },
        viewDidMount: function() {
            setTimeout(() => forceProFontWindows(calendarEl), 0);
        },
        eventsSet: function() {
            setTimeout(() => forceProFontWindows(calendarEl), 0);
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            const params = {
                ...getFilterParams(),
                start: fetchInfo.startStr,
                end: fetchInfo.endStr,
                calendar: true
            };
            
            const queryString = new URLSearchParams(params).toString();
            
            fetch(`/admin/classes/calendar-data?${queryString}`)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => {
                    console.error('Error fetching classes:', error);
                    failureCallback(error);
                });
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        firstDay: 1,
        height: 'auto',
        nowIndicator: true,
        navLinks: true,
        selectable: false,
        selectMirror: true,
        dayMaxEvents: true,
        editable: false,
        eventClick: function(info) {
            window.location.href = info.event.url;
        },
        eventDidMount: function(info) {
            // Add simple tooltip with class details
            if (info.event.extendedProps.description) {
                info.el.setAttribute('title', info.event.extendedProps.description);
            }
        },
        loading: function(isLoading) {
            const loadingEl = document.getElementById('calendar-loading');
            if (loadingEl) {
                loadingEl.style.display = isLoading ? 'flex' : 'none';
            }
        }
    });

    calendar.render();
    
    // NUCLEAR APPROACH: Force font every 100ms to prevent any changes
    setInterval(() => {
        forceProFontWindows(calendarEl);
    }, 100);
    
    // Handle view change
    const viewSelector = document.getElementById('view-selector');
    if (viewSelector) {
        viewSelector.addEventListener('change', function() {
            calendar.changeView(this.value);
        });
    }
    
    // Handle apply filters button
    const applyFiltersBtn = document.getElementById('apply-filters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            calendar.refetchEvents();
        });
    }
}

// Get filter parameters
function getFilterParams() {
    const params = new URLSearchParams();
    
    // Get instructor filter
    const instructor = document.getElementById('instructor');
    if (instructor && instructor.value) {
        params.append('instructor', instructor.value);
    }
    
    // Get status filter
    const status = document.getElementById('status');
    if (status && status.value) {
        params.append('status', status.value);
    }
    
    // Get view type
    const view = document.querySelector('input[name="view"]');
    if (view && view.value) {
        params.append('view', view.value);
    }
    
    return params.toString();
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initCalendar();
    
    // Simple tooltip initialization - using native browser tooltips
});
