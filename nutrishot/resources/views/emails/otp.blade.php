<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Kode OTP Nutrishot</title>
    </head>
    <body style="font-family: Arial, Helvetica, sans-serif; color: #333;">
        <div style="max-width:600px; margin:0 auto; padding:20px;">
            <h2 style="color:#0b6b3a;">Nutrishot</h2>
            <p>Halo,</p>
            <p>Berikut kode OTP Anda untuk aksi yang diminta:</p>
            <p style="font-size:28px; letter-spacing:4px; font-weight:700;">{{ $otp }}</p>
            <p>Kode ini akan kadaluarsa pada {{ $expiresAt->format('Y-m-d H:i') }}.</p>
            <p>Jika Anda tidak meminta kode ini, abaikan pesan ini.</p>
            <hr>
            <p style="font-size:12px; color:#666;">&copy; {{ date('Y') }} Nutrishot</p>
        </div>
    </body>
</html>
