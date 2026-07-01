<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
    public function index()
    {
        $faqs = [
            [
                'q' => 'Apa itu SanctuaryHub?',
                'a' => 'SanctuaryHub adalah sistem check-in kerja berbasis sistem pakar yang membantu membaca pola beban kerja, energi, dan kebutuhan dukungan karyawan.',
            ],
            [
                'q' => 'Bagaimana cara mengisi check-in?',
                'a' => 'Buka menu Check-in Kerja, jawab pertanyaan singkat sesuai kondisi 7 hari terakhir, lalu lihat ringkasan hasil pribadi.',
            ],
            [
                'q' => 'Apakah ini penilaian performa?',
                'a' => 'Bukan. Check-in dipakai sebagai bahan refleksi dan dukungan kerja, bukan sebagai ranking atau hukuman individu.',
            ],
            [
                'q' => 'Siapa yang dapat melihat data saya?',
                'a' => 'Anda melihat riwayat pribadi. HRD menggunakan data untuk membaca kebutuhan dukungan dan konteks kerja sesuai hak akses sistem.',
            ],
        ];

        return view('help.index', compact('faqs'));
    }
}
