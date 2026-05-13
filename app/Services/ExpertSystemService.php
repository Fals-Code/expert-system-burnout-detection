<?php

namespace App\Services;

use App\Models\Aturan;
use App\Models\Gejala;
use App\Models\Diagnosa;
use App\Models\Konsultasi;
use Illuminate\Support\Facades\Auth;

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
     * Backward Chaining Engine
     * Mencoba membuktikan setiap hipotesis (Diagnosa) secara berurutan.
     * 
     * @param array $answers ['G01' => 'Sering', ...]
     * @return array [hasil_diagnosa, cf_final, tracing]
     */
    public function solve(array $answers): array
    {
        $diagnosas = Diagnosa::orderBy('tingkat', 'desc')->get(); // Mulai dari yang terberat (Sangat Tinggi)
        $results = [];

        foreach ($diagnosas as $diagnosa) {
            $rules = Aturan::where('diagnosa_id', $diagnosa->id)->with('gejala')->get();
            $cf_diagnosa = 0.0;
            $tracing_diagnosa = [];

            foreach ($rules as $rule) {
                $cf_rule_current = 0.0;
                $rule_trace = [];

                foreach ($rule->gejala as $gejala) {
                    $user_answer = $answers[$gejala->kode] ?? 'Tidak';
                    $cf_user = $this->getCfUser($user_answer);
                    
                    // Ambil bobot pakar dari pivot (aturan_gejala)
                    $bobot_pakar = $gejala->pivot->bobot_pakar ?? $gejala->bobot;
                    
                    // CF[H,E] = CF[E] * CF[Pakar]
                    $cf_gejala = $cf_user * $bobot_pakar;
                    
                    if ($cf_gejala > 0) {
                        // CF Combine: CF_old + CF_new * (1 - CF_old)
                        $cf_rule_current = $cf_rule_current + ($cf_gejala * (1 - $cf_rule_current));
                        $rule_trace[] = [
                            'gejala' => $gejala->nama,
                            'kode' => $gejala->kode,
                            'user_ans' => $user_answer,
                            'cf_user' => $cf_user,
                            'bobot' => $bobot_pakar,
                            'cf_sub' => $cf_gejala
                        ];
                    }
                }

                // Final CF for this rule = CF_gejala_combine * CF_rule_pakar
                $cf_rule_final = $cf_rule_current * $rule->cf_pakar;

                if ($cf_rule_final > $cf_diagnosa) {
                    $cf_diagnosa = $cf_rule_final;
                    $tracing_diagnosa = [
                        'rule_kode' => $rule->kode,
                        'cf_pakar_rule' => $rule->cf_pakar,
                        'gejala_details' => $rule_trace
                    ];
                }
            }

            if ($cf_diagnosa > 0) {
                $results[] = [
                    'diagnosa' => $diagnosa,
                    'cf' => $cf_diagnosa,
                    'tracing' => $tracing_diagnosa
                ];
            }
        }

        // Urutkan berdasarkan CF tertinggi
        usort($results, fn($a, $b) => $b['cf'] <=> $a['cf']);

        return $results[0] ?? [
            'diagnosa' => Diagnosa::where('tingkat', 'RENDAH')->first(),
            'cf' => 0.0,
            'tracing' => ['message' => 'Tidak ada gejala yang signifikan terdeteksi.']
        ];
    }

    /**
     * Mencari gejala berikutnya yang perlu ditanyakan (untuk mode Wizard)
     * Berdasarkan hipotesis yang sedang diproses.
     */
    public function getNextSymptoms(array $answeredCodes): array
    {
        // Cari hipotesis yang belum terbukti
        $diagnosas = Diagnosa::all();
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
            
            if (!empty($needed)) return array_slice($needed, 0, 3); // Tanya 3 gejala sekaligus
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
