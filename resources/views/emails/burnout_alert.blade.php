<x-mail::message>
# Peringatan: Deteksi Burnout Berat

Sistem kami mendeteksi adanya indikasi burnout tingkat **BERAT** pada karyawan berikut:

- **Nama:** {{ $konsultasi->user->nama }}
- **Divisi:** {{ $konsultasi->user->divisi->nama ?? 'N/A' }}
- **Waktu Deteksi:** {{ $konsultasi->created_at->format('d M Y H:i') }}
- **Skor CF:** {{ number_format($konsultasi->cf_hasil * 100, 2) }}%

Mohon segera lakukan peninjauan dan intervensi sesuai dengan kebijakan kesehatan mental perusahaan.

<x-mail::button :url="route('hrd.employees.history', $konsultasi->user_id)">
Lihat Detail Riwayat
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }} System
</x-mail::message>
