<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:create {name} {--engine=mysql}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database in the server. Receive a name. Optional --engine by default is MariaDB/MySQL';

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
        return 0;
    }
}
