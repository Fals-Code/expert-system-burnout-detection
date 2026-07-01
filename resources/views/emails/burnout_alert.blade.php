<x-mail::message>
# Agregat Check-in Perlu Dipantau

Ada check-in terbaru pada kategori risiko yang perlu dipantau di dashboard HRD.

- **Kategori:** {{ $konsultasi->diagnosa->nama ?? 'Tidak tersedia' }}
- **Skor CF:** {{ number_format($konsultasi->cf_final * 100, 2) }}%
- **Waktu:** {{ $konsultasi->created_at->format('d M Y H:i') }}

Identitas individu dan jawaban mentah tidak dikirim melalui email untuk menjaga privasi karyawan.

<x-mail::button :url="route('hrd.dashboard')">
Buka Dashboard Agregat
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
