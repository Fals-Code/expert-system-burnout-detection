<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index()
    {
        $faqs = [
            [
                'q' => 'Apa itu BurnoutXpert?',
                'a' => 'Sistem pakar berbasis web yang dirancang untuk mendeteksi tingkat kelelahan kerja (burnout) pada karyawan menggunakan metode Backward Chaining dan Certainty Factor.'
            ],
            [
                'q' => 'Bagaimana cara melakukan deteksi?',
                'a' => 'Klik menu "Mulai Deteksi" di sidebar, lalu jawab setiap pertanyaan gejala yang muncul sesuai dengan kondisi yang Anda rasakan akhir-akhir ini.'
            ],
            [
                'q' => 'Apakah hasil deteksi ini akurat?',
                'a' => 'Sistem menggunakan basis pengetahuan dari pakar psikologi. Namun, hasil ini bersifat skrining awal. Untuk diagnosa medis yang mendalam, silakan hubungi tenaga profesional.'
            ],
            [
                'q' => 'Siapa yang bisa melihat hasil deteksi saya?',
                'a' => 'Hasil deteksi Anda dapat dipantau oleh HRD untuk keperluan evaluasi kesehatan mental di tempat kerja secara kolektif maupun individual.'
            ]
        ];

        return view('help.index', compact('faqs'));
    }
}
