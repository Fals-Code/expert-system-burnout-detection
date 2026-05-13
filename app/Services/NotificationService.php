<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Konsultasi;
use App\Models\Diagnosa;

class NotificationService
{
    /**
     * Dispatch notifications after a diagnostic session completes.
     * - Karyawan: confirmation of their result
     * - HRD: alert if burnout level is BERAT
     */
    public static function dispatchAfterDeteksi(Konsultasi $konsultasi, User $user, Diagnosa $diagnosa): void
    {
        // 1. Notifikasi untuk karyawan yang baru saja menyelesaikan deteksi
        Notification::create([
            'user_id' => $user->id,
            'title'   => 'Hasil Deteksi Tersimpan',
            'message' => "Deteksi burnout Anda telah selesai. Hasil: **{$diagnosa->nama}** dengan tingkat keyakinan " . number_format($konsultasi->cf_final * 100, 1) . "%. Lihat riwayat Anda untuk detail lengkap.",
            'is_read' => false,
        ]);

        // 2. Jika burnout BERAT, kirim peringatan ke semua tim HRD
        if ($diagnosa->tingkat === 'BERAT') {
            $hrdUsers = User::where('role', 'hrd')->get();

            foreach ($hrdUsers as $hrd) {
                Notification::create([
                    'user_id' => $hrd->id,
                    'title'   => '⚠️ Peringatan Burnout Tinggi',
                    'message' => "Karyawan **{$user->nama}** (" . ($user->divisi->nama ?? 'N/A') . ") baru saja terdeteksi dengan tingkat burnout BERAT. Segera lakukan tindak lanjut.",
                    'is_read' => false,
                ]);
            }
        }
    }

    /**
     * Dispatch a generic system notification to a user.
     */
    public static function send(int $userId, string $title, string $message): void
    {
        Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }
}
