<?php

namespace App\Services;

use App\Models\Aturan;
use App\Models\Gejala;
use App\Models\Diagnosa;
use App\Models\Konsultasi;
use Illuminate\Support\Facades\Auth;

class ExpertSystemService
{
    protected $threshold = 0.25;

    public function getCfUser($answer): float
    {
        return match ($answer) {
            'Sering' => 1.0,
            'Kadang' => 0.6,
            default => 0.0,
        };
    }

    public function getNeededSymptoms(array $rules, array $answeredCodes): array
    {
        $needed = [];
        foreach ($rules as $rule) {
            foreach ($rule->gejala as $gejala) {
                if (!in_array($gejala->kode, $answeredCodes) && !in_array($gejala->kode, $needed)) {
                    $needed[] = $gejala->kode;
                }
            }
        }
        return $needed;
    }

    public function evaluateHypothesis($rules, array $answers): array
    {
        $highestCf = -1.0;
        $bestRule = null;
        $bestTracing = [];

        foreach ($rules as $rule) {
            $gejalaList = $rule->gejala;
            $count = $gejalaList->count();
            if ($count === 0) continue;

            $sumCfWeighted = 0.0;
            $trace = [];

            foreach ($gejalaList as $gejala) {
                $ans = $answers[$gejala->kode] ?? 'Tidak Pernah';
                $cfUser = $this->getCfUser($ans);
                $cfWeighted = $cfUser * $gejala->bobot;
                $sumCfWeighted += $cfWeighted;

                $trace[] = "- {$gejala->nama} ({$gejala->kode}): CF_user={$cfUser} × bobot={$gejala->bobot} = {$cfWeighted} [{$ans}]";
            }

            $avgCf = $sumCfWeighted / $count;
            $cfFinal = $avgCf * $rule->cf_pakar;

            if ($cfFinal > $highestCf) {
                $highestCf = $cfFinal;
                $bestRule = $rule;
                $bestTracing = [
                    'rule_id' => $rule->kode,
                    'cf_pakar' => $rule->cf_pakar,
                    'avg_gejala_cf' => $avgCf,
                    'cf_final' => $cfFinal,
                    'details' => $trace,
                ];
            }
        }

        return [$highestCf, $bestRule, $bestTracing];
    }

    public function saveResult($userId, $diagnosaId, $cfFinal, array $gejalaCodes)
    {
        $gejalaIds = Gejala::whereIn('kode', $gejalaCodes)->pluck('id');
        
        $konsultasi = Konsultasi::create([
            'user_id' => $userId,
            'diagnosa_id' => $diagnosaId,
            'cf_final' => $cfFinal,
        ]);

        $konsultasi->gejala()->attach($gejalaIds);

        return $konsultasi;
    }
}
