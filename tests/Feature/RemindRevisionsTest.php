<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\User;
use App\Notifications\RevisionDueNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RemindRevisionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_notifies_once_when_a_revision_is_due(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'client',
            'phone' => '0700000000',
        ]);

        $equipment = Equipment::query()->create([
            'user_id' => $user->id,
            'name' => 'Centrala',
            'last_revision_on' => now()->subMonths(23)->toDateString(),
            'interval_months' => 24,
        ]);

        $equipment->forceFill([
            'next_revision_on' => now()->addDays(10)->toDateString(),
            'notified_on' => null,
        ])->saveQuietly();

        $this->artisan('revisions:remind')->assertSuccessful();
        $this->artisan('revisions:remind')->assertSuccessful();

        Notification::assertSentToTimes($user, RevisionDueNotification::class, 1);
    }
}
