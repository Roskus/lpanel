<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Command;

class WebsiteRename extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panel:website:rename {domain} {new_domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change the domain of a website, moving its virtual host and folder. The enabled state is kept.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $domain = $this->argument('domain');
        $new_domain = $this->argument('new_domain');

        if (! preg_match(Website::DOMAIN_PATTERN, $new_domain)) {
            $this->error("Invalid domain: $new_domain");

            return self::FAILURE;
        }

        $website = Website::where('url', $domain)->first();
        if (! $website) {
            $this->error("Website not found: $domain");

            return self::FAILURE;
        }

        if (Website::withTrashed()->where('url', $new_domain)->exists()) {
            $this->error("Domain already in use: $new_domain");

            return self::FAILURE;
        }

        $config_path = config('app.webserver_path')."/{$website->type}";
        $sites_available = "$config_path/sites-available";
        $vhost_path = is_dir($sites_available) ? $sites_available : "$config_path/conf.d";
        $links_path = is_dir($sites_available) ? "$config_path/sites-enabled" : null;

        $moves = [
            ["$vhost_path/$domain.conf", "$vhost_path/$new_domain.conf"],
            [config('app.websites_path')."/$domain", config('app.websites_path')."/$new_domain"],
        ];

        if (! is_file($moves[0][0])) {
            $this->error("Virtual host not found: {$moves[0][0]}");

            return self::FAILURE;
        }

        foreach ($moves as [, $destination]) {
            if (file_exists($destination)) {
                $this->error("Already exists: $destination");

                return self::FAILURE;
            }
        }

        $was_enabled = $links_path !== null && is_link("$links_path/$domain.conf");

        // The running config is untouched until the regenerated vhost reloads
        foreach ($moves as [$source, $destination]) {
            if (file_exists($source)) {
                rename($source, $destination);
            }
        }
        if ($was_enabled) {
            unlink("$links_path/$domain.conf");
            symlink($moves[0][1], "$links_path/$new_domain.conf");
        }

        $website->url = $new_domain;
        $website->save();

        if ($this->call('panel:website:create', ['domain' => $new_domain, 'server' => $website->type]) !== self::SUCCESS) {
            $website->url = $domain;
            $website->save();

            if ($was_enabled) {
                unlink("$links_path/$new_domain.conf");
                symlink($moves[0][0], "$links_path/$domain.conf");
            }
            foreach ($moves as [$source, $destination]) {
                if (file_exists($destination)) {
                    rename($destination, $source);
                }
            }

            return self::FAILURE;
        }

        $this->info("Website $domain renamed to $new_domain");

        return self::SUCCESS;
    }
}
