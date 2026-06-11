<?php

namespace App\Console\Commands;

use App\Models\MbiItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ImportLicensedMbiGsItems extends Command
{
    protected $signature = 'mbi:import-licensed
        {path=storage/app/private/mbi-gs.json : Path to the licensed item JSON file}';

    protected $description = 'Import licensed MBI-GS item text without committing copyrighted content to the repository';

    public function handle(): int
    {
        $path = $this->resolvePath((string) $this->argument('path'));

        if (! is_file($path) || ! is_readable($path)) {
            $this->error("Licensed item file cannot be read: {$path}");

            return self::FAILURE;
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload) || ! isset($payload['items']) || ! is_array($payload['items'])) {
            $this->error('The JSON file must contain an items array.');

            return self::FAILURE;
        }

        $items = $payload['items'];
        $validator = Validator::make(['items' => $items], [
            'items' => ['required', 'array', 'size:16'],
            'items.*.code' => ['required', 'string', 'distinct'],
            'items.*.dimension' => ['required', 'in:EX,CY,PE'],
            'items.*.position' => ['required', 'integer', 'between:1,16', 'distinct'],
            'items.*.prompt_text' => ['required', 'string', 'min:1'],
            'items.*.source_item_reference' => ['required', 'string', 'max:64'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        try {
            $this->assertDimensionCounts($items);
            $this->assertCodesMatchConfiguredSlots($items);

            DB::transaction(function () use ($items): void {
                foreach ($items as $itemData) {
                    $item = MbiItem::query()->where('code', $itemData['code'])->firstOrFail();
                    $item->update([
                        'dimension' => $itemData['dimension'],
                        'position' => $itemData['position'],
                        'prompt_text' => $itemData['prompt_text'],
                        'source_item_reference' => $itemData['source_item_reference'],
                        'is_active' => true,
                        'licensed_content_loaded_at' => now(),
                    ]);
                }
            });
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Licensed MBI-GS content imported successfully. Prompt text is encrypted at rest.');

        return self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            ? $path
            : base_path($path);
    }

    private function assertDimensionCounts(array $items): void
    {
        $counts = collect($items)->countBy('dimension');
        $expected = config('mbi.instrument.dimensions', ['EX' => 5, 'CY' => 5, 'PE' => 6]);

        foreach ($expected as $dimension => $count) {
            if ((int) ($counts[$dimension] ?? 0) !== (int) $count) {
                throw new RuntimeException("Dimension {$dimension} must contain exactly {$count} items.");
            }
        }
    }

    private function assertCodesMatchConfiguredSlots(array $items): void
    {
        $configuredCodes = MbiItem::query()
            ->where('is_active', true)
            ->pluck('code')
            ->sort()
            ->values();

        $importCodes = collect($items)
            ->pluck('code')
            ->sort()
            ->values();

        if ($configuredCodes->count() !== 16 || $configuredCodes->all() !== $importCodes->all()) {
            throw new RuntimeException('Imported item codes must exactly match the 16 configured MBI-GS slots.');
        }
    }
}
