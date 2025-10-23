<?php

namespace App\Http\Controllers;

use App\Models\FitnessClass;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorDashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->instructor) {
            return view('instructor.dashboard', ['upcomingClasses' => collect()]);
        }

        try {
            // Get all upcoming classes for this instructor
            $upcomingClasses = $user->instructor->fitnessClasses()
                ->where('class_date', '>=', now()->toDateString())
                ->orderBy('class_date')
                ->orderBy('start_time')
                ->get();

            // For each class, load the bookings for that specific date
            $upcomingClasses->each(function ($class) {
                $bookings = Booking::where('fitness_class_id', $class->id)
                    ->where('booking_date', $class->class_date)
                    ->where('status', 'confirmed')
                    ->with('user')
                    ->get();
                
                $class->setRelation('bookings', $bookings);
            });

            $upcomingOccurrences = $upcomingClasses;
        } catch (\Exception $e) {
            \Log::error('Instructor Dashboard Error', [
                'instructor_id' => $user->instructor->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $upcomingOccurrences = collect();
        }

        // Debug: Log the results to help troubleshoot
        \Log::info('Instructor Dashboard Debug', [
            'instructor_id' => $user->instructor->id,
            'classes_count' => $upcomingOccurrences->count(),
            'total_bookings' => $upcomingOccurrences->sum(function($class) { return $class->bookings->count(); }),
        ]);

        return view('instructor.dashboard', [
            'upcomingClasses' => $upcomingOccurrences,
        ]);
    }

    public function previousClasses()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $previousClasses = $user->instructor ? $user->instructor->fitnessClasses()
            ->where('class_date', '<', now()->toDateString())
            ->orderBy('class_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(15) : collect();

        return view('instructor.classes.previous', [
            'previousClasses' => $previousClasses,
        ]);
    }

    public function showMembers(FitnessClass $class, $date = null)
    {
        // Ensure the logged-in instructor is authorized to see this class's members
        $instructorId = Auth::user()->instructor->id;
        if ($class->instructor_id !== $instructorId) {
            abort(403, 'Unauthorized action.');
        }

        $bookingDate = $date ? \Carbon\Carbon::parse($date) : $class->class_date;

        // Filter bookings by the specific class date to show only members for this occurrence
        $members = Booking::where('fitness_class_id', $class->id)
            ->where('booking_date', $bookingDate->toDateString())
            ->where('status', 'confirmed')
            ->with('user')
            ->get();

        return view('instructor.classes.members', [
            'class' => $class,
            'members' => $members,
            'bookingDate' => $bookingDate,
        ]);
    }

    /**
     * Show QR scanner interface for a class
     */
    public function showScanner(FitnessClass $class)
    {
        // Ensure the logged-in instructor is authorized to scan for this class
        $instructorId = Auth::user()->instructor->id;
        if ($class->instructor_id !== $instructorId) {
            abort(403, 'Unauthorized action.');
        }

        return view('instructor.scanner', compact('class'));
    }

    /**
     * Process QR code scan and check in user
     */
    public function processQrScan(Request $request, FitnessClass $class)
    {
        // Ensure the logged-in instructor is authorized
        $instructorId = Auth::user()->instructor->id;
        if ($class->instructor_id !== $instructorId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'qr_code' => 'sometimes|string|nullable',
            'payload' => 'sometimes|string|nullable',
        ]);

        // Extract QR code robustly from either direct code or full payload URL/text
        $rawPayload = $request->input('payload');
        $rawCode = $request->input('qr_code');
        $parsed = $this->parseQrPayload($rawPayload ?: $rawCode);

        if (!$parsed || empty($parsed['qr'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code content.',
                'debug' => [
                    'parsed' => $parsed,
                ],
            ]);
        }

        // Normalize to uppercase to avoid collation issues
        $qr = strtoupper($parsed['qr']);

        // Prefer user_id from signed URL payload when available
        $user = null;
        if (!empty($parsed['user_id'])) {
            $user = User::find((int)$parsed['user_id']);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR: encoded user not found.',
                    'debug' => [
                        'parsed_qr' => $qr,
                        'parsed_user_id' => (int)$parsed['user_id']
                    ],
                ]);
            }
            if (strtoupper((string) $user->qr_code) !== $qr) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR does not match the encoded user.',
                    'user_name' => $user->name,
                    'debug' => [
                        'parsed_qr' => $qr,
                        'parsed_user_id' => (int)$parsed['user_id'],
                        'matched_user_id' => (int)$user->id,
                        'matched_user_qr' => strtoupper((string) $user->qr_code),
                    ],
                ]);
            }
        } else {
            // Fallback to lookup by QR code; detect duplicates to avoid misattribution
            $matches = User::whereRaw('UPPER(qr_code) = ?', [$qr])->limit(2)->get();
            if ($matches->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code. User not found.',
                    'debug' => [
                        'parsed_qr' => $qr,
                        'parsed_user_id' => null,
                    ],
                ]);
            }
            if ($matches->count() > 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate QR code detected. Please ask the user to open their latest QR email or regenerate their QR.',
                    'debug' => [
                        'parsed_qr' => $qr,
                        'duplicate_user_ids' => $matches->pluck('id')->values(),
                    ],
                ]);
            }
            $user = $matches->first();
        }

        // Check if user has a booking for this class on the specific date
        $booking = Booking::where('user_id', $user->id)
            ->where('fitness_class_id', $class->id)
            ->where('booking_date', $class->class_date)
            ->where('status', 'confirmed')
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => "{$user->name} is not booked for this class.",
                'user_name' => $user->name,
                'debug' => [
                    'parsed_qr' => $qr,
                    'matched_user_id' => (int)$user->id,
                    'class_id' => (int)$class->id,
                ],
            ]);
        }

        // Check if already checked in
        if ($booking->attended) {
            return response()->json([
                'success' => false,
                'message' => "{$user->name} has already been checked in at " . $booking->checked_in_at->format('g:i A'),
                'user_name' => $user->name,
                'already_checked_in' => true
            ]);
        }

        // Check in the user
        $booking->update([
            'attended' => true,
            'checked_in_at' => now(),
            'checked_in_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$user->name} successfully checked in!",
            'user_name' => $user->name,
            'checked_in_at' => now()->format('g:i A'),
            'debug' => [
                'parsed_qr' => $qr,
                'matched_user_id' => (int)$user->id,
                'class_id' => (int)$class->id,
            ],
        ]);
    }

    /**
     * Extract the QR code string from a raw scan payload or direct code.
     * Accepts:
     *  - Signed route format: /user/checkin/{user}/{qr_code}?signature=...
     *  - Direct codes like QRXXXXXXXX (letters/digits/hyphen)
     */
    private function parseQrPayload(?string $payload): ?array
    {
        if (!$payload) {
            return null;
        }
        $text = trim($payload);

        // Try parse as URL and extract the 4th segment as qr_code
        if (str_starts_with($text, 'http://') || str_starts_with($text, 'https://')) {
            try {
                $parts = parse_url($text);
                $path = $parts['path'] ?? '';
                $segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
                // Expect: ['user','checkin','{user}','{qr_code}']
                if (count($segments) >= 4 && strtolower($segments[0]) === 'user' && strtolower($segments[1]) === 'checkin') {
                    return [
                        'user_id' => is_numeric($segments[2]) ? (int)$segments[2] : null,
                        'qr' => $segments[3],
                    ];
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        // Direct code strict pattern: starts with QR and at least 6 more chars
        if (preg_match('/^QR[A-Za-z0-9\-]{6,}$/', $text)) {
            return ['user_id' => null, 'qr' => $text];
        }

        // Attempt to find QR... token anywhere as last resort (still strict)
        if (preg_match('/\b(QR[A-Za-z0-9\-]{6,})\b/', $text, $m)) {
            return ['user_id' => null, 'qr' => $m[1]];
        }

        return null;
    }
}
