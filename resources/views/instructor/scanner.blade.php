@extends('admin.layout')

@push('head')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <style>
        @supports (-webkit-touch-callout: none) {
            /* Fix for iOS viewport units */
            .ios-fix {
                height: -webkit-fill-available !important;
            }
            /* Force portrait orientation for iOS */
            @media screen and (max-width: 768px) {
                #scanner-container {
                    transform: rotate(0deg);
                    -webkit-transform: rotate(0deg);
                }
            }
        }
        /* Ensure scanner container maintains aspect ratio */
        #scanner-container {
            position: relative;
            width: 100%;
            padding-top: 100%; /* 1:1 Aspect Ratio */
            overflow: hidden;
        }
        #qr-reader {
            position: absolute;
            top: 0;
            left: 0;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
        }
        /* Scanner overlay */
        #scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
        }
    </style>
@endpush

@section('title', 'QR Scanner - ' . $class->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">QR Scanner</h1>
                <p class="text-indigo-100">{{ $class->name }}</p>
                <p class="text-sm text-indigo-200">
                    {{ \Carbon\Carbon::parse($class->class_date)->format('D, M j, Y') }} at 
                    {{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }}
                </p>
            </div>
            <div class="text-right">
                <a href="{{ route('instructor.classes.members', $class) }}" 
                   class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    View Members
                </a>
            </div>
        </div>
    </div>

    <!-- Scanner Interface -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- QR Scanner -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Scan QR Code</h2>
            
            <!-- Camera Preview (Html5Qrcode requires a container div, not a video tag) -->
            <div id="scanner-container" class="relative bg-black rounded-lg overflow-hidden mb-4">
                <div id="qr-reader" class="w-full h-full"></div>
                <div id="scanner-overlay" class="absolute inset-0 flex items-center justify-center">
                    <div class="w-64 h-64 border-4 border-indigo-500 rounded-lg"></div>
                </div>
            </div>

            <!-- Scanner Controls -->
            <div class="flex space-x-3 mb-4">
                <button id="start-scanner" 
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Start Camera
                </button>
                <button id="stop-scanner" 
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors" 
                        disabled>
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                    </svg>
                    Stop Camera
                </button>
            </div>

            <!-- Manual Input -->
            <div class="border-t border-gray-700 pt-4">
                <h3 class="text-lg font-medium text-white mb-3">Manual Entry</h3>
                <form id="manual-checkin-form" class="flex space-x-3">
                    <input type="text" 
                           id="manual-qr-code" 
                           placeholder="Enter QR code manually" 
                           class="flex-1 bg-gray-700 border border-gray-600 text-white px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Check In
                    </button>
                </form>
            </div>
        </div>

        <!-- Results Panel -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Check-in Results</h2>
            
            <!-- Status Messages -->
            <div id="scan-results" class="space-y-3">
                <div class="text-gray-400 text-center py-8">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                    <p>Ready to scan QR codes</p>
                </div>
            </div>

            <!-- Recent Check-ins -->
            <div class="border-t border-gray-700 pt-4 mt-6">
                <h3 class="text-lg font-medium text-white mb-3">Recent Check-ins</h3>
                <div id="recent-checkins" class="space-y-2">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include QR Scanner Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
let html5QrcodeScanner = null;
let recentCheckins = [];
let scanning = false;

document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('start-scanner');
    const stopBtn = document.getElementById('stop-scanner');
    const manualForm = document.getElementById('manual-checkin-form');
    const manualInput = document.getElementById('manual-qr-code');

    startBtn.addEventListener('click', startScanner);
    stopBtn.addEventListener('click', stopScanner);
    manualForm.addEventListener('submit', handleManualCheckin);

    function startScanner() {
        if (scanning) return;
        
        // Check if iOS
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || 
                     (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        
        // Clear any existing scanner
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(console.error);
        }
        
        html5QrcodeScanner = new Html5Qrcode("qr-reader");
        
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                let cameraId = devices[0].id;
                let facingMode = { ideal: "environment" };
                
                // Try to use back camera on mobile devices
                if (isIOS || /Android/i.test(navigator.userAgent)) {
                    for (let device of devices) {
                        const label = device.label.toLowerCase();
                        if (label.includes('back') || label.includes('rear') || 
                            (label.includes('camera') && !label.includes('front'))) {
                            cameraId = device.id;
                            break;
                        }
                    }
                }
                
                // iOS-specific configuration
                const config = isIOS ? {
                    fps: 5,  // Slower FPS for better performance
                    qrbox: { width: 200, height: 200 },
                    aspectRatio: 1.0,
                    disableFlip: true,  // Disable flip on iOS to prevent orientation issues
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true
                    },
                    videoConstraints: {
                        deviceId: cameraId ? { exact: cameraId } : undefined,
                        facingMode: facingMode,
                        width: { ideal: 1280 },
                        height: { ideal: 1280 },
                        frameRate: { ideal: 10, max: 15 }
                    }
                } : {
                    // Default config for other devices
                    fps: 10,
                    qrbox: { width: 220, height: 220 },
                    aspectRatio: 1.0,
                    disableFlip: false,
                    experimentalFeatures: { useBarCodeDetectorIfSupported: true },
                    videoConstraints: {
                        deviceId: cameraId ? { exact: cameraId } : undefined,
                        facingMode: facingMode,
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                };
                
                html5QrcodeScanner.start(
                    cameraId,
                    config,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    scanning = true;
                    startBtn.disabled = true;
                    stopBtn.disabled = false;
                    updateScanResults('info', 'Camera started. Point camera at QR code to scan.');
                }).catch(err => {
                    console.error('Unable to start scanning', err);
                    updateScanResults('error', 'Unable to start camera. Please check permissions and ensure you\'re using HTTPS.');
                });
            } else {
                updateScanResults('error', 'No cameras found on this device.');
            }
        }).catch(err => {
            console.error('Unable to get cameras', err);
            updateScanResults('error', 'Camera access denied. Please allow camera permissions and ensure you\'re using HTTPS.');
        });
    }

    function stopScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear().catch(() => {});
                scanning = false;
                startBtn.disabled = false;
                stopBtn.disabled = true;
                updateScanResults('info', 'Camera stopped.');
            }).catch(err => {
                console.error('Unable to stop scanning', err);
            });
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        console.log('QR Code scanned:', decodedText);
        
        // Stop scanning temporarily to prevent multiple scans
        if (html5QrcodeScanner) {
            html5QrcodeScanner.pause(true);
        }
        
        // Extract QR code from the scanned text
        const qrCode = extractQrCodeFromUrl(decodedText);
        if (qrCode) {
            processCheckin(qrCode, decodedText);
        } else {
            updateScanResults('error', `Invalid QR code format. Scanned: ${decodedText}`);
            // Resume scanning after error
            setTimeout(() => {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.resume();
                }
            }, 2000);
        }
    }

    function onScanFailure(error) {
        // Handle scan failure silently
    }

    function extractQrCodeFromUrl(url) {
        try { url = String(url); } catch(e) { return null; }
        // Accept signed route form: /user/checkin/{user}/{qr_code}
        let match = url.match(/\/user\/checkin\/\d+\/([A-Za-z0-9\-]+)/i);
        if (match) return match[1];
        // Accept raw QR strings like QRXXXXXXXX (case-insensitive) possibly longer
        match = url.match(/\b(QR[\w\-]{6,})\b/i);
        if (match) return match[1];
        return null;
    }

    function handleManualCheckin(e) {
        e.preventDefault();
        const qrCode = manualInput.value.trim();
        if (qrCode) {
            processCheckin(qrCode, qrCode);
            manualInput.value = '';
        }
    }

    function processCheckin(qrCode, payload) {
        updateScanResults('info', 'Processing check-in...');
        
        fetch(`{{ route('instructor.classes.scan', $class) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ qr_code: qrCode, payload: payload })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateScanResults('success', data.message);
                addRecentCheckin(data.user_name, data.checked_in_at, 'success');
                
                // Resume scanning after successful check-in
                setTimeout(() => {
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.resume();
                    }
                }, 1500);
            } else {
                const type = data.already_checked_in ? 'warning' : 'error';
                updateScanResults(type, data.message);
                if (data.user_name) {
                    addRecentCheckin(data.user_name, 'Already checked in', 'warning');
                }
                
                // Resume scanning after error
                setTimeout(() => {
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.resume();
                    }
                }, 1500);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            updateScanResults('error', 'An error occurred. Please try again.');
            
            // Resume scanning after error
            setTimeout(() => {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.resume();
                }
            }, 1500);
        });
    }

    function updateScanResults(type, message) {
        const resultsDiv = document.getElementById('scan-results');
        const iconMap = {
            success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
            info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        };
        
        const colorMap = {
            success: 'text-green-400 bg-green-500/20 border-green-500/30',
            error: 'text-red-400 bg-red-500/20 border-red-500/30',
            warning: 'text-yellow-400 bg-yellow-500/20 border-yellow-500/30',
            info: 'text-blue-400 bg-blue-500/20 border-blue-500/30'
        };

        resultsDiv.innerHTML = `
            <div class="flex items-center p-4 border rounded-lg ${colorMap[type]}">
                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconMap[type]}"></path>
                </svg>
                <p class="font-medium">${message}</p>
            </div>
        `;
    }

    function addRecentCheckin(userName, time, status) {
        recentCheckins.unshift({ userName, time, status, timestamp: Date.now() });
        recentCheckins = recentCheckins.slice(0, 5); // Keep only last 5
        
        const recentDiv = document.getElementById('recent-checkins');
        recentDiv.innerHTML = recentCheckins.map(checkin => {
            const statusColor = checkin.status === 'success' ? 'text-green-400' : 'text-yellow-400';
            return `
                <div class="flex items-center justify-between p-3 bg-gray-700 rounded-lg">
                    <div>
                        <p class="text-white font-medium">${checkin.userName}</p>
                        <p class="text-sm text-gray-400">${checkin.time}</p>
                    </div>
                    <div class="w-3 h-3 rounded-full ${checkin.status === 'success' ? 'bg-green-400' : 'bg-yellow-400'}"></div>
                </div>
            `;
        }).join('');
    }
});
</script>
@endsection
