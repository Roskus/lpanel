<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Website;
use App\Service\Webserver\Nginx;
use Illuminate\Console\Command;

class WebsiteDisable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panel:website:disable {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable a website in the webserver, the virtual host config is kept.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $domain = $this->argument('domain');

        $error = (new Nginx)->setEnabled($domain, false);
        if ($error !== null) {
            $this->error($error);

            return self::FAILURE;
        }

        Website::where('url', $domain)->update(['status' => Website::INACTIVE]);

        $this->info("Website $domain disabled");

        return self::SUCCESS;
    }
}
