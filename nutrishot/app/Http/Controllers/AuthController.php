<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Otp;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * 1. SEND OTP
     * Mengirim kode OTP ke email user (menghapus OTP lama)
     * Request: { email }
     */
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Jika email sudah terdaftar sebagai user, tolak
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'Email sudah terdaftar'], 409);
        }

        $otpCode = random_int(100000, 999999);

        // Hapus OTP lama untuk email ini
        Otp::where('email', $request->email)->delete();

        // Simpan OTP baru (string) + expiry 5 menit
        Otp::create([
            'email' => $request->email,
            'otp_code' => (string) $otpCode,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Kirim email (gunakan queue di production)
        Mail::to($request->email)->send(new SendOtpMail($otpCode));

        return response()->json(['message' => 'OTP telah dikirim ke email Anda']);
    }

    /**
     * 2. VERIFY OTP
     * Request: { email, otp_code }
     * Hanya memverifikasi, tidak membuat akun.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string'
        ]);

        $otp = Otp::where('email', $request->email)
            ->where('otp_code', $request->otp_code)
            ->where('expires_at', '>', now())
            ->first();

        if (! $otp) {
            return response()->json(['message' => 'OTP tidak valid atau sudah kedaluwarsa'], 400);
        }

        // Jika perlu, bisa tandai verifikasi di table OTP (opsional).
        // Tetapi untuk flow ini, kita cukup jawab valid.
        return response()->json(['message' => 'OTP valid']);
    }

    /**
     * 3. REGISTER (create account)
     * Request: { email, otp_code, password, password_confirmation }
     * NOTE: register hanya boleh jika OTP masih valid.
     */
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'otp_code' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        // cek OTP valid lagi sebelum membuat user
        $otp = Otp::where('email', $request->email)
            ->where('otp_code', $request->otp_code)
            ->where('expires_at', '>', now())
            ->first();

        if (! $otp) {
            return response()->json(['message' => 'OTP tidak valid atau sudah kedaluwarsa'], 400);
        }

        // Buat user dalam transaction
        DB::beginTransaction();
        try {
            $user = User::create([
                'email' => $request->email,
                // di UI kamu tidak meminta name -> bisa diisi nanti
                'name' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            // hapus OTP yang sudah dipakai
            Otp::where('email', $request->email)->delete();

            // generate token login (sanctum)
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Registrasi berhasil',
                'token' => $token,
                'user' => $user
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            // log($e->getMessage());
            return response()->json(['message' => 'Gagal membuat user'], 500);
        }
    }

    /**
     * 4. LOGIN (email + password)
     * Request: { email, password }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * 5. SAVE USER PROFILE
     * Request (auth bearer): { tinggi_badan, berat_badan, tanggal_lahir, jenis_kelamin }
     * Dipanggil setelah user daftar & login lalu mengisi profil.
     */
    public function saveProfile(Request $request)
    {
        $request->validate([
            'tinggi_badan' => 'required|integer',
            'berat_badan' => 'required|integer',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
        ]);

        $user = $request->user();

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'tinggi_badan' => $request->tinggi_badan,
                'berat_badan' => $request->berat_badan,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]
        );

        // reload profile relasi
        $user->load('profile');

        return response()->json([
            'message' => 'Profile saved',
            'profile' => $user->profile
        ]);
    }

    /**
     * 6. LOGOUT
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
