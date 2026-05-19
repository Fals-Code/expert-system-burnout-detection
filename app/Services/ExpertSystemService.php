<?php

namespace App\Services;

use App\Models\Aturan;
use App\Models\Gejala;
use App\Models\Diagnosa;
use App\Models\Konsultasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ExpertSystemService
{
    /**
     * Certainty Factor Mapping (CF_User)
     * Berdasarkan tingkat keyakinan pengguna
     */
    public function getCfUser($answer): float
    {
        return match ($answer) {
            'Sangat Sering', 'Pasti Ya', 'Ya' => 1.0,
            'Sering', 'Hampir Pasti'          => 0.8,
            'Kadang', 'Mungkin'               => 0.6,
            'Jarang', 'Ragu-ragu'             => 0.4,
            'Sangat Jarang', 'Sedikit'        => 0.2,
            'Tidak', 'Tidak Pernah'           => 0.0,
            default                           => 0.0,
        };
    }

    /**
     * Backward Chaining Engine (Native Logic Sync)
     * Mencoba membuktikan setiap hipotesis (Diagnosa) secara berurutan.
     * Menggunakan formula: avg(CF_user * bobot_gejala) * CF_pakar_rule
     * 
     * @param array $answers ['G01' => 'Sering', ...]
     * @return array [hasil_diagnosa, cf_final, tracing]
     */
    public function solve(array $answers): array
    {
        $diagnosas = Diagnosa::orderBy('id', 'asc')->get(); // Urutan sesuai DB (Tinggi, Sedang, Rendah)
        $threshold = 0.25;
        $final_result = null;

        foreach ($diagnosas as $diagnosa) {
            $rules = Aturan::where('diagnosa_id', $diagnosa->id)->with('gejala')->get();
            $highest_cf_for_diag = -1.0;
            $best_tracing = [];

            foreach ($rules as $rule) {
                $cf_rule_current = 0.0;
                $rule_trace = [];
                $gejala_count = $rule->gejala->count();

                if ($gejala_count === 0) continue;

                foreach ($rule->gejala as $gejala) {
                    $user_answer = $answers[$gejala->kode] ?? 'Tidak Pernah';
                    $cf_user = $this->getCfUser($user_answer);
                    $bobot_pakar = $gejala->pivot->bobot_pakar ?? $gejala->bobot;
                    
                    $cf_weighted = $cf_user * $bobot_pakar;
                    
                    // CF Combine
                    if ($cf_weighted > 0) {
                        $cf_rule_current = $cf_rule_current + ($cf_weighted * (1 - $cf_rule_current));
                    }

                    $rule_trace[] = [
                        'gejala' => $gejala->nama,
                        'kode' => $gejala->kode,
                        'user_ans' => $user_answer,
                        'cf_user' => $cf_user,
                        'bobot' => $bobot_pakar,
                        'cf_sub' => $cf_weighted
                    ];
                }

                $cf_final_rule = $cf_rule_current * $rule->cf_pakar;

                if ($cf_final_rule > $highest_cf_for_diag) {
                    $highest_cf_for_diag = $cf_final_rule;
                    $best_tracing = [
                        'rule_kode' => $rule->kode,
                        'cf_pakar_rule' => $rule->cf_pakar,
                        'cf_combine_gejala' => $cf_rule_current,
                        'gejala_details' => $rule_trace,
                        'method' => 'Backward Chaining (CF Combine)'
                    ];
                }
            }

            // Jika hipotesis ini terbukti (di atas threshold)
            if ($highest_cf_for_diag >= $threshold) {
                $final_result = [
                    'diagnosa' => $diagnosa,
                    'cf' => $highest_cf_for_diag,
                    'tracing' => $best_tracing
                ];
                break; // Backward Chaining berhenti saat hipotesis terbukti
            }
        }

        return $final_result ?? [
            'diagnosa' => Diagnosa::where('tingkat', 'RENDAH')->first(),
            'cf' => 0.0,
            'tracing' => ['message' => 'Tidak ada hipotesis yang mencapai ambang batas keyakinan (0.25).']
        ];
    }

    /**
     * Mencari gejala berikutnya yang perlu ditanyakan (untuk mode Wizard)
     * Berdasarkan hipotesis yang sedang diproses.
     */
    public function getNextSymptoms(array $answeredCodes): array
    {
        // Cari hipotesis yang belum terbukti
        $diagnosas = Diagnosa::orderBy('id', 'asc')->get();
        foreach ($diagnosas as $diagnosa) {
            $needed = Aturan::where('diagnosa_id', $diagnosa->id)
                ->with('gejala')
                ->get()
                ->pluck('gejala')
                ->flatten()
                ->whereNotIn('kode', $answeredCodes)
                ->pluck('kode')
                ->unique()
                ->toArray();
            
            if (!empty($needed)) {
                // Update session hypothesis label for UI
                Session::put('bc_engine.current_hypothesis', 'Evaluasi Fase: ' . $diagnosa->nama);
                return $needed; 
            }
        }
        
        return [];
    }

    /**
     * Simpan hasil konsultasi
     */
    public function saveResult($userId, array $result, array $allAnswers)
    {
        $konsultasi = Konsultasi::create([
            'user_id' => $userId,
            'diagnosa_id' => $result['diagnosa']->id,
            'cf_final' => $result['cf'],
            'tracing' => $result['tracing'], // Simpan sebagai JSON
        ]);

        // Simpan gejala yang dijawab "Ya" (CF > 0)
        $gejalaIds = [];
        foreach ($allAnswers as $kode => $ans) {
            if ($this->getCfUser($ans) > 0) {
                $gejala = Gejala::where('kode', $kode)->first();
                if ($gejala) $gejalaIds[] = $gejala->id;
            }
        }
        
        $konsultasi->gejala()->attach($gejalaIds);

        return $konsultasi;
    }

    /**
     * Explanation Facility – Menghasilkan penjelasan bahasa natural
     * tentang mengapa sistem sampai pada kesimpulan tertentu.
     *
     * @param array $tracing  Data tracing dari solve()
     * @param object $diagnosa Model Diagnosa
     * @param float $cfFinal  Nilai CF akhir
     * @return array ['summary' => string, 'reasoning_chain' => array, 'dominant_symptoms' => array]
     */
    public function generateExplanation(array $tracing, $diagnosa, float $cfFinal): array
    {
        $explanation = [
            'summary'           => '',
            'reasoning_chain'   => [],
            'dominant_symptoms'  => [],
            'confidence_label'  => '',
        ];

        // ── Confidence Label ──
        $pct = round($cfFinal * 100, 1);
        if ($pct >= 80) {
            $explanation['confidence_label'] = 'Sangat Yakin';
        } elseif ($pct >= 60) {
            $explanation['confidence_label'] = 'Cukup Yakin';
        } elseif ($pct >= 40) {
            $explanation['confidence_label'] = 'Cukup Mungkin';
        } elseif ($pct >= 25) {
            $explanation['confidence_label'] = 'Kemungkinan Rendah';
        } else {
            $explanation['confidence_label'] = 'Tidak Terkonfirmasi';
        }

        // ── Reasoning Chain (langkah-langkah inferensi) ──
        $explanation['reasoning_chain'][] = 'Sistem memulai proses Backward Chaining dari hipotesis tingkat burnout tertinggi (Sangat Tinggi) hingga terendah (Rendah).';

        if (isset($tracing['rule_kode'])) {
            $explanation['reasoning_chain'][] = "Sistem menguji aturan {$tracing['rule_kode']} yang berkaitan dengan diagnosis \"{$diagnosa->nama}\".";
            $explanation['reasoning_chain'][] = "Setiap gejala yang Anda laporkan dikalkulasi menggunakan formula CF (Certainty Factor) untuk mengukur tingkat keyakinan.";

            if (isset($tracing['cf_combine_gejala']) && isset($tracing['cf_pakar_rule'])) {
                $cfCombine = number_format($tracing['cf_combine_gejala'], 4);
                $cfPakar = number_format($tracing['cf_pakar_rule'], 2);
                $explanation['reasoning_chain'][] = "Hasil kombinasi seluruh gejala menghasilkan CF Gabungan = {$cfCombine}, dikalikan dengan bobot kepastian pakar ({$cfPakar}).";
                $explanation['reasoning_chain'][] = "Nilai CF akhir = {$pct}% — sistem mengkategorikan ini sebagai \"{$explanation['confidence_label']}\".";
            }
        } elseif (isset($tracing['message'])) {
            $explanation['reasoning_chain'][] = $tracing['message'];
        }

        // ── Dominant Symptoms ──
        if (isset($tracing['gejala_details'])) {
            $sorted = collect($tracing['gejala_details'])
                ->filter(fn($d) => $d['cf_sub'] > 0)
                ->sortByDesc('cf_sub')
                ->values();

            foreach ($sorted->take(3) as $detail) {
                $explanation['dominant_symptoms'][] = [
                    'nama'    => $detail['gejala'],
                    'kode'    => $detail['kode'],
                    'impact'  => round($detail['cf_sub'] * 100, 1),
                    'jawaban' => $detail['user_ans'],
                ];
            }
        }

        // ── Summary (Narasi Ringkas) ──
        $symptomCount = count($explanation['dominant_symptoms']);
        if ($symptomCount > 0) {
            $topSymptom = $explanation['dominant_symptoms'][0]['nama'];
            $explanation['summary'] = "Sistem menganalisis jawaban Anda terhadap {$symptomCount} gejala dominan dan menyimpulkan bahwa Anda berada pada kondisi \"{$diagnosa->nama}\" dengan tingkat keyakinan {$pct}%. "
                . "Faktor utama yang mempengaruhi diagnosis ini adalah gejala \"{$topSymptom}\" yang memiliki kontribusi terbesar dalam perhitungan. "
                . "Kesimpulan ini diperoleh melalui metode Backward Chaining yang dikombinasikan dengan Certainty Factor (CF) untuk menangani ketidakpastian dalam jawaban.";
        } else {
            $explanation['summary'] = "Berdasarkan analisis Backward Chaining, tidak ditemukan gejala signifikan yang menunjukkan burnout tinggi. "
                . "Sistem menyimpulkan kondisi Anda berada pada tingkat \"{$diagnosa->nama}\" dengan keyakinan {$pct}%.";
        }

        return $explanation;
    }
}
