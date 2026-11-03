<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
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

        Process::fake();
        $this->webserverPath = sys_get_temp_dir().'/lpanel_'.uniqid();
        File::makeDirectory("{$this->webserverPath}/nginx", 0755, true);
        config([
            'app.webserver_path' => $this->webserverPath,
            'app.websites_path' => "{$this->webserverPath}/www",
            // The Docker environment configures a certificate for every website
            'app.website_ssl_certificate' => null,
            'app.website_ssl_certificate_key' => null,
        ]);
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
        $this->assertStringContainsString("root {$this->webserverPath}/www/".self::DOMAIN.'/public;', $config);
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
    public function webserverIsTestedAndReloaded(): void
    {
        $this->makeDirectories('conf.d');

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);

        Process::assertRanTimes(fn ($process) => $process->command === ['sudo', '-n', '/usr/sbin/nginx', '-t'], 1);
        Process::assertRanTimes(fn ($process) => $process->command === ['sudo', '-n', '/usr/sbin/nginx', '-s', 'reload'], 1);
    }

    #[Test]
    public function newVhostIsRemovedWhenConfigTestFails(): void
    {
        $this->makeDirectories('sites-available', 'sites-enabled');
        Process::fake(fn ($process) => Process::result(errorOutput: 'bad config', exitCode: in_array('-t', $process->command) ? 1 : 0));

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])
            ->expectsOutput('Error reloading webserver: bad config')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist("{$this->webserverPath}/nginx/sites-available/".self::DOMAIN.'.conf');
        $this->assertFalse(is_link("{$this->webserverPath}/nginx/sites-enabled/".self::DOMAIN.'.conf'));
        $this->assertDatabaseCount('website', 0);
        Process::assertDidntRun(fn ($process) => in_array('reload', $process->command));
    }

    #[Test]
    public function existingVhostIsRestoredWhenConfigTestFails(): void
    {
        $this->makeDirectories('conf.d');
        $vhost = "{$this->webserverPath}/nginx/conf.d/".self::DOMAIN.'.conf';
        File::put($vhost, 'working config');
        Process::fake(fn ($process) => Process::result(errorOutput: 'bad config', exitCode: in_array('-t', $process->command) ? 1 : 0));

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(1);

        $this->assertSame('working config', File::get($vhost));
    }

    #[Test]
    public function vhostListensOnHttpsWhenCertificateIsConfigured(): void
    {
        $this->makeDirectories('conf.d');
        config([
            'app.website_ssl_certificate' => '/ssl/site.crt',
            'app.website_ssl_certificate_key' => '/ssl/site.key',
        ]);

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);

        $config = file_get_contents("{$this->webserverPath}/nginx/conf.d/".self::DOMAIN.'.conf');
        $this->assertStringContainsString('listen 443 ssl;', $config);
        $this->assertStringContainsString('ssl_certificate /ssl/site.crt;', $config);
        $this->assertStringContainsString('ssl_certificate_key /ssl/site.key;', $config);
    }

    #[Test]
    public function indexIsCreatedInNewWebsite(): void
    {
        $this->makeDirectories('conf.d');

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);

        $index = File::get("{$this->webserverPath}/www/".self::DOMAIN.'/public/index.php');
        $this->assertStringContainsString('<h1>Welcome '.self::DOMAIN.'</h1>', $index);
        $this->assertStringNotContainsString('|WEBSITE_URL|', $index);
    }

    #[Test]
    public function indexIsNotCreatedWhenWebsiteHasIndexHtml(): void
    {
        $this->makeDirectories('conf.d');
        $public = "{$this->webserverPath}/www/".self::DOMAIN.'/public';
        File::makeDirectory($public, 0755, true);
        File::put("$public/index.html", 'content');

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);

        $this->assertFileDoesNotExist("$public/index.php");
    }

    #[Test]
    public function publicDirectoryIsCreated(): void
    {
        $this->makeDirectories('conf.d');

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);

        $this->assertDirectoryExists("{$this->webserverPath}/www/".self::DOMAIN.'/public');
    }

    #[Test]
    public function existingWebsiteFilesAreKept(): void
    {
        $this->makeDirectories('conf.d');
        $index = "{$this->webserverPath}/www/".self::DOMAIN.'/public/index.php';
        File::makeDirectory(dirname($index), 0755, true);
        File::put($index, 'content');

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])->assertExitCode(0);

        $this->assertSame('content', File::get($index));
    }

    #[Test]
    public function websiteIsNotSavedWhenDirectoryCannotBeCreated(): void
    {
        $this->makeDirectories('conf.d');
        File::put("{$this->webserverPath}/www", 'not a directory');

        $this->artisan('panel:website:create', ['domain' => self::DOMAIN])
            ->expectsOutputToContain('Error creating website directory:')
            ->assertExitCode(1);

        $this->assertSame([], File::files("{$this->webserverPath}/nginx/conf.d"));
        $this->assertDatabaseCount('website', 0);
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
