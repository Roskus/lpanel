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
     *
     * @return int
     */
    public function handle() :int
    {
        //1 - Check if .env file exist or created based in the example file
        $envPath = base_path('.env');
        if(File::exists($envPath))
        {
            $this->info('.env ENVIRONMENT file exist');
        }

        if(!File::exists($envPath))
        {
            if(File::copy(base_path('.env.example'), base_path('.env'))){
                $this->info('Copy default .env.example > .env');
            }

            // Generate APP_KEY
            Artisan::call('key:generate');
            $this->info('Application key generated');
        }

        //2 - Check if DB_PASSWORD is set
        if(empty(env('DB_PASSWORD')))
        {
            $yesno = $this->ask('DB_PASSWORD is in blank. Do you want to setup? (Y/N)');
            if(strtoupper($yesno) == 'Y') {
                $password = $this->ask('Setup DB_PASSWORD:');
                $this->setEnv('DB_PASSWORD', $password);
            }
        }

        return 0;
    }

    private function setEnv(string $key, string $value)
    {
        file_put_contents(app()->environmentFilePath(), str_replace(
            $key . '=' . env($value),
            $key . '=' . $value,
            file_get_contents(app()->environmentFilePath())
        ));
    }
}
