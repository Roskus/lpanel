<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:create {name} {engine=mysql}';

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
        $status = 0;
        $schema = $this->argument('name');
        $charset = config("database.connections.mysql.charset",'utf8mb4');
        $collation = config("database.connections.mysql.collation",'utf8mb4_unicode_ci');

        config(["database.connections.mysql.database" => null]);
        $query = "CREATE DATABASE IF NOT EXISTS $schema CHARACTER SET $charset COLLATE $collation;";
        try {
            $status = DB::statement($query);
            $this->info("Database $schema created success.");
        } catch (\Throwable $t) {
            $this->error("An error occurred creating the DB: $schema");
            Log::error($t->getMessage());
        }
        return $status;
    }
}
