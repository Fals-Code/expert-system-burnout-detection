<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMbiAssessmentRequest;
use App\Models\MbiAssessment;
use App\Models\MbiItem;
use App\Services\MbiExplanationService;
use App\Services\MbiScoringService;
use App\Services\NotificationService;
use App\Services\SafetyFlagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class DeteksiController extends Controller
{
    public function __construct(
        private readonly MbiScoringService $scoringService,
        private readonly MbiExplanationService $explanationService,
        private readonly SafetyFlagService $safetyFlagService
    ) {
    }

    public function intro(): View
    {
        return view('karyawan.deteksi.index', ['savedSession' => null]);
    }

    public function index(): View
    {
        $items = MbiItem::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        $expectedItemCount = (int) config('mbi.instrument.expected_item_count', 16);
        $instrumentReady = $items->count() === $expectedItemCount
            && $items->every(fn (MbiItem $item): bool => filled($item->prompt_text));

        return view('karyawan.deteksi.form', [
            'items' => $items,
            'instrumentReady' => $instrumentReady,
            'expectedItemCount' => $expectedItemCount,
            'responseScale' => config('mbi.instrument.response_scale', []),
        ]);
    }

    public function next(StoreMbiAssessmentRequest $request): RedirectResponse
    {
        $responses = $request->validatedResponses();
        $scoreResult = $this->scoringService->score($responses);
        $safetyResult = $this->safetyFlagService->evaluate($request->redFlagResponse());

        $assessment = DB::transaction(function () use ($responses, $scoreResult, $safetyResult): MbiAssessment {
            $dimensions = $scoreResult['dimensions'];
            $assessment = MbiAssessment::query()->create([
                'user_id' => Auth::id(),
                'instrument_code' => config('mbi.instrument.code', 'MBI-GS'),
                'instrument_version' => config('mbi.instrument.version', '1996/2016'),
                'status' => $scoreResult['status'],
                'responses_count' => $scoreResult['responses_count'],
                'ex_total' => $dimensions['EX']['total'],
                'ex_score' => $dimensions['EX']['mean'],
                'cy_total' => $dimensions['CY']['total'],
                'cy_score' => $dimensions['CY']['mean'],
                'pe_total' => $dimensions['PE']['total'],
                'pe_score' => $dimensions['PE']['mean'],
                'profile_code' => $scoreResult['profile']['code'],
                'profile_basis' => $scoreResult['profile']['basis'],
                'has_red_flag' => $safetyResult['has_red_flag'],
                'red_flag_response' => $safetyResult['response'],
                'red_flag_codes' => $safetyResult['codes'],
                'disclaimer_version' => config('mbi.disclaimer_version', 'mbi-gs-screening-v1'),
                'completed_at' => now(),
            ]);

            $itemsByCode = MbiItem::query()
                ->whereIn('code', array_keys($responses))
                ->get()
                ->keyBy('code');

            $assessment->responses()->createMany(
                collect($responses)
                    ->map(function (int $score, string $code) use ($itemsByCode): array {
                        return [
                            'item_id' => $itemsByCode->get($code)->id,
                            'score' => $score,
                        ];
                    })
                    ->values()
                    ->all()
            );

            return $assessment;
        });

        Session::put('last_mbi_assessment_id', $assessment->id);

        NotificationService::send(
            Auth::id(),
            'Profil MBI-GS Tersimpan',
            'Tiga skor dimensi MBI-GS Anda telah dihitung dan tersedia pada halaman hasil.',
            icon: 'check-circle',
            color: '#2563eb'
        );

        return redirect()->route('karyawan.hasil', ['id' => $assessment->id]);
    }

    public function showResult(Request $request): View|RedirectResponse
    {
        $id = $request->integer('id') ?: Session::get('last_mbi_assessment_id');

        if (! $id) {
            return redirect()->route('karyawan.dashboard');
        }

        $assessment = MbiAssessment::query()
            ->with(['responses.item', 'user'])
            ->find($id);

        if (! $assessment) {
            return redirect()
                ->route('karyawan.dashboard')
                ->with('error', 'Hasil skrining tidak ditemukan.');
        }

        abort_if((int) $assessment->user_id !== (int) Auth::id(), 403);

        return view('karyawan.deteksi.hasil', [
            'assessment' => $assessment,
            'explanation' => $this->explanationService->build($assessment),
        ]);
    }

    public function downloadReport(int $id): View|RedirectResponse
    {
        $assessment = MbiAssessment::query()
            ->with(['responses.item', 'user'])
            ->find($id);

        if (! $assessment) {
            return redirect()
                ->route('karyawan.dashboard')
                ->with('error', 'Hasil skrining tidak ditemukan.');
        }

        abort_if((int) $assessment->user_id !== (int) Auth::id(), 403);

        return view('karyawan.deteksi.report', [
            'assessment' => $assessment,
            'explanation' => $this->explanationService->build($assessment),
        ]);
    }

    public function reset(): RedirectResponse
    {
        Session::forget('last_mbi_assessment_id');

        return redirect()
            ->route('karyawan.deteksi')
            ->with('info', 'Form skrining baru telah disiapkan.');
    }

    public function saveSession(): RedirectResponse
    {
        return redirect()
            ->route('karyawan.deteksi')
            ->with('info', 'Untuk menjaga integritas administrasi MBI-GS, seluruh 16 item harus diselesaikan dalam satu sesi.');
    }

    public function resumeSession(): RedirectResponse
    {
        return redirect()->route('karyawan.deteksi');
    }
}
