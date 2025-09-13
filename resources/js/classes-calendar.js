import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import bootstrap5Plugin from '@fullcalendar/bootstrap5';

// Import Bootstrap JS (already imported in app.js)
// The Tooltip will be available from the window.Bootstrap object

// Store calendar instance
let calendar;

// Function to initialize the calendar
function initCalendar() {
    const calendarEl = document.getElementById('classes-calendar');
    if (!calendarEl) return;

    // Destroy existing calendar if it exists
    if (calendar) {
        calendar.destroy();
    }

    // Initialize the calendar
    calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, bootstrap5Plugin],
        themeSystem: 'bootstrap5',
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
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
            // Add tooltip with class details
            if (info.event.extendedProps.description) {
                new bootstrap.Tooltip(info.el, {
                    title: info.event.extendedProps.description,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
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
    
    // Initialize tooltips using Bootstrap 5
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
