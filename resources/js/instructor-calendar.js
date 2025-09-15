import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
// Removed Bootstrap theme/plugin to avoid conflicts

// Initialize the calendar when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('instructor-calendar');
    
    if (!calendarEl) return;

    // Enforce ProFontWindows and brand theme variables
    const enforceCalendarTheme = (el) => {
        el.style.setProperty('--fc-font-family', "'ProFontWindows', monospace", 'important');
        el.style.setProperty('--fc-page-bg-color', '#111827', 'important');
        el.style.setProperty('--fc-neutral-bg-color', '#1f2937', 'important');
        el.style.setProperty('--fc-neutral-text-color', '#e5e7eb', 'important');
        el.style.setProperty('--fc-border-color', '#374151', 'important');
        el.style.setProperty('--fc-button-text-color', '#ffffff', 'important');
        el.style.setProperty('--fc-button-bg-color', '#3b82f6', 'important');
        el.style.setProperty('--fc-button-border-color', '#3b82f6', 'important');
        el.style.setProperty('--fc-button-hover-bg-color', '#2563eb', 'important');
        el.style.setProperty('--fc-button-hover-border-color', '#2563eb', 'important');
        el.style.setProperty('--fc-button-active-bg-color', '#1d4ed8', 'important');
        el.style.setProperty('--fc-button-active-border-color', '#1d4ed8', 'important');
        el.style.setProperty('--fc-today-bg-color', 'rgba(59, 130, 246, 0.2)', 'important');
        el.style.setProperty('font-family', "'ProFontWindows', monospace", 'important');
    };
    enforceCalendarTheme(calendarEl);

    // Get the instructor ID from the data attribute
    const instructorId = calendarEl.dataset.instructorId;
    
    // Initialize the calendar
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        datesSet: function() { enforceCalendarTheme(calendarEl); },
        events: {
            url: `/api/instructors/${instructorId}/classes`,
            method: 'GET',
            failure: function() {
                showAlertModal('There was an error fetching classes!', 'error');
            }
        },
        eventTimeFormat: { // like '14:30'
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        firstDay: 1, // Start week on Monday
        height: 'auto',
        nowIndicator: true,
        navLinks: true,
        selectable: false,
        selectMirror: true,
        dayMaxEvents: true,
        editable: false,
        eventClick: function(info) {
            // Open class details in a modal or navigate to the class page
            window.location.href = `/admin/classes/${info.event.id}`;
        },
        loading: function(isLoading) {
            const loadingEl = document.getElementById('calendar-loading');
            if (loadingEl) {
                loadingEl.style.display = isLoading ? 'block' : 'none';
            }
        }
    });

    calendar.render();
});
