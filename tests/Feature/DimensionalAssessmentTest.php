<?php

namespace Tests\Feature;

use App\Models\MbiAssessment;
use App\Models\MbiItem;
use App\Models\User;
use Database\Seeders\MbiGsInstrumentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DimensionalAssessmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MbiGsInstrumentSeeder::class);

        MbiItem::query()->get()->each(
            fn (MbiItem $item) => $item->update(['prompt_text' => 'Test item '.$item->code])
        );
    }

    public function test_complete_submission_is_stored(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);
        $responses = MbiItem::query()->pluck('code')->mapWithKeys(fn (string $code) => [$code => 3])->all();

        $this->actingAs($user)
            ->post(route('karyawan.deteksi.next'), ['responses' => $responses])
            ->assertRedirect();

        $assessment = MbiAssessment::query()->firstOrFail();

        $this->assertSame(MbiAssessment::STATUS_COMPLETE, $assessment->status);
        $this->assertSame(3.0, $assessment->ex_score);
        $this->assertSame(3.0, $assessment->cy_score);
        $this->assertSame(3.0, $assessment->pe_score);
        $this->assertDatabaseCount('mbi_responses', 16);
    }

    public function test_incomplete_submission_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);
        $responses = MbiItem::query()->limit(15)->pluck('code')->mapWithKeys(fn (string $code) => [$code => 3])->all();

        $this->actingAs($user)
            ->from(route('karyawan.deteksi'))
            ->post(route('karyawan.deteksi.next'), ['responses' => $responses])
            ->assertSessionHasErrors('responses');

        $this->assertDatabaseCount('mbi_assessments', 0);
    }
}
