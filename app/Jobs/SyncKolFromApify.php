<?php

namespace App\Jobs;

use App\Models\KeyOpinionLeader;
use App\Services\ThirdParty\ApifyKolSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncKolFromApify implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly KeyOpinionLeader $kol) {}

    public function handle(ApifyKolSyncService $syncService): void
    {
        $this->kol->update(['syncing_at' => now()]);

        try {
            $metrics = $syncService->sync($this->kol);

            $this->kol->update(array_merge($metrics, [
                'synced_at' => now(),
                'syncing_at' => null,
            ]));
        } catch (\Throwable $e) {
            $this->kol->update(['syncing_at' => null]);

            Log::error('SyncKolFromApify failed', [
                'kol_id' => $this->kol->id,
                'error' => $e->getMessage(),
            ]);

            if (config('influenser.creator_db.key')) {
                SyncKolFromCreatorDB::dispatch($this->kol);
            }
        }
    }
}
