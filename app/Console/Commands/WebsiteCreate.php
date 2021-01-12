<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WebsiteCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:create {domain} {--server=nginx}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and configure website in webserver. Generate virtualhost, user and home folder.';

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
        $domain = $this->argument('domain');
        echo $domain;
        //$website = Website::where('domain', $domain)->first();
        return 0;
    }
}
