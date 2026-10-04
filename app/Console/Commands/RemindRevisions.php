<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Notifications\RevisionDueNotification;
use Illuminate\Console\Command;

class RemindRevisions extends Command
{
    protected $signature = 'revisions:remind';

    protected $description = 'Trimite email si SMS pentru reviziile care se apropie';

    public function handle(): int
    {
        $until = now()->addDays(config('dds.remind_days'))->toDateString();

        Equipment::query()
            ->with('user')
            ->whereDate('next_revision_on', '>=', now()->toDateString())
            ->whereDate('next_revision_on', '<=', $until)
            ->where(function ($query) {
                $query->whereNull('notified_on')
                    ->orWhereColumn('notified_on', '!=', 'next_revision_on');
            })
            ->each(function (Equipment $equipment) {
                if (! $equipment->user) {
                    return;
                }

                $equipment->user->notify(new RevisionDueNotification($equipment));
                $equipment->forceFill(['notified_on' => $equipment->next_revision_on])->saveQuietly();
            });

        return self::SUCCESS;
    }
}
