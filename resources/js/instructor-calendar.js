import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import bootstrap5Plugin from '@fullcalendar/bootstrap5';

// Initialize the calendar when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('instructor-calendar');
    
    if (!calendarEl) return;

    // Get the instructor ID from the data attribute
    const instructorId = calendarEl.dataset.instructorId;
    
    // Initialize the calendar
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, bootstrap5Plugin],
        themeSystem: 'bootstrap5',
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: {
            url: `/api/instructors/${instructorId}/classes`,
            method: 'GET',
            failure: function() {
                alert('There was an error fetching classes!');
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
