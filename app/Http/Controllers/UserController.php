<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserController extends Controller
{
    /**
     * Generate QR code for a user
     */
    public function generateQrCode(User $user)
    {
        // Only allow users to view their own QR code or admins to view any
        if (auth()->id() !== $user->id && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        // Generate QR code with user check-in URL
        $qrUrl = route('user.checkin', [
            'user' => $user->id,
            'qr_code' => $user->qr_code
        ]);

        return response(QrCode::format('svg')
            ->size(300)
            ->color(0, 0, 0)
            ->backgroundColor(255, 255, 255)
            ->generate($qrUrl))
            ->header('Content-Type', 'image/svg+xml');
    }
}
