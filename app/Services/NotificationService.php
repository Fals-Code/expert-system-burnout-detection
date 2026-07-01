<?php

namespace App\Services;

use App\Mail\BurnoutAlert;
use App\Models\Diagnosa;
use App\Models\Konsultasi;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Dispatch notifications after a check-in session completes.
     */
    public static function dispatchAfterDeteksi(Konsultasi $konsultasi, User $user, Diagnosa $diagnosa): void
    {
        $score = number_format($konsultasi->cf_final * 100, 1);
        $supportLabel = match ((int) $diagnosa->id) {
            1 => 'Keseimbangan Stabil',
            2 => 'Butuh Dukungan Ekstra',
            3 => 'Perlu Pemantauan',
            4 => 'Perhatian Ringan',
            default => 'Ringkasan Check-in',
        };

        self::send(
            $user->id,
            'Check-in Tersimpan',
            "Ringkasan check-in Anda sudah tersedia: {$supportLabel}. Skor sistem: {$score}.",
            Notification::CATEGORY_INFORMATION,
            'check-circle',
            '#2563eb'
        );

        if (in_array($diagnosa->tingkat, ['SEDANG', 'TINGGI', 'SANGAT TINGGI'], true)) {
            $hrdUsers = User::where('role', 'hrd')->get();
            $title = 'Karyawan Perlu Perhatian';

            foreach ($hrdUsers as $hrd) {
                self::send(
                    $hrd->id,
                    $title,
                    'Ada check-in terbaru pada kategori yang perlu dipantau di dashboard agregat HRD. Identitas individu tidak ditampilkan melalui notifikasi.',
                    Notification::CATEGORY_SUPPORT,
                    'info',
                    '#f97316'
                );

                try {
                    Mail::to($hrd->email)->send(new BurnoutAlert($konsultasi));
                } catch (\Exception $e) {
                    Log::error("Gagal kirim email ke {$hrd->email}: ".$e->getMessage());
                }

                if ($hrd->no_telp) {
                    $waMessage = "[SanctuaryHub]\n\n".
                                 "Halo {$hrd->nama},\n".
                                 "Ada check-in terbaru pada kategori yang perlu dipantau.\n\n".
                                 'Silakan buka dashboard agregat HRD. Identitas individu tidak dikirim melalui notifikasi.';
                    self::sendWhatsApp($hrd->no_telp, $waMessage);
                }
            }
        }
    }

    /**
     * Dispatch a generic system notification to a user.
     */
    public static function send(
        int $userId,
        string $title,
        string $message,
        string $category = Notification::CATEGORY_INFORMATION,
        ?string $icon = null,
        ?string $color = null
    ): void {
        Notification::create([
            'user_id' => $userId,
            'category' => Notification::normalizeCategory($category),
            'title' => $title,
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
        $toCleaned = preg_replace('/[^0-9]/', '', $to);
        if (str_starts_with($toCleaned, '0')) {
            $toCleaned = '62'.substr($toCleaned, 1);
        }

        $fonnteToken = env('FONNTE_TOKEN');
        if ($fonnteToken) {
            try {
                Http::withHeaders([
                    'Authorization' => $fonnteToken,
                ])->post('https://api.fonnte.com/send', [
                    'target' => $toCleaned,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                Log::error("Gagal kirim WhatsApp via Fonnte ke {$toCleaned}: ".$e->getMessage());
            }

            return;
        }

        $twilioSid = env('TWILIO_SID');
        $twilioToken = env('TWILIO_AUTH_TOKEN');
        $twilioFrom = env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886');
        if ($twilioSid && $twilioToken) {
            try {
                Http::asForm()
                    ->withBasicAuth($twilioSid, $twilioToken)
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json", [
                        'To' => 'whatsapp:+'.$toCleaned,
                        'From' => $twilioFrom,
                        'Body' => $message,
                    ]);
            } catch (\Exception $e) {
                Log::error("Gagal kirim WhatsApp via Twilio ke {$toCleaned}: ".$e->getMessage());
            }

            return;
        }

        Log::warning("Notifikasi WhatsApp ke {$toCleaned} tidak terkirim: FONNTE_TOKEN atau TWILIO_SID belum dikonfigurasi.");
    }
}
