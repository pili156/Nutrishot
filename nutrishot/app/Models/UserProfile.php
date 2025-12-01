<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi
     */
    protected $fillable = [
        'user_id',
        'tinggi_badan',
        'berat_badan',
        'tanggal_lahir',
        'jenis_kelamin',
    ];

    /**
     * Format cast untuk tanggal
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /**
     * Relasi ke tabel users
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
