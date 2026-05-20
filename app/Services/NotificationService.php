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
     * - HRD: alert if burnout level is Moderate, High, or Severe (SEDANG, TINGGI, SANGAT TINGGI)
     */
    public static function dispatchAfterDeteksi(Konsultasi $konsultasi, User $user, Diagnosa $diagnosa): void
    {
        // 1. Notifikasi untuk karyawan yang baru saja menyelesaikan deteksi
        self::send(
            $user->id,
            'Hasil Deteksi Tersimpan',
            "Deteksi burnout Anda telah selesai. Hasil: **{$diagnosa->nama}** dengan tingkat keyakinan " . number_format($konsultasi->cf_final * 100, 1) . "%. Lihat riwayat Anda untuk detail lengkap.",
            'informasi',
            'check-circle',
            '#2563eb'
        );

        // 2. Kirim peringatan ke semua tim HRD jika terdeteksi burnout Sedang, Tinggi, atau Sangat Tinggi
        if (in_array($diagnosa->tingkat, ['SEDANG', 'TINGGI', 'SANGAT TINGGI'])) {
            $hrdUsers = User::where('role', 'hrd')->get();
            $levelName = $diagnosa->tingkat === 'SEDANG' ? 'Sedang' : ($diagnosa->tingkat === 'TINGGI' ? 'Tinggi' : 'Sangat Tinggi');
            $title = "⚠️ Peringatan Burnout {$levelName}";

            foreach ($hrdUsers as $hrd) {
                self::send(
                    $hrd->id,
                    $title,
                    "Karyawan **{$user->nama}** (" . ($user->divisi->nama ?? 'N/A') . ") terdeteksi dengan tingkat burnout **{$diagnosa->tingkat}** ({$diagnosa->nama}). Segera lakukan tindak lanjut.",
                    'peringatan',
                    'alert-triangle',
                    '#dc2626'
                );

                // Kirim Email (Asinkron via ShouldQueue agar tidak blocking)
                try {
                    \Illuminate\Support\Facades\Mail::to($hrd->email)->send(new \App\Mail\BurnoutAlert($konsultasi));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Gagal kirim email ke {$hrd->email}: " . $e->getMessage());
                }

                // Kirim WhatsApp (Mendukung Twilio / Fonnte)
                if ($hrd->no_telp) {
                    $waMessage = "[🚨 ALERT BURNOUTXPERT]\n\n" .
                                 "Halo {$hrd->nama},\n" .
                                 "Sistem mendeteksi tingkat burnout: *{$diagnosa->tingkat}* ({$diagnosa->nama}) pada karyawan:\n\n" .
                                 "Nama: {$user->nama}\n" .
                                 "Divisi: " . ($user->divisi->nama ?? 'N/A') . "\n" .
                                 "Nilai CF: " . number_format($konsultasi->cf_final * 100, 1) . "%\n\n" .
                                 "Mohon segera tinjau dashboard HRD untuk detail dan rekomendasi pemulihan.";
                    self::sendWhatsApp($hrd->no_telp, $waMessage);
                }
            }
        }
    }

    /**
     * Dispatch a generic system notification to a user.
     */
    public static function send(int $userId, string $title, string $message, string $category = 'informasi', ?string $icon = null, ?string $color = null): void
    {
        Notification::create([
            'user_id' => $userId,
            'category' => $category,
            'title'   => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'is_read' => false,
        ]);
    }

    /**
     * Send WhatsApp message via Fonnte or Twilio (graceful fallback if not configured).
     */
    public static function sendWhatsApp(string $to, string $message): void
    {
        // Bersihkan nomor telepon ke format internasional standar
        $toCleaned = preg_replace('/[^0-9]/', '', $to);
        if (str_starts_with($toCleaned, '0')) {
            $toCleaned = '62' . substr($toCleaned, 1);
        }

        $fonnteToken = env('FONNTE_TOKEN');
        if ($fonnteToken) {
            try {
                \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => $fonnteToken
                ])->post('https://api.fonnte.com/send', [
                    'target' => $toCleaned,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal kirim WhatsApp via Fonnte ke {$toCleaned}: " . $e->getMessage());
            }
            return;
        }

        $twilioSid = env('TWILIO_SID');
        $twilioToken = env('TWILIO_AUTH_TOKEN');
        $twilioFrom = env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886');
        if ($twilioSid && $twilioToken) {
            try {
                \Illuminate\Support\Facades\Http::asForm()
                    ->withBasicAuth($twilioSid, $twilioToken)
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json", [
                        'To' => "whatsapp:+" . $toCleaned,
                        'From' => $twilioFrom,
                        'Body' => $message,
                    ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal kirim WhatsApp via Twilio ke {$toCleaned}: " . $e->getMessage());
            }
            return;
        }

        \Illuminate\Support\Facades\Log::warning("Notifikasi WhatsApp ke {$toCleaned} tidak terkirim: FONNTE_TOKEN atau TWILIO_SID belum dikonfigurasi.");
    }
}
