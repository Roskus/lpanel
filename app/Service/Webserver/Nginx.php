<?php declare(strict_types=1);

namespace App\Service\Webserver;

class Nginx
{
    protected array $enabledSites = [];
    protected array $availableSites = [];

    public function __construct()
    {
        $enabled_path = $this->getSitesEnabledPath();
        if (is_dir($enabled_path)) {
            $this->enabledSites = array_diff(scandir($enabled_path), ['.', '..']);
        }

        $available_path = $this->getSitesAvailablePath();
        if (is_dir($available_path)) {
            $this->availableSites = array_diff(scandir($available_path), ['.', '..']);
        }
    }

    protected function getSitesEnabledPath(): string
    {
        if (file_exists('/etc/os-release') && str_contains(file_get_contents('/etc/os-release'), 'Ubuntu')) {
            return '/etc/nginx/sites-enabled';
        }
        return '/etc/nginx/conf.d';
    }

    protected function getSitesAvailablePath(): string
    {
        if (file_exists('/etc/os-release') && str_contains(file_get_contents('/etc/os-release'), 'Ubuntu')) {
            return '/etc/nginx/sites-available';
        }
        return '/etc/nginx/conf.d';
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
