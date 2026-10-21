<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class SyncSpatieRoles extends Command
{
    protected $signature = 'spatie:sync-roles
                            {--force : Force sync all users}
                            {--dry-run : Show what would be changed without making changes}';

    protected $description = 'Sync Spatie roles with UserRole enum for all users';

    public function handle(): int
    {
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        $roleMapping = [
            UserRole::ADMIN->value => 'admin',
            UserRole::OWNER->value => 'owner',
            UserRole::BUYER->value => 'buyer',
            UserRole::USER->value => 'user',
        ];

        $users = User::query()
            ->when(! $force, fn ($query) => $query->where('role_setup_completed', true))
            ->get();

        $this->info("Processing {$users->count()} users...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $changes = 0;

        foreach ($users as $user) {
            $spatieRoleName = $roleMapping[$user->role->value] ?? null;

            if (! $spatieRoleName) {
                $bar->advance();

                continue;
            }

            $spatieRole = Role::where('name', $spatieRoleName)
                ->where('guard_name', 'web')
                ->first();

            if (! $spatieRole) {
                $this->warn("Spatie role '{$spatieRoleName}' not found for user {$user->email}");
                $bar->advance();

                continue;
            }

            $currentRoles = $user->getRoleNames()->toArray();

            if (! in_array($spatieRoleName, $currentRoles, true)) {
                if (! $dryRun) {
                    $user->syncRoles([$spatieRole]);
                }
                $changes++;
                $this->line("\nSynced {$user->email}: {$spatieRoleName}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("Dry run complete. {$changes} users would be updated.");
        } else {
            $this->info("Sync complete. {$changes} users updated.");
        }

        return Command::SUCCESS;
    }
}
