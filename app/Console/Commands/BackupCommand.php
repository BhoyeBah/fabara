<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupCommand extends Command
{
    protected $signature = 'fabara:backup {--keep=14 : nombre de jours de sauvegardes conservées}';
    protected $description = 'Sauvegarde la base de données dans storage/app/backups';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);
        $stamp = now()->format('Ymd-His');
        $conn = config('database.connections.' . config('database.default'));

        if ($conn['driver'] === 'sqlite') {
            copy($conn['database'], "$dir/db-$stamp.sqlite");
        } elseif (in_array($conn['driver'], ['mysql', 'mariadb'], true)) {
            $p = new Process(
                ['mysqldump', '-h', $conn['host'], '-P', (string) $conn['port'], '-u', $conn['username'], $conn['database'], "--result-file=$dir/db-$stamp.sql"],
                null, ['MYSQL_PWD' => $conn['password']]
            );
            $p->setTimeout(300);
            $p->run();
            if (! $p->isSuccessful()) {
                $this->error($p->getErrorOutput());
                return self::FAILURE;
            }
        } else {
            $this->error('Type de base non pris en charge pour la sauvegarde automatique.');
            return self::FAILURE;
        }

        $limit = now()->subDays((int) $this->option('keep'))->getTimestamp();
        foreach (File::files($dir) as $f) {
            if ($f->getMTime() < $limit) {
                File::delete($f->getPathname());
            }
        }
        $this->info("Sauvegarde créée : db-$stamp");
        return self::SUCCESS;
    }
}
