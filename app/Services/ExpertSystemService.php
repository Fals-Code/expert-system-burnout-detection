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
            'Sangat Sering', 'Pasti Ya' => 1.0,
            'Sering', 'Hampir Pasti'      => 0.8,
            'Kadang', 'Mungkin'           => 0.6,
            'Jarang', 'Ragu-ragu'         => 0.4,
            'Sangat Jarang', 'Sedikit'    => 0.2,
            default                      => 0.0,
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
                $sum_cf_weighted = 0.0;
                $rule_trace = [];
                $gejala_count = $rule->gejala->count();

                if ($gejala_count === 0) continue;

                foreach ($rule->gejala as $gejala) {
                    $user_answer = $answers[$gejala->kode] ?? 'Tidak Pernah';
                    $cf_user = $this->getCfUser($user_answer);
                    $bobot_pakar = $gejala->pivot->bobot_pakar ?? $gejala->bobot;
                    
                    $cf_weighted = $cf_user * $bobot_pakar;
                    $sum_cf_weighted += $cf_weighted;

                    $rule_trace[] = [
                        'gejala' => $gejala->nama,
                        'kode' => $gejala->kode,
                        'user_ans' => $user_answer,
                        'cf_user' => $cf_user,
                        'bobot' => $bobot_pakar,
                        'cf_sub' => $cf_weighted
                    ];
                }

                $avg_cf = $sum_cf_weighted / $gejala_count;
                $cf_final_rule = $avg_cf * $rule->cf_pakar;

                if ($cf_final_rule > $highest_cf_for_diag) {
                    $highest_cf_for_diag = $cf_final_rule;
                    $best_tracing = [
                        'rule_kode' => $rule->kode,
                        'cf_pakar_rule' => $rule->cf_pakar,
                        'avg_gejala_cf' => $avg_cf,
                        'gejala_details' => $rule_trace,
                        'method' => 'Backward Chaining (Average CF)'
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
        $diagnosas = Diagnosa::orderBy('tingkat', 'desc')->get();
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
}
