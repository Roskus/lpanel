<?php declare(strict_types=1);

namespace App\Service;

class Nginx
{
    protected array $enabledSites = [];
    protected array $availableSites = [];

    public function __construct()
    {
        $this->loadEnabledSites();
    }

    protected function loadEnabledSites(): void
    {
        $path = $this->getSitesEnabledPath();
        if (is_dir($path)) {
            $this->enabledSites = array_diff(scandir($path), ['.', '..']);
        }
    }

    protected function getSitesEnabledPath(): string
    {
        if (file_exists('/etc/os-release') && str_contains(file_get_contents('/etc/os-release'), 'Ubuntu')) {
            return '/etc/nginx/sites-enabled';
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
