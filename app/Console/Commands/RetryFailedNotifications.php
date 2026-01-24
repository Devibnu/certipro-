<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailSertifikatJob;
use App\Jobs\SendWhatsAppSertifikatJob;
use App\Models\Sertifikat;
use Illuminate\Console\Command;

class RetryFailedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certipro:retry-notifications 
                            {--email : Retry only failed email notifications}
                            {--whatsapp : Retry only failed WhatsApp notifications}
                            {--all : Retry all failed notifications (default)}
                            {--dry-run : Show what would be retried without actually retrying}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed email and/or WhatsApp notifications for certificates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Checking for failed notifications...');
        $this->newLine();

        $query = Sertifikat::query()
            ->with(['pendaftaran.user', 'pendaftaran.skemaSertifikasi']);

        // Filter by notification type
        if ($this->option('email') && !$this->option('whatsapp')) {
            $query->whereNotNull('email_failed_at')
                  ->whereNull('email_sent_at');
            $this->info('📧 Mode: Email notifications only');
        } elseif ($this->option('whatsapp') && !$this->option('email')) {
            $query->whereNotNull('whatsapp_failed_at')
                  ->whereNull('whatsapp_sent_at');
            $this->info('💬 Mode: WhatsApp notifications only');
        } else {
            $query->where(function($q) {
                $q->where(function($q1) {
                    $q1->whereNotNull('email_failed_at')->whereNull('email_sent_at');
                })->orWhere(function($q2) {
                    $q2->whereNotNull('whatsapp_failed_at')->whereNull('whatsapp_sent_at');
                });
            });
            $this->info('📧💬 Mode: All failed notifications');
        }

        $certificates = $query->get();

        if ($certificates->isEmpty()) {
            $this->info('✅ No failed notifications found!');
            return Command::SUCCESS;
        }

        $this->warn("Found {$certificates->count()} certificate(s) with failed notifications:");
        $this->newLine();

        // Display summary table
        $tableData = [];
        foreach ($certificates as $cert) {
            $failedTypes = [];
            if ($cert->email_failed_at && !$cert->email_sent_at) {
                $failedTypes[] = '📧 Email';
            }
            if ($cert->whatsapp_failed_at && !$cert->whatsapp_sent_at) {
                $failedTypes[] = '💬 WhatsApp';
            }

            $tableData[] = [
                $cert->id,
                $cert->nomor_sertifikat,
                $cert->pendaftaran->user->name ?? 'N/A',
                implode(', ', $failedTypes),
                $cert->created_at->diffForHumans(),
            ];
        }

        $this->table(
            ['ID', 'Nomor Sertifikat', 'Asesi', 'Failed Types', 'Issued'],
            $tableData
        );

        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN: No jobs will be dispatched');
            return Command::SUCCESS;
        }

        // Confirm before proceeding
        if (!$this->confirm('Do you want to retry these notifications?', true)) {
            $this->info('Operation cancelled.');
            return Command::CANCELLED;
        }

        $this->newLine();
        $this->info('🚀 Dispatching retry jobs...');
        $this->newLine();

        $emailRetries = 0;
        $whatsappRetries = 0;

        $progressBar = $this->output->createProgressBar($certificates->count());
        $progressBar->start();

        foreach ($certificates as $cert) {
            // Retry email
            if ($cert->email_failed_at && !$cert->email_sent_at) {
                SendEmailSertifikatJob::dispatch($cert)
                    ->onQueue('notifications')
                    ->delay(now()->addSeconds(5));
                
                $cert->update([
                    'email_failed_at' => null,
                    'email_error' => null,
                ]);
                
                $emailRetries++;
            }

            // Retry WhatsApp
            if ($cert->whatsapp_failed_at && !$cert->whatsapp_sent_at) {
                SendWhatsAppSertifikatJob::dispatch($cert)
                    ->onQueue('notifications')
                    ->delay(now()->addSeconds(10));
                
                $cert->update([
                    'whatsapp_failed_at' => null,
                    'whatsapp_error' => null,
                ]);
                
                $whatsappRetries++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✅ Retry jobs dispatched successfully!');
        $this->newLine();
        $this->line("📧 Email retries: {$emailRetries}");
        $this->line("💬 WhatsApp retries: {$whatsappRetries}");
        $this->newLine();
        $this->comment('💡 Jobs have been queued. Check queue worker logs for results.');
        $this->comment('💡 Run: tail -f storage/logs/laravel.log | grep notification');

        return Command::SUCCESS;
    }
}
