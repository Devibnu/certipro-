<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixOrphanedPendaftaran extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:orphaned-pendaftaran';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix orphaned pendaftaran_sertifikasi records (set to draft or delete)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for orphaned pendaftaran_sertifikasi records...');

        // Find orphaned records (user_id NULL with active status)
        $orphaned = DB::table('pendaftaran_sertifikasi')
            ->whereNull('user_id')
            ->whereNotIn('status', ['draft', 'ditolak', 'dibatalkan'])
            ->get(['id', 'nomor_pendaftaran', 'status', 'created_at']);

        if ($orphaned->isEmpty()) {
            $this->info('✅ No orphaned records found. Database is clean!');
            return 0;
        }

        $this->warn("Found {$orphaned->count()} orphaned records:");
        $this->table(
            ['ID', 'Nomor Pendaftaran', 'Status', 'Created At'],
            $orphaned->map(fn($r) => [
                $r->id,
                $r->nomor_pendaftaran,
                $r->status,
                $r->created_at,
            ])
        );

        $choice = $this->choice(
            'What do you want to do with these records?',
            [
                'draft' => 'Set status to DRAFT (safe - keep data)',
                'delete' => 'DELETE records (destructive)',
                'cancel' => 'Cancel - do nothing',
            ],
            'draft'
        );

        if ($choice === 'cancel') {
            $this->info('❌ Operation cancelled');
            return 0;
        }

        if (!$this->confirm("Are you sure you want to {$choice} {$orphaned->count()} records?")) {
            $this->info('❌ Operation cancelled');
            return 0;
        }

        if ($choice === 'draft') {
            $updated = DB::table('pendaftaran_sertifikasi')
                ->whereIn('id', $orphaned->pluck('id'))
                ->update([
                    'status' => 'draft',
                    'updated_at' => now(),
                ]);

            $this->info("✅ Updated {$updated} records to DRAFT status");
        } else {
            $deleted = DB::table('pendaftaran_sertifikasi')
                ->whereIn('id', $orphaned->pluck('id'))
                ->delete();

            $this->info("✅ Deleted {$deleted} orphaned records");
        }

        $this->newLine();
        $this->info('✅ Cleanup complete! You can now run: php artisan migrate');

        return 0;
    }
}
