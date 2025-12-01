<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;

class MailController extends Controller
{
    /**
     * Send OTP to a given email address.
     * Request payload: { "email": "user@example.com" }
     */
    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $email = $data['email'];

        // generate 6-digit numeric OTP
        $otpCode = random_int(100000, 999999);

        // set expiry (10 minutes)
        $expiresAt = Carbon::now()->addMinutes(10);

        // save OTP to database
        Otp::create([
            'email' => $email,
            'otp_code' => (string) $otpCode,
            'expires_at' => $expiresAt,
        ]);

        // send email using Laravel Mail (ensure mail is configured)
        Mail::to($email)->send(new OtpMail($otpCode, $expiresAt));

        return response()->json([
            'message' => 'OTP sent successfully',
        ]);
    }
}
