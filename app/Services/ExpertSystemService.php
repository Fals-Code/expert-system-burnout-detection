<?php

namespace App\Services;

use App\Models\Gejala;
use App\Models\Konsultasi;
use Illuminate\Support\Facades\Session;

class ExpertSystemService
{
    private BackwardChainingEngine $engine;

    private CertaintyFactorCalculator $calculator;

    public function __construct(?BackwardChainingEngine $engine = null, ?CertaintyFactorCalculator $calculator = null)
    {
        $this->calculator = $calculator ?? new CertaintyFactorCalculator;
        $this->engine = $engine ?? new BackwardChainingEngine($this->calculator);
    }

    public function getCfUser($answer): float
    {
        return $this->calculator->userCf($answer);
    }

    public function getDirectedCfUser($answer, string $evidenceDirection = 'PRESENT_SUPPORTS'): float
    {
        return $this->getCfUser($answer);
    }

    public function solve(array $answers, string $conflictStrategy = 'goal_priority'): array
    {
        return $this->engine->solve($answers);
    }

    public function getNextSymptoms(array $answeredCodes, string $strategy = 'goal_stack'): array
    {
        $answers = Session::get('deteksi_answers', []);
        $next = $this->engine->nextSymptom($answers);

        if (! $next || in_array($next, $answeredCodes, true)) {
            return [];
        }

        return [$next];
    }

    public function getEmpatheticPhrasing(string $symptomCode, string $defaultName): string
    {
        $phrasings = [
            'G001' => 'Seberapa sering Anda merasa terkuras habis saat atau setelah bekerja?',
            'G002' => 'Seberapa sering Anda merasa letih dan tidak berenergi saat memulai hari kerja?',
            'G003' => 'Seberapa sering tuntutan pekerjaan membuat Anda merasa tegang atau kewalahan?',
            'G004' => 'Seberapa sering Anda mudah tersinggung atau frustrasi karena hal kecil di tempat kerja?',
            'G005' => 'Seberapa sering beban kerja fisik terasa berlebihan bagi Anda?',
            'G006' => 'Seberapa sering Anda merasakan kelelahan emosional yang mendalam?',
            'G007' => 'Seberapa sering Anda menarik diri dari interaksi di lingkungan kerja?',
            'G008' => 'Seberapa sering Anda merasa kontribusi pekerjaan Anda tidak berarti?',
            'G009' => 'Seberapa sering Anda bersikap sinis atau kehilangan ketertarikan terhadap pekerjaan?',
            'G010' => 'Seberapa sering Anda merasa kepercayaan diri terhadap hasil kerja menurun?',
            'G011' => 'Seberapa sering Anda mengalami keluhan fisik yang muncul saat tekanan kerja meningkat?',
            'G012' => 'Seberapa sering tidur Anda terganggu karena pikiran tentang pekerjaan?',
            'G013' => 'Seberapa sering Anda merasa hampa secara emosional saat berinteraksi dengan orang lain?',
            'G014' => 'Seberapa sering Anda tidak mampu pulih meskipun sudah beristirahat?',
            'G015' => 'Seberapa sering Anda defensif saat menerima tugas atau perubahan kerja?',
            'G016' => 'Seberapa sering Anda menunda pekerjaan karena merasa enggan memulainya?',
            'G017' => 'Seberapa sering Anda merasa tidak berdaya menghadapi tantangan kerja yang biasanya dapat diatasi?',
            'G018' => 'Seberapa sering Anda merasa usaha kerja Anda tidak mendapat apresiasi yang cukup?',
            'G019' => 'Seberapa sering ketegangan kerja terasa sebagai nyeri otot atau kaku tubuh?',
            'G020' => 'Seberapa sering tekanan kerja memengaruhi pola makan Anda?',
        ];

        return $phrasings[$symptomCode] ?? $defaultName;
    }

    public function saveResult($userId, array $result, array $allAnswers): Konsultasi
    {
        $konsultasi = Konsultasi::create([
            'user_id' => $userId,
            'diagnosa_id' => $result['diagnosa']->id,
            'cf_final' => $this->calculator->clamp((float) $result['cf']),
            'tracing' => $result['tracing'],
        ]);

        $gejalaIds = Gejala::query()
            ->whereIn('kode', array_keys($allAnswers))
            ->pluck('id')
            ->all();

        $konsultasi->gejala()->sync($gejalaIds);

        return $konsultasi;
    }

    public function generateExplanation(array $tracing, $diagnosa, float $cfFinal): array
    {
        $pct = round($cfFinal * 100, 1);
        $rule = $tracing['rule_kode'] ?? 'FALLBACK';
        $goal = $tracing['goal_terkonfirmasi'] ?? ($diagnosa->tingkat ?? '-');
        $average = number_format((float) ($tracing['cf_average_premis'] ?? 0.0), 4);
        $expert = number_format((float) ($tracing['cf_pakar_rule'] ?? 0.0), 2);
        $threshold = number_format((float) ($tracing['min_threshold'] ?? 0.25), 2);

        $details = collect($tracing['gejala_details'] ?? []);
        $dominant = $details
            ->filter(fn ($detail) => (float) ($detail['cf_sub'] ?? 0.0) > 0)
            ->sortByDesc('cf_sub')
            ->take(3)
            ->map(fn ($detail) => [
                'nama' => $detail['gejala'] ?? '-',
                'kode' => $detail['kode'] ?? '-',
                'kategori' => $detail['kategori'] ?? '-',
                'impact' => round(((float) ($detail['cf_sub'] ?? 0.0)) * 100, 1),
                'jawaban' => $detail['user_ans'] ?? '-',
                'evidence_direction' => 'PRESENT_SUPPORTS',
            ])
            ->values()
            ->all();

        $summary = "Hasil {$diagnosa->nama} dihitung dengan Backward Chaining goal-driven. "
            .'Sistem menguji goal Risiko Tinggi, Risiko Sedang, lalu Risiko Rendah. '
            ."Rule utama {$rule} menghasilkan CF {$pct}% dari rata-rata premis {$average} dikalikan CF pakar {$expert}.";

        if (($diagnosa->tingkat ?? '') === 'TIDAK_TERINDIKASI') {
            $summary = 'Tidak ada rule burnout yang mencapai threshold. Sistem mengembalikan fallback Tidak Terindikasi Burnout sebagai hasil skrining awal.';
        }

        return [
            'summary' => $summary,
            'reasoning_chain' => [
                'Goal dievaluasi berurutan: Risiko Tinggi, Risiko Sedang, Risiko Rendah.',
                "Goal terkonfirmasi: {$goal}.",
                "Rule utama: {$rule}.",
                "CF rule = rata-rata CF premis ({$average}) x CF pakar ({$expert}) = ".number_format($cfFinal, 4).'.',
                "Threshold konfirmasi: {$threshold}.",
            ],
            'dominant_symptoms' => $dominant,
            'confidence_label' => $this->calculator->confidenceLabel($cfFinal),
            'mbi_analysis' => [
                'ee_score' => 0.0,
                'dp_score' => 0.0,
                'pa_score' => 0.0,
                'ee_label' => 'Tidak diklaim sebagai diagnosis klinis',
                'dp_label' => 'Tidak diklaim sebagai diagnosis klinis',
                'pa_label' => 'Tidak diklaim sebagai diagnosis klinis',
            ],
        ];
    }
}
