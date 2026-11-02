<?php declare(strict_types=1);

namespace App\Service\Webserver;

use App\Models\Website;
use Illuminate\Support\Facades\Process;

class Nginx
{
    protected array $enabledSites = [];
    protected array $availableSites = [];

    public function __construct()
    {
        $enabled_path = $this->getSitesEnabledPath();
        if (is_dir($enabled_path)) {
            $this->enabledSites = array_diff(scandir($enabled_path), ['.', '..', '.gitignore']);
        }

        $available_path = $this->getSitesAvailablePath();
        if (is_dir($available_path)) {
            $this->availableSites = array_diff(scandir($available_path), ['.', '..', '.gitignore']);
        }
    }

    protected function getPath(): string
    {
        return config('app.webserver_path').'/nginx';
    }

    protected function getSitesEnabledPath(): string
    {
        $path = $this->getPath();

        return is_dir("$path/sites-enabled") ? "$path/sites-enabled" : "$path/conf.d";
    }

    protected function getSitesAvailablePath(): string
    {
        $path = $this->getPath();

        return is_dir("$path/sites-available") ? "$path/sites-available" : "$path/conf.d";
    }

    /**
     * Enable or disable a website by adding or removing its sites-enabled link
     *
     * @return string|null error message
     */
    public function setEnabled(string $domain, bool $enabled): ?string
    {
        $available = $this->getPath()."/sites-available/$domain.conf";
        $link = $this->getPath()."/sites-enabled/$domain.conf";

        if (! preg_match(Website::DOMAIN_PATTERN, $domain) || ! is_file($available)) {
            return "Website not found in sites-available: $domain";
        }

        if (is_link($link) === $enabled) {
            return null;
        }

        $enabled ? symlink($available, $link) : unlink($link);

        $error = $this->reload();
        if ($error !== null) {
            $enabled ? unlink($link) : symlink($available, $link);
        }

        return $error;
    }

    /**
     * Check the configuration and reload nginx
     *
     * @return string|null error message
     */
    public function reload(): ?string
    {
        foreach ([['-t'], ['-s', 'reload']] as $arguments) {
            $process = Process::run(['sudo', '-n', '/usr/sbin/nginx', ...$arguments]);

            if ($process->failed()) {
                return 'Error reloading webserver: '.trim($process->errorOutput());
            }
        }

        return null;
    }

    public function getEnabledSites(): array
    {
        return $this->enabledSites;
    }

    public function getAvailableSites(): array
    {
        return $this->availableSites;
    }
}
