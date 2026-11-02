<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Website;
use App\Service\Webserver\Nginx;
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
        if (! preg_match(Website::DOMAIN_PATTERN, $domain)) {
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

        $website = Website::withTrashed()->where('url', $domain)->first();
        $aliases = json_decode($website->alias ?? '[]', true) ?: [];

        foreach ($aliases as $alias) {
            if (! preg_match(Website::DOMAIN_PATTERN, $alias)) {
                $this->error("Invalid alias: $alias");

                return self::FAILURE;
            }
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

        $website_path = config('app.websites_path')."/$domain";

        $template_content = file_get_contents($template);
        $template_content = str_replace('|WEBSITE_URL|', implode(' ', [$domain, ...$aliases]), $template_content);
        $template_content = str_replace('|WEBSITE_PATH|', $website_path, $template_content);
        $template_content = str_replace('|SITE_PHP_VERSION|', $php_version, $template_content);
        $template_content = str_replace('|SITE_SSL_CONFIG|', $this->sslConfig(), $template_content);

        try {
            if (! is_dir("$website_path/public")) {
                mkdir("$website_path/public", 0755, true);
            }

            $this->createIndex("$website_path/public", $domain);
        } catch (\Throwable $th) {
            $this->error("Error creating website directory: $website_path/public");

            return self::FAILURE;
        }

        $previous_content = is_file($destination) ? file_get_contents($destination) : null;
        $link = "$sites_enabled/$domain.conf";
        $was_enabled = is_link($link);

        try {
            file_put_contents($destination, $template_content);

            // Enable virtual host
            if (is_dir($sites_available) && ! is_link($link)) {
                symlink($destination, $link);
            }
        } catch (\Throwable $th) {
            $this->error("Error creating vhost in: $destination");

            return self::FAILURE;
        }

        // A broken vhost would block the reload of every other website
        $error = (new Nginx)->reload();
        if ($error !== null) {
            if (! $was_enabled && is_link($link)) {
                unlink($link);
            }

            if ($previous_content !== null) {
                file_put_contents($destination, $previous_content);
            } else {
                unlink($destination);
            }

            $this->error($error);

            return self::FAILURE;
        }

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

        return self::SUCCESS;
    }

    /**
     * Nginx refuses an ssl listen without certificate, so it needs one configured
     */
    private function sslConfig(): string
    {
        $certificate = config('app.website_ssl_certificate');
        $key = config('app.website_ssl_certificate_key');

        if (! $certificate || ! $key) {
            return '';
        }

        return "\tlisten 443 ssl;\n\tssl_certificate $certificate;\n\tssl_certificate_key $key;";
    }

    private function createIndex(string $public_path, string $domain): void
    {
        if (is_file("$public_path/index.php") || is_file("$public_path/index.html")) {
            return;
        }

        $index = file_get_contents(resource_path('templates/website/index.php'));
        file_put_contents("$public_path/index.php", str_replace('|WEBSITE_URL|', $domain, $index));
    }
}
