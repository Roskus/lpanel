<?php

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class WebsiteCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:create {domain} {server=nginx : Webserver by default nginx} {php=8.1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and configure website in webserver. Generate virtualhost. Usage: website:create {domain} {--server=nginx}';

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
        $status = 0; //success
        $domain = $this->argument('domain');
        $server = $this->argument('server');

        $domain = str_replace('domain=', '', $domain);
        $server = str_replace('server=', '', $server);
        $server = str_replace('--server=', '', $server);

        $php_version = $this->argument('php');

        $website = Website::where('url', $domain)->first();
        if (! $website) {
            $website = new Website();
            $website->url = $domain;
            $website->created_at = now();
        }
        $website->type = $server;
        $website->updated_at = now();
        $website->status = Website::ACTIVE;
        $website->save();

        if ($server == 'nginx') {
            $template = resource_path('templates/config/nginx/site_php.conf');
        }

        $destination = "/etc/$server";
        if (is_dir("/etc/$server/sites-available")) {
            $destination = "/etc/$server/sites-available/$domain.conf";
        }

        if (is_dir("/etc/$server/conf.d")) {
            $destination = "/etc/$server/conf.d/$domain.conf";
        }

        $template_content = file_get_contents($template);
        $template_content = str_replace('|WEBSITE_URL|', $domain, $template_content);
        $template_content = str_replace('|WEBSITE_PATH|', $domain, $template_content);
        $template_content = str_replace('|SITE_PHP_VERSION|', $php_version, $template_content);

        try {
            file_put_contents($destination, $template_content);
            $this->info('Virtual host created successfully');
        } catch (\Throwable $th) {
            //throw $th;
            $this->error("Error creating vhost in: $destination");
            $status = -1;
        }

        // Enable virtual host
        if (is_dir("/etc/$server/sites-available")) {
            $virtual_host_path = "/etc/$server/sites-enabled/";
            chdir($virtual_host_path);
            $process = new Process(['ln', '-s', $destination]);
            $process->run();
        }

        // TODO: check nginx -t
        // TODO: service nginx reload
        return $status;
    }
}
