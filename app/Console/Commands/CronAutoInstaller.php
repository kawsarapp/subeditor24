<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DynamicCronService;

class CronAutoInstaller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:manage {action=status : Action to perform (status, install, uninstall, run)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dynamically check, install, or manage Linux server crontab for Laravel scheduler';

    /**
     * Execute the console command.
     */
    public function handle(DynamicCronService $cronService)
    {
        $action = strtolower($this->argument('action'));
        $basePath = base_path();
        $phpBin = PHP_BINARY ?: '/usr/bin/php';
        $cronSignature = "cd {$basePath} && {$phpBin} artisan schedule:run";
        $cronEntry = "* * * * * {$cronSignature} >> /dev/null 2>&1";

        switch ($action) {
            case 'install':
                $this->info("🔧 Analyzing server crontab...");
                $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?? '';

                if (strpos($currentCrontab, $basePath) !== false && strpos($currentCrontab, 'schedule:run') !== false) {
                    $this->warn("⚠️ Cron job is already installed for this project in server crontab.");
                    return Command::SUCCESS;
                }

                $newCrontab = trim($currentCrontab);
                if (!empty($newCrontab)) {
                    $newCrontab .= "\n";
                }
                $newCrontab .= "# Newsmanage24 Dynamic Laravel Scheduler\n{$cronEntry}\n";

                $tmpFile = tempnam(sys_get_temp_dir(), 'cron_');
                file_put_contents($tmpFile, $newCrontab);
                $result = shell_exec("crontab {$tmpFile} 2>&1");
                @unlink($tmpFile);

                $this->info("🎉 Server Crontab successfully installed dynamically!");
                $this->line("   -> Entry: <comment>{$cronEntry}</comment>");
                $cronService->recordHeartbeat('installer');
                break;

            case 'uninstall':
                $this->info("🗑️ Removing Newsmanage24 entry from server crontab...");
                $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?? '';

                if (strpos($currentCrontab, $basePath) === false) {
                    $this->warn("ℹ️ No active crontab entry found for this project.");
                    return Command::SUCCESS;
                }

                $lines = explode("\n", $currentCrontab);
                $filtered = [];
                foreach ($lines as $line) {
                    if (strpos($line, $basePath) !== false && strpos($line, 'schedule:run') !== false) {
                        continue;
                    }
                    if (strpos($line, 'Newsmanage24 Dynamic Laravel Scheduler') !== false) {
                        continue;
                    }
                    $filtered[] = $line;
                }

                $newCrontab = trim(implode("\n", $filtered));
                $tmpFile = tempnam(sys_get_temp_dir(), 'cron_');
                file_put_contents($tmpFile, !empty($newCrontab) ? $newCrontab . "\n" : "");
                shell_exec("crontab {$tmpFile} 2>&1");
                @unlink($tmpFile);

                $this->info("✅ Project crontab entry removed successfully.");
                break;

            case 'run':
                $this->info("⚡ Running immediate scheduler execution...");
                $this->call('schedule:run');
                $cronService->recordHeartbeat('manual_cli');
                $this->info("✅ Manual scheduler run complete.");
                break;

            case 'status':
            default:
                $this->info("📊 Server Crontab & Dynamic Scheduler Status:");
                $health = $cronService->getHealthStatus();

                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Cron Health Status', $health['status_label']],
                        ['Last Executed At', $health['last_run_at']],
                        ['Execution Source', $health['last_source']],
                        ['Seconds Ago', $health['seconds_ago'] !== null ? $health['seconds_ago'] . 's' : 'N/A'],
                        ['Total Recorded Runs', $health['total_runs']],
                    ]
                );

                $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?? '';
                $hasCrontab = strpos($currentCrontab, $basePath) !== false && strpos($currentCrontab, 'schedule:run') !== false;

                if ($hasCrontab) {
                    $this->info("✅ Linux Server Crontab: Configured & Detected.");
                } else {
                    $this->warn("ℹ️ Linux Server Crontab: Not yet installed in system crontab.");
                    $this->line("   💡 Run <comment>php artisan cron:manage install</comment> to auto-install it.");
                }
                break;
        }

        return Command::SUCCESS;
    }
}
