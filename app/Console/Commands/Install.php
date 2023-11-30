<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lpanel:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'LPanel Install';

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
     */
    public function handle(): int
    {
        //1 - Check if .env file exist or created based in the example file
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $this->info('.env ENVIRONMENT file exist');
        }

        if (! File::exists($envPath)) {
            if (File::copy(base_path('.env.example'), base_path('.env'))) {
                $this->info('Copy default .env.example > .env');
            }
        }

        //2- Generate APP_KEY
        if (empty(env('APP_KEY'))) {
            Artisan::call('key:generate');
            $this->info('Application key generated');
        }

        //3 - Check if DB_PASSWORD is set
        if (empty(env('DB_PASSWORD'))) {
            $yesno = $this->ask('DB_PASSWORD is in blank. Do you want to setup? (Y/N)');
            if (strtoupper($yesno) == 'Y') {
                $password = $this->ask('Setup DB_PASSWORD:');
                $this->setEnv('DB_PASSWORD', $password);
            }
        }

        //4 - Database Exist
        $this->databaseExist();

        return 0;
    }

    /**
     * @return void
     */
    private function setEnv(string $key, string $value)
    {
        file_put_contents(app()->environmentFilePath(), str_replace(
            $key.'='.env($value),
            $key.'='.$value,
            file_get_contents(app()->environmentFilePath())
        ));
    }

    private function databaseExist(): bool
    {
        $exist = false;
        try {
            DB::connection()->getPdo();
            if (DB::connection()->getDatabaseName()) {
                $this->info('Yes! Successfully connected to the DB: '.DB::connection()->getDatabaseName());
                $exist = true;
            } else {
                $this->error('Could not find the database. Please check your configuration.');
            }
        } catch (\Throwable $t) {
            $this->info('Could not open connection to database server.  Please check your configuration.');
        }

        return $exist;
    }
}
