<?php declare(strict_types=1);

namespace App\Service\Webserver;

class Apache
{
    protected array $enabledSites = [];
    protected array $availableSites = [];

    public function __construct()
    {
        $this->loadEnabledSites();
        $this->loadAvailableSites();
    }

    protected function loadEnabledSites(): void
    {
        $path = $this->getSitesEnabledPath();
        if (is_dir($path)) {
            $this->enabledSites = array_diff(scandir($path), ['.', '..']);
        }
    }

    protected function loadAvailableSites(): void
    {
        $path = $this->getSitesAvailablePath();
        if (is_dir($path)) {
            $this->availableSites = array_diff(scandir($path), ['.', '..']);
        }
    }

    protected function getSitesEnabledPath(): string
    {
        if (file_exists('/etc/os-release') && str_contains(file_get_contents('/etc/os-release'), 'Ubuntu')) {
            return '/etc/apache2/sites-enabled';
        }
        return '/etc/httpd/conf.d';
    }

    protected function getSitesAvailablePath(): string
    {
        if (file_exists('/etc/os-release') && str_contains(file_get_contents('/etc/os-release'), 'Ubuntu')) {
            return '/etc/apache2/sites-available';
        }
        return '/etc/httpd/conf.d';
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
