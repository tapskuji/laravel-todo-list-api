<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class BackupLogFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backups:logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will create backups of files in the log directory';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $absolutePath = storage_path('logs');
        $files = ['laravel', 'email'];

        foreach ($files as $fileName) {
            // backup file
            $logFile =  "{$absolutePath}/{$fileName}.log";
            $this->info($logFile);
            $logFileBackup = "{$absolutePath}/{$fileName}-" . Carbon::now()->toDateString() . '.log';
            $this->info($logFileBackup);
            if (File::exists($logFile)) {
                File::move($logFile, $logFileBackup);
            }

            // delete old backup
            $oldLogFileBackup = "{$absolutePath}/{$fileName}-" . Carbon::today()->subDays(30)->toDateString() . '.log';
            $this->info($oldLogFileBackup);
            if (File::exists($oldLogFileBackup)) {
                File::delete($oldLogFileBackup);
            }
        }


        return 0;
    }
}
