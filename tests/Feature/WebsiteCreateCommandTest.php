<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebsiteCreateCommandTest extends TestCase
{
    use RefreshDatabase;

    private const DOMAIN = 'test-website.localhost';

    private string $webserverPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webserverPath = sys_get_temp_dir().'/lpanel_'.uniqid();
        File::makeDirectory("{$this->webserverPath}/nginx", 0755, true);
        config(['app.webserver_path' => $this->webserverPath]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->webserverPath);

        parent::tearDown();
    }

    private function makeDirectories(string ...$directories): void
    {
        foreach ($directories as $directory) {
            File::makeDirectory("{$this->webserverPath}/nginx/$directory");
        }
    }

    #[Test]
    public function vhostMustHaveConfigFileCreated(): void
    {
        $this->makeDirectories('conf.d');

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN, 'php' => '8.4'])
            ->expectsOutput('Virtual host created successfully')
            ->assertExitCode(0);

        $config = file_get_contents("{$this->webserverPath}/nginx/conf.d/".self::DOMAIN.'.conf');
        $this->assertStringContainsString('server_name '.self::DOMAIN.';', $config);
        $this->assertStringContainsString('root /var/www/'.self::DOMAIN.'/public;', $config);
        $this->assertStringContainsString('unix:/var/run/php/php8.4-fpm.sock', $config);
        $this->assertDoesNotMatchRegularExpression('/\|[A-Z_]+\|/', $config);
        // nginx refuses an ssl listen without ssl_certificate
        $this->assertDoesNotMatchRegularExpression('/^\s*listen 443 ssl/m', $config);

        $this->assertDatabaseHas('website', [
            'url' => self::DOMAIN,
            'type' => 'nginx',
            'status' => Website::ACTIVE,
        ]);
    }

    #[Test]
    public function vhostIsEnabledWhenServerHasSitesAvailable(): void
    {
        $this->makeDirectories('conf.d', 'sites-available', 'sites-enabled');

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);

        $available = "{$this->webserverPath}/nginx/sites-available/".self::DOMAIN.'.conf';
        $enabled = "{$this->webserverPath}/nginx/sites-enabled/".self::DOMAIN.'.conf';

        $this->assertFileExists($available);
        $this->assertTrue(is_link($enabled));
        $this->assertSame($available, readlink($enabled));
        $this->assertFileDoesNotExist("{$this->webserverPath}/nginx/conf.d/".self::DOMAIN.'.conf');
    }

    #[Test]
    public function phpVersionDefaultsToRunningMajorAndMinor(): void
    {
        $this->makeDirectories('conf.d');

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);

        $socket = 'php'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'-fpm.sock';
        $this->assertStringContainsString(
            $socket,
            file_get_contents("{$this->webserverPath}/nginx/conf.d/".self::DOMAIN.'.conf')
        );
    }

    #[Test]
    public function commandCanRunTwiceForTheSameDomain(): void
    {
        $this->makeDirectories('sites-available', 'sites-enabled');

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);
        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);

        $this->assertDatabaseCount('website', 1);
    }

    #[Test]
    public function softDeletedWebsiteIsRestored(): void
    {
        $this->makeDirectories('conf.d');
        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);
        Website::where('url', self::DOMAIN)->first()->delete();

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);

        $this->assertDatabaseCount('website', 1);
        $this->assertNotSoftDeleted('website', ['url' => self::DOMAIN]);
    }

    #[Test]
    #[DataProvider('invalidArguments')]
    public function invalidArgumentsAreRejected(array $arguments, string $message): void
    {
        $this->makeDirectories('conf.d');

        $this->artisan('panel:website:create', $arguments)
            ->expectsOutput($message)
            ->assertExitCode(1);

        $this->assertSame([], File::files("{$this->webserverPath}/nginx/conf.d"));
        $this->assertDatabaseCount('website', 0);
    }

    public static function invalidArguments(): array
    {
        return [
            'path traversal' => [['domain' => '../../evil'], 'Invalid domain: ../../evil'],
            'config injection' => [['domain' => "a.com;\n"], "Invalid domain: a.com;\n"],
            'php version' => [['domain' => self::DOMAIN, 'php' => '8.4/x'], 'Invalid PHP version: 8.4/x'],
            'unsupported server' => [['domain' => self::DOMAIN, 'server' => 'apache'], 'Unsupported webserver: apache'],
        ];
    }

    #[Test]
    public function websiteIsNotSavedWhenVhostCannotBeWritten(): void
    {
        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])
            ->expectsOutputToContain('Error creating vhost in:')
            ->assertExitCode(1);

        $this->assertDatabaseCount('website', 0);
    }
}
