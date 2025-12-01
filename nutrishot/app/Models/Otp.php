<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi
     */
    protected $fillable = [
        'email',
        'otp_code',
        'expires_at',
    ];

    /**
     * Casting untuk kolom tanggal
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
