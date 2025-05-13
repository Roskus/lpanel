<?php declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LinuxUserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panel:linux:user-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a linux user and home folder.';

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
