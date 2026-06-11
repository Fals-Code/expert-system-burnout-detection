<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCbiAssessmentRequest;
use App\Models\CbiAssessment;
use App\Models\CbiItem;
use App\Services\CbiExplanationService;
use App\Services\CbiScoringService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Throwable;

class DeteksiController extends Controller
{
    public function __construct(
        private readonly CbiScoringService $scoringService,
        private readonly CbiExplanationService $explanationService
    ) {
    }

    public function intro(): View
    {
        return view('karyawan.deteksi.index', ['savedSession' => null]);
    }

    public function index(): View
    {
        $items = CbiItem::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        $expectedItemCount = (int) config('cbi.instrument.expected_item_count', 19);

        return view('karyawan.deteksi.form', [
            'items' => $items,
            'instrumentReady' => $items->count() === $expectedItemCount,
            'expectedItemCount' => $expectedItemCount,
            'responseOptions' => config('cbi.response_options', []),
            'translationNote' => config('cbi.translation_note'),
        ]);
    }

    public function next(StoreCbiAssessmentRequest $request): RedirectResponse
    {
        $scoreResult = $this->scoringService->score(
            $request->validatedResponses()
        );

        $assessment = DB::transaction(function () use ($scoreResult): CbiAssessment {
            $dimensions = $scoreResult['dimensions'];

            $assessment = CbiAssessment::query()->create([
                'user_id' => Auth::id(),
                'instrument_code' => config('cbi.instrument.code', 'CBI'),
                'instrument_version' => config('cbi.instrument.version', '2005-ID-adapted'),
                'status' => $scoreResult['status'],
                'responses_count' => $scoreResult['responses_count'],
                'personal_total' => $dimensions['PB']['total'],
                'personal_score' => $dimensions['PB']['mean'],
                'work_total' => $dimensions['WB']['total'],
                'work_score' => $dimensions['WB']['mean'],
                'client_total' => $dimensions['CB']['total'],
                'client_score' => $dimensions['CB']['mean'],
                'disclaimer_version' => config('cbi.disclaimer_version', 'cbi-screening-v1'),
                'completed_at' => now(),
            ]);

            $assessment->responses()->createMany(
                collect($scoreResult['normalized_responses'])
                    ->map(fn (array $response): array => [
                        'item_id' => $response['item_id'],
                        'answer_key' => $response['answer_key'],
                        'raw_score' => $response['raw_score'],
                        'normalized_score' => $response['normalized_score'],
                    ])
                    ->values()
                    ->all()
            );

            return $assessment;
        });

        Session::put('last_cbi_assessment_id', $assessment->id);

        try {
            NotificationService::send(
                (int) Auth::id(),
                'Profil CBI Tersimpan',
                'Tiga skor dimensi Copenhagen Burnout Inventory Anda telah dihitung.',
                icon: 'check-circle',
                color: '#2563eb'
            );
        } catch (Throwable $exception) {
            Log::error('Gagal mengirim notifikasi setelah hasil CBI tersimpan.', [
                'assessment_id' => $assessment->id,
                'user_id' => Auth::id(),
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('karyawan.hasil', ['id' => $assessment->id]);
    }

    public function showResult(Request $request): View|RedirectResponse
    {
        $id = $request->integer('id') ?: Session::get('last_cbi_assessment_id');

        if (! $id) {
            return redirect()->route('karyawan.dashboard');
        }

        $assessment = CbiAssessment::query()
            ->with(['responses.item', 'user'])
            ->find($id);

        if (! $assessment) {
            return redirect()
                ->route('karyawan.dashboard')
                ->with('error', 'Hasil skrining CBI tidak ditemukan.');
        }

        abort_if((int) $assessment->user_id !== (int) Auth::id(), 403);

        return view('karyawan.deteksi.hasil', [
            'assessment' => $assessment,
            'explanation' => $this->explanationService->build($assessment),
        ]);
    }

    public function downloadReport(int $id): View|RedirectResponse
    {
        $assessment = CbiAssessment::query()
            ->with(['responses.item', 'user'])
            ->find($id);

        if (! $assessment) {
            return redirect()
                ->route('karyawan.dashboard')
                ->with('error', 'Hasil skrining CBI tidak ditemukan.');
        }

        abort_if((int) $assessment->user_id !== (int) Auth::id(), 403);

        return view('karyawan.deteksi.report', [
            'assessment' => $assessment,
            'explanation' => $this->explanationService->build($assessment),
        ]);
    }

    public function reset(): RedirectResponse
    {
        Session::forget('last_cbi_assessment_id');

        return redirect()
            ->route('karyawan.deteksi')
            ->with('info', 'Form skrining CBI baru telah disiapkan.');
    }

    public function saveSession(): RedirectResponse
    {
        return redirect()
            ->route('karyawan.deteksi')
            ->with('info', 'Seluruh 19 item CBI harus diselesaikan dalam satu sesi.');
    }

    public function resumeSession(): RedirectResponse
    {
        return redirect()->route('karyawan.deteksi');
    }
}
