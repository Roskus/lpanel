<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Command;

class WebsiteCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panel:website:create {domain} {server=nginx : Webserver by default nginx} {php? : PHP version by default the running one}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and configure website in webserver. Generate virtualhost. Usage: panel:website:create {domain} {server=nginx} {php?}';

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
        $domain = str_replace('domain=', '', $this->argument('domain'));
        $server = str_replace(['--server=', 'server='], '', $this->argument('server'));
        $php_version = str_replace('php=', '', (string) $this->argument('php'));
        $php_version = $php_version ?: PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;

        // The domain ends up in a file path and in the webserver config
        if (! preg_match('/^[a-z0-9]([a-z0-9._-]*[a-z0-9])?$/iD', $domain)) {
            $this->error("Invalid domain: $domain");

            return self::FAILURE;
        }

        if (! preg_match('/^\d+\.\d+$/D', $php_version)) {
            $this->error("Invalid PHP version: $php_version");

            return self::FAILURE;
        }

        // TODO: add apache template
        if ($server != 'nginx') {
            $this->error("Unsupported webserver: $server");

            return self::FAILURE;
        }

        $template = resource_path('templates/config/nginx/site_php.conf');

        $config_path = config('app.webserver_path')."/$server";
        $sites_available = "$config_path/sites-available";
        $sites_enabled = "$config_path/sites-enabled";

        // Ubuntu has both directories, conf.d alone would skip sites-enabled
        if (is_dir($sites_available)) {
            $destination = "$sites_available/$domain.conf";
        } else {
            $destination = "$config_path/conf.d/$domain.conf";
        }

        $template_content = file_get_contents($template);
        $template_content = str_replace('|WEBSITE_URL|', $domain, $template_content);
        $template_content = str_replace('|WEBSITE_PATH|', $domain, $template_content);
        $template_content = str_replace('|SITE_PHP_VERSION|', $php_version, $template_content);

        try {
            file_put_contents($destination, $template_content);

            // Enable virtual host
            if (is_dir($sites_available) && ! is_link("$sites_enabled/$domain.conf")) {
                symlink($destination, "$sites_enabled/$domain.conf");
            }
        } catch (\Throwable $th) {
            $this->error("Error creating vhost in: $destination");

            return self::FAILURE;
        }

        $website = Website::withTrashed()->where('url', $domain)->first();
        if (! $website) {
            $website = new Website;
            $website->url = $domain;
            $website->created_at = now();
        }
        $website->deleted_at = null;
        $website->type = $server;
        $website->updated_at = now();
        $website->status = Website::ACTIVE;
        $website->save();

        $this->info('Virtual host created successfully');

        // TODO: check nginx -t
        // TODO: service nginx reload
        return self::SUCCESS;
    }
}
