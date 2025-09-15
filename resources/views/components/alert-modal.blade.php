<!-- Alert Modal Component -->
<div id="alert-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-black bg-opacity-80" onclick="closeAlertModal()"></div>

        <!-- Modal panel -->
        <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-gray-800 border border-gray-700 shadow-2xl rounded-xl">
            <!-- Icon -->
            <div class="flex items-center justify-center w-14 h-14 mx-auto mb-4 rounded-full" id="alert-icon-container">
                <!-- Success Icon -->
                <svg id="alert-success-icon" class="w-7 h-7 text-green-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <!-- Error Icon -->
                <svg id="alert-error-icon" class="w-7 h-7 text-red-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <!-- Info Icon -->
                <svg id="alert-info-icon" class="w-7 h-7 hidden" style="color: #c8b7ed" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <!-- Warning Icon -->
                <svg id="alert-warning-icon" class="w-7 h-7 text-yellow-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>

            <!-- Title -->
            <h3 id="alert-title" class="text-xl font-semibold text-white text-center mb-3">Alert</h3>

            <!-- Message -->
            <p id="alert-message" class="text-sm text-gray-300 text-center mb-6 leading-relaxed">Message content</p>

            <!-- Buttons -->
            <div class="flex justify-center space-x-3">
                <button id="alert-ok-btn" onclick="closeAlertModal()" class="px-6 py-2.5 text-sm font-medium text-black bg-primary border border-transparent rounded-lg hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary focus:ring-offset-gray-800 transition-all duration-200" style="background-color: #c8b7ed">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global alert modal functions
function showAlertModal(message, type = 'info', title = null) {
    const modal = document.getElementById('alert-modal');
    const titleEl = document.getElementById('alert-title');
    const messageEl = document.getElementById('alert-message');
    const iconContainer = document.getElementById('alert-icon-container');
    
    // Hide all icons first
    document.querySelectorAll('[id^="alert-"][id$="-icon"]').forEach(icon => {
        icon.classList.add('hidden');
    });
    
    // Set message
    messageEl.textContent = message;
    
    // Set title and icon based on type
    switch(type) {
        case 'success':
            titleEl.textContent = title || 'Success';
            document.getElementById('alert-success-icon').classList.remove('hidden');
            iconContainer.className = 'flex items-center justify-center w-14 h-14 mx-auto mb-4 rounded-full bg-green-900/30 border border-green-700';
            break;
        case 'error':
            titleEl.textContent = title || 'Error';
            document.getElementById('alert-error-icon').classList.remove('hidden');
            iconContainer.className = 'flex items-center justify-center w-14 h-14 mx-auto mb-4 rounded-full bg-red-900/30 border border-red-700';
            break;
        case 'warning':
            titleEl.textContent = title || 'Warning';
            document.getElementById('alert-warning-icon').classList.remove('hidden');
            iconContainer.className = 'flex items-center justify-center w-14 h-14 mx-auto mb-4 rounded-full bg-yellow-900/30 border border-yellow-700';
            break;
        default: // info
            titleEl.textContent = title || 'Information';
            document.getElementById('alert-info-icon').classList.remove('hidden');
            iconContainer.className = 'flex items-center justify-center w-14 h-14 mx-auto mb-4 rounded-full bg-gray-700 border border-gray-600';
            iconContainer.style.backgroundColor = 'rgba(200, 183, 237, 0.2)';
            iconContainer.style.borderColor = '#c8b7ed';
    }
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAlertModal() {
    const modal = document.getElementById('alert-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

// Override the native alert function
window.alert = function(message) {
    showAlertModal(message, 'info');
};

// Confirmation modal function
function showConfirmModal(message, onConfirm, onCancel = null) {
    const modal = document.getElementById('alert-modal');
    const titleEl = document.getElementById('alert-title');
    const messageEl = document.getElementById('alert-message');
    const iconContainer = document.getElementById('alert-icon-container');
    const okBtn = document.getElementById('alert-ok-btn');
    
    // Hide all icons first
    document.querySelectorAll('[id^="alert-"][id$="-icon"]').forEach(icon => {
        icon.classList.add('hidden');
    });
    
    // Set message and title
    titleEl.textContent = 'Confirm Action';
    messageEl.textContent = message;
    
    // Show warning icon
    document.getElementById('alert-warning-icon').classList.remove('hidden');
    iconContainer.className = 'flex items-center justify-center w-14 h-14 mx-auto mb-4 rounded-full bg-yellow-900/30 border border-yellow-700';
    
    // Replace OK button with Yes/No buttons
    okBtn.outerHTML = `
        <div class="flex justify-center space-x-3">
            <button id="confirm-yes-btn" class="px-6 py-2.5 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 focus:ring-offset-gray-800 transition-all duration-200">
                Yes
            </button>
            <button id="confirm-no-btn" class="px-6 py-2.5 text-sm font-medium text-gray-300 bg-gray-700 border border-gray-600 rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 focus:ring-offset-gray-800 transition-all duration-200">
                No
            </button>
        </div>
    `;
    
    // Add event listeners
    document.getElementById('confirm-yes-btn').onclick = function() {
        closeAlertModal();
        if (onConfirm) onConfirm();
        // Restore original OK button
        restoreOkButton();
    };
    
    document.getElementById('confirm-no-btn').onclick = function() {
        closeAlertModal();
        if (onCancel) onCancel();
        // Restore original OK button
        restoreOkButton();
    };
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function restoreOkButton() {
    const buttonContainer = document.querySelector('#alert-modal .flex.justify-center.space-x-3');
    if (buttonContainer) {
        buttonContainer.outerHTML = `
            <div class="flex justify-center space-x-3">
                <button id="alert-ok-btn" onclick="closeAlertModal()" class="px-6 py-2.5 text-sm font-medium text-black bg-primary border border-transparent rounded-lg hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary focus:ring-offset-gray-800 transition-all duration-200" style="background-color: #c8b7ed">
                    OK
                </button>
            </div>
        `;
    }
}

// Add keyboard support
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('alert-modal');
        if (!modal.classList.contains('hidden')) {
            closeAlertModal();
        }
    }
});
</script>
