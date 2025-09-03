<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SurveyLock;
use Illuminate\Support\Facades\Log;

class CleanupExpiredLocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:cleanup-locks {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expired survey locks from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired survey locks...');
        
        try {
            // Get count of expired locks before cleanup
            $expiredCount = SurveyLock::where('expires_at', '<', now())->count();
            
            if ($expiredCount === 0) {
                $this->info('No expired locks found.');
                return Command::SUCCESS;
            }
            
            $this->info("Found {$expiredCount} expired locks.");
            
            // Ask for confirmation unless --force flag is used
            if (!$this->option('force') && !$this->confirm('Do you want to proceed with cleanup?')) {
                $this->info('Cleanup cancelled.');
                return Command::SUCCESS;
            }
            
            // Perform cleanup
            $deletedCount = SurveyLock::cleanupExpiredLocks();
            
            $this->info("Successfully cleaned up {$deletedCount} expired locks.");
            Log::info("Cleaned up {$deletedCount} expired survey locks via command.");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed to cleanup expired locks: ' . $e->getMessage());
            Log::error('Failed to cleanup expired locks via command: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
