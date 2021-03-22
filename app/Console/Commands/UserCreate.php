<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create lpanel user';

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
        $name = $this->ask('Name?');
        $email = $this->ask('Email?');
        $pwd = $this->secret('Password?');
        $lang = $this->ask('Lang? (en/es)');

        User::query()
            ->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($pwd),
                'lang' => $lang
            ]);

        $this->info('Account created for '.$name);
        return 0;
    }
}
