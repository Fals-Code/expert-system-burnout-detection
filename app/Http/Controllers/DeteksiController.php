<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInferenceAnswerRequest;
use App\Models\CbiItem;
use App\Models\InferenceSession;
use App\Services\AdaptiveInterviewService;
use App\Services\InferenceExplanationService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Throwable;

class DeteksiController extends Controller
{
    public function __construct(
        private readonly AdaptiveInterviewService $interviewService,
        private readonly InferenceExplanationService $explanationService
    ) {
    }

    public function intro(): View
    {
        $savedSession = InferenceSession::query()
            ->where('user_id', Auth::id())
            ->where('status', InferenceSession::STATUS_IN_PROGRESS)
            ->latest('id')
            ->first();

        return view('karyawan.deteksi.index', compact('savedSession'));
    }

    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $session = $this->interviewService->getOrCreateSession($user);
        $result = $this->interviewService->advance($session);
        $session->refresh();

        if ($session->status !== InferenceSession::STATUS_IN_PROGRESS) {
            Session::put('last_inference_session_id', $session->id);
            $this->notifyCompletion($session);

            return redirect()->route('karyawan.hasil', [
                'id' => $session->id,
            ]);
        }

        $question = $result->question;

        abort_if(! $question, 500, 'Inference engine did not return a required fact.');

        return view('karyawan.deteksi.form', [
            'session' => $session,
            'question' => $question,
            'responseOptions' => config('cbi.response_options', []),
            'answeredCount' => $session->answers()->count(),
            'currentGoalLabel' => config(
                "expert_system.goal_labels.{$session->current_goal}",
                $session->current_goal
            ),
            'tracePreview' => $session->traces()
                ->latest('sequence')
                ->limit(4)
                ->get()
                ->sortBy('sequence')
                ->values(),
        ]);
    }

    public function next(
        StoreInferenceAnswerRequest $request
    ): RedirectResponse {
        $session = InferenceSession::query()->findOrFail(
            $request->integer('session_id')
        );
        $item = CbiItem::query()
            ->where('code', $request->string('item_code')->toString())
            ->where('is_active', true)
            ->firstOrFail();

        $this->interviewService->recordAnswer(
            $session,
            $item,
            $request->answerKey()
        );

        return redirect()->route('karyawan.deteksi');
    }

    public function showResult(Request $request): View|RedirectResponse
    {
        $id = $request->integer('id')
            ?: Session::get('last_inference_session_id');

        if (! $id) {
            return redirect()->route('karyawan.dashboard');
        }

        $session = InferenceSession::query()
            ->with(['traces', 'answers.item', 'user'])
            ->find($id);

        if (! $session) {
            return redirect()
                ->route('karyawan.dashboard')
                ->with('error', 'Hasil inferensi tidak ditemukan.');
        }

        abort_if((int) $session->user_id !== (int) Auth::id(), 403);

        return view('karyawan.deteksi.hasil', [
            'session' => $session,
            'explanation' => $this->explanationService->build($session),
        ]);
    }

    public function downloadReport(int $id): View|RedirectResponse
    {
        $session = InferenceSession::query()
            ->with(['traces', 'answers.item', 'user'])
            ->find($id);

        if (! $session) {
            return redirect()
                ->route('karyawan.dashboard')
                ->with('error', 'Hasil inferensi tidak ditemukan.');
        }

        abort_if((int) $session->user_id !== (int) Auth::id(), 403);

        return view('karyawan.deteksi.report', [
            'session' => $session,
            'explanation' => $this->explanationService->build($session),
        ]);
    }

    public function reset(): RedirectResponse
    {
        InferenceSession::query()
            ->where('user_id', Auth::id())
            ->where('status', InferenceSession::STATUS_IN_PROGRESS)
            ->update([
                'status' => InferenceSession::STATUS_CANCELLED,
                'current_question_code' => null,
                'completed_at' => now(),
            ]);

        Session::forget('last_inference_session_id');

        return redirect()
            ->route('karyawan.deteksi')
            ->with('info', 'Sesi lama dibatalkan dan wawancara adaptif baru dimulai.');
    }

    public function saveSession(): RedirectResponse
    {
        return redirect()
            ->route('karyawan.dashboard')
            ->with(
                'success',
                'Progres sudah tersimpan otomatis di database dan dapat dilanjutkan.'
            );
    }

    public function resumeSession(): RedirectResponse
    {
        return redirect()->route('karyawan.deteksi');
    }

    private function notifyCompletion(InferenceSession $session): void
    {
        try {
            $label = config(
                "expert_system.goal_labels.{$session->conclusion}",
                $session->conclusion
            );

            NotificationService::send(
                (int) Auth::id(),
                'Inferensi Backward Chaining Selesai',
                "Kesimpulan rule-based tersedia: **{$label}**.",
                icon: 'git-branch',
                color: '#2563eb'
            );
        } catch (Throwable $exception) {
            Log::error('Gagal mengirim notifikasi hasil inferensi.', [
                'inference_session_id' => $session->id,
                'user_id' => Auth::id(),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
