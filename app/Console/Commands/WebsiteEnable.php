<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Website;
use App\Service\Webserver\Nginx;
use Illuminate\Console\Command;

class WebsiteEnable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panel:website:enable {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable a website in the webserver, the virtual host config is kept.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $domain = $this->argument('domain');

        $error = (new Nginx)->setEnabled($domain, true);
        if ($error !== null) {
            $this->error($error);

            return self::FAILURE;
        }

        Website::where('url', $domain)->update(['status' => Website::ACTIVE]);

        $this->info("Website $domain enabled");

        return self::SUCCESS;
    }
}
